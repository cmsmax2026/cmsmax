<?php

namespace App\Support\Installer;

use Illuminate\Support\Facades\DB;

class DatabasePreflight
{
    public static function check(array $dbConfig): array
    {
        $driver = static::normalizeDriver($dbConfig['dbdriver'] ?? $dbConfig['db_connection'] ?? '');
        if ($driver === 'sqlite') {
            return static::checkSqlite($dbConfig);
        }

        return static::checkRemoteDatabase($dbConfig, $driver);
    }

    private static function checkRemoteDatabase(array $dbConfig, string $driver): array
    {
        $extension = [
            'mysql' => 'pdo_mysql',
            'pgsql' => 'pdo_pgsql',
            'sqlsrv' => 'pdo_sqlsrv',
        ][$driver] ?? 'pdo_mysql';

        if (!extension_loaded($extension)) {
            $message = installTrans('install_preflight_ext_missing', ['ext' => $extension, 'driver' => $driver]);
            InstallLogger::log('db_preflight_fail', [
                'driver' => $driver,
                'error' => $message,
            ]);

            return [
                'msg' => $message,
                'status' => 40000,
                'data' => ['database_exists' => null],
            ];
        }

        $host = $dbConfig['hostname'] ?? $dbConfig['dbhost'] ?? '';
        $port = $dbConfig['hostport'] ?? $dbConfig['dbport'] ?? static::defaultPort($driver);
        $username = $dbConfig['username'] ?? $dbConfig['dbuser'] ?? '';
        $password = $dbConfig['password'] ?? $dbConfig['dbpw'] ?? '';
        $database = $dbConfig['database'] ?? $dbConfig['dbname'] ?? '';
        $adminDatabase = static::adminDatabase($driver);

        try {
            config(['database.connections.install_preflight' => array_merge(config('database.connections.' . $driver, []), [
                'driver' => $driver,
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'password' => $password,
                'database' => $adminDatabase,
            ])]);

            DB::purge('install_preflight');
            DB::connection('install_preflight')->getPdo();

            $message = installTrans('install_preflight_auth_success');
            if ($database !== '') {
                if (static::databaseExists($driver, $database)) {
                    $result = [
                        'msg' => installTrans('install_preflight_auth_success_db_exists'),
                        'status' => 200,
                        'data' => ['database_exists' => true],
                    ];
                    InstallLogger::log('db_preflight_success', [
                        'host' => $host,
                        'port' => $port,
                        'database' => $database,
                        'database_exists' => true,
                        'driver' => $driver,
                    ]);

                    return $result;
                }

                $result = [
                    'msg' => installTrans('install_preflight_auth_success_db_missing'),
                    'status' => 200,
                    'data' => ['database_exists' => false],
                ];
                InstallLogger::log('db_preflight_database_missing', [
                    'host' => $host,
                    'port' => $port,
                    'database' => $database,
                    'database_exists' => false,
                    'driver' => $driver,
                ]);

                return $result;
            }

            $result = [
                'msg' => $message,
                'status' => 200,
                'data' => ['database_exists' => null],
            ];
            InstallLogger::log('db_preflight_success', [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'database_exists' => null,
                'driver' => $driver,
            ]);

            return $result;
        } catch (\Throwable $exception) {
            $result = [
                'msg' => (stripos($exception->getMessage(), 'could not find driver') !== false
                    ? installTrans('install_preflight_driver_unavailable', ['driver' => $driver, 'ext' => $extension])
                    : installTrans('install_preflight_auth_failed') . $exception->getMessage()),
                'status' => 40000,
                'data' => ['database_exists' => null],
            ];
            InstallLogger::log('db_preflight_fail', [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'error' => $exception->getMessage(),
                'driver' => $driver,
            ]);

            return $result;
        } finally {
            DB::purge('install_preflight');
        }
    }

    private static function defaultPort(string $driver): string
    {
        return [
            'mysql' => '3306',
            'pgsql' => '5432',
            'sqlsrv' => '1433',
        ][$driver] ?? '3306';
    }

    private static function adminDatabase(string $driver): string
    {
        return [
            'mysql' => '',
            'pgsql' => 'postgres',
            'sqlsrv' => 'master',
        ][$driver] ?? '';
    }

    private static function databaseExists(string $driver, string $database): bool
    {
        if ($driver === 'pgsql') {
            return (bool) DB::connection('install_preflight')->selectOne(
                'SELECT 1 FROM pg_database WHERE datname = ?',
                [$database]
            );
        }

        if ($driver === 'sqlsrv') {
            return (bool) DB::connection('install_preflight')->selectOne(
                'SELECT name FROM sys.databases WHERE name = ?',
                [$database]
            );
        }

        return (bool) DB::connection('install_preflight')->selectOne(
            'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ? LIMIT 1',
            [$database]
        );
    }

    private static function checkSqlite(array $dbConfig): array
    {
        if (!extension_loaded('pdo_sqlite') || !extension_loaded('sqlite3')) {
            $missing = [];
            if (!extension_loaded('pdo_sqlite')) {
                $missing[] = 'pdo_sqlite';
            }
            if (!extension_loaded('sqlite3')) {
                $missing[] = 'sqlite3';
            }
            $message = installTrans('install_preflight_sqlite_ext_missing', ['ext' => implode(installTrans('install_list_separator'), $missing)]);
            InstallLogger::log('db_preflight_fail', [
                'driver' => 'sqlite',
                'error' => $message,
            ]);
            return ['msg' => $message, 'status' => 40000, 'data' => ['database_exists' => null]];
        }

        $database = static::normalizeSqlitePath((string) ($dbConfig['sqlite_database'] ?? $dbConfig['dbsqlite'] ?? $dbConfig['database'] ?? $dbConfig['dbname'] ?? ''));
        $directory = dirname($database);

        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            $message = installTrans('install_preflight_sqlite_dir_uncreatable', ['dir' => $directory]);
            InstallLogger::log('db_preflight_fail', [
                'driver' => 'sqlite',
                'database' => $database,
                'error' => $message,
            ]);

            return ['msg' => $message, 'status' => 40000, 'data' => ['database_exists' => null]];
        }

        if (!is_writable($directory)) {
            $message = installTrans('install_preflight_sqlite_dir_unwritable', ['dir' => $directory]);
            InstallLogger::log('db_preflight_fail', [
                'driver' => 'sqlite',
                'database' => $database,
                'error' => $message,
            ]);

            return ['msg' => $message, 'status' => 40000, 'data' => ['database_exists' => null]];
        }

        if (is_file($database) && !is_writable($database)) {
            $message = installTrans('install_preflight_sqlite_file_unwritable', ['file' => $database]);
            InstallLogger::log('db_preflight_fail', [
                'driver' => 'sqlite',
                'database' => $database,
                'error' => $message,
            ]);

            return ['msg' => $message, 'status' => 40000, 'data' => ['database_exists' => true]];
        }

        $message = is_file($database)
            ? installTrans('install_preflight_sqlite_file_ready')
            : installTrans('install_preflight_sqlite_file_create');

        InstallLogger::log('db_preflight_success', [
            'driver' => 'sqlite',
            'database' => $database,
            'database_exists' => is_file($database),
        ]);

        return [
            'msg' => $message,
            'status' => 200,
            'data' => [
                'database_exists' => is_file($database),
                'database_path' => $database,
            ],
        ];
    }

    private static function normalizeDriver(?string $driver): string
    {
        $driver = strtolower(trim((string) $driver));

        return in_array($driver, ['mysql', 'sqlite', 'pgsql', 'sqlsrv'], true) ? $driver : 'mysql';
    }

    private static function normalizeSqlitePath(string $database): string
    {
        $database = trim($database);
        if ($database === '') {
            $database = database_path('database.sqlite');
        }

        if (!preg_match('#^(?:[A-Za-z]:[\\\\/]|/)#', $database)) {
            $database = base_path($database);
        }

        return str_replace('\\', '/', $database);
    }
}
