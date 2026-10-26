<?php

namespace Modules\System\Listeners;

use App\Support\PackageManifest\CompatibilityChecker;
use App\Support\PackageManifest\PackageManifest;
use App\Support\PackageManifest\PackageLinkManager;
use App\Support\Telemetry\StatisticReporter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Install {

    public function handle(\Modules\System\Events\Install $event) {
        //事件逻辑 ...
        $data = $event->data;
        $request = \request()->all();
        $request = array_merge($request,$data);
        $cloud_type = $request['cloud_type'];

        if ($cloud_type == \Modules\Main\Models\Modules::Plugin) {
            //插件
            $res = $this->modulePluginInstall();
        } elseif ($cloud_type == \Modules\Main\Models\Modules::Module) {
            //模块
            $res = $this->modulePluginInstall();
        } elseif ($cloud_type == \Modules\Main\Models\Modules::Theme) {
            //主题
            $res = $this->themesInstall();
        }

        return $res;
    }

    public function modulePluginInstall() {
        set_time_limit(0);
        $get = \request()->all();
        if (!$get['m']) return returnArr(0, '参数有误!');
        $manifest = PackageManifest::load($get['m'], $get['cloud_type']);
        if (!$manifest) {
            StatisticReporter::reportBlocked('InstallBlocked', $get['m'], $get['cloud_type'], 'manifest_missing');
            return returnArr(0, '配置不完整，请重新下载!');
        }
        $packageDirectory = $manifest['directory'] ?? package_directory_name($manifest['identification'], $get['cloud_type']);

        $module = new \Modules\Main\Models\Modules();
        $installableError = CompatibilityChecker::checkInstallable($manifest);
        if ($installableError) {
            StatisticReporter::reportBlocked('InstallBlocked', $manifest['identification'], $get['cloud_type'], $installableError);
            return returnArr(0, $installableError);
        }

        $skipInstallRecord = $this->shouldSkipModuleInstallRecord($manifest, $get['cloud_type']);
        if (!$skipInstallRecord) {
            $res = $module->where('identification', '=', $manifest['identification'])
                ->where("cloud_type", "=", $get['cloud_type'])
                ->first();
            if ($res) {
                StatisticReporter::reportBlocked('InstallBlocked', $manifest['identification'], $get['cloud_type'], 'already_installed');
                return returnArr(0, '请勿重复安装!');
            }

            $inster_modules_data['name'] = $manifest['name'];
            $inster_modules_data['identification'] = $manifest['identification'];
            $inster_modules_data['cloud_type'] = $get['cloud_type'];
            $inster_modules_data['type'] = $manifest['type'] ?? 'function';
            $inster_modules_data['domain'] = $manifest['ui']['domain'] ?? 'n';//默认没有绑定域名
            $inster_modules_data['status'] = 1;
            $inster_modules_data['form'] = $get['form'] ?? "cloud";
            // 内置模块（core/base 级）默认置顶：与后台「置顶」按钮共用 order 字段，>=9999 排最前
            $inster_modules_data['order'] = in_array($manifest['identification'] ?? '', ['System', 'Auth', 'Formtools', 'Member'], true) ? 9999 : 0;
            $inster_modules_data['created_at'] = $inster_modules_data['updated_at'] = date('Y-m-d H:i:s');
            $res = $module->insert($inster_modules_data);
        } else {
            $res = true;
        }

        if ($res) {
            if (!$skipInstallRecord) {
                $packageBasePath = [
                    \Modules\Main\Models\Modules::Module => modules_base_path(),
                    \Modules\Main\Models\Modules::Plugin => plugins_base_path(),
                ];
                $lock_file = $packageBasePath[$get['cloud_type']] . '/' . $packageDirectory . '/lock';
                @chmod($lock_file, 0777);
                @unlink($lock_file);
                $lock_stream = @fopen($lock_file, 'w+');//创建 锁文件
                if ($lock_stream !== false) {
                    fwrite($lock_stream, date('Y-m-d H:i:s'));//写入
                    fclose($lock_stream);
                }

                if (in_array($get['cloud_type'], [
                    \Modules\Main\Models\Modules::Module,
                    \Modules\Main\Models\Modules::Plugin,
                ], true)) {
                    PackageLinkManager::syncManagedLinks($manifest);
                }
            }

            if ($get['cloud_type'] == \Modules\Main\Models\Modules::Module) {
                try {

                    Artisan::call('migrate', [
                        '--path' => modules_relative_path($packageDirectory . '/Database/Migrations/install'),
                        '--force' => 1,
                    ]);
                    if (!$skipInstallRecord && $this->shouldRunModuleSeederOnInstall($manifest, $packageDirectory)) {
                        Artisan::call('db:seed', [
                            '--class' => "Modules\\".$manifest['identification']."\Database\Seeders\DatabaseSeeder",
                            '--force' => 1,
                        ]);
                    }

                }catch (\Exception $exception){

                }
            }

            if ($get['return_type'] == 'api') {
                StatisticReporter::reportSuccess('Install', $manifest['identification'], $get['cloud_type'], [
                    'source' => $get['form'] ?? 'cloud',
                    'directory' => $packageDirectory,
                    'skip_install_record' => $skipInstallRecord ? 1 : 0,
                ]);
                $message = $manifest['name'] . '安装成功；';
                if ($skipInstallRecord) {
                    $message .= '系统内置模块默认加载，已跳过 modules 表写入，仅执行迁移。';
                }
                return returnArr(200, $message);
            } else {
                StatisticReporter::reportSuccess('Install', $manifest['identification'], $get['cloud_type'], [
                    'source' => $get['form'] ?? 'cloud',
                    'directory' => $packageDirectory,
                    'skip_install_record' => $skipInstallRecord ? 1 : 0,
                ]);
                $message = $manifest['name'] . '安装成功；';
                if ($skipInstallRecord) {
                    $message .= '系统内置模块默认加载，已跳过 modules 表写入，仅执行迁移。';
                }
                if ($get['cloud_type'] == \Modules\Main\Models\Modules::Plugin) {
                    return returnArr(200, $message, ['url' => url('admin/plugin')]);
                } else {
                    return returnArr(200, $message, ['url' => url('admin/module')]);
                }
            }
        }
        return returnArr(0, '安装失败，重新再试!');
    }

    private function shouldSkipModuleInstallRecord(array $manifest, string $cloudType): bool
    {
        if ($cloudType !== \Modules\Main\Models\Modules::Module) {
            return false;
        }

        $identification = trim((string) ($manifest['identification'] ?? ''));
        return in_array($identification, ['Main', 'Install'], true);
    }

    private function shouldRunModuleSeederOnInstall(array $manifest, string $packageDirectory): bool
    {
        $seederDirectory = modules_base_path() . DIRECTORY_SEPARATOR . $packageDirectory . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Seeders';
        $databaseSeederPath = $seederDirectory . DIRECTORY_SEPARATOR . 'DatabaseSeeder.php';
        if (!is_file($databaseSeederPath)) {
            return false;
        }

        if (!$this->moduleSeederContainsDestructiveStatements($seederDirectory)) {
            return true;
        }

        $targetTables = $this->resolveModuleInstallMigrationTables($packageDirectory);
        if (empty($targetTables)) {
            return false;
        }

        foreach ($targetTables as $table) {
            if (\Schema::hasTable($table) && DB::table($table)->exists()) {
                return false;
            }
        }

        return true;
    }

    private function moduleSeederContainsDestructiveStatements(string $seederDirectory): bool
    {
        foreach (glob($seederDirectory . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
            $content = (string) @file_get_contents($file);
            if ($content === '') {
                continue;
            }

            if (preg_match('/->delete\s*\(|->truncate\s*\(|DB::statement\s*\(\s*[\'"]TRUNCATE/i', $content)) {
                return true;
            }
        }

        return false;
    }

    private function resolveModuleInstallMigrationTables(string $packageDirectory): array
    {
        $installDirectory = modules_base_path() . DIRECTORY_SEPARATOR . $packageDirectory . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Migrations' . DIRECTORY_SEPARATOR . 'install';
        $tables = [];
        foreach (glob($installDirectory . DIRECTORY_SEPARATOR . 'mxzcms_*.php') ?: [] as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            if (strpos($filename, 'mxzcms_') !== 0) {
                continue;
            }

            $table = substr($filename, 7);
            if ($table !== '') {
                $tables[] = $table;
            }
        }

        return array_values(array_unique($tables));
    }

    public function themesInstall() {
        $all = \request()->all();
        if (!$all['m']) {
            StatisticReporter::reportBlocked('InstallBlocked', '', \Modules\Main\Models\Modules::Theme, 'identification_required');
            return returnArr(0, '请选择主题');
        }
        //安装主题
        $checkData = DB::table("themes")->where("identification","=", $all['m'])->count();
        if( $checkData != 0 ) {
            StatisticReporter::reportBlocked('InstallBlocked', $all['m'], \Modules\Main\Models\Modules::Theme, 'already_installed');
            return returnArr(0, '主题已存在');
        }

        $themeConfig = PackageManifest::load($all['m'], \Modules\Main\Models\Modules::Theme);
        if (!$themeConfig) {
            StatisticReporter::reportBlocked('InstallBlocked', $all['m'], \Modules\Main\Models\Modules::Theme, 'manifest_missing');
            return returnArr(0, '主题配置不完整，请重新下载!');
        }

        $installableError = CompatibilityChecker::checkInstallable($themeConfig);
        if ($installableError) {
            StatisticReporter::reportBlocked('InstallBlocked', $themeConfig['identification'], \Modules\Main\Models\Modules::Theme, $installableError);
            return returnArr(0, $installableError);
        }

        $inster['name'] = $themeConfig['name'];
        $inster['identification'] = $all['m'];
        $inster['preview'] = $themeConfig['preview'];
        $inster['status'] = 2;
        $inster['form'] = $all['form'] ?? "cloud";
        $inster['created_at'] = $inster['updated_at'] = date("Y-m-d H:i:s");
        $res = DB::table("themes")->insert($inster);
        if ($res) {
            StatisticReporter::reportSuccess('Install', $themeConfig['identification'], \Modules\Main\Models\Modules::Theme, [
                'source' => $all['form'] ?? 'cloud',
            ]);
            return returnArr(200, '主题安装成功', ['url' => url("admin/theme")]);
        }
        return returnArr(0, '主题安装失败');
    }

}
