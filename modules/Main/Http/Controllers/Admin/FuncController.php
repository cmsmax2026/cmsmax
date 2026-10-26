<?php

namespace Modules\Main\Http\Controllers\Admin;

use App\Support\PackageManifest\DataPolicyManager;
use App\Support\PackageManifest\CompatibilityChecker;
use App\Support\PackageManifest\LifecyclePolicy;
use App\Support\PackageManifest\PackageManifest;
use App\Support\PackageManifest\PackageLinkManager;
use App\Support\Telemetry\StatisticReporter;
use App\Support\Theme\ThemeManager;
use App\Support\Update\UpdateMetaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Main\Helper\Func;
use Modules\Formtools\Models\FormModel;
use Modules\Main\Models\Modules;
use Modules\ModulesController as Controller;
use Mxzcms\Modules\session\SessionKey;

class FuncController extends Controller {

    private const LEVEL_LABELS = [
        PackageManifest::LEVEL_CORE => '核心层',
        PackageManifest::LEVEL_BASE => '基础层',
        PackageManifest::LEVEL_OPTIONAL => '可选层',
        PackageManifest::LEVEL_BUSINESS => '业务层',
        PackageManifest::LEVEL_EXTENSION => '扩展层',
    ];
    private const TOP_ORDER_THRESHOLD = 9999;

    function delete()
    {
        $all = $this->request->all();
        $all['m'] = trim((string) ($all['m'] ?? ''));
        $all['cloud_type'] = trim((string) ($all['cloud_type'] ?? ''));
        if ($all['m'] === '' || $all['cloud_type'] === '') {
            return back()->with('errormsg', '参数不完整');
        }

        $manifest = PackageManifest::load($all['m'], $all['cloud_type']);
        if (!$manifest) {
            return back()->with('errormsg', '删除失败，未找到包配置');
        }

        if($all['cloud_type']=="module" || $all['cloud_type']=="plugin"){
            $check = Modules::query()
                ->where("cloud_type",$all['cloud_type'])
                ->where("identification",$all['m'])
                ->count();
        }elseif ($all['cloud_type']=="theme"){
            $check = DB::table("themes")->where("identification", $all['m'])->count();
        }
        if($check){
            return back()->with('errormsg','此标识不能直接删除');
        }
        $deletePolicy = LifecyclePolicy::canDelete($manifest);
        if (!$deletePolicy['allowed']) {
            return back()->with('errormsg', $deletePolicy['message']);
        }
        $dataPolicy = DataPolicyManager::resolve($manifest, DataPolicyManager::STAGE_DELETE);
        if (!$dataPolicy['allowed']) {
            return back()
                ->with('errormsg', $dataPolicy['message'])
                ->with('datapolicy_detail', $dataPolicy['detail']);
        }

        if($all['cloud_type']=="module"){
            $deletePath = modules_base_path($manifest['directory'] ?? $all['m']);
            $url = url("admin/module");
        }else if($all['cloud_type']=="plugin"){
            $deletePath = plugins_base_path($manifest['directory'] ?? $all['m']);
            $url = url("admin/plugin");
        }else if ($all['cloud_type']=="theme"){
            $deletePath = THEME_PATH . '/' . ($manifest['directory'] ?? $all['m']);
            $url = url("admin/theme");
        }

        $recordFile = null;
        if (($dataPolicy['policy'] ?? '') !== DataPolicyManager::POLICY_KEEP) {
            $recordFile = DataPolicyManager::record($manifest, DataPolicyManager::STAGE_DELETE, [
                'delete_path' => $deletePath,
            ]);
        }
        $cleanup = DataPolicyManager::executeManagedCleanup($manifest, DataPolicyManager::STAGE_DELETE);
        PackageLinkManager::removeManagedLinks($manifest);
        del_dir_files($deletePath);

        return redirect($url)->with('successmsg', "删除成功" . DataPolicyManager::buildSuccessNote(
            $manifest,
            DataPolicyManager::STAGE_DELETE,
            $cleanup,
            $recordFile
        ) . "!");

    }

    //模块列表
    function module(Request $request) {
        $get = $request->all();
        //获取模块下的模块文件夹和配置
        $local_modules = $content = getDirContent(modules_base_path()) ?: [];

        //获取已安装的module
        $modules_install_datas = [];
        $dataList = Modules::query()
            ->where("cloud_type",'module')
            ->whereNotIn('identification', ['Main', 'Install'])
            ;
        $dataList = $dataList->orderByRaw('CASE WHEN ' . DB::getQueryGrammar()->wrap('order') . ' >= ? THEN 1 ELSE 0 END DESC', [self::TOP_ORDER_THRESHOLD])
            ->orderBy("order","desc")
            ->orderBy("id","desc")
            ->get()
            ->toArray();
        foreach ($dataList as $module) {
            $modules_install_datas[$module['identification']] = $module;
        }

        //判断未安装的模块
        foreach ($modules_install_datas as $key => $value) {
            if (in_array($value['identification'], $local_modules)) {
                foreach ($content as $k => $v) {
                    if ($v == $value['identification']) {
                        unset($content[$k]); //移除已安装的key
                    }
                }
            }
            // 「内置模块」标签复用置顶逻辑：order >= 阈值(9999) 即视为内置，与手动「置顶」按钮共用 order 字段
            $filterType = $get['type'] ?? '';
            if ($filterType === 'system') {
                if ((int) ($value['order'] ?? 0) < self::TOP_ORDER_THRESHOLD) {
                    unset($modules_install_datas[$key]);
                }
            } elseif ($filterType && $filterType != $value['type']) {
                unset($modules_install_datas[$key]);
            }

            if (($get['keyword'] ?? '') && !strstr($value['name'], $get['keyword'])) {
                unset($modules_install_datas[$key]);
            }
        }
        foreach ($modules_install_datas as $key => $value) {
            $config = PackageManifest::load($value['identification'], Modules::Module);
            if ($config) {
                $modules_install_datas[$key] = $this->decorateInstalledPackage(array_merge($value, $config), Modules::Module);
            } else {
                unset($modules_install_datas[$key]);
            }
        }

        //获取未安装的配置信息
        $modules_not_install_datas = [];
        foreach ($content as $k => $v) {
            $configArr = PackageManifest::load($v, Modules::Module);
            if ($configArr) {
                if ($configArr['identification'] && !in_array($configArr['identification'], ['Main', 'Install'])) {
                    $modules_not_install_datas[$v] = $this->decorateLocalPackage($configArr);
                }
            }
        }


        return view('admin/func/feature', [
            'modules_install_datas' => $modules_install_datas,
            'modules_not_install_datas' => $modules_not_install_datas,
            'local_modules' => $local_modules,
            'historicalEntryList' => Func::getHistoricalEntry(),
        ]);
    }

    //插件管理
    function plugin() {

        //获取模块下的模块文件夹和配置
        $local_plugin = getDirContent(plugins_base_path()) ?: [];
        $plugin_install_datas = [];
        $dataList = Modules::query()
            ->where("cloud_type",'plugin')
            ->orderByRaw('CASE WHEN ' . DB::getQueryGrammar()->wrap('order') . ' >= ? THEN 1 ELSE 0 END DESC', [self::TOP_ORDER_THRESHOLD])
            ->orderBy("order","desc")
            ->orderBy("id","desc")
            ->get()
            ->toArray();
        foreach ($dataList as $module) {
            $plugin_install_datas[$module['identification']] = $module;
        }
//        $plugin_install_datas = cache()->get(\Mxzcms\Modules\cache\CacheKey::PluginsActive);
        $plugin_not_install_datas = [];
        //判断未安装的模块
        foreach ($plugin_install_datas as $data) {
            $k = array_search($data['identification'], $local_plugin);
            unset($local_plugin[$k]);
        }
        foreach ($plugin_install_datas as $key => $value) {
            $config = PackageManifest::load($value['identification'], Modules::Plugin);
            if ($config) {
                $plugin_install_datas[$key] = $this->decorateInstalledPackage(array_merge($value, $config), Modules::Plugin);
            } else {
                unset($plugin_install_datas[$key]);
            }
        }

        foreach ($local_plugin as $k => $value) {
            $manifest = PackageManifest::load($value, Modules::Plugin);
            if ($manifest) {
                $plugin_not_install_datas[$k] = $this->decorateLocalPackage($manifest);
            }
        }
        return view('admin/func/plugin', [
            'plugin_install_datas' => $plugin_install_datas,
            'plugin_not_install_datas' => $plugin_not_install_datas,
            'local_modules' => []
        ]);
    }

    function theme() {
        //获取模块下的模块文件夹和配置
        $local_themes = getDirContent(THEME_PATH) ?: [];
        $localThemeDirMap = [];
        foreach ($local_themes as $dir) {
            $manifest = PackageManifest::load($dir, Modules::Theme);
            if (!$manifest) {
                continue;
            }
            $id = trim((string) ($manifest['identification'] ?? ''));
            $directory = trim((string) ($manifest['directory'] ?? ''));
            foreach (array_values(array_unique([
                strtolower($dir),
                strtolower($id),
                strtolower($directory),
            ])) as $key) {
                if ($key === '' || isset($localThemeDirMap[$key])) {
                    continue;
                }
                $localThemeDirMap[$key] = $dir;
            }
        }
        $themeQuery = DB::table('themes');
        if (\Illuminate\Support\Facades\Schema::hasTable('themes') && \Illuminate\Support\Facades\Schema::hasColumn('themes', 'order')) {
            $themeQuery->orderBy('order', 'desc');
        }
        $themes_install_datas = $themeQuery->orderBy("status", "asc")
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        $themes_not_install_datas = [];
        //判断未安装的模块
        foreach ($themes_install_datas as $data) {
            $installedKey = strtolower(trim((string) ($data->identification ?? '')));
            $dir = $installedKey !== '' ? ($localThemeDirMap[$installedKey] ?? '') : '';
            if ($dir === '') {
                $k = array_search($data->identification, $local_themes, true);
                if ($k !== false) {
                    unset($local_themes[$k]);
                }
                continue;
            }
            $k = array_search($dir, $local_themes, true);
            if ($k !== false) {
                unset($local_themes[$k]);
            }
        }
        foreach ($themes_install_datas as $key => $value) {
            $installedKey = strtolower(trim((string) ($value->identification ?? '')));
            $dir = $installedKey !== '' ? ($localThemeDirMap[$installedKey] ?? '') : '';
            $manifest = PackageManifest::load($dir !== '' ? $dir : $value->identification, Modules::Theme);
            if (!$manifest) {
                unset($themes_install_datas[$key]);
                continue;
            }
            $themes_install_datas[$key] = $this->decorateInstalledTheme($value, $manifest);
        }

        foreach ($local_themes as $k => $value) {
            $manifest = PackageManifest::load($value, Modules::Theme);
            if ($manifest) {
                $themes_not_install_datas[$k] = (object) $this->decorateLocalPackage($manifest);
            }
        }
        return view('admin/func/theme', [
            'themes_install_datas' => $themes_install_datas,
            'themes_not_install_datas' => $themes_not_install_datas,
            'local_themes' => []
        ]);
    }

    //主题安装
    function themeInstall() {
        $request = \request();
        try {
            $res = hook('Install', ['moduleName' => 'System', 'cloud_type' => \Modules\Main\Models\Modules::Theme])[0];
        } catch (\Exception $exception) {
            $res = returnArr(0, $exception->getMessage());
        }
        if ($res['data']['url']) {
            return redirect($res['data']['url'])->with('successmsg', $res['msg']);
        } else {
            if ($request->return_type == 'api') {
                return $res;
            } else {
                return back()->with($res['status'] == 200 ? 'successmsg' : 'errormsg', $res['msg']);
            }
        }
    }

    //主题卸载
    function themeUninstall() {
        $request = \request();
        try {
            $res = hook('UnInstall', ['moduleName' => 'System', 'cloud_type' => \Modules\Main\Models\Modules::Theme])[0];
        } catch (\Exception $exception) {
            $res = returnArr(0, $exception->getMessage());
        }
        if ($res['data']['url']) {
            return redirect($res['data']['url'])->with('successmsg', $res['msg']);
        } else {
            if ($request->return_type == 'api') {
                return $res;
            } else {
                return back()->with($res['status'] == 200 ? 'successmsg' : 'errormsg', $res['msg']);
            }
        }
    }

    function changeThemeStatus() {
        $all = $this->request->all();
        if (!$all['m']) {
            StatisticReporter::reportBlocked('EnableBlocked', '', \Modules\Main\Models\Modules::Theme, 'identification_required');
            return back()->with('errormsg', '请选择主题');
        }
        //安装主题
        $checkData = DB::table("themes")->where("identification", $all['m'])->first();
        if (!$checkData) {
            StatisticReporter::reportBlocked('EnableBlocked', $all['m'], \Modules\Main\Models\Modules::Theme, 'not_installed');
            return back()->with('errormsg', '主题不存在！');
        }
        DB::table("themes")->update(['status' => 2]);
        $themeConfig['status'] = 1;
        $themeConfig['updated_at'] = date("Y-m-d H:i:s");
        $res = DB::table("themes")->where("identification", $all['m'])->update($themeConfig);
        if ($res) {
            cache()->forever('theme', $all['m']);
            StatisticReporter::reportSuccess('Enable', $all['m'], \Modules\Main\Models\Modules::Theme);
            return redirect("admin/theme")->with('successmsg', '主题启用成功！');
        }
        return back()->with("errormsg", "主题启用失败！");
    }

    function changeThemeTop(Request $request)
    {
        $get = $request->all();
        $get['m'] = isset($get['m']) ? trim((string) $get['m']) : '';
        $get['top'] = isset($get['top']) ? intval($get['top']) : 1;
        if ($get['m'] === '') {
            return back()->with('errormsg', '主题标识为必传项');
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('themes') || !\Illuminate\Support\Facades\Schema::hasColumn('themes', 'order')) {
            return back()->with('errormsg', '当前数据库不支持主题置顶，请先执行升级迁移');
        }

        $theme = DB::table('themes')->where('identification', $get['m'])->first();
        if (!$theme) {
            return back()->with('errormsg', '主题不存在！');
        }

        $order = $get['top'] === 1 ? 9999 : 0;
        $updated = DB::table('themes')->where('identification', $get['m'])->update([
            'order' => $order,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if ($updated) {
            return back()->with('successmsg', $get['top'] === 1 ? '已置顶' : '已取消置顶');
        }
        return back()->with('errormsg', '操作失败，重新再试!');
    }

    function setting() {
        // 主题列表页「进入配置」进的就是这里，是各主题管理页的总入口，
        // 导航交给 admin.func.themeSubNav 统一渲染，主题配置/更新槽位/短标签/钩子说明都从这里进。
        $themeIdentification = trim((string) request()->query('m', ''));
        if ($themeIdentification === '') {
            $themeIdentification = \App\Support\Theme\ThemeManager::current();
        }
        StatisticReporter::reportSuccess('Usage', $themeIdentification, \Modules\Main\Models\Modules::Theme, [
            'entry' => 'admin_theme_setting',
        ]);

        return view('admin/func/themeSetting', [
            'theme' => $themeIdentification,
            'active' => 'preview',
            'pageData' => ['title' => '前台预览', 'subtitle' => '前台预览'],
        ]);
    }

    function preview() {
        $all = $this->request->all();
        $theme = trim((string) ($all['m'] ?? '')) ?: ThemeManager::current();

        // 预览的可能是「未启用」的主题，必须把当前主题临时切过去。
        // 否则 @themeAsset() / @themeInclude() 仍按已启用主题的继承链解析，
        // 被预览主题的静态资源会全部 404，页面自然就乱了。
        $previousTheme = ThemeManager::previewTheme();
        ThemeManager::asTheme($theme);

        try {
            // 取菜单要带语言，与前台 CheckHome 中间件保持一致
            $menuLang = currentHomeLang();
            $topMenu = hook('GetHomeMenu', ['moduleName' => 'System', 'position' => 'top', 'lang' => $menuLang])[0];
            $bottomMenu = hook('GetHomeMenu', ['moduleName' => 'System', 'position' => 'bottom', 'lang' => $menuLang])[0];
            $footerMenu = hook('GetHomeMenu', ['moduleName' => 'System', 'position' => 'footer', 'lang' => $menuLang])[0];
            $models = FormModel::query()->where("show_home_page", "yes")->get();

            $homeMenu = [
                'topMenu' => $topMenu,
                'bottomMenu' => $bottomMenu,
                'footerMenu' => $footerMenu,
            ];

            // 必须 share：@themeInclude() 会创建一个独立的 view 实例，
            // 它只接收显式传入的数据，拿不到父视图的变量 ——
            // 只把 homeMenu 当视图数据传进去的话，菜单片段里是空的。
            view()->share([
                'homeMenu' => $homeMenu,
                'userInfo' => session(SessionKey::HomeInfo),
            ]);

            // 走 HomeView 让短标签也被解析；同时在这里就地渲染，
            // 保证渲染发生在预览主题生效期间（控制器 return 后 finally 就恢复了）。
            return HomeView('index.index', [
                'homeMenu' => $homeMenu,
                'models' => $models,
            ])->render();
        } finally {
            ThemeManager::asTheme($previousTheme);
        }
    }

    //模块安装
    function install(Request $request) {
        $cloudType = trim((string) ($request->cloud_type ?? ''));
        if (!in_array($cloudType, [
            \Modules\Main\Models\Modules::Module,
            \Modules\Main\Models\Modules::Plugin,
            \Modules\Main\Models\Modules::Theme,
        ], true)) {
            $cloudType = \Modules\Main\Models\Modules::Module;
        }
        try {
            $res = hook('Install', ['moduleName' => 'System', 'cloud_type' => $cloudType])[0];
        } catch (\Exception $exception) {
            $res = returnArr(0, $exception->getMessage());
        }
        if ($request->return_type == 'api') {
            return $res;
        }

        $redirectUrl = is_array($res['data'] ?? null) ? trim((string) ($res['data']['url'] ?? '')) : '';
        if ($redirectUrl !== '') {
            return redirect($redirectUrl)->with('successmsg', $res['msg']);
        }

        return back()->with(($res['status'] ?? 0) == 200 ? 'successmsg' : 'errormsg', $res['msg'] ?? '');
    }

    function recoverInstall(Request $request)
    {
        $cloudType = trim((string) ($request->cloud_type ?? ''));
        if (!in_array($cloudType, [
            \Modules\Main\Models\Modules::Module,
            \Modules\Main\Models\Modules::Plugin,
            \Modules\Main\Models\Modules::Theme,
        ], true)) {
            $cloudType = \Modules\Main\Models\Modules::Module;
        }

        try {
            if ($cloudType === \Modules\Main\Models\Modules::Theme) {
                $res = $this->recoverThemeInstallRecord($request);
            } else {
                $res = $this->recoverModulePluginInstallRecord($request, $cloudType);
            }
        } catch (\Throwable $exception) {
            $res = returnArr(0, $exception->getMessage());
        }

        if ($request->return_type == 'api') {
            return $res;
        }

        $redirectUrl = is_array($res['data'] ?? null) ? trim((string) ($res['data']['url'] ?? '')) : '';
        if ($redirectUrl !== '') {
            return redirect($redirectUrl)->with(($res['status'] ?? 0) == 200 ? 'successmsg' : 'errormsg', $res['msg'] ?? '');
        }

        return back()->with(($res['status'] ?? 0) == 200 ? 'successmsg' : 'errormsg', $res['msg'] ?? '');
    }

    private function recoverModulePluginInstallRecord(Request $request, string $cloudType): array
    {
        $identification = trim((string) ($request->m ?? ''));
        if ($identification === '') {
            return returnArr(0, '标识不能为空');
        }

        $manifest = PackageManifest::load($identification, $cloudType);
        if (!$manifest) {
            return returnArr(0, '本地包配置不存在，请先确认文件完整');
        }

        $resolvedIdentification = trim((string) ($manifest['identification'] ?? $identification));
        $directory = $this->resolveExistingPackageDirectory($manifest, $cloudType, $identification, $resolvedIdentification);
        $packagePath = $this->resolveLocalPackagePath($cloudType, $directory);
        if ($directory === '' || !is_dir($packagePath)) {
            return returnArr(0, '本地包目录不存在，无法恢复安装记录');
        }

        if ($cloudType === Modules::Module && $this->shouldSkipCoreModuleInstallRecord($resolvedIdentification)) {
            return returnArr(200, $resolvedIdentification . ' 为系统内置模块，默认加载，无需恢复安装记录。', ['url' => url('admin/module')]);
        }

        $module = new Modules();
        $exists = $module->where('identification', '=', $resolvedIdentification)
            ->where('cloud_type', '=', $cloudType)
            ->first();
        if ($exists) {
            return returnArr(0, '当前包已存在安装记录，无需恢复');
        }

        $now = date('Y-m-d H:i:s');
        $insertData = [
            'name' => trim((string) ($manifest['name'] ?? $resolvedIdentification)) ?: $resolvedIdentification,
            'identification' => $resolvedIdentification,
            'cloud_type' => $cloudType,
            'type' => trim((string) ($manifest['type'] ?? 'function')) ?: 'function',
            'domain' => trim((string) ($manifest['ui']['domain'] ?? $manifest['domain'] ?? 'n')) ?: 'n',
            'status' => 1,
            'form' => trim((string) ($request->form ?? 'recover')) ?: 'recover',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (!$module->insert($insertData)) {
            return returnArr(0, '恢复安装记录失败，请稍后再试');
        }

        $this->refreshRecoveredPackageRuntimeState($cloudType, $directory, $resolvedIdentification, $manifest);
        StatisticReporter::reportSuccess('RecoverInstall', $resolvedIdentification, $cloudType, [
            'source' => $insertData['form'],
            'directory' => $directory,
        ]);

        $url = $cloudType === Modules::Plugin ? url('admin/plugin') : url('admin/module');
        return returnArr(200, $insertData['name'] . '恢复安装成功；仅补充安装记录，未执行安装流程。', ['url' => $url]);
    }

    private function recoverThemeInstallRecord(Request $request): array
    {
        $identification = trim((string) ($request->m ?? ''));
        if ($identification === '') {
            return returnArr(0, '主题标识不能为空');
        }

        $manifest = PackageManifest::load($identification, Modules::Theme);
        if (!$manifest) {
            return returnArr(0, '主题配置不存在，请先确认文件完整');
        }

        $resolvedIdentification = trim((string) ($manifest['identification'] ?? $identification));
        $directory = $this->resolveExistingPackageDirectory($manifest, Modules::Theme, $identification, $resolvedIdentification);
        $themePath = $this->resolveLocalPackagePath(Modules::Theme, $directory);
        if ($directory === '' || !is_dir($themePath)) {
            return returnArr(0, '本地主题目录不存在，无法恢复安装记录');
        }

        $exists = DB::table('themes')->where('identification', '=', $resolvedIdentification)->first();
        if ($exists) {
            return returnArr(0, '当前主题已存在安装记录，无需恢复');
        }

        $now = date('Y-m-d H:i:s');
        $insertData = [
            'name' => trim((string) ($manifest['name'] ?? $resolvedIdentification)) ?: $resolvedIdentification,
            'identification' => $resolvedIdentification,
            'preview' => trim((string) ($manifest['preview'] ?? 'preview.png')) ?: 'preview.png',
            'status' => 2,
            'form' => trim((string) ($request->form ?? 'recover')) ?: 'recover',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (!DB::table('themes')->insert($insertData)) {
            return returnArr(0, '恢复主题安装记录失败，请稍后再试');
        }

        StatisticReporter::reportSuccess('RecoverInstall', $resolvedIdentification, Modules::Theme, [
            'source' => $insertData['form'],
            'directory' => $directory,
        ]);

        return returnArr(200, $insertData['name'] . '恢复安装成功；仅补充安装记录，未执行安装流程。', ['url' => url('admin/theme')]);
    }

    private function resolvePackageDirectoryName(array $manifest, string $cloudType, string $fallbackIdentification): string
    {
        $directory = trim((string) ($manifest['directory'] ?? ''));
        if ($directory !== '') {
            return $directory;
        }

        return package_directory_name($fallbackIdentification, $cloudType);
    }

    private function shouldSkipCoreModuleInstallRecord(string $identification): bool
    {
        return in_array(trim($identification), ['Main', 'Install'], true);
    }

    private function resolveExistingPackageDirectory(array $manifest, string $cloudType, string $requestedIdentification, string $resolvedIdentification): string
    {
        $candidates = array_values(array_unique(array_filter([
            trim((string) ($manifest['directory'] ?? '')),
            $resolvedIdentification,
            trim($requestedIdentification),
        ])));

        foreach ($candidates as $candidate) {
            $resolved = $this->findLocalPackageDirectory($cloudType, $candidate);
            if ($resolved !== '') {
                return $resolved;
            }
        }

        $matchedByManifest = $this->findLocalPackageDirectoryByManifest($cloudType, $resolvedIdentification, trim((string) ($manifest['directory'] ?? '')));
        if ($matchedByManifest !== '') {
            return $matchedByManifest;
        }

        return $this->resolvePackageDirectoryName($manifest, $cloudType, $resolvedIdentification);
    }

    private function findLocalPackageDirectory(string $cloudType, string $candidate): string
    {
        $candidate = trim($candidate);
        if ($candidate === '') {
            return '';
        }

        $root = $this->resolveLocalPackagePath($cloudType, '');
        if (!is_dir($root)) {
            return '';
        }

        foreach (scandir($root) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (!is_dir($root . DIRECTORY_SEPARATOR . $entry)) {
                continue;
            }
            if (strcasecmp($entry, $candidate) === 0) {
                return $entry;
            }
        }

        return '';
    }

    private function findLocalPackageDirectoryByManifest(string $cloudType, string $identification, string $directory): string
    {
        $identification = trim($identification);
        $directory = trim($directory);
        $root = $this->resolveLocalPackagePath($cloudType, '');
        if (!is_dir($root)) {
            return '';
        }

        foreach (scandir($root) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (!is_dir($root . DIRECTORY_SEPARATOR . $entry)) {
                continue;
            }

            $entryManifest = PackageManifest::load($entry, $cloudType);
            if (!$entryManifest) {
                continue;
            }

            $entryIdentification = trim((string) ($entryManifest['identification'] ?? ''));
            $entryDirectory = trim((string) ($entryManifest['directory'] ?? ''));
            if (($identification !== '' && strcasecmp($entryIdentification, $identification) === 0)
                || ($directory !== '' && strcasecmp($entryDirectory, $directory) === 0)) {
                return $entry;
            }
        }

        return '';
    }

    private function resolveLocalPackagePath(string $cloudType, string $directory): string
    {
        switch ($cloudType) {
            case Modules::Plugin:
                return plugins_base_path($directory);
            case Modules::Theme:
                return THEME_PATH . '/' . trim($directory, '/\\');
            case Modules::Module:
            default:
                return modules_base_path($directory);
        }
    }

    private function refreshRecoveredPackageRuntimeState(string $cloudType, string $directory, string $identification, array $manifest): void
    {
        $packageBasePath = [
            Modules::Module => modules_base_path(),
            Modules::Plugin => plugins_base_path(),
        ];
        if (!isset($packageBasePath[$cloudType])) {
            return;
        }

        $lockFile = $packageBasePath[$cloudType] . '/' . $directory . '/lock';
        @chmod($lockFile, 0777);
        @unlink($lockFile);
        $handle = @fopen($lockFile, 'w+');
        if (is_resource($handle)) {
            fwrite($handle, date('Y-m-d H:i:s'));
            fclose($handle);
        }

        try {
            PackageLinkManager::syncManagedLinks($manifest);
        } catch (\Throwable $exception) {
        }
    }

    //卸载
    function uninstall(Request $request) {
        try {
            $res = hook('UnInstall', ['moduleName' => 'System', 'cloud_type' => \Modules\Main\Models\Modules::Module])[0];
        } catch (\Exception $exception) {
            $res = returnArr(0, $exception->getMessage());
        }
        if ($res['data']['url']) {
            return redirect($res['data']['url'])->with('successmsg', $res['msg']);
        } else {
            if ($request->return_type == 'api') {
                return $res;
            } else {
                return back()->with($res['status'] == 200 ? 'successmsg' : 'errormsg', $res['msg']);
            }
        }
    }

    //模块的启用与禁用操作流程处理
    function changeStatus(Request $request) {
        $get = $request->all();
        $get['m'] = isset($get['m']) ? $get['m'] : '';
        $get['status'] = isset($get['status']) ? $get['status'] : 1;
        $get['cloud_type'] = isset($get['cloud_type']) ? $get['cloud_type'] : '';
        if (!$get['m']) {
            StatisticReporter::reportBlocked('StatusChangeBlocked', '', $get['cloud_type'], 'identification_required');
            return back()->with('errormsg', '标识为必传项');
        }

        //判断是否安装
        $module = new \Modules\Main\Models\Modules();
        $find = $module->where('identification', '=', $get['m'])->where('cloud_type', '=', $get['cloud_type'])->first();
        if (!$find) {
            StatisticReporter::reportBlocked('StatusChangeBlocked', $get['m'], $get['cloud_type'], 'not_installed');
            return back()->with('errormsg', '未安装!');
        }
        $manifest = PackageManifest::load($get['m'], $get['cloud_type'])
            ?: PackageManifest::normalize(['identification' => $get['m'], 'name' => $get['m']], $get['cloud_type'], $get['m']);
        $successNote = '';
        if ((int) $get['status'] !== 1) {
            $disablePolicy = LifecyclePolicy::canDisable($manifest);
            if (!$disablePolicy['allowed']) {
                StatisticReporter::reportBlocked('DisableBlocked', $get['m'], $get['cloud_type'], $disablePolicy['message']);
                return back()->with('errormsg', $disablePolicy['message']);
            }
            $dependents = CompatibilityChecker::findDependents($manifest);
            if (!empty($dependents)) {
                StatisticReporter::reportBlocked('DisableBlocked', $get['m'], $get['cloud_type'], 'has_dependents', [
                    'dependents' => implode(',', $dependents),
                ]);
                return back()->with('errormsg', '「' . implode('、', $dependents) . '」正在使用它，无法停用，请先停用对方');
            }
            $dataPolicy = DataPolicyManager::resolve($manifest, DataPolicyManager::STAGE_DISABLE);
            if (!$dataPolicy['allowed']) {
                StatisticReporter::reportBlocked('DisableBlocked', $get['m'], $get['cloud_type'], $dataPolicy['message']);
                return back()
                    ->with('errormsg', $dataPolicy['message'])
                    ->with('datapolicy_detail', $dataPolicy['detail']);
            }
            $recordFile = null;
            if (($dataPolicy['policy'] ?? '') !== DataPolicyManager::POLICY_KEEP) {
                $recordFile = DataPolicyManager::record($manifest, DataPolicyManager::STAGE_DISABLE, [
                    'status' => 0,
                ]);
            }
            PackageLinkManager::removeManagedLinks($manifest);
            $successNote = DataPolicyManager::buildSuccessNote(
                $manifest,
                DataPolicyManager::STAGE_DISABLE,
                [
                    'performed' => false,
                    'summary' => ($dataPolicy['policy'] ?? '') === DataPolicyManager::POLICY_DELETE
                        ? '当前禁用动作暂无内建托管数据清理项'
                        : '',
                    'warnings' => [],
                ],
                $recordFile
            );
        }
        $res = $module->where('identification', '=', $get['m'])->where('cloud_type', '=', $get['cloud_type'])->update(['status' => $get['status']]);
        if ($res) {
            if ((int) $get['status'] === 1) {
                PackageLinkManager::syncManagedLinks($manifest);
            }
            StatisticReporter::reportSuccess((int) $get['status'] === 1 ? 'Enable' : 'Disable', $get['m'], $get['cloud_type']);
            $successMessage = '操作成功' . ($successNote ?: '') . '!';
            if ($get['cloud_type'] == \Modules\Main\Models\Modules::Plugin) {
                Cache::put(\Mxzcms\Modules\cache\CacheKey::PluginsActive,
                    $this->mergeActivePackageCache(\Mxzcms\Modules\cache\CacheKey::PluginsActive, $get['m'], $get['status']),
                    86400
                );
                return redirect(url('admin/plugin'))->with('successmsg', $successMessage);
            } else {
                Cache::put(\Mxzcms\Modules\cache\CacheKey::ModulesActive,
                    $this->mergeActivePackageCache(\Mxzcms\Modules\cache\CacheKey::ModulesActive, $get['m'], $get['status']),
                    86400
                );
                return redirect(url('admin/module'))->with('successmsg', $successMessage);
            }
        } else {
            return back()->with('errormsg', '操作失败，重新再试!');
        }
    }

    /**
     * 把启停结果合并回包缓存。
     *
     * 缓存可能因为过期、手动清理或首次启停而不存在对应键，
     * 直接 array_merge(null) 在 PHP8 下会抛 TypeError，这里统一兜底。
     */
    private function mergeActivePackageCache(string $cacheKey, string $identification, $status): array
    {
        $cached = \cache($cacheKey);
        $cached = is_array($cached) ? $cached : [];
        $key = strtolower(trim($identification));
        if ($key === '') {
            return $cached;
        }

        $cached[$key] = array_merge(
            (array) ($cached[$key] ?? []),
            [
                'identification' => $identification,
                'status' => $status,
            ]
        );

        return $cached;
    }

    //是否设置为网站首页模块
    function changeIndex(Request $request) {
        $get = $request->all();
        $get['m'] = isset($get['m']) ? $get['m'] : '';
        $get['is_index'] = isset($get['is_index']) ? $get['is_index'] : 1;
        if (!$get['m']) return back()->with('errormsg', '模块标识为必传项');
        if(cacheGlobalSettingsByKey('moduleHomeLock') =='lock'){
            return back()->with('errormsg', '操作失败，请先取消模块首页锁定!');
        }
        //判断是否安装
        $module = new \Modules\Main\Models\Modules();
        $res = $module->where('identification', '=', $get['m'])->where('cloud_type', \Modules\Main\Models\Modules::Module)->first();
        if (!$res) return back()->with('errormsg', '模块未安装!');
        $module->where('id', '>', 0)->where('identification', '<>', $get['m'])->where('cloud_type', \Modules\Main\Models\Modules::Module)->update(['is_index' => 0]);
        $res = $module->where('identification', '=', $get['m'])->where('cloud_type', \Modules\Main\Models\Modules::Module)->update(['is_index' => $get['is_index']]);
        if ($res) return back()->with('successmsg', '操作成功!');
        else return back()->with('errormsg', '操作失败，重新再试!');
    }

    function changeBack(Request $request) {
        $get = $request->all();
        $get['m'] = isset($get['m']) ? $get['m'] : '';
        $get['is_backend'] = isset($get['is_backend']) ? $get['is_backend'] : 1;
        if (!$get['m']) return back()->with('errormsg', '模块标识为必传项');
        //判断是否安装
        $module = new \Modules\Main\Models\Modules();
        $res = $module->where('identification', '=', $get['m'])->where('cloud_type', \Modules\Main\Models\Modules::Module)->first();
        if (!$res) return back()->with('errormsg', '模块未安装!');
        $module->where('id', '>', 0)->where('identification', '<>', $get['m'])->where('cloud_type', \Modules\Main\Models\Modules::Module)->update(['is_backend' => 0]);
        $res = $module->where('identification', '=', $get['m'])->where('cloud_type', \Modules\Main\Models\Modules::Module)->update(['is_backend' => $get['is_backend']]);
        if ($res) return back()->with('successmsg', '操作成功!');
        else return back()->with('errormsg', '操作失败，重新再试!');
    }

    function changeTop(Request $request)
    {
        $get = $request->all();
        $get['m'] = isset($get['m']) ? trim((string) $get['m']) : '';
        $get['top'] = isset($get['top']) ? intval($get['top']) : 1;
        $get['cloud_type'] = isset($get['cloud_type']) ? trim((string) $get['cloud_type']) : \Modules\Main\Models\Modules::Module;
        if ($get['m'] === '') {
            return back()->with('errormsg', '模块标识为必传项');
        }
        if (!in_array($get['cloud_type'], [\Modules\Main\Models\Modules::Module, \Modules\Main\Models\Modules::Plugin], true)) {
            return back()->with('errormsg', 'cloud_type 参数错误');
        }

        $module = new \Modules\Main\Models\Modules();
        $res = $module->where('identification', '=', $get['m'])
            ->where('cloud_type', $get['cloud_type'])
            ->first();
        if (!$res) {
            return back()->with('errormsg', '模块未安装!');
        }

        $order = $get['top'] === 1 ? self::TOP_ORDER_THRESHOLD : 0;
        $updated = $module->where('identification', '=', $get['m'])
            ->where('cloud_type', $get['cloud_type'])
            ->update(['order' => $order]);

        if ($updated) {
            return back()->with('successmsg', $get['top'] === 1 ? '已置顶' : '已取消置顶');
        }

        return back()->with('errormsg', '操作失败，重新再试!');
    }

    //下载模块
    function onlineCloudList() {
        if (cacheGlobalSettingsByKey('use_of_cloud') != 1) return back();
        $all = request()->all();
        $all['cloud_type'] = trim((string) ($all['cloud_type'] ?? 'module')) ?: 'module';
        $all['index'] = max(0, intval($all['index'] ?? 0));
        switch ($all['cloud_type']) {
            case 'module':
                $view = 'admin/online/module';
                $local_data = [];
                $localDirs = getDirContent(modules_base_path()) ?: [];
                foreach ($localDirs as $dir) {
                    $config = PackageManifest::load($dir, Modules::Module);
                    if (!$config) {
                        continue;
                    }
                    $identification = trim((string) ($config['identification'] ?? $dir));
                    $version = (string) ($config['version'] ?? '');
                    foreach (array_values(array_unique([$identification, strtolower($identification)])) as $key) {
                        if ($key === '' || isset($local_data[$key])) {
                            continue;
                        }
                        $local_data[$key] = [
                            'version' => $version,
                            'directory' => $dir,
                        ];
                    }
                }
                break;
            case 'plugin':
                $local_data = [];
                $localDirs = getDirContent(plugins_base_path()) ?: [];
                foreach ($localDirs as $dir) {
                    $config = PackageManifest::load($dir, Modules::Plugin);
                    if (!$config) {
                        continue;
                    }
                    $identification = trim((string) ($config['identification'] ?? $dir));
                    $version = (string) ($config['version'] ?? '');
                    foreach (array_values(array_unique([$identification, strtolower($identification)])) as $key) {
                        if ($key === '' || isset($local_data[$key])) {
                            continue;
                        }
                        $local_data[$key] = [
                            'version' => $version,
                            'directory' => $dir,
                        ];
                    }
                }
                $view = 'admin/online/plugin';
                break;
            case 'theme':
                $local_data = [];
                $localDirs = getDirContent(public_path("views/themes")) ?: [];
                foreach ($localDirs as $dir) {
                    $config = PackageManifest::load($dir, Modules::Theme);
                    if (!$config) {
                        continue;
                    }
                    $identification = trim((string) ($config['identification'] ?? $dir));
                    $directory = trim((string) ($config['directory'] ?? $dir));
                    $version = (string) ($config['version'] ?? '');
                    $keys = [
                        $identification,
                        strtolower($identification),
                        strtoupper($identification),
                        ucfirst(strtolower($identification)),
                        $directory,
                        strtolower($directory),
                        strtoupper($directory),
                        ucfirst(strtolower($directory)),
                    ];
                    foreach (array_values(array_unique($keys)) as $key) {
                        if ($key === '' || isset($local_data[$key])) {
                            continue;
                        }
                        $local_data[$key] = [
                            'version' => $version,
                            'directory' => $dir,
                        ];
                    }
                }
                $view = 'admin/online/theme';
                break;
            default:
                returnArr(0, '参数错误');
        }
        $params = [
            'cloudtype' => $all['cloud_type'],
            'action' => 'get-app-list',
            'page' => $all['page'] ?: 1,
            'limit' => $all['limit'] ?: 12,
            'search' => $all['search'] ?: '',
            'cate_pid' => intval($all['cate_pid'] ?? 0),
            'cate_id' => intval($all['cate_id'] ?? 0),
            'platform' => trim((string) ($all['platform'] ?? '')),
            'isfree' => trim((string) ($all['isfree'] ?? '')),
            'sort' => trim((string) ($all['sort'] ?? '')),
            'order' => $all['order'] ?: 'id',
            'by' => $all['by'] ?: 'desc'
        ];

        $UPDATECMS = new \Modules\Main\Libs\UPDATECMS();
        $res = $UPDATECMS->appAction($params);
        $res = json_decode($res, true);
        if ($res['status'] != 200) {
            return back()->with('errormsg', "获取云端数据失败");
        }

        $listDatas = $res['data'];
        if (!empty($listDatas['list']['data']) && is_array($listDatas['list']['data'])) {
            $metaService = app(UpdateMetaService::class);
            foreach ($listDatas['list']['data'] as $index => $item) {
                if (is_array($item)) {
                    $metaService->cacheAppMeta([
                        'cloudtype' => $all['cloud_type'],
                        'identification' => $item['identification'] ?? '',
                    ], $item);
                }
                $listDatas['list']['data'][$index] = $this->decorateCloudPackage($item, $all['cloud_type']);
            }
        }
        StatisticReporter::reportSuccess('Usage', ($all['cloud_type'] ?? 'unknown') . '-market', $all['cloud_type'] ?? 'unknown', [
            'entry' => 'admin_online_cloud',
            'page' => $params['page'],
            'limit' => $params['limit'],
            'search' => $params['search'],
            'order' => $params['order'],
            'by' => $params['by'],
        ]);

        return view($view, [
            'listDatas' => $listDatas,
            'local_data' => $local_data,
            'currentFilters' => $all,
        ]);
    }

    private function decorateInstalledPackage(array $package, string $packageType): array
    {
        $disablePolicy = LifecyclePolicy::canDisable($package);
        $uninstallPolicy = LifecyclePolicy::canUninstall($package);
        $disableDataPolicy = DataPolicyManager::resolve($package, DataPolicyManager::STAGE_DISABLE);
        $uninstallDataPolicy = DataPolicyManager::resolve($package, DataPolicyManager::STAGE_UNINSTALL);
        $dependents = CompatibilityChecker::findDependents($package);
        $dependencyNames = collect($package['dependencies'] ?? [])
            ->map(function ($dependency) {
                $type = ($dependency['type'] ?? '') === Modules::Plugin ? '插件' : '模块';
                $name = $dependency['name'] ?? '';
                $version = $dependency['version'] ?? '*';
                return trim($name . ' ' . $type . ($version && $version !== '*' ? ' ' . $version : ''));
            })
            ->filter()
            ->values()
            ->toArray();

        $disableReason = '';
        if (!$disablePolicy['allowed']) {
            $disableReason = $disablePolicy['message'];
        } elseif (!$disableDataPolicy['allowed']) {
            $disableReason = $disableDataPolicy['message'];
        } elseif (!empty($dependents)) {
            $disableReason = '「' . implode('、', $dependents) . '」正在使用它，请先停用对方再停用本项';
        }

        $uninstallReason = '';
        if (!$uninstallPolicy['allowed']) {
            $uninstallReason = $uninstallPolicy['message'];
        } elseif (!$uninstallDataPolicy['allowed']) {
            $uninstallReason = $uninstallDataPolicy['message'];
        } elseif (!empty($dependents)) {
            $uninstallReason = '「' . implode('、', $dependents) . '」正在使用它，请先卸载对方再卸载本项';
        }

        $package['level_label'] = self::LEVEL_LABELS[$package['level'] ?? ''] ?? '未分级';
        $package['disable_allowed'] = $disableReason === '';
        $package['disable_reason'] = $disableReason;
        $package['uninstall_allowed'] = $uninstallReason === '';
        $package['uninstall_reason'] = $uninstallReason;
        $package['dependents'] = $dependents;
        $package['dependency_names'] = $dependencyNames;
        $package['compatibility_summary'] = $this->buildCompatibilitySummary($package);
        $package['disable_help'] = DataPolicyManager::help($package, DataPolicyManager::STAGE_DISABLE);
        $package['uninstall_help'] = DataPolicyManager::help($package, DataPolicyManager::STAGE_UNINSTALL);
        $package['package_type_label'] = $packageType === Modules::Plugin
            ? '插件'
            : (($package['type'] ?? '') === 'system' ? '内置模块' : '功能模块');

        return $package;
    }

    private function decorateLocalPackage(array $package): array
    {
        $package['level_label'] = self::LEVEL_LABELS[$package['level'] ?? ''] ?? '未分级';
        $package['dependency_names'] = collect($package['dependencies'] ?? [])
            ->map(function ($dependency) {
                $type = ($dependency['type'] ?? '') === Modules::Plugin ? '插件' : '模块';
                $name = $dependency['name'] ?? '';
                $version = $dependency['version'] ?? '*';
                return trim($name . ' ' . $type . ($version && $version !== '*' ? ' ' . $version : ''));
            })
            ->filter()
            ->values()
            ->toArray();
        $package['compatibility_summary'] = $this->buildCompatibilitySummary($package);
        $package = $this->decorateInstallablePackage($package);
        $package = $this->decorateDeleteablePackage($package);

        return $package;
    }

    private function decorateInstallablePackage(array $package): array
    {
        $installReason = CompatibilityChecker::checkInstallable($package);

        $package['install_allowed'] = $installReason === null;
        $package['install_reason'] = $installReason ?: '';

        return $package;
    }

    private function decorateDeleteablePackage(array $package): array
    {
        $deletePolicy = LifecyclePolicy::canDelete($package);
        $deleteDataPolicy = DataPolicyManager::resolve($package, DataPolicyManager::STAGE_DELETE);
        $deleteReason = '';
        if (!$deletePolicy['allowed']) {
            $deleteReason = $deletePolicy['message'];
        } elseif (!$deleteDataPolicy['allowed']) {
            $deleteReason = $deleteDataPolicy['message'];
        }

        $package['delete_allowed'] = $deleteReason === '';
        $package['delete_reason'] = $deleteReason;
        $package['delete_help'] = DataPolicyManager::help($package, DataPolicyManager::STAGE_DELETE);

        return $package;
    }

    private function decorateInstalledTheme(object $theme, array $manifest): object
    {
        $theme->name = $manifest['name'] ?? $theme->identification;
        $theme->description = $manifest['description'] ?? '';
        $theme->author = $manifest['author'] ?? '';
        $theme->version = $manifest['version'] ?? '';
        $theme->preview = $manifest['preview'] ?? ($theme->preview ?? 'preview.png');
        $theme->level = $manifest['level'] ?? PackageManifest::LEVEL_EXTENSION;
        $theme->level_label = self::LEVEL_LABELS[$theme->level] ?? '未分级';
        $theme->dependency_names = collect($manifest['dependencies'] ?? [])
            ->map(function ($dependency) {
                $type = ($dependency['type'] ?? '') === Modules::Plugin ? '插件' : '模块';
                $name = $dependency['name'] ?? '';
                $version = $dependency['version'] ?? '*';
                return trim($name . ' ' . $type . ($version && $version !== '*' ? ' ' . $version : ''));
            })
            ->filter()
            ->values()
            ->toArray();
        $theme->compatibility_summary = $this->buildCompatibilitySummary($manifest);

        $uninstallPolicy = LifecyclePolicy::canUninstall($manifest);
        $uninstallDataPolicy = DataPolicyManager::resolve($manifest, DataPolicyManager::STAGE_UNINSTALL);
        $theme->uninstall_allowed = $uninstallPolicy['allowed'] && $uninstallDataPolicy['allowed'] && (int) $theme->status !== 1;
        if (!$uninstallPolicy['allowed']) {
            $theme->uninstall_reason = $uninstallPolicy['message'];
        } elseif (!$uninstallDataPolicy['allowed']) {
            $theme->uninstall_reason = $uninstallDataPolicy['message'];
        } elseif ((int) $theme->status === 1) {
            $theme->uninstall_reason = '当前主题正在使用中，不能卸载';
        } else {
            $theme->uninstall_reason = '';
        }
        $theme->uninstall_help = DataPolicyManager::help($manifest, DataPolicyManager::STAGE_UNINSTALL);

        return $theme;
    }


    private function decorateCloudPackage(array $package, string $packageType): array
    {
        $normalized = PackageManifest::normalize($package, $packageType, $package['identification'] ?? '');
        $normalized['level_label'] = self::LEVEL_LABELS[$normalized['level'] ?? ''] ?? '未分级';
        $normalized['compatibility_summary'] = $this->buildCompatibilitySummary($normalized);
        $normalized['dependency_names'] = collect($normalized['dependencies'] ?? [])
            ->map(function ($dependency) {
                $type = ($dependency['type'] ?? '') === Modules::Plugin ? '插件' : '模块';
                $name = $dependency['name'] ?? '';
                $version = $dependency['version'] ?? '*';
                return trim($name . ' ' . $type . ($version && $version !== '*' ? ' ' . $version : ''));
            })
            ->filter()
            ->values()
            ->toArray();
        $normalized = $this->decorateInstallablePackage($normalized);
        $normalized = $this->decorateCloudDownloadPrompt($normalized);

        return $normalized;
    }

    private function decorateCloudDownloadPrompt(array $package): array
    {
        $installType = intval($package['install_type'] ?? 1);
        $price = floatval($package['price'] ?? 0);
        $prompt = trim((string) ($package['download_prompt'] ?? ''));

        $defaultPrompt = '';
        $promptTag = '';
        if ($installType === 2) {
            $promptTag = '私有化部署';
            $defaultPrompt = '该应用采用私有化部署方式交付，请先联系开发者确认授权、交付内容、部署环境和售后支持。';
        } elseif ($price > 0) {
            $promptTag = '收费资源';
            $defaultPrompt = '该应用为收费资源，请先联系开发者完成购买或授权，再继续下载和安装。';
        } elseif ($prompt !== '') {
            $promptTag = '下载提示';
        }

        if ($prompt === '' && $defaultPrompt !== '') {
            $prompt = '<p>' . e($defaultPrompt) . '</p>';
        }

        $package['download_prompt_html'] = $prompt;
        $package['download_prompt_title'] = $promptTag ?: '下载提示';
        $package['should_prompt_before_install'] = $prompt !== '';
        $package['prompt_allows_continue_download'] = $prompt !== '' && $installType !== 2 && $price <= 0;
        $package['prompt_requires_license_check'] = $prompt !== '' && $installType !== 2 && $price > 0;
        $package['prompt_continue_button_text'] = $price > 0 ? '校验授权后下载' : '继续下载';

        return $package;
    }
    private function buildCompatibilitySummary(array $package): string
    {
        $compatibility = $package['compatibility'] ?? [];
        $parts = [];
        foreach (['cms' => 'CMS', 'php' => 'PHP', 'laravel' => 'Laravel'] as $key => $label) {
            $value = $compatibility[$key] ?? '*';
            if ($value !== '' && $value !== '*') {
                $parts[] = $label . ':' . $value;
            }
        }

        return implode(' | ', $parts);
    }
}
