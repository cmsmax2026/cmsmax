<?php

namespace App\Support\Database;

use Illuminate\Support\Facades\DB;

class DatabaseCharsetResolver
{
    public static function describeRuntimeConnection(?string $connection = null): array
    {
        $connection = $connection ?: config('database.default');
        $driver = strtolower(trim((string) config('database.connections.' . $connection . '.driver', '')));
        $charset = trim((string) (static::resolveConfiguredCharset($connection) ?: ''));
        if ($charset === '') {
            $charset = $driver === 'mysql' ? 'utf8mb4' : '';
        }

        $configuredCollation = trim((string) config('database.connections.' . $connection . '.configured_collation', ''));
        $runtimeCollation = static::resolveRuntimeCollation($connection, $charset);
        $serverVersion = $driver === 'mysql' ? static::fetchServerVersion($connection) : '';
        $collationSource = 'unresolved';

        if ($configuredCollation !== '' && $runtimeCollation !== '' && strcasecmp($configuredCollation, $runtimeCollation) === 0) {
            $collationSource = 'configured';
        } elseif ($runtimeCollation !== '') {
            $collationSource = 'auto_detected';
        }

        return [
            'connection' => $connection,
            'driver' => $driver,
            'charset' => $charset,
            'configured_collation' => $configuredCollation,
            'runtime_collation' => $runtimeCollation ?: '',
            'collation_source' => $collationSource,
            'server_version' => $serverVersion,
        ];
    }

    public static function resolveConfigCollation(?string $driver, ?string $charset = null, ?string $configuredCollation = null): ?string
    {
        $driver = strtolower(trim((string) $driver));
        $charset = trim((string) ($charset ?: 'utf8mb4'));
        $configuredCollation = trim((string) $configuredCollation);

        if ($configuredCollation !== '') {
            return $configuredCollation;
        }

        if ($driver !== 'mysql') {
            return null;
        }

        if ($charset === 'utf8mb4') {
            return 'utf8mb4_unicode_ci';
        }

        if ($charset === 'utf8') {
            return 'utf8_unicode_ci';
        }

        return null;
    }

    public static function resolveConfiguredCharset(?string $connection = null): ?string
    {
        $connection = $connection ?: config('database.default');

        return trim((string) config('database.connections.' . $connection . '.charset', ''));
    }

    public static function resolveRuntimeCollation(?string $connection = null, ?string $charset = null): ?string
    {
        $connection = $connection ?: config('database.default');
        $driver = (string) config('database.connections.' . $connection . '.driver', '');
        if ($driver !== 'mysql') {
            return null;
        }

        $charset = trim((string) ($charset ?: static::resolveConfiguredCharset($connection) ?: 'utf8mb4'));
        $configuredCollation = trim((string) config('database.connections.' . $connection . '.configured_collation', ''));
        if ($configuredCollation !== '' && static::supportsCollation($configuredCollation, $connection)) {
            return $configuredCollation;
        }

        foreach (static::resolveMysqlCollationCandidates($charset, $connection) as $collation) {
            if (static::supportsCollation($collation, $connection)) {
                return $collation;
            }
        }

        return null;
    }

    public static function normalizeMysqlDumpCollation(string $sql, ?string $connection = null, ?string $charset = null): string
    {
        $connection = $connection ?: config('database.default');
        $driver = (string) config('database.connections.' . $connection . '.driver', '');
        if ($driver !== 'mysql') {
            return $sql;
        }

        $charset = trim((string) ($charset ?: static::resolveConfiguredCharset($connection) ?: 'utf8mb4'));
        $targetCollation = static::resolveRuntimeCollation($connection, $charset);
        if ($targetCollation === null || $targetCollation === '') {
            return $sql;
        }

        $pattern = '/\b' . preg_quote($charset, '/') . '_(0900_ai_ci|unicode_ci|general_ci)\b/i';

        return (string) preg_replace($pattern, $targetCollation, $sql);
    }

    private static function resolveMysqlCollationCandidates(string $charset, string $connection): array
    {
        $charset = trim((string) ($charset ?: 'utf8mb4'));
        if ($charset === 'utf8mb4') {
            $version = static::fetchServerVersion($connection);
            $candidates = [];

            if (static::isMysql8OrHigher($version) && !static::isMariaDb($version)) {
                $candidates[] = 'utf8mb4_0900_ai_ci';
            }

            $candidates[] = 'utf8mb4_unicode_ci';
            $candidates[] = 'utf8mb4_general_ci';

            return array_values(array_unique(array_filter($candidates)));
        }

        if ($charset === 'utf8') {
            return ['utf8_unicode_ci', 'utf8_general_ci'];
        }

        return [];
    }

    private static function fetchServerVersion(string $connection): string
    {
        $driver = (string) config('database.connections.' . $connection . '.driver', '');
        if (strtolower(trim($driver)) !== 'mysql') {
            return '';
        }

        try {
            $row = DB::connection($connection)->selectOne('select version() as version');

            return strtolower(trim((string) ($row->version ?? '')));
        } catch (\Throwable $exception) {
            return '';
        }
    }

    private static function supportsCollation(string $collation, string $connection): bool
    {
        try {
            $row = DB::connection($connection)->selectOne(
                'select count(*) as aggregate from information_schema.COLLATIONS where COLLATION_NAME = ?',
                [$collation]
            );

            return (int) ($row->aggregate ?? 0) > 0;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private static function isMysql8OrHigher(string $version): bool
    {
        if ($version === '') {
            return false;
        }

        if (!preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $matches)) {
            return false;
        }

        return version_compare($matches[0], '8.0.0', '>=');
    }

    private static function isMariaDb(string $version): bool
    {
        return str_contains($version, 'mariadb');
    }
}
