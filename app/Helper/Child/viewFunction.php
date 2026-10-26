<?php

//获取条数
function getLen($all = []) {
    $len = intval($all['pagesize']) ?: $_REQUEST['pagesize'];
    return intval($len) > 0 ? intval($len) : __E('admin_page_count');
}

//获取时间格式
function getDay($type = 1) {
    if ($type == 2) {
        return date('Y-m-d');
    } else {
        return date('Y-m-d H:i:s');
    }
}

//处理时间范围
function dealTimeRang($str, $type = 1) {
    $arr = explode(' - ', $str);
    if ($type == 1) {
        $arr = [$arr[0] . ' 00:00:00', $arr[1] . ' 23:59:59'];
    }
    return $arr;
}

//返回数组json
function returnJson($status, $msg, $data = array(), $other = array()) {
    return json_encode(['status' => $status, 'msg' => $msg, 'data' => $data, 'other' => $other], JSON_UNESCAPED_UNICODE);
}

//返回数组
function returnArr($status, $msg, $data = array(), $other = array()) {
    return ['status' => $status, 'msg' => $msg, 'data' => $data, 'other' => $other];
}

//返回数组json
function dealPage($data = array()) {
    $tmp['list'] = $data['data'];
    $tmp['page'] = $data['current_page'];
    $tmp['pagesize'] = $data['per_page'];
    $tmp['total'] = $data['total'];
    return $tmp;
}

function backMsg($msg) {
    return back()->with('errormsg', $msg);
}


function moduleHomeTemplate($moduleName) {
    $dir = 'home';
    $str = strtolower($moduleName) . "::{$dir}.";
    return $str;
}

//模块后台的静态资源文件路径
function moduleHomeResource($moduleName = '', $assets = 'assets') {
    $moduleName = strtolower($moduleName);
    $str = asset("views/modules/$moduleName/".$assets);
    return $str;
}


//后台模块的模板地址简化函数
//参数$moduleName 是模块名
function moduleAdminTemplate($moduleName) {
    $str = strtolower($moduleName) . "::admin.";
    return $str;
}

//模块后台的静态资源文件路径
function moduleAdminResource($moduleName = '') {
    $str = MODULE_ADMIN_ASSET . "/assets";
    return $str;
}

//公共静态资源路径
function commonAsset($path = '') {
    $path = trim($path, '/');
    if ($path === '') {
        return COMMON_ASSET;
    }
    return COMMON_ASSET . $path;
}


//模块后台跳转链接简化
function moduleAdminJump($moduleName, $path) {
    $str = url('admin/' . strtolower($moduleName)) . '/' . $path;
    return $str;
}

//模块后台跳转链接简化
function moduleHomeJump($moduleName, $path) {
    $str = url($moduleName) . '/' . $path;
    return $str;
}

/**
 * 返回信息
 * @param $arr [200,'成功','/admin/index']
 * @return redirect
 */

function oneFlash($arr) {
    session(['pageDataStatus' => $arr[0]]);
    session(['pageDataMsg' => $arr[1]]);
    if ($arr[2]) return redirect($arr[2]);
    return back();
}

//获取URI详情
function getURIByRoute($request) {
    if ($request->route()) {
        $return['uri'] = $request->route()->uri;
        $return['namespace'] = $request->route()->getAction()['namespace'];
        $return['moduleName'] = explode('\\', $request->route()->getAction()['namespace'])[1];
        $temp = explode('@', str_replace($return['namespace'] . '\\', '', $request->route()->getAction()['controller']));
        $return['controller'] = str_replace('Controller', '', $temp[0]);
        $return['action'] = $temp[1];
        return $return;
    }
    return [];
}

function HomeView($viewPath, $data = []) {
    // 必须走 ThemeManager::current()：后台预览未启用主题时会临时切换当前主题，
    // 直接读 cache('theme') 会绕过预览态，导致预览渲染成已启用主题的页面。
    $theme = \App\Support\Theme\ThemeManager::current();
    // 包装一层：渲染后自动解析短标签 {mx:list ...}，主题作者无需改控制器
    return new \App\Support\Theme\ThemeView(view("themes." . $theme . "." . $viewPath, $data));
}

function currentHomeLang($default = \App\Support\I18n\ThemeTranslator::DEFAULT_LOCALE) {
    return \App\Support\I18n\ThemeTranslator::currentLocale() ?: $default;
}

function themeTrans($key, $replace = [], $theme = null, $lang = null) {
    return \App\Support\I18n\ThemeTranslator::translate($key, $replace, $theme, $lang);
}

function frontendRecordData($value): array {
    if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
        return $value->toArray();
    }
    if (is_array($value)) {
        return $value;
    }
    if (is_object($value)) {
        return get_object_vars($value);
    }

    return [];
}

function model_url_suffix(string $access, ?array $modelData = null): string {
    static $suffixCache = [];

    $access = trim($access, '/');
    if ($access === '') {
        return 'html';
    }

    if (is_array($modelData) && isset($modelData['home_config'])) {
        $homeConfig = (array) $modelData['home_config'];
        $suffix = strtolower(trim((string) ($homeConfig['url_suffix'] ?? 'html')));
        return $suffix === 'none' ? 'none' : 'html';
    }

    if (array_key_exists($access, $suffixCache)) {
        return $suffixCache[$access];
    }

    $suffix = 'html';
    if (class_exists(\Modules\Formtools\Models\FormModel::class)) {
        $model = \Modules\Formtools\Models\FormModel::query()
            ->where('access_identification', $access)
            ->value('home_config');
        $homeConfig = json_decode((string) $model, true) ?: [];
        $suffix = strtolower(trim((string) ($homeConfig['url_suffix'] ?? 'html')));
        $suffix = $suffix === 'none' ? 'none' : 'html';
    }

    $suffixCache[$access] = $suffix;
    return $suffix;
}

function model_has_html_suffix(string $access, ?array $modelData = null): bool {
    return model_url_suffix($access, $modelData) === 'html';
}

function model_list_url(string $access, array $query = [], ?array $modelData = null): string {
    $path = 'list/' . trim($access, '/');
    if (model_has_html_suffix($access, $modelData)) {
        $path .= '.html';
    }
    $url = url($path);
    if (!$query) {
        return $url;
    }

    return $url . '?' . http_build_query($query);
}

function model_list_path(string $access, ?array $modelData = null): string {
    $path = 'list/' . trim($access, '/');
    if (model_has_html_suffix($access, $modelData)) {
        $path .= '.html';
    }

    return $path;
}

function model_detail_url(string $access, $id, array $query = [], ?array $modelData = null): string {
    $path = 'detail/' . trim($access, '/') . '/' . trim((string) $id, '/');
    if (model_has_html_suffix($access, $modelData)) {
        $path .= '.html';
    }
    $url = url($path);
    if (!$query) {
        return $url;
    }

    return $url . '?' . http_build_query($query);
}

function model_detail_path(string $access, $id, ?array $modelData = null): string {
    $path = 'detail/' . trim($access, '/') . '/' . trim((string) $id, '/');
    if (model_has_html_suffix($access, $modelData)) {
        $path .= '.html';
    }

    return $path;
}

function ModelView($viewPath, $data = [], $fallback = \Modules\Formtools\Support\FormTemplateResolver::DEFAULT_LIST_TEMPLATE) {
    // 同 HomeView：必须走 ThemeManager::current() 才能支持后台预览态
    $theme = \App\Support\Theme\ThemeManager::current();
    if (str_contains((string) $viewPath, '::')) {
        $data['template'] = $viewPath;
        $data['template_name'] = $viewPath;
        $data['template_source'] = 'namespaced';
    } else {
        $resolved = \Modules\Formtools\Support\FormTemplateResolver::resolveFrontendTemplate($theme, $viewPath, $fallback);
        $data['template'] = $resolved['template'];
        $data['template_name'] = $resolved['name'];
        $data['template_source'] = $resolved['source'];
    }

    // 沿「当前主题 -> parent -> default」解析布局外壳，主题少放文件也不会整站 500
    $layout = \App\Support\Theme\ThemeManager::viewName('model.LoadTemplate', $theme);

    return new \App\Support\Theme\ThemeView(view($layout, $data));
}

function form_model_scene_template($model, string $scene, string $fallback = ''): string
{
    $modelData = frontendRecordData($model);
    if ($modelData === []) {
        return $fallback;
    }

    foreach (['home_config', 'other_config', 'home_seo_config', 'home_seo_detail_config'] as $field) {
        if (isset($modelData[$field]) && is_string($modelData[$field])) {
            $modelData[$field] = json_decode($modelData[$field], true) ?: [];
        }
    }

    $template = \Modules\Formtools\Support\FormModelTemplateManager::resolveSceneTemplate($modelData, $scene);
    if ($template !== '') {
        return $template;
    }

    if ($fallback !== '') {
        return $fallback;
    }

    return $scene === \Modules\Formtools\Support\FormTemplateCatalog::SCENE_DETAIL
        ? \Modules\Formtools\Support\FormTemplateResolver::DEFAULT_DETAIL_TEMPLATE
        : \Modules\Formtools\Support\FormTemplateResolver::DEFAULT_LIST_TEMPLATE;
}

function form_model_home_section_view($model, string $fallback = 'list'): string
{
    $modelData = frontendRecordData($model);
    if ($modelData === []) {
        return '';
    }

    foreach (['home_config', 'other_config', 'home_seo_config', 'home_seo_detail_config'] as $field) {
        if (isset($modelData[$field]) && is_string($modelData[$field])) {
            $modelData[$field] = json_decode($modelData[$field], true) ?: [];
        }
    }

    $template = form_model_scene_template($modelData, \Modules\Formtools\Support\FormTemplateCatalog::SCENE_HOME_SECTION, $fallback);
    $template = \Modules\Formtools\Support\FormTemplateResolver::sanitizeTemplateName($template);
    if ($template === '') {
        return '';
    }

    $theme = \App\Support\Theme\ThemeManager::current();

    // 沿「当前主题 -> parent -> default」继承链查找，主题只放差异文件即可
    return \App\Support\Theme\ThemeManager::resolveView("index.templates.{$template}", $theme) ?: '';
}

function form_model_home_section_html($model): string
{
    return \Modules\Formtools\Support\FormModelTemplateManager::renderHomeSection($model);
}

function getListByModel($model, $limit = 1, $orderby = "desc") {

    if (!is_array($model)) {
        $model = (array)$model->toarray();
    }
    $model['other_config'] = json_decode($model['other_config'], true);
    if ($model['other_config']["data_source"] == "api") {
        $data_source_field_mapping = explode('\n', $model['other_config']['data_source_field_mapping']);
        $data_source_field_mappings = [];
        foreach ($data_source_field_mapping as $v) {
            $temp = explode("=>", $v);
            if (count($temp) == 2) $data_source_field_mappings[$temp[0]] = $temp[1];
        }

        $curldatas = json_decode(curl_request($model['other_config']["data_source_api_url"]), true);
        $data = [];
        foreach ($curldatas['data'] as $key => $value) {
            if ($key >= $limit) {
                break;
            }
            foreach ($data_source_field_mappings as $k => $val) {
                $value[$k] = $value[$val];
            }
            $data[] = (object)$value;
        }

        return $data;
    }

    $tableName = "module_formtools_" . $model['identification'];
    $data = \Illuminate\Support\Facades\DB::table($tableName);
    if (\Illuminate\Support\Facades\Schema::hasColumn($tableName, 'status')) {
        $data->where('status', 1);
    }

    $data = $data->orderBy("id", $orderby);
    if ($limit) {
        $data = $data->limit($limit);
    }
    $data = $data->get();

    return $data;
}

function getFormRadioList($array) {
    $list = [];
    foreach ($array as $key => $item) {
        $list[] = ['value' => $key, 'name' => $item];
    }
    return $list;
}

/* ================== Formtools 付费内容助手函数 ================== */

if (!function_exists('formtool_payment_service')) {
    function formtool_payment_service(): \Modules\Formtools\Services\FormtoolsPaymentService {
        static $service = null;
        if ($service === null) {
            $service = app(\Modules\Formtools\Services\FormtoolsPaymentService::class);
        }
        return $service;
    }
}

if (!function_exists('formtool_is_paid')) {
    /**
     * 当前用户是否已购买指定动态内容
     */
    function formtool_is_paid(string $model, int $contentId, $user = null): bool {
        return formtool_payment_service()->isPaid($model, $contentId, $user);
    }
}

if (!function_exists('formtool_price_format')) {
    /**
     * 格式化价格：>0 显示 "￥xx.xx"，=0 显示 "免费"
     */
    function formtool_price_format($price): string {
        $price = round((float) $price, 2);
        return $price > 0 ? ('￥' . number_format($price, 2)) : '免费';
    }
}

if (!function_exists('formtool_pay_url')) {
    /**
     * 动态内容购买入口 URL
     */
    function formtool_pay_url(string $model, int $contentId): string {
        return url('/formtools/buy/' . trim($model, '/') . '/' . (int) $contentId);
    }
}

if (!function_exists('formtool_price_status')) {
    /**
     * 取一个内容记录的完整付费状态（用于模板一次性渲染）
     */
    function formtool_price_status(string $model, $record, $user = null): array {
        return formtool_payment_service()->getPriceStatus($model, $record, $user);
    }
}

if (!function_exists('formtool_paid_label')) {
    /**
     * "免费"/"￥xx.xx"/"已购买" 等简短标签
     */
    function formtool_paid_label(string $model, $record, $user = null): string {
        $status = formtool_price_status($model, $record, $user);
        if (!empty($status['is_paid']) && $status['price'] > 0) {
            return '已购买';
        }
        return formtool_price_format($status['price']);
    }
}

