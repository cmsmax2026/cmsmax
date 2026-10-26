<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbMigrateCross extends Command
{
    protected $signature = 'db:migrate-cross
                            {source : 源数据库连接名称 (mysql/sqlite)}
                            {target : 目标数据库连接名称 (mysql/sqlite)}
                            {--tables=* : 指定要迁移的表，留空则迁移所有表}
                            {--truncate : 迁移前清空目标表}';

    protected $description = '在不同数据库之间迁移数据 (支持MySQL和SQLite互导)';

    protected $sourceConnection;
    protected $targetConnection;

    public function handle()
    {
        $source = $this->argument('source');
        $target = $this->argument('target');
        $tables = $this->option('tables');
        $truncate = $this->option('truncate');

        $this->info("========================================");
        $this->info("数据库迁移工具");
        $this->info("========================================");
        $this->info("源数据库: <info>{$source}</info>");
        $this->info("目标数据库: <info>{$target}</info>");

        try {
            $this->sourceConnection = DB::connection($source);
            $this->targetConnection = DB::connection($target);
            
            $this->testConnection();
            
            if (empty($tables)) {
                $tables = $this->getAllTables($this->sourceConnection);
            }
            
            $this->info("共 <info>" . count($tables) . "</info> 个表需要迁移");
            $this->info("");
            
            $successCount = 0;
            foreach ($tables as $table) {
                $this->info("处理表: <comment>{$table}</comment>");
                
                try {
                    $this->migrateTable($table, $truncate);
                    $this->info("  ✓ 迁移成功");
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("  ✗ 迁移失败: " . $e->getMessage());
                }
                
                $this->info("");
            }
            
            $this->info("========================================");
            $this->info("迁移完成！成功 <info>{$successCount}/" . count($tables) . "</info> 个表");
            $this->info("========================================");
            
        } catch (\Exception $e) {
            $this->error("错误: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    protected function testConnection()
    {
        $this->info("测试连接...");
        $this->sourceConnection->getPdo();
        $this->targetConnection->getPdo();
        $this->info("✓ 连接成功");
        $this->info("");
    }

    protected function getAllTables($connection)
    {
        $driver = $connection->getDriverName();
        $prefix = $connection->getTablePrefix();
        $tables = [];
        
        if ($driver === 'mysql') {
            $results = $connection->select('SHOW TABLES');
            foreach ($results as $row) {
                $row = (array)$row;
                $table = reset($row);
                
                if ($prefix && strpos($table, $prefix) === 0) {
                    $table = substr($table, strlen($prefix));
                }
                
                $tables[] = $table;
            }
        } elseif ($driver === 'sqlite') {
            $results = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($results as $row) {
                $table = $row->name;
                
                if ($prefix && strpos($table, $prefix) === 0) {
                    $table = substr($table, strlen($prefix));
                }
                
                $tables[] = $table;
            }
        }
        
        return $tables;
    }

    protected function migrateTable($table, $truncate)
    {
        if (is_object($table)) {
            $table = (array)$table;
            $table = reset($table);
        }

        $this->ensureTargetTableExists($table);

        $sourceData = $this->sourceConnection->table($table)->get();

        if ($sourceData->isEmpty()) {
            $this->info("  ⓘ 没有数据");
            return;
        }

        if ($truncate) {
            try {
                $this->truncateTable($table);
                $this->info("  ⓘ 已清空目标表");
            } catch (\Exception $e) {
                $this->info("  ⓘ 清空失败（可能表不存在）: " . $e->getMessage());
            }
        }

        $dataArray = $sourceData->map(function ($item) {
            return (array)$item;
        })->toArray();

        $chunkSize = 100;
        $chunks = array_chunk($dataArray, $chunkSize);

        $total = count($dataArray);
        $this->info("  共 <info>{$total}</info> 条记录，分 <comment>" . count($chunks) . "</comment> 批导入");

        foreach ($chunks as $index => $chunk) {
            try {
                $this->targetConnection->table($table)->insert($chunk);
                $this->info("  批次 " . ($index + 1) . "/" . count($chunks) . " ✓");
            } catch (\Exception $e) {
                $this->error("  批次 " . ($index + 1) . " 失败: " . $e->getMessage());
                $this->info("  尝试单条导入...");
                
                foreach ($chunk as $row) {
                    try {
                        $this->targetConnection->table($table)->insert($row);
                    } catch (\Exception $e2) {
                        $this->error("  单条导入失败: " . json_encode($row, JSON_UNESCAPED_UNICODE));
                        $this->error("  错误: " . $e2->getMessage());
                    }
                }
            }
        }
    }

    protected function truncateTable(string $table): void
    {
        $driver = $this->targetConnection->getDriverName();

        if ($driver === 'sqlite') {
            $wrappedTable = $this->targetConnection->getQueryGrammar()->wrapTable($table);
            $this->targetConnection->statement("DELETE FROM {$wrappedTable}");
            return;
        }

        $this->targetConnection->table($table)->truncate();
    }

    protected function ensureTargetTableExists(string $table): void
    {
        if ($this->targetConnection->getSchemaBuilder()->hasTable($table)) {
            return;
        }

        $sourceDriver = $this->sourceConnection->getDriverName();
        $targetDriver = $this->targetConnection->getDriverName();

        if ($sourceDriver === 'mysql' && $targetDriver === 'sqlite') {
            $this->createSqliteTableFromMysql($table);
            $this->info("  ⓘ 已自动创建 SQLite 表结构");
            return;
        }

        throw new \RuntimeException("目标表不存在，且当前不支持自动建表：{$sourceDriver} -> {$targetDriver}");
    }

    protected function createSqliteTableFromMysql(string $table): void
    {
        $sourceTable = $this->sourceConnection->getTablePrefix() . $table;
        $columns = $this->sourceConnection->select("SHOW FULL COLUMNS FROM `{$sourceTable}`");
        if (empty($columns)) {
            throw new \RuntimeException("无法读取源表结构：{$sourceTable}");
        }

        $indexes = $this->sourceConnection->select("SHOW INDEX FROM `{$sourceTable}`");
        $primaryColumns = [];
        $columnSql = [];

        foreach ($columns as $column) {
            $name = (string) $column->Field;
            $type = (string) $column->Type;
            $nullable = strtoupper((string) $column->Null) === 'YES';
            $default = $column->Default;
            $extra = strtolower((string) ($column->Extra ?? ''));
            $isPrimary = strtoupper((string) $column->Key) === 'PRI';

            if ($isPrimary) {
                $primaryColumns[] = $name;
            }

            $mappedType = $this->mapMysqlTypeToSqlite($type);
            $definition = '"' . $name . '" ';

            $singleIntegerPrimary = $isPrimary
                && $this->isIntegerLikeType($type)
                && $this->countPrimaryColumns($columns) === 1;

            if ($singleIntegerPrimary) {
                $definition .= 'INTEGER PRIMARY KEY';
                if (str_contains($extra, 'auto_increment')) {
                    $definition .= ' AUTOINCREMENT';
                }
                $columnSql[] = $definition;
                continue;
            }

            $definition .= $mappedType;
            if (!$nullable) {
                $definition .= ' NOT NULL';
            }
            if ($default !== null) {
                $definition .= ' DEFAULT ' . $this->normalizeDefaultValue($default);
            }

            $columnSql[] = $definition;
        }

        if (count($primaryColumns) > 1 || (count($primaryColumns) === 1 && !$this->hasInlinePrimaryKey($columnSql))) {
            $columnSql[] = 'PRIMARY KEY (' . implode(', ', array_map(function ($column) {
                return '"' . $column . '"';
            }, $primaryColumns)) . ')';
        }

        $targetTable = $this->targetConnection->getTablePrefix() . $table;
        $createSql = 'CREATE TABLE "' . $targetTable . '" (' . implode(', ', $columnSql) . ')';
        $this->targetConnection->statement($createSql);

        $this->createSqliteIndexes($table, $indexes);
    }

    protected function createSqliteIndexes(string $table, array $indexes): void
    {
        $targetTable = $this->targetConnection->getTablePrefix() . $table;
        $grouped = [];

        foreach ($indexes as $index) {
            $keyName = (string) $index->Key_name;
            if ($keyName === 'PRIMARY') {
                continue;
            }

            $grouped[$keyName]['unique'] = ((int) $index->Non_unique) === 0;
            $grouped[$keyName]['columns'][(int) $index->Seq_in_index] = '"' . (string) $index->Column_name . '"';
        }

        foreach ($grouped as $keyName => $meta) {
            ksort($meta['columns']);
            $indexName = $targetTable . '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $keyName);
            $sql = sprintf(
                'CREATE %s INDEX IF NOT EXISTS "%s" ON "%s" (%s)',
                !empty($meta['unique']) ? 'UNIQUE' : '',
                $indexName,
                $targetTable,
                implode(', ', $meta['columns'])
            );
            $sql = preg_replace('/\s+/', ' ', $sql);
            $this->targetConnection->statement($sql);
        }
    }

    protected function mapMysqlTypeToSqlite(string $type): string
    {
        $baseType = strtolower(preg_replace('/\(.*/', '', $type));

        if (in_array($baseType, ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint'], true)) {
            return 'INTEGER';
        }

        if (in_array($baseType, ['decimal', 'numeric', 'float', 'double', 'real'], true)) {
            return 'REAL';
        }

        if (in_array($baseType, ['blob', 'binary', 'varbinary'], true)) {
            return 'BLOB';
        }

        return 'TEXT';
    }

    protected function isIntegerLikeType(string $type): bool
    {
        $baseType = strtolower(preg_replace('/\(.*/', '', $type));

        return in_array($baseType, ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint'], true);
    }

    protected function countPrimaryColumns(array $columns): int
    {
        $count = 0;

        foreach ($columns as $column) {
            if (strtoupper((string) ($column->Key ?? '')) === 'PRI') {
                $count++;
            }
        }

        return $count;
    }

    protected function hasInlinePrimaryKey(array $columnSql): bool
    {
        foreach ($columnSql as $sql) {
            if (str_contains($sql, 'PRIMARY KEY')) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeDefaultValue($default): string
    {
        if ($default === null) {
            return 'NULL';
        }

        $value = (string) $default;
        $upper = strtoupper($value);

        if (in_array($upper, ['CURRENT_TIMESTAMP', 'CURRENT_DATE', 'CURRENT_TIME'], true)) {
            return $upper;
        }

        return "'" . str_replace("'", "''", $value) . "'";
    }
}
