<?php

namespace App\Support\Installer;

class InstallerInspector
{
    private const DEFAULT_PHP_CONSTRAINT = '^8.0.2';

    /**
     * Keep the legacy view structure for install step 2,
     * so we can refactor logic without rewriting the Blade first.
     */
    public static function inspectForLegacyView(): array
    {
        $phpVersion = (string) phpversion();
        $legacyExtensions = ExtensionInspector::inspectLegacy();
        $phpConstraint = static::getPhpConstraint();
        $minimumPhpVersion = static::extractMinimumPhpVersion($phpConstraint);

        return [
            'phpversion' => $phpVersion,
            'os' => PHP_OS,
            'phpversion_msg' => static::htmlStatus(
                version_compare($phpVersion, $minimumPhpVersion, '>='),
                installTrans('install_php_version_with_requirement', ['version' => $phpVersion, 'requirement' => $phpConstraint])
            ),
            'php_requirement' => $phpConstraint,
            'pdo' => $legacyExtensions['pdo'],
            'curl' => $legacyExtensions['curl'],
            'gd' => $legacyExtensions['gd'],
            'mbstring' => $legacyExtensions['mbstring'],
            'fileinfo' => $legacyExtensions['fileinfo'],
            'upload_size' => static::buildUploadStatus(),
            'session' => FunctionInspector::inspectLegacy()['session'],
            'always_populate_raw_post_data' => static::buildAlwaysPopulateRawPostDataStatus($phpVersion),
            'show_always_populate_raw_post_data_tip' => static::shouldShowAlwaysPopulateRawPostDataTip($phpVersion),
            'folders' => DirectoryInspector::inspectLegacy(),
            'database_drivers' => ExtensionInspector::databaseDrivers(),
            'checks' => static::inspectStructuredChecks($phpVersion),
        ];
    }

    public static function inspectStructuredChecks(?string $phpVersion = null): array
    {
        $phpVersion = $phpVersion ?: (string) phpversion();
        $phpConstraint = static::getPhpConstraint();
        $minimumPhpVersion = static::extractMinimumPhpVersion($phpConstraint);

        return [
            'environment' => [
                static::buildCheck('os', installTrans('install_os'), PHP_OS, installTrans('install_no_limit'), true),
                static::buildCheck('php_version', installTrans('install_php_version'), $phpVersion, $phpConstraint, version_compare($phpVersion, $minimumPhpVersion, '>=')),
            ],
            'extensions' => ExtensionInspector::inspectStructured(),
            'functions' => [
                ...FunctionInspector::inspectStructured(),
            ],
            'directories' => DirectoryInspector::inspectStructured(),
        ];
    }

    private static function buildCheck(string $key, string $title, $current, $expected, bool $passed, bool $required = true): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'current' => $current,
            'expected' => $expected,
            'required' => $required,
            'status' => $passed ? 'pass' : 'fail',
            'message' => installTrans($passed ? 'install_check_pass' : 'install_check_fail'),
            'risk_level' => $passed ? 'low' : ($required ? 'high' : 'medium'),
        ];
    }

    private static function buildUploadStatus(): string
    {
        if (!ini_get('file_uploads')) {
            return static::htmlStatus(false, installTrans('install_upload_forbidden'));
        }

        return static::htmlStatus(true, (string) ini_get('upload_max_filesize'));
    }

    private static function buildAlwaysPopulateRawPostDataStatus(string $phpVersion): string
    {
        $failed = version_compare($phpVersion, '8.0.0', '>=') &&
            version_compare($phpVersion, '8.2.2', '<') &&
            ini_get('always_populate_raw_post_data') != -1;

        return static::htmlStatus(!$failed, $failed ? installTrans('install_always_populate_not_closed') : installTrans('install_always_populate_closed'));
    }

    private static function shouldShowAlwaysPopulateRawPostDataTip(string $phpVersion): bool
    {
        return version_compare($phpVersion, '8.0.0', '>=') &&
            version_compare($phpVersion, '8.2.2', '<') &&
            ini_get('always_populate_raw_post_data') != -1;
    }

    private static function htmlStatus(bool $passed, string $message, bool $messageContainsIcon = false): string
    {
        $class = $passed ? 'check correct' : 'remove error';
        $icon = '<i class="fa fa-' . $class . '"></i> ';
        return $messageContainsIcon ? $icon . $message : $icon . e($message);
    }

    private static function getPhpConstraint(): string
    {
        $composerPath = base_path('composer.json');
        if (!is_file($composerPath)) {
            return static::DEFAULT_PHP_CONSTRAINT;
        }

        $decoded = json_decode((string) file_get_contents($composerPath), true);
        $constraint = (string) ($decoded['require']['php'] ?? '');

        return $constraint !== '' ? $constraint : static::DEFAULT_PHP_CONSTRAINT;
    }

    private static function extractMinimumPhpVersion(string $constraint): string
    {
        if (preg_match('/\d+\.\d+(?:\.\d+)?/', $constraint, $matches)) {
            return $matches[0];
        }

        return '8.0.2';
    }
}
