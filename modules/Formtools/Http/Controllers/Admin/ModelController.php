<?php

namespace Modules\Formtools\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Formtools\Models\FormModel;
use Modules\Main\Models\Common;
use Modules\ModulesController;

class ModelController extends ModulesController {
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function loadModel() {
        set_time_limit(0);
        $all = $this->request->all();
        if (empty($all['model']) || empty($all['action']) || !method_exists($this, $all['action'])) {
            return back()->with('pageDataMsg', '参数有误');
        }
        $modeldetaill = FormModel::query()
            ->where("identification", $all['model'])
            ->first();
        if (!$modeldetaill) {
            oneFlash([400, '模型不存在']);
        }
        $all['modeldetaill'] = $modeldetaill;
        return call_user_func([$this, $all['action']], $all);

    }

    public function List($all) {
        $pageData = getURIByRoute($this->request);
        $pageData['moduleName'] = strtolower((string) ($pageData['moduleName'] ?? 'formtools'));
        $pageData['title'] = $all['modeldetaill']->name ;
//        $pageData[''] = "列表";
        $pageData['model'] = $all['model'];
        $pageData['access_identification'] = $all['modeldetaill']->access_identification;
        $pageData['currentModelId'] = $all['modeldetaill']->id ?? 0;
        $pageData['action'] = "model?action=List&model=" . $all['model'];
        $tableName = 'module_formtools_' . $all['model'];
        $tableColumns = $this->getDynamicTableColumns($tableName);
        $pageData['modeldetaill'] = $this->buildListFields(json_decode($all['modeldetaill']->fields, true) ?: [], $tableColumns);
        if (in_array('price', $tableColumns, true) && !in_array('price', array_column($pageData['modeldetaill'], 'identification'), true)) {
            array_unshift($pageData['modeldetaill'], [
                'identification' => 'price',
                'name' => '价格(元)',
                'remark' => '价格(元)',
                'formtype' => 'number',
                'datas' => [],
            ]);
        }
        $pageData['searchableFields'] = $this->buildSearchableFields(json_decode($all['modeldetaill']->fields, true) ?: [], $tableColumns);
        $pageData['showCreatedAt'] = in_array('created_at', $tableColumns, true);
        $pageData['tableName'] = $tableName;
        $adminConfig = json_decode((string) ($all['modeldetaill']->admin_config ?? ''), true) ?: [];
        $listMode = (string) ($adminConfig['admin_list_mode'] ?? 'table');
        $overrideView = (string) $this->request->query('_view', '');
        if (in_array($overrideView, ['table', 'tree', 'doc'], true)) {
            $listMode = $overrideView;
        }
        $pageData['listMode'] = $listMode;

        $leftJoin = [];
        $select = [];
        foreach ($pageData['modeldetaill'] as $k => $v) {
            if (!empty($v['foreign']) && in_array($v['identification'], $tableColumns, true)) {
                $temp = explode("-", $v['foreign']);
                $foreignTableName = 'module_formtools_' . ($temp[0] ?? '');
                if (
                    !empty($temp[0]) &&
                    $this->hasDynamicTableColumn($foreignTableName, 'id') &&
                    !empty($v['foreign_key']) &&
                    $this->hasDynamicTableColumn($foreignTableName, $v['foreign_key'])
                ) {
                    $leftJoin[] = $temp[0] . '-' . $v['identification'] . '-' . $v['foreign_key'];
                }
            }
        }
        $pageData['datas'] = DB::table($tableName);
        foreach ($leftJoin as $k => $v) {
            $temp = explode("-", $v);
            $pageData['datas']->leftJoin('module_formtools_' . $temp[0] . ' as ' . $temp[0], $tableName . '.' . $temp[1], '=', $temp[0] . '.id');
            $select[] = $temp[0] . '.' . $temp[2] . ' as ' . $temp[1];
        }
        //排序
        $identificationArray = array_column($pageData['modeldetaill']?:[], 'identification');
        if (in_array('sorts', $identificationArray)) {
            $pageData['datas'] = $pageData['datas']->orderBy($tableName . ".sorts", "desc");
        } elseif (in_array('sort', $identificationArray)) {
            $pageData['datas'] = $pageData['datas']->orderBy($tableName . ".sort", "desc");
        }
        $pageData['datas'] = $pageData['datas']->orderBy($tableName . ".id", "desc")
            ->select(array_merge([$tableName . '.*'], $select));

        $keyword = trim((string) $this->request->query('keyword', ''));
        $searchField = (string) $this->request->query('search_field', '');
        $statusFilter = (string) $this->request->query('status', '');
        $pageData['searchKeyword'] = $keyword;
        $pageData['searchField'] = $searchField;
        $pageData['statusFilter'] = $statusFilter;
        $pageData['hasStatusColumn'] = in_array('status', $tableColumns, true);
        $pageData['statusOptions'] = [
            '1' => '通过',
            '0' => '待审核',
            '2' => '下架',
        ];
        if ($keyword !== '') {
            $allowedSearchFields = array_column($pageData['searchableFields'], 'identification');
            if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
                $pageData['datas']->where($tableName . '.' . $searchField, 'like', '%' . $keyword . '%');
            } elseif ($allowedSearchFields) {
                $pageData['datas']->where(function ($query) use ($allowedSearchFields, $tableName, $keyword) {
                    foreach ($allowedSearchFields as $index => $field) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $query->{$method}($tableName . '.' . $field, 'like', '%' . $keyword . '%');
                    }
                });
            }
        }
        if ($pageData['hasStatusColumn'] && $statusFilter !== '' && array_key_exists($statusFilter, $pageData['statusOptions'])) {
            $pageData['datas']->where($tableName . '.status', $statusFilter);
        }

        $isTreeMode = $listMode === 'tree'
            && $this->hasDynamicTableColumn($tableName, 'pid');
        $isDocMode = $listMode === 'doc'
            && $this->hasDynamicTableColumn($tableName, 'pid');
        if (($isTreeMode || $isDocMode) && ($all['modeldetaill']->type == "multi" || !$all['modeldetaill']->type)) {
            $moduleName = strtolower((string) ($all['moduleName'] ?: $pageData['moduleName'] ?: 'formtools'));
            $rows = $pageData['datas']->get();
            $pageData['datas'] = $this->buildTreeRows($rows);
            $pageData['moduleName'] = $moduleName;

            if ($isDocMode) {
                $pageData['tableColumns'] = $tableColumns;
                $pageData['treeCount'] = count($rows);
                $pageData['labelColumn'] = $this->resolveTreeLabelColumn($tableName, $tableColumns);
                $pageData['subtitle'] = '文档工作台';
                $pageData['page'] = $this->request->query('page', '');

                $selectedId = (int) $this->request->query('id', 0);
                $selectedRow = null;
                if ($selectedId > 0) {
                    $selectedRow = DB::table($tableName)->where('id', $selectedId)->first();
                }
                if (!$selectedRow && !empty($pageData['datas'])) {
                    $firstNode = $this->findFirstTreeNode($pageData['datas']);
                    if ($firstNode) {
                        $selectedRow = DB::table($tableName)->where('id', (int) ($firstNode->id ?? 0))->first();
                        $selectedId = (int) ($firstNode->id ?? 0);
                    }
                }
                $pageData['selectedId'] = $selectedId;
                $pageData['selectedRow'] = $selectedRow;
                if ($selectedRow) {
                    $fieldsList = json_decode($all['modeldetaill']->fields, true) ?: [];
                    $pageData['selectedDetail'] = $this->buildContentDetailForDoc($fieldsList, $selectedRow);
                    $pageData['selectedBreadcrumb'] = $this->buildDocBreadcrumb($tableName, $selectedRow);
                    $pageData['selectedTitle'] = $this->resolveContentTitle($selectedRow);
                }

                return view("formtools::admin.model.listDoc", [
                    "pageData" => $pageData
                ]);
            }

            $fields = [];
            foreach ($pageData['modeldetaill'] as $field) {
                $identification = (string) ($field['identification'] ?? '');
                if ($identification === '') {
                    continue;
                }
                $fields[$identification] = array_merge([
                    'identification' => $identification,
                    'name' => $field['name'] ?? ($field['remark'] ?? $identification),
                    'formtype' => $field['formtype'] ?? '',
                    'datas' => $field['datas'] ?? [],
                    'cssClass' => $field['cssClass'] ?? '',
                    'callback' => $field['callback'] ?? null,
                    'jsfunction' => $field['jsfunction'] ?? '',
                ], $field);
            }
            $fields['rightaction'] = [
                'identification' => 'rightaction',
                'name' => '操作',
                'value' => '',
                'formtype' => '',
                'datas' => [
                    [
                        'actionName' => '编辑',
                        'actionUrl' => url("admin/" . $moduleName . "/model"),
                        'cssClass' => 'btn-info',
                        'popup' => false,
                        'confirm' => false,
                        'param' => [
                            'moduleName' => $moduleName,
                            'action' => 'Edit',
                            'model' => $all['model'],
                        ],
                    ],
                    [
                        'actionName' => '删除',
                        'actionUrl' => url("admin/" . $moduleName . "/model"),
                        'cssClass' => 'btn-danger',
                        'popup' => false,
                        'confirm' => true,
                        'param' => [
                            'moduleName' => $moduleName,
                            'action' => 'Del',
                            'model' => $all['model'],
                        ],
                    ],
                ],
                'actionby' => 'id',
                'notIdArray' => [],
            ];

            $pageData['fields'] = $fields;
            $pageData['popup'] = false;
            $pageData['searchFields'] = [];
            $pageData['searchClearEmpty'] = '';
            $pageData['listActions'] = [
                [
                    'actionName' => '添加内容',
                    'actionUrl' => url("admin/" . $pageData['moduleName'] . "/model"),
                    'cssClass' => 'label-info',
                    'popup' => false,
                    'confirm' => false,
                    'target' => '',
                    'param' => [
                        'moduleName' => $pageData['moduleName'],
                        'action' => 'Add',
                        'model' => $all['model'],
                    ],
                ],
            ];
            $pageData['subtitle'] = '数据列表';
            return view("formtools::admin.formtooltemp.listTree", [
                "pageData" => $pageData
            ]);
        }

        $pageData['totalCount'] = DB::table($tableName)->count();
        $pageData['filteredCount'] = (clone $pageData['datas'])->count();
        $pageData['searchEnabledCount'] = count($pageData['searchableFields']);
        $pageData['statusCountMap'] = $pageData['hasStatusColumn']
            ? [
                '1' => DB::table($tableName)->where('status', 1)->count(),
                '0' => DB::table($tableName)->where('status', 0)->count(),
                '2' => DB::table($tableName)->where('status', 2)->count(),
            ]
            : [];
        $pageData['metricAvailableMap'] = [
            'access_count' => in_array('access_count', $tableColumns, true),
            'good_count' => in_array('good_count', $tableColumns, true),
            'download_count' => in_array('download_count', $tableColumns, true),
            'uid' => in_array('uid', $tableColumns, true),
        ];
        $pageData['metricTotalMap'] = [
            'access_count' => $pageData['metricAvailableMap']['access_count'] ? (int) DB::table($tableName)->sum('access_count') : 0,
            'good_count' => $pageData['metricAvailableMap']['good_count'] ? (int) DB::table($tableName)->sum('good_count') : 0,
            'download_count' => $pageData['metricAvailableMap']['download_count'] ? (int) DB::table($tableName)->sum('download_count') : 0,
        ];
        $pageData['publisherCount'] = $pageData['metricAvailableMap']['uid']
            ? (int) DB::table($tableName)->where('uid', '>', 0)->distinct()->count('uid')
            : 0;

        if ($all['moduleName']) $pageData['moduleName'] = strtolower((string) $all['moduleName']);

        if($all['modeldetaill']->type=="multi" || !$all['modeldetaill']->type){
            $pageData['datas'] = $pageData['datas'] ->paginate(getLen());
            return view("formtools::admin.model.list", [
                "pageData" => $pageData
            ]);
        }
        $pageData['datas'] = $pageData['datas'] ->first();

        return view("formtools::admin.model.single", [
            "pageData" => $pageData
        ]);
    }

    public function Batch($all) {
        if (!$this->request->isMethod('post')) {
            return back()->with('pageDataMsg', '非法请求');
        }

        $pageData = getURIByRoute($this->request);
        $tableName = 'module_formtools_' . $all['model'];
        $ids = array_values(array_filter(array_map('intval', (array) $this->request->input('ids', []))));
        $batchAction = (string) $this->request->input('batch_action', '');
        $redirectUrl = $this->buildContentListUrl($pageData['moduleName'], $all['model'], [
            'page' => $this->request->input('page', ''),
            'keyword' => $this->request->input('keyword', ''),
            'search_field' => $this->request->input('search_field', ''),
            'status' => $this->request->input('status', ''),
        ]);

        if (!$ids) {
            return redirect($redirectUrl)->with('pageDataMsg', '你还没勾选要操作的内容');
        }

        $query = DB::table($tableName)->whereIn('id', $ids);
        if ($batchAction === 'delete') {
            $affected = $query->delete();
            return redirect($redirectUrl)
                ->with('pageDataMsg', $affected ? '批量删除成功' : '批量删除失败')
                ->with('pageDataStatus', $affected ? 200 : 400);
        }

        if (!$this->hasDynamicTableColumn($tableName, 'status')) {
            return redirect($redirectUrl)->with('pageDataMsg', '当前模型没有开启审核功能');
        }

        $statusMap = [
            'approve' => 1,
            'reject' => 0,
            'offline' => 2,
        ];
        if (!array_key_exists($batchAction, $statusMap)) {
            return redirect($redirectUrl)->with('pageDataMsg', '请选择一个批量操作');
        }

        $updateData = ['status' => $statusMap[$batchAction]];
        if ($this->hasDynamicTableColumn($tableName, 'updated_at')) {
            $updateData['updated_at'] = date("Y-m-d H:i:s", time());
        }
        $affected = $query->update($updateData);

        return redirect($redirectUrl)
            ->with('pageDataMsg', $affected ? '批量操作成功' : '批量操作失败')
            ->with('pageDataStatus', $affected ? 200 : 400);
    }

    public function QuickStatus($all) {
        $pageData = getURIByRoute($this->request);
        $tableName = 'module_formtools_' . $all['model'];
        $redirectUrl = $this->buildContentListUrl($pageData['moduleName'], $all['model'], [
            'page' => $all['page'] ?? '',
            'keyword' => $all['keyword'] ?? '',
            'search_field' => $all['search_field'] ?? '',
            'status' => $all['status'] ?? '',
        ]);

        $id = (int) ($all['id'] ?? 0);
        if ($id <= 0) {
            return redirect($redirectUrl)->with('pageDataMsg', '没找到要操作的内容');
        }

        if (!$this->hasDynamicTableColumn($tableName, 'status')) {
            return redirect($redirectUrl)->with('pageDataMsg', '当前模型没有开启审核功能');
        }

        $statusAction = (string) ($all['status_action'] ?? '');
        $statusMap = [
            'approve' => ['value' => 1, 'label' => '审核通过'],
            'reject' => ['value' => 0, 'label' => '设为待审核'],
            'offline' => ['value' => 2, 'label' => '下架'],
        ];
        if (!array_key_exists($statusAction, $statusMap)) {
            return redirect($redirectUrl)->with('pageDataMsg', '请选择一个审核操作');
        }

        $current = DB::table($tableName)->where('id', $id)->first();
        if (!$current) {
            return redirect($redirectUrl)->with('pageDataMsg', '数据不存在或已删除');
        }

        $targetStatus = $statusMap[$statusAction]['value'];
        if ((string) ($current->status ?? '') === (string) $targetStatus) {
            return redirect($redirectUrl)
                ->with('pageDataMsg', '当前内容已是"' . $statusMap[$statusAction]['label'] . '"状态')
                ->with('pageDataStatus', 200);
        }

        $updateData = ['status' => $targetStatus];
        if ($this->hasDynamicTableColumn($tableName, 'updated_at')) {
            $updateData['updated_at'] = date("Y-m-d H:i:s", time());
        }
        $affected = DB::table($tableName)->where('id', $id)->update($updateData);

        return redirect($redirectUrl)
            ->with('pageDataMsg', $affected ? ($statusMap[$statusAction]['label'] . '成功') : ($statusMap[$statusAction]['label'] . '失败'))
            ->with('pageDataStatus', $affected ? 200 : 400);
    }

    public function Add($all) {
        return $this->renderContentForm($all);
    }

    public function Edit($all) {
        $tableName = 'module_formtools_' . $all['model'];
        $data = DB::table($tableName)->where("id", $all['id'])->first();
        if (!$data) {
            return back()->with('pageDataMsg', '数据不存在');
        }
        return $this->renderContentForm($all, $data);
    }

    public function Submit($all) {

        if (!$this->request->isMethod("post")) {
            return back()->with("pageDataMsg", "非法请求");
        }
        $pageData = getURIByRoute($this->request);
        $redirectUrl = $this->buildContentListUrl($pageData['moduleName'], $all['model'], [
            'page' => $all['page'] ?? '',
            'keyword' => $all['keyword'] ?? '',
            'search_field' => $all['search_field'] ?? '',
            'status' => $all['status'] ?? '',
        ]);
        $tableName = 'module_formtools_' . $all['model'];
        $modeldetaill = json_decode($all['modeldetaill']->fields, true);
        $tableColumns = $this->getDynamicTableColumns($tableName);
        $uploadKey = [];
        $currentData = null;
        if (!empty($all['id'])) {
            $currentData = DB::table($tableName)->where("id", $all['id'])->first();
        }
        foreach ($modeldetaill as $f) {
            if (!in_array($f['identification'], $tableColumns, true)) {
                continue;
            }
            //判断字段规则
            $fieldValue = $all[$f['identification']] ?? '';
            $insterdata[$f['identification']] = is_array($fieldValue) ? implode(',', $fieldValue) : $fieldValue;
            if (in_array($f['formtype'], ['upload', 'image', 'uploadAjax', 'imageAjax'], true)) {
                $uploadKey[] = $f['identification'];
            }
        }
        if ($uploadKey) {
            foreach ($uploadKey as $key) {
                unset($insterdata[$key]);
                if (!empty($_FILES[$key]['size']) && $_FILES[$key]['size'] > 0) {
                    try {
                        $insterdata[$key] = UploadFile(\Request(), $key, "file/" . date("Y/m/d/") . uniqid(), ALLOWEXT, __E("upload_driver"));
                    } catch (\Exception $exception) {
                        return back()->with("pageDataMsg", $exception->getMessage());
                    }
                } elseif ($currentData && isset($currentData->{$key})) {
                    $insterdata[$key] = $currentData->{$key};
                }
            }
        }

        if ($this->hasDynamicTableColumn($tableName, 'pid')) {
            $pid = (int) ($all['pid'] ?? 0);
            if (!empty($all['id']) && $pid > 0 && $pid === (int) $all['id']) {
                return back()->with("pageDataMsg", "父级不能选择自己");
            }
            $this->assignColumnValue($insterdata, $tableName, 'pid', $pid);
        }


        $this->assignColumnValue($insterdata, $tableName, 'remark', $all['remark'] ?? '');
        $this->assignColumnValue($insterdata, $tableName, 'price', floatval($all['price'] ?? 0));
        $this->assignColumnValue($insterdata, $tableName, 'seo_title', $all['seo_title'] ?? '');
        $this->assignColumnValue($insterdata, $tableName, 'seo_keywords', $all['seo_keywords'] ?? '');
        $this->assignColumnValue($insterdata, $tableName, 'seo_description', $all['seo_description'] ?? '');
        if ($all['id'] > 0) {
            $this->assignColumnValue($insterdata, $tableName, 'status', $all['status'] ?? 1);
            $res = DB::table($tableName)->where("id", $all['id'])->update($insterdata);
            if ($res) {
                return redirect($redirectUrl)
                    ->with("pageDataMsg", "修改成功")
                    ->with('pageDataStatus', 200);
            }
            return back()->with("pageDataMsg", "修改失败");
        }
        $this->assignColumnValue($insterdata, $tableName, 'created_at', date("Y-m-d H:i:s", time()));
        $this->assignColumnValue($insterdata, $tableName, 'uid', session()->get('admin_info')->uid ?? 0);
        $this->assignColumnValue($insterdata, $tableName, 'status', 1);
        unset($insterdata['id']);

        $driver = Schema::getConnection()->getDriverName();
        $prefix = DB::connection()->getTablePrefix();
        $physicalTableName = $prefix !== '' ? ($prefix . $tableName) : $tableName;
        $idColumn = $driver === 'sqlite'
            ? DB::select("PRAGMA table_info('{$physicalTableName}')")
            : DB::select("SHOW COLUMNS FROM `{$physicalTableName}` LIKE 'id'");
        $needManualId = false;
        if ($driver === 'sqlite') {
            foreach ($idColumn as $column) {
                if ((string) ($column->name ?? '') !== 'id') {
                    continue;
                }
                $columnType = strtolower((string) ($column->type ?? ''));
                $isPrimary = (int) ($column->pk ?? 0) === 1;
                $needManualId = $isPrimary && !str_contains($columnType, 'int');
                if ($needManualId && (!array_key_exists('id', $insterdata) || trim((string) $insterdata['id']) === '')) {
                    $insterdata['id'] = 'ftc_' . strtolower(substr(bin2hex(random_bytes(12)), 0, 24));
                }
                break;
            }
        } elseif ($idColumn) {
            $column = $idColumn[0];
            $columnType = strtolower((string) ($column->Type ?? ''));
            $columnKey = strtoupper((string) ($column->Key ?? ''));
            $columnExtra = strtolower((string) ($column->Extra ?? ''));
            if ($columnKey === 'PRI' && !str_contains($columnExtra, 'auto_increment')) {
                $needManualId = true;
                if (!array_key_exists('id', $insterdata) || trim((string) $insterdata['id']) === '') {
                    if (str_contains($columnType, 'char') || str_contains($columnType, 'text') || str_contains($columnType, 'binary')) {
                        $insterdata['id'] = 'ftc_' . strtolower(substr(bin2hex(random_bytes(12)), 0, 24));
                    }
                }
            }
        }

        if ($needManualId) {
            if (!array_key_exists('id', $insterdata) || trim((string) $insterdata['id']) === '') {
                return back()->with("pageDataMsg", "添加失败：主键生成异常，请检查数据表配置");
            }
            $res = DB::table($tableName)->insert($insterdata) ? $insterdata['id'] : 0;
        } else {
            $res = DB::table($tableName)->insertGetId($insterdata);
        }

        if ($res) {
            return redirect($redirectUrl)
                ->with("pageDataMsg", "添加成功")
                ->with('pageDataStatus', 200);
        }
        return back()->with("pageDataMsg", "添加失败");
    }

    public function DocDetail($all) {
        $tableName = 'module_formtools_' . $all['model'];
        $id = (int) $this->request->query('id', 0);
        if ($id <= 0) {
            return response()->json(['code' => 400, 'msg' => '缺少文档 ID']);
        }
        $row = DB::table($tableName)->where('id', $id)->first();
        if (!$row) {
            return response()->json(['code' => 404, 'msg' => '文档不存在']);
        }
        $fields = json_decode($all['modeldetaill']->fields, true) ?: [];
        $moduleName = strtolower((string) ($all['moduleName'] ?? $this->request->query('moduleName', 'formtools')));
        $accessIdentification = $all['modeldetaill']->access_identification ?? '';
        return response()->json([
            'code' => 200,
            'data' => [
                'id' => $id,
                'title' => $this->resolveContentTitle($row),
                'price' => (float) ($row->price ?? 0),
                'status' => (string) ($row->status ?? '1'),
                'created_at' => (string) ($row->created_at ?? ''),
                'updated_at' => (string) ($row->updated_at ?? ''),
                'pid' => (int) ($row->pid ?? 0),
                'breadcrumb' => $this->buildDocBreadcrumb($tableName, $row),
                'fields' => $this->buildContentDetailForDoc($fields, $row),
                'edit_url' => url("admin/" . $moduleName . "/model?" . http_build_query([
                    'action' => 'Edit',
                    'moduleName' => $moduleName,
                    'model' => $all['model'],
                    'id' => $id,
                ])),
                'detail_url' => $accessIdentification
                    ? model_detail_url($accessIdentification, $id)
                    : '',
            ],
        ]);
    }

    public function Del($all) {
        $pageData = getURIByRoute($this->request);
        $tableName = 'module_formtools_' . $all['model'];
        $redirectUrl = $this->buildContentListUrl($pageData['moduleName'], $all['model'], [
            'page' => $all['page'] ?? '',
            'keyword' => $all['keyword'] ?? '',
            'search_field' => $all['search_field'] ?? '',
            'status' => $all['status'] ?? '',
        ]);
        $current = DB::table($tableName)->where("id", $all['id'])->first();
        if (!$current) {
            return redirect($redirectUrl)
                ->with("pageDataMsg", "数据不存在或已删除");
        }
        try {
            $res = DB::table($tableName)->where("id", $all['id'])->delete();
        } catch (\Exception $exception) {
            return back()->with("pageDataMsg", "删除失败");
        }
        if ($res) {
            return redirect($redirectUrl)
                ->with("pageDataMsg", "删除成功")
                ->with('pageDataStatus', 200);
        }
        return back()->with("pageDataMsg", "删除失败");
    }

    public function uploadImg($all) {
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        $request = \Request();
        if ($_FILES['file']['size'] <= 0) return json_encode(["msg" => '请选择有效文件']);
        //if ($_FILES['file']['size'] > 1 * 1024 * 1024) return json_encode(["msg" => '文件不能大于1M']);
        try {
            $file = UploadFile($request, 'file', "file/" . date("Y/m/d/") . uniqid(), ALLOWEXT, __E("upload_driver"));
            return json_encode(["location" => GetUrlByPath($file)]);
        } catch (\Exception $exception) {
            return json_encode(["msg" => $exception->getMessage()]);
        }

    }

    private function renderContentForm(array $all, $data = null) {
        $pageData = getURIByRoute($this->request);
        $isEdit = $data !== null;
        $pageData['title'] = $all['modeldetaill']->name;
        $pageData['subtitle'] = $isEdit ? "编辑数据" : "添加数据";
        $pageData['model'] = $all['model'];
        $pageData['access_identification'] = $all['modeldetaill']->access_identification;
        $pageData['action'] = "model?action=List&model=" . $all['model'];
        $pageData['page'] = $all['page'] ?? '';
        if (!empty($all['moduleName'])) {
            $pageData['moduleName'] = $all['moduleName'];
        }

        $tableName = 'module_formtools_' . $all['model'];
        $tableColumns = $this->getDynamicTableColumns($tableName);
        $fields = $this->buildContentFields(json_decode($all['modeldetaill']->fields, true) ?: [], $tableName, $tableColumns, $data);
        $formtool = FormTool::create();
        $query = [
            'action' => 'Submit',
            'moduleName' => $pageData['moduleName'],
            'model' => $all['model'],
        ];
        if (!empty($pageData['page'])) {
            $query['page'] = $pageData['page'];
        }
        if (!empty($all['keyword'])) {
            $query['keyword'] = $all['keyword'];
        }
        if (!empty($all['search_field'])) {
            $query['search_field'] = $all['search_field'];
        }
        if (isset($all['status']) && $all['status'] !== '') {
            $query['status'] = $all['status'];
        }
        $formtool->formAction(url("admin/" . strtolower($pageData['moduleName']) . "/model?" . http_build_query($query)));
        $formtool->csrf_field();
        $formtool->formid('formtoolsContentForm');
        $formtool->tips('在这里填写内容后，就可以继续查看前台展示效果；如果设置了单条内容的 SEO，会优先用于详情页。');

        if ($isEdit) {
            $formtool->field('id', '', $all['id'], 'hidden');
        }

        $formtool->section('content_guide', '内容工作台', '在这里填写或修改内容。');
        $formtool->field('content_frontend_guide', '前台联调', $this->buildContentPreviewGuide($pageData, $data))
            ->formtype('content_frontend_guide', 'word')
            ->notes('content_frontend_guide', '保存后可回到列表继续查看，也可以直接打开前台页面确认效果。');

        $adminConfig = json_decode((string) ($all['modeldetaill']->admin_config ?? ''), true) ?: [];
        $listMode = (string) ($adminConfig['admin_list_mode'] ?? 'table');
        $hasPidForeign = false;
        foreach ($fields as $field) {
            if (($field['identification'] ?? '') === 'pid' && !empty($field['foreign'])) {
                $hasPidForeign = true;
                break;
            }
        }

        if (($listMode === 'tree' || $listMode === 'doc') && !$hasPidForeign && $this->hasDynamicTableColumn($tableName, 'pid')) {
            $pidValue = (string) (($data->pid ?? '') !== '' ? ($data->pid ?? '0') : '0');
            $labelColumn = $this->resolveTreeLabelColumn($tableName, $tableColumns);
            $selfId = (int) ($data->id ?? 0);
            $treeOptions = $this->buildForeignTreeOptions($tableName, $labelColumn, $selfId, $tableName);
            $treeOptions = array_merge([
                [
                    'value' => 0,
                    'name' => '作为顶级',
                    'children' => [],
                ],
            ], $treeOptions);

            $formtool->section('content_tree', '层级', '如果内容要分上下级（比如栏目或章节），在这里设置它的归属关系。');
            $formtool->field('pid', '父级', $pidValue, 'select', $treeOptions)
                ->notes('pid', '选好上级后，这条内容会挂在它下面；最顶层请选“作为顶级”。不能选自己或自己的下级。');
        }

        $formtool->section('content_fields', '内容字段');
        foreach ($fields as $field) {
            $formtool->field($field['identification'], $field['name'], $field['value'], $field['formtype'], $field['datas']);
            $formtool->required($field['identification'], $field['required'] ?? '');
            $formtool->placeholder($field['identification'], $field['placeholder'] ?? '');
            $formtool->notes($field['identification'], $field['notes'] ?? '');
            $formtool->disabled($field['identification'], $field['disabled'] ?? '');
            $formtool->showtype($field['identification'], $field['showtype'] ?? 'row');
            $formtool->rows($field['identification'], $field['rows'] ?? 5);
            $formtool->template($field['identification'], $field['template'] ?? '');
            $formtool->cssClass($field['identification'], $field['cssClass'] ?? '');
        }

        $metaFields = $this->buildContentMetaFields($tableName, $data);
        $pageData['contentWorkbench'] = $this->buildContentWorkbench($pageData, $fields, $metaFields, $data);
        if ($metaFields) {
            $formtool->section('content_review', '审核与 SEO', '这里可以设置审核状态、备注和详情页 SEO。');
            foreach ($metaFields as $metaField) {
                $formtool->field($metaField['identification'], $metaField['name'], $metaField['value'], $metaField['formtype'], $metaField['datas'] ?? []);
                $formtool->placeholder($metaField['identification'], $metaField['placeholder'] ?? '');
                $formtool->notes($metaField['identification'], $metaField['notes'] ?? '');
                $formtool->rows($metaField['identification'], $metaField['rows'] ?? 5);
            }
        }

        return $formtool->formView($pageData);
    }

    private function buildContentFields(array $fields, string $tableName, array $tableColumns, $data = null): array {
        $rowData = $data ? get_object_vars($data) : [];
        $metaColumns = ['id', 'status', 'remark', 'price', 'seo_title', 'seo_keywords', 'seo_description'];
        $fields = array_values(array_filter($fields, function ($field) use ($tableColumns) {
            return in_array($field['identification'] ?? '', $tableColumns, true);
        }));
        $fields = array_values(array_filter($fields, function ($field) use ($metaColumns) {
            return !in_array($field['identification'] ?? '', $metaColumns, true);
        }));
        $singleContentField = count($fields) === 1
            && (($fields[0]['identification'] ?? '') === 'content')
            && empty($fields[0]['formtype']);
        foreach ($fields as $key => $field) {
            if (!empty($field['datas']) && !is_array($field['datas'])) {
                $field['datas'] = json_decode($field['datas'], true) ?: [];
            }
            if ($singleContentField && ($field['identification'] ?? '') === 'content' && empty($field['formtype'])) {
                $field['formtype'] = 'editor';
            }
            $field['formtype'] = !empty($field['formtype']) ? (string) $field['formtype'] : 'text';
            if (!empty($field['foreign'])) {
                $temp = explode("-", $field['foreign']);
                $foreignTableName = 'module_formtools_' . ($temp[0] ?? '');
                if (
                    !empty($temp[0]) &&
                    !empty($field['foreign_key']) &&
                    $this->hasDynamicTableColumn($foreignTableName, 'id') &&
                    $this->hasDynamicTableColumn($foreignTableName, $field['foreign_key'])
                ) {
                    $field['datas'] = $this->buildForeignTreeOptions($foreignTableName, (string) $field['foreign_key']);
                }
            }
            $field['value'] = $rowData[$field['identification']] ?? ($field['value'] ?? '');
            $fields[$key] = $field;
        }

        return $fields;
    }

    private function buildListFields(array $fields, array $tableColumns): array
    {
        $listFields = [];
        foreach ($fields as $field) {
            if (($field['is_show_list'] ?? '1') != 1) {
                continue;
            }
            if (!in_array($field['identification'] ?? '', $tableColumns, true)) {
                continue;
            }
            if (!empty($field['datas']) && !is_array($field['datas'])) {
                $datas = json_decode($field['datas'], true) ?: [];
                $field['datas'] = array_column($datas, 'name', 'value');
            }
            $listFields[] = $field;
        }

        return $listFields;
    }

    private function buildSearchableFields(array $fields, array $tableColumns): array
    {
        $searchableFields = [];
        foreach ($fields as $field) {
            if (($field['is_show_admin_list_search'] ?? '2') != 1) {
                continue;
            }
            if (!in_array($field['identification'] ?? '', $tableColumns, true)) {
                continue;
            }
            $searchableFields[] = [
                'identification' => $field['identification'],
                'name' => $field['name'] ?: ($field['remark'] ?: $field['identification']),
            ];
        }

        return $searchableFields;
    }

    private function buildContentMetaFields(string $tableName, $data = null): array
    {
        $metaFields = [];
        if ($this->hasDynamicTableColumn($tableName, 'price')) {
            $metaFields[] = [
                'identification' => 'price',
                'name' => '价格(元)',
                'value' => (string) floatval($data->price ?? 0),
                'formtype' => 'number',
                'placeholder' => '默认 0 表示免费',
                'notes' => '设置 >0 表示当前内容需要付费查看。',
            ];
        }
        if ($this->hasDynamicTableColumn($tableName, 'status')) {
            $metaFields[] = [
                'identification' => 'status',
                'name' => '审核状态',
                'value' => (string) ($data->status ?? '1'),
                'formtype' => 'radio',
                'datas' => [
                    ['name' => '通过', 'value' => '1'],
                    ['name' => '不通过', 'value' => '0'],
                    ['name' => '下架', 'value' => '2'],
                ],
                'notes' => '控制当前内容在后台和前台中的可用状态。',
            ];
        }
        if ($this->hasDynamicTableColumn($tableName, 'remark')) {
            $metaFields[] = [
                'identification' => 'remark',
                'name' => '审核备注',
                'value' => (string) ($data->remark ?? ''),
                'formtype' => 'text',
                'placeholder' => '请输入审核备注',
            ];
        }
        if ($this->hasDynamicTableColumn($tableName, 'seo_title')) {
            $metaFields[] = [
                'identification' => 'seo_title',
                'name' => 'SEO 标题',
                'value' => (string) ($data->seo_title ?? ''),
                'formtype' => 'text',
                'placeholder' => '请输入 SEO 标题',
                'notes' => '不填写时将优先回退到 name 或 title 字段。',
            ];
        }
        if ($this->hasDynamicTableColumn($tableName, 'seo_keywords')) {
            $metaFields[] = [
                'identification' => 'seo_keywords',
                'name' => 'SEO 关键词',
                'value' => (string) ($data->seo_keywords ?? ''),
                'formtype' => 'text',
                'placeholder' => '请输入 SEO 关键词',
                'notes' => '不填写时将自动回退到模型级或通配 SEO。',
            ];
        }
        if ($this->hasDynamicTableColumn($tableName, 'seo_description')) {
            $metaFields[] = [
                'identification' => 'seo_description',
                'name' => 'SEO 描述',
                'value' => (string) ($data->seo_description ?? ''),
                'formtype' => 'textarea',
                'rows' => 4,
                'placeholder' => '请输入 SEO 描述',
                'notes' => '不填写时将自动回退到模型级或通配 SEO。',
            ];
        }

        return $metaFields;
    }

    private function assignColumnValue(array &$data, string $tableName, string $column, $value): void
    {
        if ($this->hasDynamicTableColumn($tableName, $column)) {
            $data[$column] = $value;
        }
    }

    private function getDynamicTableColumns(string $tableName): array
    {
        if (!Schema::hasTable($tableName)) {
            return [];
        }

        return Schema::getColumnListing($tableName);
    }

    private function hasDynamicTableColumn(string $tableName, string $column): bool
    {
        return Schema::hasTable($tableName) && Schema::hasColumn($tableName, $column);
    }

    private function findFirstTreeNode(array $tree): ?object
    {
        if (empty($tree)) {
            return null;
        }
        $first = $tree[0];
        if (empty($first->children)) {
            return $first;
        }
        $subFirst = $this->findFirstTreeNode($first->children);
        return $subFirst ?? $first;
    }

    private function buildContentDetailForDoc(array $fields, $row): array
    {
        $rowArray = $row ? get_object_vars($row) : [];
        $skipColumns = [
            'id', 'pid', 'uid', 'status', 'remark', 'price',
            'seo_title', 'seo_keywords', 'seo_description',
            'access_count', 'good_count', 'comment_count', 'download_count',
            'created_at', 'updated_at', 'sort', 'sorts',
        ];
        $result = [];
        foreach ($fields as $field) {
            $ident = (string) ($field['identification'] ?? '');
            if ($ident === '' || in_array($ident, $skipColumns, true)) {
                continue;
            }
            $value = $rowArray[$ident] ?? '';
            $formtype = (string) ($field['formtype'] ?? '');
            $datas = $field['datas'] ?? [];
            $displayValue = $value;
            $displayType = 'text';
            if (is_array($datas) && $value !== '' && array_key_exists((string) $value, $datas)) {
                $displayValue = $datas[(string) $value];
            } elseif (is_array($datas) && $value !== '' && array_key_exists($value, $datas)) {
                $displayValue = $datas[$value];
            }
            if (in_array($formtype, ['upload', 'uploadAjax', 'image', 'imageAjax'], true) && $value !== '') {
                $ext = strtolower(pathinfo((string) $value, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $displayType = 'image';
                } else {
                    $displayType = 'file';
                }
            }
            $result[] = [
                'identification' => $ident,
                'name' => (string) ($field['name'] ?? $ident),
                'formtype' => $formtype,
                'value' => $displayValue,
                'raw' => $value,
                'display_type' => $displayType,
            ];
        }
        return $result;
    }

    private function buildDocBreadcrumb(string $tableName, $row): array
    {
        $breadcrumb = [];
        $pid = (int) ($row->pid ?? 0);
        $maxDepth = 10;
        while ($pid > 0 && $maxDepth-- > 0) {
            $parent = DB::table($tableName)->where('id', $pid)->first();
            if (!$parent) {
                break;
            }
            array_unshift($breadcrumb, [
                'id' => (int) ($parent->id ?? 0),
                'title' => $this->resolveContentTitle($parent),
            ]);
            $pid = (int) ($parent->pid ?? 0);
        }
        return $breadcrumb;
    }

    private function buildTreeRows($rows): array
    {
        $nodes = [];
        foreach ($rows as $row) {
            $data = toArray($row);
            $id = (int) ($data['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $obj = (object) $data;
            $obj->children = [];
            $obj->pid_path = '';
            $nodes[$id] = $obj;
        }

        $roots = [];
        foreach ($nodes as $id => $node) {
            $pid = (int) ($node->pid ?? 0);
            if ($pid > 0 && $pid !== $id && isset($nodes[$pid])) {
                $nodes[$pid]->children[] = $node;
            } else {
                $roots[] = $node;
            }
        }

        $sortKey = function ($node) {
            $sort = $node->sorts ?? ($node->sort ?? null);
            if ($sort === null) {
                return 0;
            }
            return (int) $sort;
        };
        $sortChildren = function ($items) use (&$sortChildren, $sortKey) {
            usort($items, function ($a, $b) use ($sortKey) {
                $sa = $sortKey($a);
                $sb = $sortKey($b);
                if ($sa === $sb) {
                    return ((int) ($b->id ?? 0)) <=> ((int) ($a->id ?? 0));
                }
                return $sb <=> $sa;
            });
            foreach ($items as $item) {
                if (!empty($item->children) && is_array($item->children)) {
                    $item->children = $sortChildren($item->children);
                }
            }
            return $items;
        };
        $roots = $sortChildren($roots);

        $walk = function ($node, array $ancestors) use (&$walk) {
            $node->pid_path = implode(',', $ancestors);
            $next = array_merge($ancestors, [(int) ($node->id ?? 0)]);
            foreach (($node->children ?? []) as $child) {
                $walk($child, $next);
            }
        };
        foreach ($roots as $root) {
            $walk($root, []);
        }

        return $roots;
    }

    private function buildForeignTreeOptions(string $foreignTableName, string $labelColumn, int $excludeId = 0, ?string $excludeSubtreeSourceTable = null): array
    {
        $hasPid = $this->hasDynamicTableColumn($foreignTableName, 'pid');
        $hasSorts = $this->hasDynamicTableColumn($foreignTableName, 'sorts');
        $hasSort = $this->hasDynamicTableColumn($foreignTableName, 'sort');

        $query = Common::query()->from($foreignTableName)->select(['id as value', $labelColumn . ' as name']);
        if ($hasPid) {
            $query->addSelect('pid');
        }
        if ($hasSorts) {
            $query->addSelect('sorts');
        } elseif ($hasSort) {
            $query->addSelect('sort');
        }

        if ($hasSorts) {
            $query->orderByDesc('sorts');
        } elseif ($hasSort) {
            $query->orderByDesc('sort');
        }
        $query->orderByDesc('id');

        $rows = $query->get()->toArray();
        $items = json_decode(json_encode($rows), true) ?: [];

        $excludeIds = [];
        if ($excludeId > 0) {
            $excludeIds[(int) $excludeId] = true;
            if ($hasPid && $foreignTableName === ($excludeSubtreeSourceTable ?: $foreignTableName)) {
                $childrenMap = [];
                foreach ($items as $it) {
                    $pid = (int) ($it['pid'] ?? 0);
                    if ($pid > 0) {
                        $childrenMap[$pid][] = (int) ($it['value'] ?? 0);
                    }
                }
                $queue = [(int) $excludeId];
                $guard = 0;
                while ($queue && $guard++ < 10000) {
                    $current = array_shift($queue);
                    foreach ($childrenMap[$current] ?? [] as $cid) {
                        $cid = (int) $cid;
                        if (!isset($excludeIds[$cid])) {
                            $excludeIds[$cid] = true;
                            $queue[] = $cid;
                        }
                    }
                }
            }
        }

        if (!empty($excludeIds)) {
            $items = array_values(array_filter($items, function ($it) use ($excludeIds) {
                return !isset($excludeIds[(int) ($it['value'] ?? 0)]);
            }));
        }
        if (!$hasPid) {
            return $items;
        }

        $nodes = [];
        foreach ($items as $item) {
            $id = (int) ($item['value'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $item['children'] = [];
            $nodes[$id] = $item;
        }

        $sortFn = function (array $a, array $b): int {
            $sa = (int) ($a['sorts'] ?? ($a['sort'] ?? 0));
            $sb = (int) ($b['sorts'] ?? ($b['sort'] ?? 0));
            if ($sa === $sb) {
                return (int) ($b['value'] ?? 0) <=> (int) ($a['value'] ?? 0);
            }
            return $sb <=> $sa;
        };

        $childrenMap = [];
        $rootIds = [];
        foreach ($nodes as $id => $node) {
            $pid = (int) ($node['pid'] ?? 0);
            if ($pid > 0 && $pid !== $id && isset($nodes[$pid])) {
                $childrenMap[$pid][] = $id;
                continue;
            }
            $rootIds[] = $id;
        }

        $sortIds = function (array $ids) use ($sortFn, $nodes): array {
            usort($ids, function ($aId, $bId) use ($sortFn, $nodes) {
                return $sortFn($nodes[(int) $aId] ?? [], $nodes[(int) $bId] ?? []);
            });
            return $ids;
        };

        $buildTree = function (int $id) use (&$buildTree, $nodes, $childrenMap, $sortIds): array {
            $node = $nodes[$id] ?? ['value' => $id, 'name' => (string) $id, 'children' => []];
            $childIds = $childrenMap[$id] ?? [];
            if ($childIds) {
                $childIds = $sortIds($childIds);
                $node['children'] = array_map(fn ($childId) => $buildTree((int) $childId), $childIds);
            }
            return $node;
        };

        $rootIds = $sortIds($rootIds);
        return array_map(fn ($rootId) => $buildTree((int) $rootId), $rootIds);
    }

    private function resolveTreeLabelColumn(string $tableName, array $tableColumns): string
    {
        $candidates = ['title', 'name', 'cate_name', 'company_name', 'full_name'];
        foreach ($candidates as $col) {
            if (in_array($col, $tableColumns, true)) {
                return $col;
            }
        }
        if (in_array('id', $tableColumns, true)) {
            return 'id';
        }
        $columns = $this->getDynamicTableColumns($tableName);
        return in_array('id', $columns, true) ? 'id' : ($columns[0] ?? 'id');
    }

    private function buildContentPreviewGuide(array $pageData, $data = null): string {
        if (empty($pageData['access_identification'])) {
            return '<div>当前模型还没配置前台访问标识，先把这一项填上，才能预览前台效果。</div>';
        }

        $listUrl = model_list_url($pageData['access_identification']);
        $detailText = $data && !empty($data->id)
            ? '<code>' . model_detail_url($pageData['access_identification'], $data->id) . '</code>'
            : '<code>' . model_detail_url($pageData['access_identification'], '{id}') . '</code>';

        return <<<HTML
<div style="line-height: 1.9;">
    <div><strong>前台列表：</strong><code>{$listUrl}</code></div>
    <div><strong>前台详情：</strong>{$detailText}</div>
</div>
HTML;
    }

    private function buildContentWorkbench(array $pageData, array $fields, array $metaFields, $data = null): array
    {
        $isEdit = $data !== null;
        $recordTitle = $this->resolveContentTitle($data);
        $statusMap = [
            '1' => '通过',
            '0' => '不通过',
            '2' => '下架',
        ];
        $statusValue = (string) ($data->status ?? '1');
        $listUrl = url("admin/" . $pageData['moduleName'] . "/model?moduleName=" . $pageData['moduleName'] . "&action=List&model=" . $pageData['model']);
        if (!empty($pageData['page'])) {
            $listUrl .= '&page=' . $pageData['page'];
        }

        $actions = [
            [
                'label' => '返回列表',
                'url' => $listUrl,
                'class' => 'btn btn-default btn-sm',
                'target' => '',
                'confirm' => '',
            ],
        ];
        if (!empty($pageData['access_identification'])) {
            $actions[] = [
                'label' => '前台列表',
                'url' => model_list_url($pageData['access_identification']),
                'class' => 'btn btn-info btn-sm',
                'target' => '_blank',
                'confirm' => '',
            ];
            if ($isEdit && !empty($data->id)) {
                $actions[] = [
                    'label' => '前台详情',
                    'url' => model_detail_url($pageData['access_identification'], $data->id),
                    'class' => 'btn btn-primary btn-sm',
                    'target' => '_blank',
                    'confirm' => '',
                ];
            }
        }
        if ($isEdit && !empty($data->id)) {
            $actions[] = [
                'label' => '删除当前内容',
                'url' => url("admin/" . $pageData['moduleName'] . "/model?action=Del&moduleName=" . $pageData['moduleName'] . "&model=" . $pageData['model'] . "&id=" . $data->id . (!empty($pageData['page']) ? "&page=" . $pageData['page'] : '')),
                'class' => 'btn btn-danger btn-sm',
                'target' => '',
                'confirm' => '确定要删除内容"' . $recordTitle . '"吗？这会直接删除当前记录。',
            ];
        }

        return [
            'isEdit' => $isEdit,
            'title' => $recordTitle,
            'tableName' => 'module_formtools_' . $pageData['model'],
            'fieldCount' => count($fields),
            'metaFieldCount' => count($metaFields),
            'seoFieldCount' => count(array_filter($metaFields, static fn($field) => str_starts_with((string) ($field['identification'] ?? ''), 'seo_'))),
            'requiredCount' => count(array_filter($fields, static fn($field) => ($field['required'] ?? '') === 'required')),
            'statusLabel' => $statusMap[$statusValue] ?? '未设置',
            'actions' => $actions,
        ];
    }

    private function resolveContentTitle($data): string
    {
        if (!$data) {
            return '新内容';
        }
        $row = get_object_vars($data);
        foreach (['title', 'name', 'cate_name', 'company_name', 'full_name'] as $field) {
            if (!empty($row[$field])) {
                return (string) $row[$field];
            }
        }

        return 'ID #' . ($row['id'] ?? '');
    }

    private function buildContentListUrl(string $moduleName, string $model, array $params = []): string
    {
        $query = array_filter(array_merge([
            'moduleName' => $moduleName,
            'action' => 'List',
            'model' => $model,
        ], $params), static fn ($value) => $value !== null && $value !== '');

        return url('admin/' . strtolower($moduleName) . '/model?' . http_build_query($query));
    }


}
