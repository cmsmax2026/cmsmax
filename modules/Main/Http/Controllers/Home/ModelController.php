<?php

namespace Modules\Main\Http\Controllers\Home;

use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Formtools\Models\FormModel;
use Modules\Formtools\Support\FormModelTemplateManager;
use Modules\Formtools\Support\FormTemplateResolver;
use Modules\Formtools\Services\FormtoolsPaymentService;
use Modules\Main\Models\Common;
use Modules\ModulesController;
use Modules\System\Http\Controllers\Common\SessionKey;
use Modules\System\Http\Requests\verifyFunction;

class ModelController extends ModulesController {
    use verifyFunction;
    private $login_unique;


    public function __construct(Request $request) {
        parent::__construct($request);
        $this->login_unique = SessionKey::HomeInfo;
    }

    public function list($access) {
        $uri = getURIByRoute($this->request)['uri'];
        $useHtmlSuffix = model_has_html_suffix($access);

        if (!Str::startsWith($this->request->path(), 'api/')) {
            $currentPath = trim($this->request->path(), '/');
            $plainPath = 'list/' . trim($access, '/');
            $canonicalPath = $useHtmlSuffix ? ($plainPath . '.html') : $plainPath;
            if ($useHtmlSuffix && in_array($currentPath, [$plainPath, 'page/' . trim($access, '/')], true)) {
                return redirect()->to(model_list_url($access, $this->request->query()), 301);
            }
            if (!$useHtmlSuffix && in_array($currentPath, [$plainPath . '.html', 'page/' . trim($access, '/')], true)) {
                return redirect()->to(model_list_url($access, $this->request->query()), 301);
            }
            if ($currentPath !== $canonicalPath && Str::startsWith($currentPath, $plainPath)) {
                return redirect()->to(model_list_url($access, $this->request->query()), 301);
            }
        }

        $modelData = FormModel::query()->where('access_identification', $access)->first();
        if (!$modelData) return abort(404);

        $model = $modelData->toArray();
        $model['fields'] = $model['fields'] ? json_decode($model['fields'], true) : [];
        $model['other_config'] = json_decode($model['other_config'], true);
        $model['home_config'] = json_decode($model['home_config'], true);
        $model['home_seo_config'] = json_decode($model['home_seo_config'], true);
        $model['home_seo_detail_config'] = json_decode($model['home_seo_detail_config'], true);
        $model = FormTemplateResolver::normalizeModelData($model);
        $model = $this->normalizeFrontendFieldSchema($model);

        if ($this->isTreeListScene($model)) {
            $model['home_config']['page_num'] = 0;
            $model['page_num'] = 0;
        }

        $sortKey = $this->normalizeListSortKey((string) $this->request->query('sort', 'newest'));
        $pidScope = $this->normalizePidScope((string) $this->request->query('pid_scope', 'descendants'));
        $pidRaw = $this->request->query('pid', null);

        $searchKeyword = trim((string) $this->request->query('keyword', ''));
        $searchFields = $this->resolveFrontendSearchFields($model, $model['other_config']['data_source'] === 'api' ? null : "module_formtools_{$model['identification']}");
        $pidOptions = [];
        $hasPidColumn = false;

        if ($model['other_config']['data_source'] == "api") {
            $apiMappings = $this->parseApiFieldMappings($model['other_config']['data_source_field_mapping'] ?? '');
            $apiResponse = $this->requestApiPayload($model['other_config']['data_source_api_url'] ?? '');
            $list = $this->mapApiList($this->extractApiListItems($apiResponse), $apiMappings);
            $list = $this->filterApiListByActiveStatus($list);
            $hasPidColumn = $this->apiListHasPid($list);
            $pidOptions = $hasPidColumn ? $this->buildPidFilterOptionsFromApiList($list) : [];
            $list = $this->applyApiPidFilter($list, $pidRaw, $pidScope, $hasPidColumn);
            $list = $this->filterApiListByKeyword($list, $searchKeyword, $searchFields);
            $list = $this->applyApiSort($list, $sortKey);
            if (($model['type'] ?? 'multi') !== 'multi') {
                $list = $list[0] ?? [];
            }
        } else {
            $tableName = "module_formtools_{$model['identification']}";
            $foreignTreeFilter = $this->resolveForeignTreeFilterField($model, $tableName);
            $hasPidColumn = $this->hasDynamicTableColumn($tableName, 'pid');
            if ($foreignTreeFilter) {
                $hasPidColumn = true;
                $pidOptions = $this->buildPidFilterOptionsFromTable($foreignTreeFilter['foreignTable']);
            } else {
                $pidOptions = $hasPidColumn ? $this->buildPidFilterOptionsFromTable($tableName) : [];
            }
            $list = $this->applyActiveStatusScope(Common::query()->from($tableName), $tableName);
            if ($foreignTreeFilter) {
                $list = $this->applyLocalForeignTreeFilter($list, $tableName, $foreignTreeFilter['field'], $foreignTreeFilter['foreignTable'], $pidRaw, $pidScope);
            } else {
                $list = $this->applyLocalPidFilter($list, $tableName, $pidRaw, $pidScope, $hasPidColumn);
            }
            $this->applyFrontendKeywordSearch($list, $tableName, $searchKeyword, $searchFields);
            $this->applyLocalSort($list, $tableName, $sortKey);
            if($model['type']=="multi"){
                if ($model['home_config']['page_num'] > 0) {
                    $list = $list->paginate($model['home_config']['page_num']);
                } else {
                    $list = $list->get()->toArray();
                }
            }else{
                $list = $list->first();
            }

        }

        $param['model'] = $access;
        if (substr($uri, 0, 4) == 'api/') {
            return json_encode([
                'status' => 200,
                'msg' => 'success',
                'data' => [
                    'list' => $list,
                    'model' => $model,
                    'param' => $param,
                ],
            ], JSON_UNESCAPED_UNICODE);
        }
        $listTemplate = $this->resolveSingleModelTemplate(
            $model,
            FormModelTemplateManager::resolveSceneTemplate($model, \Modules\Formtools\Support\FormTemplateCatalog::SCENE_LIST)
                ?: ($model['list_template'] ?: FormTemplateResolver::DEFAULT_LIST_TEMPLATE)
        );
        $paidMap = $this->buildPaidMapForList($model, $list);
        $visualListHtml = FormModelTemplateManager::renderSceneLayout($model, \Modules\Formtools\Support\FormTemplateCatalog::SCENE_LIST, [
            'model_list_items' => $this->buildVisualListItems($model, $list, $access),
            'pagination_html' => $this->buildListVisualPaginationHtml($list, $model),
            'paid_map' => $paidMap,
        ]);
        if ($visualListHtml !== '') {
            return ModelView('formtools::home.sceneVisual', [
                'data' => $list,
                'model' => $model,
                'param' => $param,
                'data_source' => $model['other_config']['data_source'],
                'listContext' => $this->buildListContext($model, $list, $searchKeyword, $searchFields, [
                    'pid' => $pidRaw === null ? '' : (string) $pidRaw,
                    'pid_scope' => $pidScope,
                    'pid_options' => $pidOptions,
                    'has_pid' => $hasPidColumn,
                    'sort' => $sortKey,
                ]),
                'visualSceneHtml' => $visualListHtml,
                'paidMap' => $paidMap,
            ], FormTemplateResolver::DEFAULT_LIST_TEMPLATE);
        }
        return ModelView($listTemplate, [
            'data' => $list,
            'model' => $model,
            'param' => $param,
            'data_source' => $model['other_config']['data_source'],
            'listContext' => $this->buildListContext($model, $list, $searchKeyword, $searchFields, [
                'pid' => $pidRaw === null ? '' : (string) $pidRaw,
                'pid_scope' => $pidScope,
                'pid_options' => $pidOptions,
                'has_pid' => $hasPidColumn,
                'sort' => $sortKey,
            ]),
            'paidMap' => $paidMap,
        ], FormTemplateResolver::DEFAULT_LIST_TEMPLATE);
    }

    private function buildPaidMapForList(array $model, $list): array
    {
        $tableName = "module_formtools_{$model['identification']}";
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'price')) {
            return [];
        }
        $contentIds = [];
        foreach ($list as $item) {
            $id = is_object($item) ? (int) ($item->id ?? 0) : (int) ($item['id'] ?? 0);
            if ($id > 0) {
                $contentIds[] = $id;
            }
        }
        if (empty($contentIds)) {
            return [];
        }
        $paymentService = app(FormtoolsPaymentService::class);
        $user = $paymentService->normalizeUser(null);
        if ($user['id'] <= 0) {
            return [];
        }
        return $paymentService->markPaidList($model['identification'], $contentIds, $user);
    }

    private function isTreeListScene(array $model): bool
    {
        $sceneMap = FormModelTemplateManager::resolveSceneMapForFrontend($model, \Modules\Formtools\Support\FormTemplateCatalog::SCENE_LIST);
        if (($sceneMap['mode'] ?? 'preset') !== 'preset') {
            return false;
        }

        $templateKey = FormTemplateResolver::sanitizeTemplateName((string) ($sceneMap['template_key'] ?? ''));
        if ($templateKey === '') {
            $templateKey = FormTemplateResolver::sanitizeTemplateName((string) ($model['list_template'] ?? ''));
        }

        return $templateKey === 'treeList';
    }

    private function normalizeListSortKey(string $sort): string
    {
        $sort = strtolower(trim($sort));
        if (!in_array($sort, ['newest', 'oldest', 'likes', 'views'], true)) {
            return 'newest';
        }
        return $sort;
    }

    private function normalizePidScope(string $scope): string
    {
        $scope = strtolower(trim($scope));
        if (!in_array($scope, ['descendants', 'direct'], true)) {
            return 'descendants';
        }
        return $scope;
    }

    private function applyLocalSort($query, string $tableName, string $sortKey): void
    {
        if ($sortKey === 'oldest') {
            $query->orderBy($tableName . '.id', 'asc');
            return;
        }

        if ($sortKey === 'likes' && $this->hasDynamicTableColumn($tableName, 'good_count')) {
            $query->orderByDesc($tableName . '.good_count')->orderByDesc($tableName . '.id');
            return;
        }

        if ($sortKey === 'views' && $this->hasDynamicTableColumn($tableName, 'access_count')) {
            $query->orderByDesc($tableName . '.access_count')->orderByDesc($tableName . '.id');
            return;
        }

        $query->orderByDesc($tableName . '.id');
    }

    private function applyApiSort(array $list, string $sortKey): array
    {
        if (!$list) {
            return $list;
        }

        usort($list, function ($a, $b) use ($sortKey) {
            $a = is_array($a) ? $a : frontendRecordData($a);
            $b = is_array($b) ? $b : frontendRecordData($b);
            $idA = (int) ($a['id'] ?? 0);
            $idB = (int) ($b['id'] ?? 0);

            if ($sortKey === 'oldest') {
                return $idA <=> $idB;
            }

            if ($sortKey === 'likes') {
                $ga = (int) ($a['good_count'] ?? 0);
                $gb = (int) ($b['good_count'] ?? 0);
                if ($ga !== $gb) return $gb <=> $ga;
                return $idB <=> $idA;
            }

            if ($sortKey === 'views') {
                $va = (int) ($a['access_count'] ?? 0);
                $vb = (int) ($b['access_count'] ?? 0);
                if ($va !== $vb) return $vb <=> $va;
                return $idB <=> $idA;
            }

            return $idB <=> $idA;
        });

        return $list;
    }

    private function applyLocalPidFilter($query, string $tableName, $pidRaw, string $pidScope, bool $hasPidColumn)
    {
        if (!$hasPidColumn || $pidRaw === null || $pidRaw === '') {
            return $query;
        }

        $pid = (int) $pidRaw;
        if ($pid <= 0) {
            return $query->where($tableName . '.pid', 0);
        }

        if ($pidScope === 'direct') {
            return $query->where($tableName . '.pid', $pid);
        }

        $descendantIds = $this->resolveDescendantIdsFromTable($tableName, $pid);
        if (!$descendantIds) {
            return $query->whereRaw('1=0');
        }

        return $query->whereIn($tableName . '.id', $descendantIds);
    }

    private function applyLocalForeignTreeFilter($query, string $tableName, string $foreignField, string $foreignTableName, $pidRaw, string $pidScope)
    {
        if ($pidRaw === null || $pidRaw === '') {
            return $query;
        }

        $pid = (int) $pidRaw;
        if ($pid <= 0) {
            return $query;
        }

        if ($pidScope === 'direct') {
            return $query->where($tableName . '.' . $foreignField, $pid);
        }

        $descendantIds = $this->resolveDescendantIdsFromTable($foreignTableName, $pid);
        $categoryIds = array_values(array_unique(array_merge([$pid], $descendantIds)));
        if (!$categoryIds) {
            return $query->whereRaw('1=0');
        }

        return $query->whereIn($tableName . '.' . $foreignField, $categoryIds);
    }

    private function applyApiPidFilter(array $list, $pidRaw, string $pidScope, bool $hasPidColumn): array
    {
        if (!$hasPidColumn || $pidRaw === null || $pidRaw === '') {
            return $list;
        }

        $pid = (int) $pidRaw;
        if ($pid <= 0) {
            return array_values(array_filter($list, fn ($item) => (int) (($item['pid'] ?? 0)) === 0));
        }

        if ($pidScope === 'direct') {
            return array_values(array_filter($list, fn ($item) => (int) (($item['pid'] ?? 0)) === $pid));
        }

        $descendantIds = $this->collectDescendantIdsFromRows($list, $pid);
        if (!$descendantIds) {
            return [];
        }

        $idSet = array_flip($descendantIds);
        return array_values(array_filter($list, function ($item) use ($idSet) {
            $id = (int) ($item['id'] ?? 0);
            return $id > 0 && isset($idSet[$id]);
        }));
    }

    private function apiListHasPid(array $list): bool
    {
        foreach ($list as $item) {
            if (is_array($item) && array_key_exists('pid', $item)) {
                return true;
            }
        }
        return false;
    }

    private function resolveForeignTreeFilterField(array $model, string $tableName): ?array
    {
        $fields = $model['fields'] ?? [];
        if (!is_array($fields) || !$fields) {
            return null;
        }

        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }
            $identification = trim((string) ($field['identification'] ?? ''));
            if ($identification === '') {
                continue;
            }
            $foreign = trim((string) ($field['foreign'] ?? ''));
            if ($foreign === '') {
                continue;
            }
            if (!$this->hasDynamicTableColumn($tableName, $identification)) {
                continue;
            }

            $temp = explode('-', $foreign);
            $foreignModel = trim((string) ($temp[0] ?? ''));
            if ($foreignModel === '') {
                continue;
            }
            $foreignTableName = 'module_formtools_' . $foreignModel;
            if (!$this->hasDynamicTableColumn($foreignTableName, 'pid')) {
                continue;
            }

            return [
                'field' => $identification,
                'foreignTable' => $foreignTableName,
            ];
        }

        return null;
    }

    private function resolveDescendantIdsFromTable(string $tableName, int $rootId): array
    {
        if ($rootId <= 0) {
            return [];
        }

        $edgeQuery = $this->applyActiveStatusScope(Common::query()->from($tableName)->select(['id', 'pid']), $tableName);
        $rows = $edgeQuery->get()->toArray();
        return $this->collectDescendantIdsFromRows($rows, $rootId);
    }

    private function collectDescendantIdsFromRows(array $rows, int $rootId): array
    {
        if ($rootId <= 0) {
            return [];
        }

        $childrenByPid = [];
        foreach ($rows as $row) {
            $row = is_array($row) ? $row : frontendRecordData($row);
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) continue;
            $pid = (int) ($row['pid'] ?? 0);
            $childrenByPid[$pid][] = $id;
        }

        $queue = [$rootId];
        $seen = [];
        $result = [];

        while ($queue) {
            $current = array_shift($queue);
            if (isset($seen[$current])) {
                continue;
            }
            $seen[$current] = 1;

            foreach ($childrenByPid[$current] ?? [] as $childId) {
                if (!isset($seen[$childId])) {
                    $result[] = $childId;
                    $queue[] = $childId;
                }
            }
        }

        return array_values(array_unique(array_filter($result)));
    }

    private function buildPidFilterOptionsFromTable(string $tableName): array
    {
        $labelColumn = $this->resolvePidLabelColumn($tableName);
        $selectColumns = ['id', 'pid'];
        if ($labelColumn !== 'id') {
            $selectColumns[] = $labelColumn;
        }
        $query = $this->applyActiveStatusScope(Common::query()->from($tableName)->select($selectColumns), $tableName)
            ->orderByDesc('id');

        $rows = $query->limit(2000)->get()->toArray();
        return $this->buildPidFilterOptionsFromRows($rows, $labelColumn);
    }

    private function buildPidFilterOptionsFromApiList(array $list): array
    {
        return $this->buildPidFilterOptionsFromRows($list, null);
    }

    private function buildPidFilterOptionsFromRows(array $rows, ?string $labelColumn): array
    {
        $nodes = [];
        foreach ($rows as $row) {
            $row = is_array($row) ? $row : frontendRecordData($row);
            if (!$row) continue;
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) continue;
            $pid = (int) ($row['pid'] ?? 0);
            $label = '';
            if ($labelColumn !== null) {
                $label = trim((string) ($row[$labelColumn] ?? ''));
            } else {
                $label = trim((string) ($row['title'] ?? $row['name'] ?? ''));
            }
            $label = $label !== '' ? $label : ('#' . $id);
            $nodes[$id] = [
                'id' => $id,
                'pid' => $pid,
                'label' => $label,
                'children' => [],
            ];
        }

        $roots = [];
        foreach ($nodes as $id => $node) {
            $pid = (int) $node['pid'];
            if ($pid > 0 && $pid !== $id && isset($nodes[$pid])) {
                $nodes[$pid]['children'][] = &$nodes[$id];
            } else {
                $roots[] = &$nodes[$id];
            }
        }

        $flat = [];
        $walk = function ($node, int $depth) use (&$walk, &$flat) {
            $flat[] = [
                'value' => $node['id'],
                'label' => ($depth > 0 ? str_repeat('— ', $depth) : '') . $node['label'],
                'depth' => $depth,
            ];
            foreach ($node['children'] as $child) {
                $walk($child, $depth + 1);
            }
        };
        foreach ($roots as $root) {
            $walk($root, 0);
        }

        return array_values($flat);
    }

    private function resolvePidLabelColumn(string $tableName): string
    {
        $columns = $this->getDynamicTableColumns($tableName);
        foreach (['title', 'name', 'cate_name'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }
        return 'id';
    }

    public function detail($access, $id) {
        $uri = getURIByRoute($this->request)['uri'];
        $useHtmlSuffix = model_has_html_suffix($access);

        if (!Str::startsWith($this->request->path(), 'api/')) {
            $currentPath = trim($this->request->path(), '/');
            $plainPath = 'detail/' . trim($access, '/') . '/' . trim((string) $id, '/');
            $canonicalPath = $useHtmlSuffix ? ($plainPath . '.html') : $plainPath;
            if ($useHtmlSuffix && $currentPath === $plainPath) {
                return redirect()->to(model_detail_url($access, $id, $this->request->query()), 301);
            }
            if (!$useHtmlSuffix && $currentPath === ($plainPath . '.html')) {
                return redirect()->to(model_detail_url($access, $id, $this->request->query()), 301);
            }
            if ($currentPath !== $canonicalPath && Str::startsWith($currentPath, 'detail/' . trim($access, '/') . '/')) {
                return redirect()->to(model_detail_url($access, $id, $this->request->query()), 301);
            }
        }

        $modelData = FormModel::query()->where('access_identification', $access)->first();
        if (!$modelData) return abort(404);
        $pid = 'pid';//默认上级id
        //通过自定义的字段查询某个外键
        foreach (json_decode($modelData['fields'], true) as $fields) {
            if ($fields['foreign_key']) {
                $pid = $fields['identification'];
                break;
            }
        }
        $model = $modelData->toArray();
        $model['fields'] = $model['fields'] ? json_decode($model['fields'], true) : [];
        $model['other_config'] = json_decode($model['other_config'], true);
        $model['home_config'] = json_decode($model['home_config'], true);
        $model['home_seo_config'] = json_decode($model['home_seo_config'], true);
        $model['home_seo_detail_config'] = json_decode($model['home_seo_detail_config'], true);
        $model = FormTemplateResolver::normalizeModelData($model);
        $model = $this->normalizeFrontendFieldSchema($model);

        if ($model['other_config']['data_source'] == "api") {
            $apiMappings = $this->parseApiFieldMappings($model['other_config']['data_source_field_mapping'] ?? '');
            $detailUrl = $this->buildApiDetailUrl($model['other_config']['data_source_api_url_detail'] ?? '', (string) $id);
            $apiResponse = $this->requestApiPayload($detailUrl);
            $detailItem = $this->extractApiDetailItem($apiResponse);
            if (!$detailItem) {
                $listPayload = $this->requestApiPayload($model['other_config']['data_source_api_url'] ?? '');
                $detailItem = $this->findApiDetailFromListPayload($listPayload, $apiMappings, (string) $id);
                if (!$apiResponse) {
                    $apiResponse = $listPayload;
                }
            }
            $data = $this->mapApiItem($detailItem, $apiMappings);
            if (!$data || !$this->isFrontendAccessibleApiItem($data)) {
                abort(404);
            }
            $list = $this->filterApiListByActiveStatus($this->mapApiList($this->extractApiRelatedItems($apiResponse), $apiMappings));
            $data['prev_id'] = null;
            $data['next_id'] = null;
            $data['last_id'] = null;
            $data['prev_item'] = null;
            $data['next_item'] = null;

        } else {
            $tableName = "module_formtools_{$model['identification']}";
            $data = $this->applyActiveStatusScope(Common::query()->from($tableName), $tableName)
                ->where('id', $id)
                ->first();
            if (!$data) return abort(404);
            $relationKey = $this->hasDynamicTableColumn($tableName, $pid) ? $pid : null;
            //访问数加1
            if ($this->hasDynamicTableColumn($tableName, 'access_count')) {
                Common::query()->from($tableName)->where('id', $id)->increment("access_count");
                $data->access_count = (int) ($data->access_count ?? 0) + 1;
            }
            $listQuery = $this->applyActiveStatusScope(Common::query()->from($tableName), $tableName)
                ->whereNot('id', $id)
                ->latest('id');
            if ($relationKey !== null) {
                $listQuery->where($relationKey, $data[$relationKey]);
            }
            $list = $listQuery->limit(10)->get()->toArray();
            $selectColumns = array_values(array_filter([
                'id',
                $this->hasDynamicTableColumn($tableName, 'title') ? 'title' : null,
                $this->hasDynamicTableColumn($tableName, 'name') ? 'name' : null,
                $this->hasDynamicTableColumn($tableName, 'created_at') ? 'created_at' : null,
            ]));

            $scopeValue = $relationKey !== null ? ($data[$relationKey] ?? null) : null;
            $prevItem = $this->resolveAdjacentDetailItem($tableName, $id, 'previous', $selectColumns, $relationKey, $scopeValue);
            $nextItem = $this->resolveAdjacentDetailItem($tableName, $id, 'next', $selectColumns, $relationKey, $scopeValue);

            $data['prev_item'] = $prevItem ? frontendRecordData($prevItem) : null;
            $data['next_item'] = $nextItem ? frontendRecordData($nextItem) : null;
            $data['prev_id'] = $prevItem->id ?? null;
            $data['next_id'] = $nextItem->id ?? null;
            $data['last_id'] = $nextItem->id ?? null;
        }


        $param['model'] = $access;
        $param['id'] = $id;


        if (substr($uri, 0, 4) == 'api/') {
            return json_encode([
                'status' => 200,
                'msg' => 'success',
                'data' => [
                    'data' => $data,
                    'list' => $list,
                    'model' => $model,
                    'param' => $param,
                ],
            ], JSON_UNESCAPED_UNICODE);
        }

        $frontDocMode = (string) data_get($model, 'home_config.front_doc_mode', 'off');
        $foreignFilter = $this->resolveForeignTreeFilterField($model, $tableName);
        $hasCateTable = $foreignFilter && Schema::hasTable($foreignFilter['foreignTable']);
        $useCateTree = $hasCateTable && Schema::hasColumn($foreignFilter['foreignTable'], 'pid');
        $hasSelfPid = $this->hasDynamicTableColumn($tableName ?? '', 'pid');
        $isFrontDocMode = $frontDocMode === 'on'
            && ($model['type'] ?? 'multi') !== 'single'
            && (is_object($data) || is_array($data))
            && ($useCateTree || $hasSelfPid);
        if ($isFrontDocMode) {
            $paymentService = app(FormtoolsPaymentService::class);
            $modelIdent = (string) ($model['identification'] ?? '');
            $currentContentRecord = is_object($data) ? (array) $data : (is_array($data) ? $data : []);
            $currentForeignValue = 0;
            $treeSource = [
                'mode' => $useCateTree ? 'foreign' : 'self',
                'foreignField' => '',
                'foreignTable' => '',
                'table' => $tableName,
                'model' => $modelIdent,
                'accessIdent' => (string) ($model['access_identification'] ?? $access ?? $modelIdent),
            ];
            if ($foreignFilter && $useCateTree) {
                $treeSource['foreignField'] = $foreignFilter['field'];
                $treeSource['foreignTable'] = $foreignFilter['foreignTable'];
                $currentForeignValue = (int) ($currentContentRecord[$foreignFilter['field']] ?? 0);
            }
            $treeData = $this->buildFrontDocTree($treeSource, (string) data_get($model, 'home_config.front_doc_price_mode', 'inherit'));
            $selectedId = (int) ($currentContentRecord['id'] ?? 0);
            $priceStatus = $paymentService->getPriceStatus($modelIdent, $data);
            $detailRecord = frontendRecordData($data);
            $detailRecord['price_status'] = $priceStatus;
            $docPayload = [
                'data' => $data,
                'detailRecord' => $detailRecord,
                'detailInteractions' => $this->buildDetailInteractions($model, $access, $detailRecord),
                'model' => $model,
                'param' => $param,
                'priceStatus' => $priceStatus,
                'treeData' => $treeData,
                'treeSource' => $treeSource,
                'selectedId' => $selectedId,
                'activeCategoryId' => $currentForeignValue,
                'frontDocTitle' => (string) data_get($model, 'home_config.front_doc_title', '') ?: ($model['name'] ?? ''),
                'frontDocDesc' => (string) data_get($model, 'home_config.front_doc_desc', '') ?: '',
            ];
            return ModelView('formtools::home.doc', $docPayload, FormTemplateResolver::DEFAULT_DETAIL_TEMPLATE);
        }

        $detailTemplate = $this->resolveSingleModelTemplate(
            $model,
            FormModelTemplateManager::resolveSceneTemplate($model, \Modules\Formtools\Support\FormTemplateCatalog::SCENE_DETAIL)
                ?: ($model['detail_template'] ?: FormTemplateResolver::DEFAULT_DETAIL_TEMPLATE)
        );
        $detailRecord = frontendRecordData($data);

        $paymentService = app(FormtoolsPaymentService::class);
        $priceStatus = $paymentService->getPriceStatus($model['identification'], $detailRecord);
        $detailRecord['price_status'] = $priceStatus;
        if (isset($detailRecord['fields']) && is_array($detailRecord['fields'])) {
            $detailRecord['fields'] = $paymentService->maskContent($model['identification'], $detailRecord['fields'], $detailRecord);
        }
        $visualDetailHtml = FormModelTemplateManager::renderSceneLayout($model, \Modules\Formtools\Support\FormTemplateCatalog::SCENE_DETAIL, [
            'model_detail_item' => $this->buildVisualDetailItem($model, $detailRecord, $access),
            'priceStatus' => $priceStatus,
        ]);
        if ($visualDetailHtml !== '') {
            return ModelView('formtools::home.sceneVisual', [
                'data' => $data,
                'detailRecord' => $detailRecord,
                'detailInteractions' => $this->buildDetailInteractions($model, $access, $detailRecord),
                'list' => $list,
                'model' => $model,
                'param' => $param,
                'priceStatus' => $priceStatus,
                'visualSceneHtml' => $visualDetailHtml,
            ], FormTemplateResolver::DEFAULT_DETAIL_TEMPLATE);
        }
        return ModelView($detailTemplate, [
            'data' => $data,
            'detailRecord' => $detailRecord,
            'detailInteractions' => $this->buildDetailInteractions($model, $access, $detailRecord),
            'list' => $list,
            'model' => $model,
            'param' => $param,
            'priceStatus' => $priceStatus,
        ], FormTemplateResolver::DEFAULT_DETAIL_TEMPLATE);
    }

    public function good($access, $id)
    {
        $modelData = FormModel::query()->where('access_identification', $access)->first();
        if (!$modelData) {
            return $this->detailActionResponse(false, '模型不存在', [], 404);
        }

        $model = $modelData->toArray();
        $model['other_config'] = json_decode($model['other_config'], true);
        $model = FormTemplateResolver::normalizeModelData($model);
        if (($model['other_config']['data_source'] ?? 'local') !== 'local') {
            return $this->detailActionResponse(false, '当前模型暂不支持点赞统计', [], 400);
        }

        $tableName = 'module_formtools_' . $model['identification'];
        if (!$this->hasDynamicTableColumn($tableName, 'good_count')) {
            return $this->detailActionResponse(false, '当前模型未启用点赞统计字段', [], 400);
        }

        $record = $this->applyActiveStatusScope(Common::query()->from($tableName), $tableName)
            ->where('id', (int) $id)
            ->first();
        if (!$record) {
            return $this->detailActionResponse(false, '内容不存在或未通过审核', [], 404);
        }

        Common::query()->from($tableName)->where('id', (int) $id)->increment('good_count');
        $goodCount = (int) ($record->good_count ?? 0) + 1;

        return $this->detailActionResponse(true, '点赞成功', [
            'good_count' => $goodCount,
        ]);
    }

    public function download($access, $id, $field, $index = 0)
    {
        $modelData = FormModel::query()->where('access_identification', $access)->first();
        if (!$modelData) {
            abort(404);
        }

        $model = $modelData->toArray();
        $model['fields'] = $model['fields'] ? json_decode($model['fields'], true) : [];
        $model['other_config'] = json_decode($model['other_config'], true);
        $model = FormTemplateResolver::normalizeModelData($model);
        $model = $this->normalizeFrontendFieldSchema($model);
        if (($model['other_config']['data_source'] ?? 'local') !== 'local') {
            abort(404);
        }

        $tableName = 'module_formtools_' . $model['identification'];
        $record = $this->applyActiveStatusScope(Common::query()->from($tableName), $tableName)
            ->where('id', (int) $id)
            ->first();
        if (!$record) {
            abort(404);
        }

        $detailRecord = frontendRecordData($record);
        $downloads = $this->resolveDownloadableFields($model, $detailRecord, $access);
        $fieldKey = (string) $field;
        $fieldDownloads = $downloads[$fieldKey]['items'] ?? [];
        $fileIndex = max(0, (int) $index);
        $downloadItem = $fieldDownloads[$fileIndex] ?? null;
        if (!$downloadItem || empty($downloadItem['path'])) {
            abort(404);
        }

        if ($this->hasDynamicTableColumn($tableName, 'download_count')) {
            Common::query()->from($tableName)->where('id', (int) $id)->increment('download_count');
        }

        return redirect($downloadItem['url']);
    }

    public function handle($access) {
        $modelData = FormModel::query()->where('access_identification', $access)->first();
        if (!$modelData) {
            return $this->handleFrontendSubmitResponse(false, '模型不存在', [], 404);
        }

        $model = $modelData->toArray();
        $model['fields'] = $model['fields'] ? json_decode($model['fields'], true) : [];
        $model['other_config'] = json_decode($model['other_config'], true);
        $model['home_config'] = json_decode($model['home_config'], true);
        $model['home_seo_config'] = json_decode($model['home_seo_config'], true);
        $model['home_seo_detail_config'] = json_decode($model['home_seo_detail_config'], true);
        $model = FormTemplateResolver::normalizeModelData($model);
        $model = $this->normalizeFrontendFieldSchema($model);

        if ($this->request->isMethod('post')) {
            return $this->submitFrontendHandle($access, $model);
        }

        return ModelView($this->resolveHandleTemplate($model), [
            'data' => $this->loadHandlePageData($model),
            'model' => $model,
            'param' => ['model' => $access],
        ], FormTemplateResolver::DEFAULT_HANDLE_TEMPLATE);
    }

    private function normalizeFrontendFieldSchema(array $model): array {
        $fields = $model['fields'] ?? [];
        foreach ($fields as $index => $field) {
            $fields[$index]['is_show_home_form'] = (string) ($field['is_show_home_form'] ?? '1');
            $fields[$index]['is_show_home_list'] = (string) ($field['is_show_home_list'] ?? '1');
            $fields[$index]['is_show_home_detail'] = (string) ($field['is_show_home_detail'] ?? '1');
            $fields[$index]['is_show_home_list_search'] = (string) ($field['is_show_home_list_search'] ?? '2');
        }

        $model['fields'] = $fields;
        $model['frontend_schema'] = [
            'form' => array_values(array_filter($fields, function ($field) {
                return (string) ($field['is_show_home_form'] ?? '1') === '1';
            })),
            'list' => array_values(array_filter($fields, function ($field) {
                return (string) ($field['is_show_home_list'] ?? '1') === '1';
            })),
            'detail' => array_values(array_filter($fields, function ($field) {
                return (string) ($field['is_show_home_detail'] ?? '1') === '1';
            })),
            'search' => array_values(array_filter($fields, function ($field) {
                return (string) ($field['is_show_home_list_search'] ?? '2') === '1';
            })),
        ];

        return $model;
    }

    private function submitFrontendHandle(string $access, array $model)
    {
        $tableName = 'module_formtools_' . $model['identification'];
        $insertData = [];
        $uploadKeys = [];

        foreach ($model['frontend_schema']['form'] ?? [] as $field) {
            $key = $field['identification'];
            $value = $this->request->input($key, '');
            $insertData[$key] = is_array($value) ? implode(',', $value) : $value;
            if (in_array($field['formtype'] ?? '', ['upload', 'image'], true)) {
                $uploadKeys[] = $key;
            }
        }

        foreach ($uploadKeys as $key) {
            unset($insertData[$key]);
            if (!empty($_FILES[$key]['size']) && $_FILES[$key]['size'] > 0) {
                try {
                    $insertData[$key] = UploadFile(\Request(), $key, "file/" . date("Y/m/d/") . uniqid(), ALLOWEXT, __E("upload_driver"));
                } catch (\Exception $exception) {
                    return $this->handleFrontendSubmitResponse(false, $exception->getMessage());
                }
            }
        }

        if ($this->hasDynamicTableColumn($tableName, 'created_at')) {
            $insertData['created_at'] = getDay();
        }
        if ($this->hasDynamicTableColumn($tableName, 'updated_at')) {
            $insertData['updated_at'] = getDay();
        }
        if ($this->hasDynamicTableColumn($tableName, 'uid')) {
            $insertData['uid'] = (int) (session()->get($this->login_unique)['uid'] ?? 0);
        }

        if ($this->hasDynamicTableColumn($tableName, 'status')) {
            $insertData['status'] = 0;
        }

        $id = Common::query()->from($tableName)->insertGetId($insertData);
        if (!$id) {
            return $this->handleFrontendSubmitResponse(false, '提交失败，请稍后重试');
        }

        return $this->handleFrontendSubmitResponse(true, '提交成功', [
            'id' => $id,
            'jumpUrl' => $this->resolveFrontendJumpUrl($access, $model, $id),
        ]);
    }

    private function handleFrontendSubmitResponse(bool $success, string $message, array $data = [], int $status = 200)
    {
        if ($this->request->expectsJson() || $this->request->ajax() || str_starts_with(getURIByRoute($this->request)['uri'] ?? '', 'api/')) {
            return response()->json([
                'status' => $success ? 200 : $status,
                'msg' => $message,
                'data' => $data,
            ], $success ? 200 : $status);
        }

        if ($success) {
            return back()->with([
                'pageDataMsg' => $message,
                'pageDataStatus' => 200,
            ]);
        }

        return back()->with('pageDataMsg', $message);
    }

    private function resolveFrontendSearchFields(array $model, ?string $tableName = null): array
    {
        $fields = collect($model['frontend_schema']['search'] ?? [])
            ->map(function ($field) {
                return [
                    'identification' => trim((string) ($field['identification'] ?? '')),
                    'name' => trim((string) ($field['name'] ?? '')),
                ];
            })
            ->filter(function ($field) {
                return $field['identification'] !== '';
            })
            ->values()
            ->all();

        if (!$fields) {
            $fallbackFields = ['title', 'name', 'content'];
            foreach ($fallbackFields as $fallbackField) {
                if ($tableName !== null && !$this->hasDynamicTableColumn($tableName, $fallbackField)) {
                    continue;
                }
                $fields[] = [
                    'identification' => $fallbackField,
                    'name' => $this->resolveDefaultSearchFieldLabel($fallbackField),
                ];
            }
        }

        if ($tableName !== null) {
            $fields = array_values(array_filter($fields, function ($field) use ($tableName) {
                return $this->hasDynamicTableColumn($tableName, $field['identification']);
            }));
        }

        return $fields;
    }

    private function resolveDefaultSearchFieldLabel(string $field): string
    {
        return match ($field) {
            'title' => '标题',
            'name' => '名称',
            'content' => '内容',
            default => $field,
        };
    }

    private function applyFrontendKeywordSearch($query, string $tableName, string $keyword, array $searchFields): void
    {
        if ($keyword === '' || !$searchFields) {
            return;
        }

        $query->where(function ($subQuery) use ($keyword, $searchFields, $tableName) {
            $hasCondition = false;
            foreach ($searchFields as $field) {
                $column = $field['identification'] ?? '';
                if ($column === '' || !$this->hasDynamicTableColumn($tableName, $column)) {
                    continue;
                }
                if (!$hasCondition) {
                    $subQuery->where($column, 'like', '%' . $keyword . '%');
                    $hasCondition = true;
                } else {
                    $subQuery->orWhere($column, 'like', '%' . $keyword . '%');
                }
            }
        });
    }

    private function filterApiListByKeyword($list, string $keyword, array $searchFields): array
    {
        $list = is_array($list) ? $list : [];
        if ($keyword === '' || !$searchFields) {
            return $list;
        }

        $keyword = Str::lower($keyword);
        return array_values(array_filter($list, function ($item) use ($keyword, $searchFields) {
            foreach ($searchFields as $field) {
                $value = data_get($item, $field['identification'] ?? '');
                if ($value === null) {
                    continue;
                }
                $valueText = Str::lower(trim(strip_tags(is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE))));
                if ($valueText !== '' && str_contains($valueText, $keyword)) {
                    return true;
                }
            }

            return false;
        }));
    }

    private function filterApiListByActiveStatus(array $list): array
    {
        return array_values(array_filter($list, function ($item) {
            return $this->isFrontendAccessibleApiItem($item);
        }));
    }

    private function isFrontendAccessibleApiItem($item): bool
    {
        $record = frontendRecordData($item);
        if (!array_key_exists('status', $record)) {
            return true;
        }

        return (string) ($record['status'] ?? '') === '1';
    }

    private function parseApiFieldMappings(?string $mappingText): array
    {
        $mappingText = str_replace("\r\n", "\n", (string) $mappingText);
        $mappings = [];
        foreach (explode("\n", $mappingText) as $line) {
            $line = trim($line);
            if ($line === '' || !str_contains($line, '=>')) {
                continue;
            }
            [$target, $source] = array_map('trim', explode('=>', $line, 2));
            if ($target === '' || $source === '') {
                continue;
            }
            $mappings[$target] = $source;
        }

        return $mappings;
    }

    private function requestApiPayload(string $url): array
    {
        $url = trim($url);
        if ($url === '') {
            return [];
        }

        $response = curl_request($url);
        if (!is_string($response) || trim($response) === '') {
            return [];
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function buildApiDetailUrl(string $detailUrl, string $id): string
    {
        $detailUrl = trim($detailUrl);
        if ($detailUrl === '') {
            return '';
        }

        if (str_contains($detailUrl, '{id}')) {
            return str_replace('{id}', $id, $detailUrl);
        }
        if (preg_match('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', $detailUrl)) {
            return preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', rawurlencode($id), $detailUrl, 1) ?? $detailUrl;
        }
        if (str_contains($detailUrl, ':id')) {
            return str_replace(':id', $id, $detailUrl);
        }
        if (preg_match('/[?&][^=]+=$/', $detailUrl)) {
            return $detailUrl . rawurlencode($id);
        }

        return rtrim($detailUrl, '/') . '/' . rawurlencode($id);
    }

    private function findApiDetailFromListPayload(array $payload, array $mappings, string $id): array
    {
        foreach ($this->extractApiListItems($payload) as $item) {
            $rawRecord = frontendRecordData($item);
            $mappedRecord = $this->mapApiItem($rawRecord, $mappings);
            if ($this->matchesApiDetailIdentifier($mappedRecord, $id) || $this->matchesApiDetailIdentifier($rawRecord, $id)) {
                return $mappedRecord ?: $rawRecord;
            }
        }

        return [];
    }

    private function matchesApiDetailIdentifier($record, string $id): bool
    {
        $record = frontendRecordData($record);
        if (!$record) {
            return false;
        }

        foreach (['id', 'dir_id', 'doc_id', 'article_id', 'content_id'] as $key) {
            if (array_key_exists($key, $record) && (string) ($record[$key] ?? '') === $id) {
                return true;
            }
        }

        return false;
    }

    private function extractApiListItems(array $payload): array
    {
        $items = $payload['data'] ?? $payload['list'] ?? $payload['items'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, fn ($item) => is_array($item) || is_object($item)));
    }

    private function extractApiDetailItem(array $payload): array
    {
        $item = $payload['data'] ?? $payload['item'] ?? [];
        if (is_object($item) || is_array($item)) {
            return frontendRecordData($item);
        }

        $items = $this->extractApiListItems($payload);
        return $items[0] ?? [];
    }

    private function extractApiRelatedItems(array $payload): array
    {
        $items = $payload['other'] ?? $payload['related'] ?? $payload['list'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, fn ($item) => is_array($item) || is_object($item)));
    }

    private function mapApiList(array $items, array $mappings): array
    {
        return array_values(array_filter(array_map(function ($item) use ($mappings) {
            $mapped = $this->mapApiItem($item, $mappings);
            return $mapped ?: null;
        }, $items)));
    }

    private function mapApiItem($item, array $mappings): array
    {
        $record = frontendRecordData($item);
        if (!$record) {
            return [];
        }

        if (!$mappings) {
            return $record;
        }

        foreach ($mappings as $target => $source) {
            $value = data_get($record, $source);
            if ($value === null && array_key_exists($source, $record)) {
                $value = $record[$source];
            }
            if ($value !== null) {
                $record[$target] = $value;
            }
        }

        return $record;
    }

    private function buildListContext(array $model, $list, string $searchKeyword, array $searchFields, array $filters = []): array
    {
        $fieldNames = array_values(array_filter(array_map(function ($field) {
            return trim((string) ($field['name'] ?? ''));
        }, $searchFields)));

        $sortKey = $this->normalizeListSortKey((string) ($filters['sort'] ?? $this->request->query('sort', 'newest')));
        $pidScope = $this->normalizePidScope((string) ($filters['pid_scope'] ?? $this->request->query('pid_scope', 'descendants')));
        $pid = (string) ($filters['pid'] ?? ($this->request->query('pid', '') ?? ''));
        $pidOptions = (array) ($filters['pid_options'] ?? []);
        $hasPid = (bool) ($filters['has_pid'] ?? false);

        return [
            'keyword' => $searchKeyword,
            'hasKeyword' => $searchKeyword !== '',
            'searchFields' => $searchFields,
            'searchFieldNames' => $fieldNames,
            'searchFieldSummary' => implode(' / ', $fieldNames),
            'resultCount' => $this->resolveListItemCount($list),
            'totalCount' => $this->resolveListTotalCount($list),
            'pageTitle' => $model['home_config']['home_page_title'] ?? ($model['name'] ?? '内容列表'),
            'pageDescription' => $model['home_config']['home_page_describe'] ?? '浏览最新内容、快速筛选重点信息。',
            'hasPid' => $hasPid,
            'pid' => $pid,
            'pidScope' => $pidScope,
            'pidOptions' => $pidOptions,
            'sort' => $sortKey,
            'sortOptions' => [
                ['value' => 'newest', 'label' => '倒序（最新）'],
                ['value' => 'oldest', 'label' => '顺序（最早）'],
                ['value' => 'likes', 'label' => '点赞最多'],
                ['value' => 'views', 'label' => '浏览最多'],
            ],
            'pidScopeOptions' => [
                ['value' => 'descendants', 'label' => '包含子孙'],
                ['value' => 'direct', 'label' => '仅子级'],
            ],
        ];
    }

    private function resolveListItemCount($list): int
    {
        if ($list instanceof AbstractPaginator) {
            return count($list->items());
        }
        if (is_array($list)) {
            return count($list);
        }
        if ($list instanceof \Countable) {
            return count($list);
        }

        return $list ? 1 : 0;
    }

    private function resolveListTotalCount($list): int
    {
        if ($list instanceof AbstractPaginator) {
            return (int) $list->total();
        }

        return $this->resolveListItemCount($list);
    }

    private function buildDetailInteractions(array $model, string $access, array $detailRecord): array
    {
        if (($model['other_config']['data_source'] ?? 'local') !== 'local') {
            return [
                'can_like' => false,
                'like_url' => '',
                'downloads' => [],
                'download_fields' => [],
            ];
        }

        $tableName = 'module_formtools_' . $model['identification'];
        $recordId = (int) ($detailRecord['id'] ?? 0);
        $downloadFields = $this->resolveDownloadableFields($model, $detailRecord, $access);
        $downloads = [];
        foreach ($downloadFields as $fieldDownloads) {
            foreach ($fieldDownloads['items'] as $downloadItem) {
                $downloads[] = $downloadItem;
            }
        }

        return [
            'can_like' => $recordId > 0 && $this->hasDynamicTableColumn($tableName, 'good_count'),
            'like_url' => $recordId > 0 ? url('detail/' . $access . '/' . $recordId . '/good') : '',
            'downloads' => $downloads,
            'download_fields' => $downloadFields,
        ];
    }

    private function resolveDownloadableFields(array $model, array $detailRecord, string $access): array
    {
        $recordId = (int) ($detailRecord['id'] ?? 0);
        if ($recordId <= 0) {
            return [];
        }

        $downloads = [];
        foreach (($model['frontend_schema']['detail'] ?? []) as $field) {
            if (!$this->isDownloadableField($field)) {
                continue;
            }

            $fieldKey = (string) ($field['identification'] ?? '');
            if ($fieldKey === '') {
                continue;
            }

            $storedFiles = $this->splitStoredFiles($detailRecord[$fieldKey] ?? '');
            if (!$storedFiles) {
                continue;
            }

            $items = [];
            foreach ($storedFiles as $index => $storedPath) {
                $items[] = [
                    'label' => count($storedFiles) > 1 ? (($field['name'] ?? $fieldKey) . ' ' . ($index + 1)) : ($field['name'] ?? $fieldKey),
                    'path' => $storedPath,
                    'url' => url('detail/' . $access . '/' . $recordId . '/download/' . $fieldKey . '/' . $index),
                    'direct_url' => $this->resolveFileUrl($storedPath),
                ];
            }

            $downloads[$fieldKey] = [
                'label' => $field['name'] ?? $fieldKey,
                'items' => $items,
            ];
        }

        return $downloads;
    }

    private function isDownloadableField(array $field): bool
    {
        return in_array((string) ($field['formtype'] ?? ''), ['upload', 'uploadAjax', 'file'], true);
    }

    private function splitStoredFiles($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $value), static fn ($item) => $item !== ''));
        }

        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn ($item) => $item !== ''));
    }

    private function resolveFileUrl(string $path): string
    {
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        return GetUrlByPath($path);
    }

    private function detailActionResponse(bool $success, string $message, array $data = [], int $status = 200)
    {
        if ($this->request->expectsJson() || $this->request->ajax() || str_starts_with(getURIByRoute($this->request)['uri'] ?? '', 'api/')) {
            return response()->json([
                'status' => $success ? 200 : $status,
                'msg' => $message,
                'data' => $data,
            ], $success ? 200 : $status);
        }

        return back()->with('pageDataMsg', $message);
    }

    private function resolveAdjacentDetailItem(
        string $tableName,
        int $currentId,
        string $direction,
        array $selectColumns,
        ?string $relationKey = null,
        $scopeValue = null
    ) {
        $scopedItem = null;
        if ($relationKey !== null && $scopeValue !== null && $scopeValue !== '') {
            $scopedItem = $this->queryAdjacentDetailItem(
                $tableName,
                $currentId,
                $direction,
                $selectColumns,
                [$relationKey => $scopeValue]
            );
        }

        if ($scopedItem) {
            return $scopedItem;
        }

        return $this->queryAdjacentDetailItem($tableName, $currentId, $direction, $selectColumns);
    }

    private function queryAdjacentDetailItem(
        string $tableName,
        int $currentId,
        string $direction,
        array $selectColumns,
        array $extraWhere = []
    ) {
        $query = $this->applyActiveStatusScope(Common::query()->from($tableName), $tableName);

        foreach ($extraWhere as $column => $value) {
            $query->where($column, $value);
        }

        if ($direction === 'previous') {
            return $query->where('id', '>', $currentId)
                ->orderBy('id')
                ->first($selectColumns);
        }

        return $query->where('id', '<', $currentId)
            ->orderByDesc('id')
            ->first($selectColumns);
    }

    private function resolveSingleModelTemplate(array $model, string $template): string
    {
        if (($model['type'] ?? 'multi') !== 'single' || $template !== FormTemplateResolver::DEFAULT_LIST_TEMPLATE) {
            return $template;
        }

        return match ($model['identification'] ?? '') {
            'about_us' => 'about',
            'contact_us' => 'contacts',
            default => $template,
        };
    }

    private function buildVisualListItems(array $model, $list, string $access): array
    {
        $items = [];
        $records = [];
        if ($list instanceof AbstractPaginator) {
            $records = $list->items();
        } elseif (is_array($list)) {
            $isList = $list === [] || array_keys($list) === range(0, count($list) - 1);
            $records = $isList ? $list : [$list];
        } elseif ($list) {
            $records = [$list];
        }

        foreach ($records as $record) {
            $row = frontendRecordData($record);
            if ($row === []) {
                continue;
            }

            $id = (int) ($row['id'] ?? 0);
            $url = trim((string) ($row['url'] ?? $row['link'] ?? ''));
            if ($url === '' && $id > 0) {
                $url = model_detail_url($access, $id, [], $model);
            }

            $items[] = [
                'title' => $this->pickVisualValue($row, ['title', 'name', 'cate_name', 'company_name', 'full_name', 'username'], '未命名内容'),
                'summary' => $this->pickVisualSummary($row),
                'date' => $this->pickVisualValue($row, ['date', 'publish_time', 'created_at', 'updated_at']),
                'image' => $this->pickVisualValue($row, ['cover', 'thumb', 'image', 'logo', 'avatar', 'pic']),
                'url' => $url,
            ];
        }

        return $items;
    }

    private function buildVisualDetailItem(array $model, array $record, string $access): array
    {
        $id = (int) ($record['id'] ?? 0);
        $url = trim((string) ($record['url'] ?? $record['link'] ?? ''));
        if ($url === '' && $id > 0) {
            $url = model_detail_url($access, $id, [], $model);
        }

        return [
            'title' => $this->pickVisualValue($record, ['title', 'name', 'cate_name', 'company_name', 'full_name', 'username'], '未命名内容'),
            'summary' => $this->pickVisualSummary($record),
            'content' => $this->pickVisualValue($record, ['content', 'body', 'detail', 'describe', 'description', 'summary', 'remark']),
            'date' => $this->pickVisualValue($record, ['date', 'publish_time', 'created_at', 'updated_at']),
            'image' => $this->pickVisualValue($record, ['cover', 'thumb', 'image', 'logo', 'avatar', 'pic']),
            'url' => $url,
        ];
    }

    private function buildListVisualPaginationHtml($list, array $model): string
    {
        if (!$list instanceof AbstractPaginator) {
            return '';
        }

        $pagePosition = data_get($model, 'home_config.list_page_template', $model['list_page_template'] ?? 'center');
        return (string) $list->appends($_GET)->links('themes.default.public.pagination', [
            'data' => [
                'side_num' => 2,
                'page_position' => $pagePosition,
            ],
        ]);
    }

    private function pickVisualValue(array $row, array $keys, string $default = ''): string
    {
        foreach ($keys as $key) {
            $value = $row[$key] ?? '';
            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return $default;
    }

    private function pickVisualSummary(array $row): string
    {
        $summary = $this->pickVisualValue($row, ['summary', 'describe', 'description', 'intro', 'remark']);
        if ($summary !== '') {
            return $summary;
        }

        $content = $this->pickVisualValue($row, ['content', 'body', 'detail']);
        if ($content === '') {
            return '';
        }

        $content = trim(strip_tags($content));
        return mb_substr($content, 0, 120);
    }

    private function resolveHandleTemplate(array $model): string
    {
        if (($model['list_template'] ?? '') === 'feedback' || ($model['identification'] ?? '') === 'feedback') {
            return 'feedback';
        }

        return FormTemplateResolver::DEFAULT_HANDLE_TEMPLATE;
    }

    private function loadHandlePageData(array $model): array
    {
        if (($model['list_template'] ?? '') !== 'feedback' && ($model['identification'] ?? '') !== 'feedback') {
            return [];
        }

        $tableName = 'module_formtools_' . $model['identification'];
        if (!Schema::hasTable($tableName)) {
            return [];
        }

        return $this->applyActiveStatusScope(Common::query()->from($tableName), $tableName)
            ->latest('id')
            ->limit(6)
            ->get()
            ->toArray();
    }

    private function resolveFrontendJumpUrl(string $access, array $model, int $id): string
    {
        if (($model['list_template'] ?? '') === 'feedback' || ($model['identification'] ?? '') === 'feedback') {
            return model_list_url($access);
        }

        if (($model['type'] ?? 'multi') === 'single') {
            return model_list_url($access);
        }

        return model_detail_url($access, $id);
    }

    private function applyActiveStatusScope($query, string $tableName)
    {
        if ($this->hasDynamicTableColumn($tableName, 'status')) {
            $query->where('status', 1);
        }

        return $query;
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

    private function buildFrontDocTree(array $treeSource, string $priceMode = 'inherit', string $contentTable = ''): array
    {
        $mode = $treeSource['mode'] ?? 'self';
        $treeTable = $mode === 'foreign' ? ($treeSource['foreignTable'] ?? '') : ($treeSource['table'] ?? '');
        $contentTable = $contentTable ?: ($treeSource['table'] ?? '');
        $modelIdent = (string) ($treeSource['model'] ?? '');
        $foreignField = (string) ($treeSource['foreignField'] ?? '');
        $accessIdent = (string) ($treeSource['accessIdent'] ?? $modelIdent);

        if ($treeTable === '' || !Schema::hasTable($treeTable)) {
            return [];
        }

        $selectColumns = ['id', 'pid', 'sorts', 'sort'];
        // 依次尝试 title / name / cate_name 兼容不同的分类表命名约定
        $titleCandidates = [];
        foreach (['title', 'name', 'cate_name', 'category_name'] as $candidate) {
            if (Schema::hasColumn($treeTable, $candidate)) {
                $titleCandidates[] = $candidate;
                $selectColumns[] = $candidate;
            }
        }
        if (Schema::hasColumn($treeTable, 'price')) {
            $selectColumns[] = 'price';
        }
        if (Schema::hasColumn($treeTable, 'status')) {
            $selectColumns[] = 'status';
        }

        $resolveTitle = function ($row) use ($titleCandidates) {
            foreach ($titleCandidates as $candidate) {
                $value = isset($row->{$candidate}) ? (string) $row->{$candidate} : '';
                if ($value !== '') {
                    return $value;
                }
            }
            return '';
        };

        $rows = Common::query()
            ->from($treeTable)
            ->select(array_values(array_unique($selectColumns)))
            ->orderBy('sorts', 'desc')
            ->orderBy('sort', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($row) use ($resolveTitle) {
                $row->price = (float) ($row->price ?? 0);
                $row->doc_id = (int) $row->id;
                $row->doc_pid = (int) ($row->pid ?? 0);
                $row->doc_title = $resolveTitle($row);
                $row->doc_status = (string) ($row->status ?? '1');
                return $row;
            })
            ->all();

        if ($priceMode === 'free_only') {
            $rows = array_values(array_filter($rows, fn ($r) => (float) ($r->price ?? 0) <= 0));
        } elseif ($priceMode === 'paid_only') {
            $rows = array_values(array_filter($rows, fn ($r) => (float) ($r->price ?? 0) > 0));
        }

        $byId = [];
        foreach ($rows as $row) {
            $byId[(int) $row->id] = $row;
        }
        $childrenMap = [];
        $rootIds = [];
        foreach ($rows as $row) {
            $id = (int) $row->id;
            $pid = (int) ($row->pid ?? 0);
            if ($pid > 0 && isset($byId[$pid])) {
                $childrenMap[$pid][] = $id;
            } else {
                $rootIds[] = $id;
            }
        }

        $contentByCategory = [];
        if ($mode === 'foreign' && $contentTable !== '' && $foreignField !== '' && Schema::hasTable($contentTable)) {
            $contentSelects = ['id', $foreignField];
            if (Schema::hasColumn($contentTable, 'title')) $contentSelects[] = 'title';
            if (Schema::hasColumn($contentTable, 'name')) $contentSelects[] = 'name';
            if (Schema::hasColumn($contentTable, 'price')) $contentSelects[] = 'price';
            if (Schema::hasColumn($contentTable, 'status')) $contentSelects[] = 'status';
            if (Schema::hasColumn($contentTable, 'sorts')) $contentSelects[] = 'sorts';
            if (Schema::hasColumn($contentTable, 'sort')) $contentSelects[] = 'sort';
            $contentRows = Common::query()
                ->from($contentTable)
                ->select(array_values(array_unique($contentSelects)))
                ->orderBy('sorts', 'desc')
                ->orderBy('sort', 'desc')
                ->orderBy('id', 'desc')
                ->get()
                ->all();
            if ($priceMode === 'free_only') {
                $contentRows = array_values(array_filter($contentRows, fn ($r) => (float) ($r->price ?? 0) <= 0));
            } elseif ($priceMode === 'paid_only') {
                $contentRows = array_values(array_filter($contentRows, fn ($r) => (float) ($r->price ?? 0) > 0));
            }
            foreach ($contentRows as $cr) {
                $cid = (int) ($cr->{$foreignField} ?? 0);
                if ($cid > 0) {
                    $contentByCategory[$cid][] = $cr;
                }
            }
        }

        $buildContentNode = function ($row) use ($accessIdent) {
            $cid = (int) $row->id;
            $title = (string) ($row->title ?? $row->name ?? '');
            if ($title === '') {
                $title = '内容 #' . $cid;
            }
            $price = (float) ($row->price ?? 0);
            return [
                'type' => 'content',
                'id' => $cid,
                'title' => $title,
                'price' => $price,
                'status' => (string) ($row->status ?? '1'),
                'url' => model_detail_url($accessIdent, $cid),
                'is_leaf' => true,
                'clickable' => true,
            ];
        };

        $buildNode = function (int $id) use (&$buildNode, &$buildContentNode, &$childrenMap, $byId, $mode, $accessIdent, &$contentByCategory) {
            $row = $byId[$id] ?? null;
            if (!$row) {
                return null;
            }
            $childCategoryIds = $childrenMap[$id] ?? [];
            $childCategories = [];
            foreach ($childCategoryIds as $cid) {
                $node = $buildNode((int) $cid);
                if ($node) {
                    $childCategories[] = $node;
                }
            }
            $contentItems = [];
            if ($mode === 'foreign') {
                foreach ($contentByCategory[$id] ?? [] as $cr) {
                    $contentItems[] = $buildContentNode($cr);
                }
            }
            $url = '';
            $hasContent = !empty($contentItems);
            if ($mode === 'foreign') {
                $firstContent = $contentItems[0] ?? null;
                $url = $firstContent ? $firstContent['url'] : '';
            } else {
                $url = '';
            }
            $categoryTitle = (string) ($row->doc_title ?? '');
            if ($categoryTitle === '') {
                $categoryTitle = '分类 #' . $id;
            }
            $categoryNode = [
                'type' => 'category',
                'id' => (int) $id,
                'pid' => (int) ($row->pid ?? 0),
                'title' => $categoryTitle,
                'price' => (float) ($row->price ?? 0),
                'status' => (string) ($row->status ?? '1'),
                'url' => $url,
                'is_leaf' => empty($childCategories) && !$hasContent,
                'clickable' => $hasContent,
                'children' => $childCategories,
                'contents' => $contentItems,
            ];
            return $categoryNode;
        };

        $tree = [];
        foreach ($rootIds as $rid) {
            $node = $buildNode((int) $rid);
            if ($node) {
                $tree[] = $node;
            }
        }
        return $tree;
    }

    private function resolveFirstFrontDocId(string $tableName, string $priceMode = 'inherit'): int
    {
        if (!Schema::hasTable($tableName)) {
            return 0;
        }
        $query = Common::query()->from($tableName)->select(['id']);
        if (Schema::hasColumn($tableName, 'price')) {
            if ($priceMode === 'free_only') {
                $query->where(function ($q) {
                    $q->whereNull('price')->orWhere('price', '<=', 0);
                });
            } elseif ($priceMode === 'paid_only') {
                $query->where('price', '>', 0);
            }
        }
        if (Schema::hasColumn($tableName, 'sorts')) {
            $query->orderByDesc('sorts');
        } elseif (Schema::hasColumn($tableName, 'sort')) {
            $query->orderByDesc('sort');
        }
        $query->orderByDesc('id');
        $row = $query->first();
        return $row ? (int) $row->id : 0;
    }
}
