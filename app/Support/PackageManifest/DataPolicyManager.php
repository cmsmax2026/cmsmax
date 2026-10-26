<?php

namespace App\Support\PackageManifest;

use Illuminate\Support\Facades\Artisan;
use Modules\System\Models\Setting;

class DataPolicyManager
{
    public const POLICY_KEEP = 'keep';
    public const POLICY_BACKUP = 'backup';
    public const POLICY_DELETE = 'delete';
    public const POLICY_MANUAL = 'manual';

    public const STAGE_DISABLE = 'disable';
    public const STAGE_UNINSTALL = 'uninstall';
    public const STAGE_DELETE = 'delete';

    public static function resolve(array $manifest, string $stage): array
    {
        $stage = static::normalizeStage($stage);
        $policy = static::normalizePolicy($manifest['data_policy']['on_' . $stage] ?? null);
        $name = $manifest['name'] ?? ($manifest['identification'] ?? '当前包');

        return [
            'stage' => $stage,
            'policy' => $policy,
            'allowed' => $policy !== static::POLICY_MANUAL,
            'message' => $policy === static::POLICY_MANUAL
                ? static::manualSummary($manifest, $stage)
                : '',
            'detail' => $policy === static::POLICY_MANUAL
                ? static::manualDetail($manifest, $stage)
                : '',
        ];
    }

    /**
     * 手动处理策略被拦截时的简短提示（给 toast 用，避免 250px 小框塞一长串）。
     */
    private static function manualSummary(array $manifest, string $stage): string
    {
        $name = $manifest['name'] ?? ($manifest['identification'] ?? '当前包');

        return $name . ' 的' . static::stageLabel($stage) . '数据策略为「手动处理」，需先人工处理数据后才能继续（操作步骤见弹窗）。';
    }

    /**
     * 手动处理策略被拦截时的完整可操作指引（给弹窗用）。
     * 系统对 manual 策略不会自动触碰数据，也没有「我已处理完」的勾选入口，
     * 唯一放行方式是把包配置里的 data_policy.on_<stage> 由 manual 改为 keep/delete，
     * 因此这里必须把「处理什么 + 改哪个文件哪一行 + 改完怎么继续」一次说清，避免用户卡死。
     */
    private static function manualDetail(array $manifest, string $stage): string
    {
        $name = $manifest['name'] ?? ($manifest['identification'] ?? '当前包');
        $stageLabel = static::stageLabel($stage);
        $directory = $manifest['directory'] ?? ($manifest['identification'] ?? '');
        $packageType = $manifest['package_type'] ?? '';

        if ($packageType === 'plugin') {
            $configFile = 'plugins/' . $directory . '/Config/config.php';
        } elseif ($packageType === 'theme') {
            $configFile = 'public/views/themes/' . $directory . '/Config/config.php';
        } else {
            $configFile = 'modules/' . $directory . '/Config/config.php';
        }

        $key = 'on_' . $stage;

        return '处理步骤：<br>'
            . '1. 人工处理数据：清空 / 备份该模块相关业务数据表。<br>'
            . '2. 修改 <b>' . $configFile . '</b> 中 <b>data_policy.' . $key . '</b> 的值：<br>'
            . '&nbsp;&nbsp;· keep = 仅删除包、保留数据；<br>'
            . '&nbsp;&nbsp;· delete = 由系统回滚迁移并清理数据。<br>'
            . '3. 保存文件后，重新执行' . $stageLabel . '即可继续。';
    }

    public static function record(array $manifest, string $stage, array $extra = []): ?string
    {
        $stage = static::normalizeStage($stage);
        $payload = array_merge([
            'time' => date('c'),
            'identification' => $manifest['identification'] ?? '',
            'name' => $manifest['name'] ?? '',
            'package_type' => $manifest['package_type'] ?? '',
            'stage' => $stage,
            'policy' => static::resolve($manifest, $stage)['policy'],
        ], $extra);

        $dir = storage_path('manifest-lifecycle');
        if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
            return null;
        }

        $file = sprintf(
            '%s_%s_%s_%s.json',
            date('Ymd_His'),
            strtolower((string) ($manifest['package_type'] ?? 'package')),
            strtolower((string) ($manifest['identification'] ?? 'unknown')),
            $stage
        );

        $path = $dir . DIRECTORY_SEPARATOR . $file;
        @file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $file;
    }

    public static function executeManagedCleanup(array $manifest, string $stage): array
    {
        $stage = static::normalizeStage($stage);
        $policy = static::resolve($manifest, $stage)['policy'];

        if (!in_array($stage, [static::STAGE_UNINSTALL, static::STAGE_DELETE], true)) {
            return [
                'performed' => false,
                'summary' => '当前动作暂无内建托管数据清理项',
                'warnings' => [],
            ];
        }

        if (($manifest['package_type'] ?? '') !== PackageManifest::PACKAGE_MODULE) {
            return [
                'performed' => false,
                'summary' => '当前包类型暂无内建托管数据清理项',
                'warnings' => [],
            ];
        }

        $warnings = [];
        $actions = [];

        // 1) 业务数据表：严格按数据策略执行，只有 delete 策略才回滚 install 迁移。
        //    keep / backup 的语义是"保留业务数据"，用户可能还要留着做备份或迁移。
        if ($policy === static::POLICY_DELETE) {
            $packageDirectory = $manifest['directory'] ?? package_directory_name(
                $manifest['identification'] ?? '',
                $manifest['package_type'] ?? PackageManifest::PACKAGE_MODULE
            );
            $migrationPath = modules_base_path($packageDirectory . '/Database/Migrations/install');
            if (is_dir($migrationPath)) {
                try {
                    Artisan::call('migrate:rollback', [
                        '--path' => modules_relative_path($packageDirectory . '/Database/Migrations/install'),
                        '--force' => 1,
                        '--step' => 100000000,
                    ]);
                    $actions[] = 'install 迁移已回滚';
                } catch (\Throwable $throwable) {
                    $warnings[] = 'install 迁移回滚失败：' . $throwable->getMessage();
                }
            }
        }

        // 2) 模块配置（module_system_settings）：与业务表策略解耦，**卸载/删除时一律清理**。
        //    配置不是业务数据，包都卸载了还留着会污染共享表，且重装后会读到陈旧配置。
        // 默认按 identification 清理；模块若因历史原因用了别的 module 键，
        // 可在 Config/config.php 里声明 settings_module 覆盖。
        $settingsModuleKey = (string) ($manifest['settings_module'] ?? $manifest['identification'] ?? '');
        $settingsSummary = static::cleanupModuleSettings($settingsModuleKey);
        if ($settingsSummary !== null) {
            $actions[] = $settingsSummary;
        }

        if (empty($actions)) {
            $actions[] = '未发现可自动清理的托管数据';
        }

        return [
            'performed' => true,
            'summary' => implode('；', $actions),
            'warnings' => $warnings,
        ];
    }

    /**
     * 清理模块在共享配置表 module_system_settings 中的配置行。
     *
     * 约定：任何复用该表的模块，写入时 module 列必须填自己的 identification，
     * 卸载时才能被这里按 identification 精确清掉。返回给用户的摘要；
     * 无对应行时返回 null（不打扰用户）。
     */
    public static function cleanupModuleSettings(string $identification): ?string
    {
        $identification = trim($identification);
        if ($identification === '') {
            return null;
        }

        try {
            $deleted = Setting::query()->where('module', $identification)->delete();
        } catch (\Throwable $throwable) {
            return '模块设置清理失败：' . $throwable->getMessage();
        }

        return $deleted > 0 ? '模块设置已清理(' . $deleted . ')' : null;
    }

    public static function buildSuccessNote(array $manifest, string $stage, array $cleanup = [], ?string $recordFile = null): string
    {
        $plan = static::resolve($manifest, $stage);
        $policy = $plan['policy'];
        $parts = [];

        $summary = trim((string) ($cleanup['summary'] ?? ''));
        if ($policy === static::POLICY_KEEP) {
            $parts[] = '业务数据按策略保留';
            if (!empty($cleanup['performed']) && $summary !== '') {
                $parts[] = $summary;
            }
        } elseif ($policy === static::POLICY_BACKUP) {
            $parts[] = '业务数据按策略保留，请先完成备份';
            if (!empty($cleanup['performed']) && $summary !== '') {
                $parts[] = $summary;
            }
        } elseif ($policy === static::POLICY_DELETE) {
            $parts[] = $summary !== '' ? $summary : '已按策略执行托管数据清理';
        }

        if (!empty($cleanup['warnings'])) {
            $parts[] = '清理警告：' . implode('；', $cleanup['warnings']);
        }

        if ($recordFile) {
            $parts[] = '记录文件：storage/manifest-lifecycle/' . $recordFile;
        }

        return empty($parts) ? '' : '；' . implode('；', $parts);
    }

    private static function normalizeStage(string $stage): string
    {
        $stage = trim(strtolower($stage));
        if (in_array($stage, [static::STAGE_DISABLE, static::STAGE_UNINSTALL, static::STAGE_DELETE], true)) {
            return $stage;
        }

        return static::STAGE_DISABLE;
    }

    private static function normalizePolicy(?string $policy): string
    {
        $policy = trim(strtolower((string) $policy));
        if (in_array($policy, [
            static::POLICY_KEEP,
            static::POLICY_BACKUP,
            static::POLICY_DELETE,
            static::POLICY_MANUAL,
        ], true)) {
            return $policy;
        }

        return static::POLICY_KEEP;
    }

    private static function stageLabel(string $stage): string
    {
        switch ($stage) {
            case static::STAGE_UNINSTALL:
                return '卸载';
            case static::STAGE_DELETE:
                return '删除';
            default:
                return '禁用';
        }
    }

    /**
     * 给前台「?」帮助图标用的可读说明：对任意数据策略（含 manual）都返回一段 HTML。
     * 与 resolve() 的区别：resolve() 只在 manual 时返回拦截信息，这里无论什么策略都给说明，
     * 让用户在删除/禁用/卸载按钮旁就能预览系统会怎样处理数据。
     */
    public static function help(array $manifest, string $stage): string
    {
        $stage = static::normalizeStage($stage);
        $policy = static::normalizePolicy($manifest['data_policy']['on_' . $stage] ?? null);
        $name = $manifest['name'] ?? ($manifest['identification'] ?? '当前包');
        $stageLabel = static::stageLabel($stage);

        $intro = $name . ' 的' . $stageLabel . '数据策略：<b>' . $policy . '</b><br>';

        switch ($policy) {
            case static::POLICY_KEEP:
                $body = '该操作时系统<b>保留</b>业务数据，仅移除包文件与安装记录，数据表不会被删除。';
                break;
            case static::POLICY_BACKUP:
                $body = '系统保留数据，但建议你先手动备份相关业务数据表后再继续。';
                break;
            case static::POLICY_DELETE:
                $body = '系统自动回滚 install 迁移（drop 掉该模块的数据表）并清理模块设置。';
                break;
            case static::POLICY_MANUAL:
            default:
                $body = static::manualDetail($manifest, $stage);
                break;
        }

        return $intro . $body;
    }
}
