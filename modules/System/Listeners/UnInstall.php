<?php

namespace Modules\System\Listeners;

use App\Support\PackageManifest\DataPolicyManager;
use App\Support\PackageManifest\CompatibilityChecker;
use App\Support\PackageManifest\LifecyclePolicy;
use App\Support\PackageManifest\PackageManifest;
use App\Support\PackageManifest\PackageLinkManager;
use App\Support\Telemetry\StatisticReporter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Modules\System\Models\Setting;

class UnInstall {

    public function handle(\Modules\System\Events\UnInstall $event) {
        //事件逻辑 ...
        $request = array_merge($event->data,\request()->all());
        $cloud_type = $request['cloud_type'];

        if ($cloud_type == \Modules\Main\Models\Modules::Plugin) {
            //插件
            $res = $this->modulePluginUnInstall();
        } elseif ($cloud_type == \Modules\Main\Models\Modules::Module) {
            //模块
            $res = $this->modulePluginUnInstall();

        } elseif ($cloud_type == \Modules\Main\Models\Modules::Theme) {
            //主题
            $res = $this->themesUnInstall();
        }

        return $res;
    }

    public function modulePluginUnInstall() {
        $get = \request()->all();
        $module_name = isset($get['m']) ? $get['m'] : '';
        $get['cloud_type'] = isset($get['cloud_type']) ? $get['cloud_type'] : '';
        if (!$module_name) {
            StatisticReporter::reportBlocked('UnInstallBlocked', '', $get['cloud_type'], 'identification_required');
            return returnArr(0, '参数有误');
        }

        $manifest = PackageManifest::load($module_name, $get['cloud_type'])
            ?: PackageManifest::normalize(['identification' => $module_name, 'name' => $module_name], $get['cloud_type'], $module_name);
        $packageDirectory = $manifest['directory'] ?? package_directory_name($module_name, $get['cloud_type']);
        $uninstallPolicy = LifecyclePolicy::canUninstall($manifest);
        if (!$uninstallPolicy['allowed']) {
            StatisticReporter::reportBlocked('UnInstallBlocked', $module_name, $get['cloud_type'], $uninstallPolicy['message']);
            return returnArr(0, $uninstallPolicy['message']);
        }
        $dataPolicy = DataPolicyManager::resolve($manifest, DataPolicyManager::STAGE_UNINSTALL);
        if (!$dataPolicy['allowed']) {
            StatisticReporter::reportBlocked('UnInstallBlocked', $module_name, $get['cloud_type'], $dataPolicy['message']);
            return returnArr(0, $dataPolicy['message'], ['detail' => $dataPolicy['detail']]);
        }
        $recordFile = null;
        if (($dataPolicy['policy'] ?? '') !== DataPolicyManager::POLICY_KEEP) {
            $recordFile = DataPolicyManager::record($manifest, DataPolicyManager::STAGE_UNINSTALL, [
                'cloud_type' => $get['cloud_type'],
            ]);
        }
        $cleanup = DataPolicyManager::executeManagedCleanup($manifest, DataPolicyManager::STAGE_UNINSTALL);
        $deletePolicy = LifecyclePolicy::canDelete($manifest);

        //判断是否安装
        $module = new \Modules\Main\Models\Modules();

        $resData = $module->where('identification', '=', $module_name)->where('cloud_type', '=', $get['cloud_type'])->first();
        if (!$resData) {
            StatisticReporter::reportBlocked('UnInstallBlocked', $module_name, $get['cloud_type'], 'not_installed');
            return returnArr(0, '未安装');
        }
        if ($resData->status == 1) {
            StatisticReporter::reportBlocked('UnInstallBlocked', $module_name, $get['cloud_type'], 'package_enabled');
            return returnArr(0, '请先禁用，再进行‘卸载’操作');
        }

        $dependents = CompatibilityChecker::findDependents($manifest);
        if (!empty($dependents)) {
            StatisticReporter::reportBlocked('UnInstallBlocked', $module_name, $get['cloud_type'], 'has_dependents', [
                'dependents' => implode(',', $dependents),
            ]);
            return returnArr(0, '「' . implode('、', $dependents) . '」正在使用它，无法卸载，请先卸载对方');
        }

        if (is_file(module_path($get['m'], 'lock')) && $get['cloud_type'] == \Modules\Main\Models\Modules::Module) {
            StatisticReporter::reportBlocked('UnInstallBlocked', $module_name, $get['cloud_type'], 'lock_file_exists');
            return returnArr(0, '请先删除模块下的锁文件[lock]');
        }

        try {
            $res = $module->where('identification', '=', $module_name)->where('cloud_type', '=', $get['cloud_type'])->delete();
            if ($res) {
                PackageLinkManager::removeManagedLinks($manifest);

                if ($get['cloud_type'] == \Modules\Main\Models\Modules::Module) {
                    $deletePath = modules_base_path($packageDirectory);
                }

                if ($get['cloud_type'] == \Modules\Main\Models\Modules::Plugin) {
                    $deletePath = plugins_base_path($packageDirectory);
                }

                if ($resData->form =="cloud" && $deletePolicy['allowed']) {
                    del_dir_files($deletePath);
                }

                StatisticReporter::reportSuccess('UnInstall', $module_name, $get['cloud_type'], [
                    'source' => $resData->form ?? 'cloud',
                    'directory' => $packageDirectory,
                ]);
                $message = '卸载成功'
                    . (($resData->form =="cloud" && !$deletePolicy['allowed']) ? '；包目录按 deletable 策略保留' : '')
                    . DataPolicyManager::buildSuccessNote($manifest, DataPolicyManager::STAGE_UNINSTALL, $cleanup, $recordFile);
                $res = returnArr(200, $message);
                return $res;
            }
            return returnArr(0, '卸载失败，重新再试');

        } catch (\Exception $e) {
            return returnArr(0, '卸载失败，重新再试');
        }
    }

    public function themesUnInstall() {
        $all = \request()->all();
        if (!$all['m']) {
            StatisticReporter::reportBlocked('UnInstallBlocked', '', \Modules\Main\Models\Modules::Theme, 'identification_required');
            return returnArr(0, '请选择主题');
        }
        $themeManifest = PackageManifest::load($all['m'], \Modules\Main\Models\Modules::Theme)
            ?: PackageManifest::normalize(['identification' => $all['m'], 'name' => $all['m']], \Modules\Main\Models\Modules::Theme, $all['m']);
        $themeDataPolicy = DataPolicyManager::resolve($themeManifest, DataPolicyManager::STAGE_UNINSTALL);
        if (!$themeDataPolicy['allowed']) {
            StatisticReporter::reportBlocked('UnInstallBlocked', $all['m'], \Modules\Main\Models\Modules::Theme, $themeDataPolicy['message']);
            return returnArr(0, $themeDataPolicy['message']);
        }
        $themeRecordFile = null;
        if (($themeDataPolicy['policy'] ?? '') !== DataPolicyManager::POLICY_KEEP) {
            $themeRecordFile = DataPolicyManager::record($themeManifest, DataPolicyManager::STAGE_UNINSTALL);
        }
        $themeDeletePolicy = LifecyclePolicy::canDelete($themeManifest);
        $themeCleanup = DataPolicyManager::executeManagedCleanup($themeManifest, DataPolicyManager::STAGE_UNINSTALL);
        if ($all['m'] == "default") {
            StatisticReporter::reportBlocked('UnInstallBlocked', $all['m'], \Modules\Main\Models\Modules::Theme, 'default_theme_protected');
            return returnArr(0, '系统主题无法卸载');
        }


        //卸载主题
        $checkData = DB::table("themes")->where("identification", $all['m'])->first();
        if (!$checkData) {
            StatisticReporter::reportBlocked('UnInstallBlocked', $all['m'], \Modules\Main\Models\Modules::Theme, 'not_installed');
            return returnArr(0, '主题未安装');
        }
        if ($checkData->status == 1) {
            StatisticReporter::reportBlocked('UnInstallBlocked', $all['m'], \Modules\Main\Models\Modules::Theme, 'theme_in_use');
            return returnArr(0, '主题正在使用中，无法卸载');
        }

        $res = DB::table("themes")->where("identification", $all['m'])->delete();
        if ($res) {
            if ($checkData->form =="cloud" && $themeDeletePolicy['allowed']) {
                del_dir_files(THEME_PATH . '/' . $all['m']);
            }
            StatisticReporter::reportSuccess('UnInstall', $all['m'], \Modules\Main\Models\Modules::Theme, [
                'source' => $checkData->form ?? 'cloud',
            ]);
            $message = '主题卸载成功'
                . (($checkData->form =="cloud" && !$themeDeletePolicy['allowed']) ? '；包目录按 deletable 策略保留' : '')
                . DataPolicyManager::buildSuccessNote($themeManifest, DataPolicyManager::STAGE_UNINSTALL, $themeCleanup, $themeRecordFile);
            return returnArr(200, $message,['url'=>url("admin/theme")]);
        }
        return returnArr(0, '主题卸载失败');
    }

}
