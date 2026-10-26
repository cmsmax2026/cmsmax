<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DbBackup extends Command
{
    protected $signature = 'db:backup
                            {--path=storage/backups : 备份文件存放路径}
                            {--filename= : 备份文件名 (默认自动生成)}';

    protected $description = '备份当前数据库到SQL文件';

    public function handle()
    {
        $path = $this->option('path');
        $filename = $this->option('filename') ?? 'backup_' . date('Ymd_His') . '.sql';
        $fullPath = base_path($path . '/' . $filename);
        
        $this->info("========================================");
        $this->info("数据库备份工具");
        $this->info("========================================");
        
        try {
            if (!File::isDirectory(base_path($path))) {
                File::makeDirectory(base_path($path), 0755, true);
                $this->info("创建目录: {$path}");
            }
            
            $this->info("正在导出...");
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            $database = $connection->getDatabaseName();
            
            $sql = $this->generateBackupSql($connection);
            
            File::put($fullPath, $sql);
            
            $this->info("✓ 备份成功!");
            $this->info("位置: {$fullPath}");
            $this->info("大小: " . $this->formatFileSize(File::size($fullPath)));
            
        } catch (\Exception $e) {
            $this->error("错误: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    protected function generateBackupSql($connection)
    {
        $driver = $connection->getDriverName();
        $sql = "-- 数据库备份\n";
        $sql .= "-- 时间: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- 驱动: {$driver}\n\n";
        
        $tables = $this->getTables($connection);
        
        foreach ($tables as $table) {
            if (is_object($table)) {
                $table = (array)$table;
                $table = reset($table);
            }
            
            if (strpos($table, 'sqlite_') === 0) {
                continue;
            }
            
            $this->info("处理表: {$table}");
            
            $sql .= "-- 表: {$table}\n";
            
            $data = $connection->table($table)->get();
            if ($data->isEmpty()) {
                $sql .= "-- 没有数据\n\n";
                continue;
            }
            
            $this->info("  " . count($data) . " 条记录");
            
            foreach ($data as $row) {
                $row = (array)$row;
                $columns = array_map(function ($col) {
                    return "`{$col}`";
                }, array_keys($row));
                
                $values = array_map(function ($value) {
                    if ($value === null) {
                        return 'NULL';
                    }
                    return "'" . addslashes($value) . "'";
                }, array_values($row));
                
                $sql .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            
            $sql .= "\n";
        }
        
        return $sql;
    }

    protected function getTables($connection)
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

    protected function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
