<?php

namespace App\Support\Installer;

class ExtensionInspector
{
    /**
     * 安装器支持的数据库类型元数据。
     * available 由当前环境已启用的 PHP 扩展实时判定，选择建议用于安装向导展示。
     */
    public static function databaseDrivers(): array
    {
        return [
            [
                'driver' => 'mysql',
                'label' => 'MySQL',
                'required_extensions' => 'pdo_mysql',
                'available' => extension_loaded('pdo_mysql'),
                'pros' => installTrans('install_db_mysql_pros'),
                'reason' => installTrans('install_db_mysql_reason'),
                'when' => installTrans('install_db_mysql_when'),
            ],
            [
                'driver' => 'sqlite',
                'label' => 'SQLite',
                'required_extensions' => 'pdo_sqlite + sqlite3',
                'available' => extension_loaded('pdo_sqlite') && extension_loaded('sqlite3'),
                'pros' => installTrans('install_db_sqlite_pros'),
                'reason' => installTrans('install_db_sqlite_reason'),
                'when' => installTrans('install_db_sqlite_when'),
            ],
            [
                'driver' => 'pgsql',
                'label' => 'PostgreSQL',
                'required_extensions' => 'pdo_pgsql',
                'available' => extension_loaded('pdo_pgsql'),
                'pros' => installTrans('install_db_pgsql_pros'),
                'reason' => installTrans('install_db_pgsql_reason'),
                'when' => installTrans('install_db_pgsql_when'),
            ],
            [
                'driver' => 'sqlsrv',
                'label' => 'SQL Server',
                'required_extensions' => 'pdo_sqlsrv',
                'available' => extension_loaded('pdo_sqlsrv'),
                'pros' => installTrans('install_db_sqlsrv_pros'),
                'reason' => installTrans('install_db_sqlsrv_reason'),
                'when' => installTrans('install_db_sqlsrv_when'),
            ],
        ];
    }

    public static function inspectLegacy(): array
    {
        $gdInfo = function_exists('gd_info') ? gd_info() : [];

        return [
            'pdo' => static::htmlStatus(class_exists('pdo'), class_exists('pdo') ? installTrans('install_status_on') : installTrans('install_status_off')),
            'curl' => static::htmlStatus(extension_loaded('curl'), extension_loaded('curl') ? installTrans('install_status_on') : installTrans('install_status_off')),
            'gd' => static::buildGdStatus($gdInfo),
            'mbstring' => static::htmlStatus(extension_loaded('mbstring'), extension_loaded('mbstring') ? installTrans('install_status_on') : installTrans('install_status_off')),
            'fileinfo' => static::htmlStatus(extension_loaded('fileinfo'), extension_loaded('fileinfo') ? installTrans('install_status_on') : installTrans('install_status_off')),
        ];
    }

    public static function inspectStructured(): array
    {
        $drivers = static::databaseDrivers();
        $availableLabels = array_column(array_filter($drivers, static fn(array $item) => $item['available']), 'label');
        $driverReady = count($availableLabels) > 0;

        return [
            static::buildCheck('pdo', 'PDO', class_exists('pdo'), true, class_exists('pdo')),
            [
                'key' => 'database_driver',
                'title' => installTrans('install_check_db_driver_title'),
                'current' => $driverReady
                    ? installTrans('install_check_db_available_prefix') . implode(installTrans('install_list_separator'), $availableLabels)
                    : installTrans('install_check_db_none_available'),
                'expected' => installTrans('install_check_db_expected'),
                'required' => true,
                'status' => $driverReady ? 'pass' : 'fail',
                'message' => $driverReady
                    ? installTrans('install_check_db_supported_prefix') . implode(installTrans('install_list_separator'), $availableLabels)
                    : installTrans('install_check_db_not_detected'),
                'risk_level' => $driverReady ? 'low' : 'high',
            ],
            static::buildCheck('curl', 'CURL', extension_loaded('curl'), true, extension_loaded('curl')),
            static::buildCheck('gd', 'GD', extension_loaded('gd'), true, extension_loaded('gd')),
            static::buildCheck('mbstring', 'MBstring', extension_loaded('mbstring'), true, extension_loaded('mbstring')),
            static::buildCheck('fileinfo', 'fileinfo', extension_loaded('fileinfo'), true, extension_loaded('fileinfo')),
            static::buildCheck('openssl', 'OpenSSL', extension_loaded('openssl'), true, extension_loaded('openssl')),
            static::buildCheck('json', 'JSON', extension_loaded('json'), true, extension_loaded('json')),
        ];
    }

    private static function buildGdStatus(array $gdInfo): string
    {
        if (!extension_loaded('gd')) {
            $message = installTrans('install_status_off');
            if (function_exists('imagettftext')) {
                $message .= '<br><i class="fa fa-remove error"></i> ' . installTrans('install_freetype_disabled');
            }

            return static::htmlStatus(false, $message, true);
        }

        $version = $gdInfo['GD Version'] ?? installTrans('install_status_on');
        return static::htmlStatus(true, $version);
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

    private static function htmlStatus(bool $passed, string $message, bool $messageContainsIcon = false): string
    {
        $class = $passed ? 'check correct' : 'remove error';
        $icon = '<i class="fa fa-' . $class . '"></i> ';

        return $messageContainsIcon ? $icon . $message : $icon . e($message);
    }
}
