<?php

namespace App\Support\Telemetry;

use App\Support\PackageManifest\PackageManifest;

class StatisticReporter
{
    public static function report(string $action, string $identification, string $type, array $extra = []): void
    {
        if (!function_exists('hook')) {
            return;
        }

        if (!static::shouldReport($action, $identification, $type)) {
            return;
        }

        $payload = array_merge([
            'moduleName' => 'System',
            'action' => $action,
            'identification' => $identification,
            'type' => $type,
        ], $extra);

        try {
            hook('Statistic', $payload);
        } catch (\Throwable $throwable) {
        }
    }

    public static function reportBlocked(
        string $action,
        string $identification,
        string $type,
        string $reason,
        array $extra = []
    ): void {
        static::report($action, $identification, $type, array_merge([
            'result' => 'blocked',
            'reason' => $reason,
        ], $extra));
    }

    public static function reportSuccess(string $action, string $identification, string $type, array $extra = []): void
    {
        static::report($action, $identification, $type, array_merge([
            'result' => 'success',
        ], $extra));
    }

    public static function reportFailure(string $action, string $identification, string $type, string $reason, array $extra = []): void
    {
        static::report($action, $identification, $type, array_merge([
            'result' => 'failure',
            'reason' => $reason,
        ], $extra));
    }

    private static function shouldReport(string $action, string $identification, string $type): bool
    {
        $telemetryKey = static::resolveTelemetryKey($action);
        if ($telemetryKey === null) {
            return true;
        }

        if ($identification === '' || $type === '' || $type === 'cms') {
            return true;
        }

        if (!in_array($type, [
            PackageManifest::PACKAGE_MODULE,
            PackageManifest::PACKAGE_PLUGIN,
            PackageManifest::PACKAGE_THEME,
        ], true)) {
            return true;
        }

        $manifest = PackageManifest::load($identification, $type);
        if (!$manifest) {
            return true;
        }

        return (bool) ($manifest['telemetry'][$telemetryKey] ?? true);
    }

    private static function resolveTelemetryKey(string $action): ?string
    {
        if (strpos($action, 'Install') === 0) {
            return 'install';
        }

        if (strpos($action, 'UnInstall') === 0) {
            return 'uninstall';
        }

        if (strpos($action, 'Enable') === 0) {
            return 'enable';
        }

        if (strpos($action, 'Disable') === 0) {
            return 'disable';
        }

        if (strpos($action, 'Update') === 0) {
            return 'upgrade';
        }

        if (strpos($action, 'Usage') === 0) {
            return 'usage';
        }

        return null;
    }
}
