<?php

namespace App\Support\Database;

use App\Support\Async\AsyncArtisanDispatcher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AutoSchemaRepairService
{
    private const CACHE_LOCK_PREFIX = 'auto_schema_repair:';
    private const LOCK_TTL_SECONDS = 600;

    public function attempt(\Throwable $exception, array $context = []): ?array
    {
        if ($this->shouldSkipCurrentRequest()) {
            return null;
        }

        $analysis = $this->analyzeException($exception, $context);
        if (!$analysis['repairable']) {
            return null;
        }

        $lockKey = $this->buildLockKey($analysis);
        if (!$this->acquireLock($lockKey)) {
            $result = [
                'queued' => false,
                'reason' => 'locked',
                'message_public' => '检测到数据库结构缺失，系统正在处理中，请稍后刷新重试',
                'message_admin' => '检测到数据库结构缺失，系统正在处理中，请稍后刷新重试',
                'analysis' => $analysis,
                'tasks' => [],
            ];
            $this->writeRepairLog('schema_repair_locked', $analysis, $result, $context);
            return $result;
        }

        $targets = $this->resolveTargets($analysis, $context);
        if (!$targets) {
            $result = [
                'queued' => false,
                'reason' => 'unresolved',
                'message_public' => '检测到数据库结构缺失，但暂未定位到可自动修复的迁移包，请联系管理员处理',
                'message_admin' => '检测到数据库结构缺失，但暂未定位到可自动修复的迁移包，请检查迁移目录或手动执行修复',
                'analysis' => $analysis,
                'tasks' => [],
            ];
            $this->writeRepairLog('schema_repair_unresolved', $analysis, $result, $context);
            return $result;
        }

        $tasks = [];
        foreach ($targets as $target) {
            try {
                $task = app(AsyncArtisanDispatcher::class)->dispatch('migrate', [
                    '--path' => $target['path'],
                    '--force' => 1,
                ], $target['module'], [
                    'source' => 'auto_schema_repair',
                    'stage' => 'schema_repair',
                    'repair_type' => $analysis['type'],
                    'table' => $analysis['table'] ?? '',
                    'column' => $analysis['column'] ?? '',
                    'path' => $target['path'],
                ]);

                $tasks[] = [
                    'module' => $target['module'],
                    'type' => $target['type'],
                    'path' => $target['path'],
                    'async_id' => $task['async_id'] ?? '',
                    'status' => $task['status'] ?? 'queued',
                ];
            } catch (\Throwable $dispatchException) {
                $tasks[] = [
                    'module' => $target['module'],
                    'type' => $target['type'],
                    'path' => $target['path'],
                    'async_id' => '',
                    'status' => 'failed',
                    'error' => $dispatchException->getMessage(),
                ];
            }
        }

        $queuedTasks = array_values(array_filter($tasks, static function (array $task) {
            return ($task['status'] ?? '') !== 'failed';
        }));

        if (!$queuedTasks) {
            $result = [
                'queued' => false,
                'reason' => 'dispatch_failed',
                'message_public' => '检测到数据库结构缺失，但自动修复任务提交失败，请稍后重试',
                'message_admin' => '检测到数据库结构缺失，但自动修复任务提交失败，请检查异步任务与日志',
                'analysis' => $analysis,
                'tasks' => $tasks,
            ];
            $this->writeRepairLog('schema_repair_dispatch_failed', $analysis, $result, $context);
            return $result;
        }

        $asyncIds = array_values(array_filter(array_map(static function (array $task) {
            return trim((string) ($task['async_id'] ?? ''));
        }, $queuedTasks)));

        $result = [
            'queued' => true,
            'reason' => 'queued',
            'message_public' => '检测到数据库结构缺失，系统已自动提交修复任务，请稍后刷新重试',
            'message_admin' => '检测到数据库结构缺失，系统已自动提交修复任务，请稍后刷新重试' . ($asyncIds ? '，任务号：' . implode(', ', $asyncIds) : ''),
            'analysis' => $analysis,
            'tasks' => $tasks,
            'async_ids' => $asyncIds,
            'first_async_id' => $asyncIds[0] ?? '',
        ];
        $this->writeRepairLog('schema_repair_queued', $analysis, $result, $context);
        return $result;
    }

    private function analyzeException(\Throwable $exception, array $context = []): array
    {
        $message = trim((string) $exception->getMessage());
        $sql = method_exists($exception, 'getSql') ? trim((string) $exception->getSql()) : '';
        $errorInfo = property_exists($exception, 'errorInfo') ? (array) ($exception->errorInfo ?? []) : [];
        $sqlState = strtoupper(trim((string) ($errorInfo[0] ?? '')));
        $driverCode = trim((string) ($errorInfo[1] ?? ''));

        $result = [
            'repairable' => false,
            'type' => '',
            'table' => '',
            'column' => '',
            'sql' => $sql,
            'message' => $message,
            'tables' => [],
            'sql_state' => $sqlState,
            'driver_code' => $driverCode,
        ];

        if (preg_match("/Base table or view not found:\\s*1146\\s*Table\\s*'[^']+\\.([^']+)'\\s*doesn't exist/i", $message, $matches)
            || preg_match("/SQLSTATE\\[42S02\\].*?Table\\s*[\\'`\\\"][^\\'`\\\"]+\\.([^\\'`\\\"]+)[\\'`\\\"].*?doesn'?t exist/i", $message, $matches)) {
            $result['repairable'] = true;
            $result['type'] = 'missing_table';
            $result['table'] = $this->normalizeTableName($matches[1] ?? '');
        } elseif (preg_match("/Unknown column\\s*'([^']+)'/i", $message, $matches)
            || $sqlState === '42S22'
            || $driverCode === '1054') {
            $result['repairable'] = true;
            $result['type'] = 'missing_column';
            $result['column'] = trim((string) ($matches[1] ?? ''));
        }

        $tables = $this->extractTablesFromSql($sql);
        if ($result['table'] !== '') {
            array_unshift($tables, $result['table']);
        }
        $result['tables'] = array_values(array_unique(array_filter(array_map([$this, 'normalizeTableName'], $tables))));
        if ($result['table'] === '' && !empty($result['tables'])) {
            $result['table'] = $result['tables'][0];
        }

        if ($result['repairable'] && empty($result['tables']) && !empty($context['module'])) {
            $result['tables'] = [];
        }

        return $result;
    }

    private function resolveTargets(array $analysis, array $context = []): array
    {
        $targets = [];
        $catalog = $this->buildMigrationCatalog();

        foreach ($analysis['tables'] as $table) {
            foreach ($catalog as $package) {
                if ($this->packageContainsTable($package, $table)) {
                    foreach ($this->buildPackageTargets($package, $analysis['type']) as $target) {
                        $targets[$target['type'] . ':' . $target['module'] . ':' . $target['path']] = $target;
                    }
                }
            }
        }

        if (!$targets && !empty($analysis['table'])) {
            $fallbackPackage = $this->resolveFallbackPackageByTable($analysis['table'], $catalog);
            if ($fallbackPackage) {
                foreach ($this->buildPackageTargets($fallbackPackage, $analysis['type']) as $target) {
                    $targets[$target['type'] . ':' . $target['module'] . ':' . $target['path']] = $target;
                }
            }
        }

        if (!$targets && !empty($context['module'])) {
            foreach ($catalog as $package) {
                if (strcasecmp((string) $package['module'], (string) $context['module']) === 0) {
                    foreach ($this->buildPackageTargets($package, 'missing_column') as $target) {
                        $targets[$target['type'] . ':' . $target['module'] . ':' . $target['path']] = $target;
                    }
                    break;
                }
            }
        }

        return array_values($targets);
    }

    private function buildMigrationCatalog(): array
    {
        $packages = [];
        foreach (['module', 'plugin'] as $type) {
            $root = package_root_path($type);
            if (!is_dir($root)) {
                continue;
            }

            foreach (scandir($root) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $packageRoot = rtrim($root, '\\/') . DIRECTORY_SEPARATOR . $entry;
                if (!is_dir($packageRoot)) {
                    continue;
                }

                $config = $this->readPackageConfig($packageRoot);
                $module = trim((string) ($config['identification'] ?? $entry));
                $directory = trim((string) ($config['directory'] ?? $entry));
                $installRelative = $type === 'plugin'
                    ? plugins_relative_path($entry . '/Database/Migrations/install')
                    : modules_relative_path($entry . '/Database/Migrations/install');
                $updateRelative = $type === 'plugin'
                    ? plugins_relative_path($entry . '/Database/Migrations/update')
                    : modules_relative_path($entry . '/Database/Migrations/update');

                $installAbsolute = base_path($installRelative);
                $updateAbsolute = base_path($updateRelative);

                $packages[] = [
                    'module' => $module,
                    'type' => $type,
                    'directory' => $directory,
                    'install_path' => $installRelative,
                    'update_path' => $updateRelative,
                    'install_exists' => is_dir($installAbsolute),
                    'update_exists' => is_dir($updateAbsolute),
                    'migration_content' => strtolower(
                        $this->readMigrationDirectoryContent($installAbsolute)
                        . "\n"
                        . $this->readMigrationDirectoryContent($updateAbsolute)
                    ),
                ];
            }
        }

        return $packages;
    }

    private function readPackageConfig(string $packageRoot): array
    {
        $configFile = $packageRoot . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'config.php';
        if (!is_file($configFile)) {
            return [];
        }

        try {
            $config = include $configFile;
            return is_array($config) ? $config : [];
        } catch (\Throwable $exception) {
            return [];
        }
    }

    private function readMigrationDirectoryContent(string $dir): string
    {
        if (!is_dir($dir)) {
            return '';
        }

        $content = [];
        foreach (glob(rtrim($dir, '\\/') . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
            if (!is_file($file)) {
                continue;
            }
            $content[] = strtolower((string) file_get_contents($file));
        }

        return implode("\n", $content);
    }

    private function packageContainsTable(array $package, string $table): bool
    {
        $table = strtolower($this->normalizeTableName($table));
        if ($table === '') {
            return false;
        }

        $content = (string) ($package['migration_content'] ?? '');
        if ($content === '') {
            return false;
        }

        if (strpos($content, strtolower($table)) !== false) {
            return true;
        }

        $prefixedTable = strtolower((string) env('DB_PREFIX')) . strtolower($table);
        return $prefixedTable !== strtolower($table) && strpos($content, $prefixedTable) !== false;
    }

    private function buildPackageTargets(array $package, string $repairType): array
    {
        $targets = [];
        // Auto repair must never run install migrations, because many legacy install files
        // still contain drop-and-recreate logic that can destroy existing data during upgrade.
        if (!empty($package['update_exists'])) {
            $targets[] = [
                'module' => $package['module'],
                'type' => 'update',
                'path' => $package['update_path'],
            ];
        }

        return array_values(array_filter($targets, static function (array $target) {
            return trim((string) ($target['path'] ?? '')) !== '';
        }));
    }

    private function resolveFallbackPackageByTable(string $table, array $catalog): ?array
    {
        $normalized = strtolower($this->normalizeTableName($table));
        if ($normalized === '') {
            return null;
        }

        $maps = [
            'modules' => 'Main',
            'users' => 'Auth',
        ];

        if (isset($maps[$normalized])) {
            foreach ($catalog as $package) {
                if (strcasecmp((string) $package['module'], $maps[$normalized]) === 0) {
                    return $package;
                }
            }
        }

        if (preg_match('/^(module|plugin)_([a-z0-9]+)/', $normalized, $matches)) {
            $prefix = strtolower($matches[2] ?? '');
            foreach ($catalog as $package) {
                $module = strtolower((string) ($package['module'] ?? ''));
                $directory = strtolower((string) ($package['directory'] ?? ''));
                if ($module === $prefix || $directory === $prefix) {
                    return $package;
                }
            }
        }

        return null;
    }

    private function extractTablesFromSql(string $sql): array
    {
        if ($sql === '') {
            return [];
        }

        $tables = [];
        $patterns = [
            '/\bfrom\s+`?([a-zA-Z0-9_\.]+)`?/i',
            '/\bjoin\s+`?([a-zA-Z0-9_\.]+)`?/i',
            '/\bupdate\s+`?([a-zA-Z0-9_\.]+)`?/i',
            '/\binto\s+`?([a-zA-Z0-9_\.]+)`?/i',
            '/\btable\s+`?([a-zA-Z0-9_\.]+)`?/i',
        ];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $sql, $matches);
            foreach ((array) ($matches[1] ?? []) as $match) {
                $tables[] = $match;
            }
        }

        return array_values(array_unique(array_filter(array_map([$this, 'normalizeTableName'], $tables))));
    }

    private function normalizeTableName(string $table): string
    {
        $table = trim($table, " \t\n\r\0\x0B`'\"");
        if ($table === '') {
            return '';
        }

        if (strpos($table, '.') !== false) {
            $parts = explode('.', $table);
            $table = (string) end($parts);
        }

        $prefix = (string) env('DB_PREFIX');
        if ($prefix !== '' && Str::startsWith($table, $prefix)) {
            $table = substr($table, strlen($prefix));
        }

        return trim($table);
    }

    private function buildLockKey(array $analysis): string
    {
        return self::CACHE_LOCK_PREFIX . md5(json_encode([
            'type' => $analysis['type'] ?? '',
            'table' => $analysis['table'] ?? '',
            'column' => $analysis['column'] ?? '',
        ], JSON_UNESCAPED_UNICODE));
    }

    private function acquireLock(string $lockKey): bool
    {
        return Cache::add($lockKey, 1, now()->addSeconds(self::LOCK_TTL_SECONDS));
    }

    private function writeRepairLog(string $category, array $analysis, array $result, array $context = []): void
    {
        try {
            hook('Loger', [
                'module' => $context['module'] ?? 'System',
                'type' => 'system',
                'two_type' => 'autoSchemaRepair',
                'params' => [
                    'schema' => 'system.auto_schema_repair.v1',
                    'category' => $category,
                    'module' => strtolower(trim((string) ($context['module'] ?? 'system'))) ?: 'system',
                    'requestid' => trim((string) (request()->requestid ?? '')),
                    'path' => trim((string) request()->path()),
                    'ip' => trim((string) get_ip()),
                    'status' => !empty($result['queued']) ? 200 : 0,
                    'status_code' => !empty($result['queued']) ? 200 : 0,
                    'reason_code' => trim((string) ($result['reason'] ?? '')),
                    'message' => trim((string) ($analysis['message'] ?? '')),
                    'repair_type' => trim((string) ($analysis['type'] ?? '')),
                    'table' => trim((string) ($analysis['table'] ?? '')),
                    'column' => trim((string) ($analysis['column'] ?? '')),
                    'tables' => $analysis['tables'] ?? [],
                    'tasks' => $result['tasks'] ?? [],
                    'async_ids' => $result['async_ids'] ?? [],
                ],
                'remark' => '自动结构修复',
                'unique_id' => trim((string) ($analysis['table'] ?? '')) ?: trim((string) ($analysis['column'] ?? '')),
                'requestid' => trim((string) (request()->requestid ?? '')),
            ]);
        } catch (\Throwable $exception) {
            // ignore logging failures
        }
    }

    private function shouldSkipCurrentRequest(): bool
    {
        $path = trim((string) request()->path());
        if ($path === 'api/asynCall') {
            return true;
        }

        return (bool) request()->attributes->get('auto_schema_repair_handled', false);
    }
}
