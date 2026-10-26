<?php

namespace App\Support\Database;

use Illuminate\Support\Facades\DB;

/**
 * 跨数据库兼容的原始表达式片段构造器。
 * 把 MySQL 专属的 SQL 写法（FIND_IN_SET / DATE_FORMAT / JSON_CONTAINS）转换为
 * mysql / pgsql / sqlsrv 各自的等价写法，使调用处可被任意驱动执行。
 *
 * 用法示例：
 *   whereRaw(QueryCompat::findInSet('col'), [$val])
 *   where(DB::raw(QueryCompat::dateFormat('created_at', '%Y-%m')), date('Y-m'))
 *   whereRaw(QueryCompat::jsonContains('json_str', '$.notice_type'), [json_encode($val)])
 */
class QueryCompat
{
    /**
     * 跨库等价的 FIND_IN_SET(col, ?) 片段。
     */
    public static function findInSet(string $column, string $placeholder = '?'): string
    {
        $driver = DB::connection()->getDriverName();
        switch ($driver) {
            case 'pgsql':
                return $placeholder . ' = ANY(string_to_array(' . $column . ', \',\'))';
            case 'sqlsrv':
                return 'CHARINDEX(\',\' + CAST(' . $placeholder . ' AS varchar(255)) + \',\', \',\' + ISNULL(' . $column . ', \'\') + \',\') > 0';
            case 'mysql':
            default:
                return 'FIND_IN_SET(' . $placeholder . ', ' . $column . ')';
        }
    }

    /**
     * 跨库等价的 DATE_FORMAT(col, fmt) 表达式（作为 ->where(DB::raw(...), $val) 的左侧）。
     */
    public static function dateFormat(string $column, string $mysqlFormat): string
    {
        $driver = DB::connection()->getDriverName();
        $fmt = self::mapDateFormat($mysqlFormat, $driver);
        switch ($driver) {
            case 'pgsql':
                return 'TO_CHAR(' . $column . ', \'' . $fmt . '\')';
            case 'sqlsrv':
                return 'FORMAT(' . $column . ', \'' . $fmt . '\')';
            case 'mysql':
            default:
                return 'DATE_FORMAT(' . $column . ', \'' . $fmt . '\')';
        }
    }

    private static function mapDateFormat(string $mysqlFormat, string $driver): string
    {
        $pg = ['%Y' => 'YYYY', '%m' => 'MM', '%d' => 'DD', '%H' => 'HH24', '%i' => 'MI', '%s' => 'SS'];
        $ss = ['%Y' => 'yyyy', '%m' => 'MM', '%d' => 'dd', '%H' => 'HH', '%i' => 'mm', '%s' => 'ss'];
        $map = $driver === 'pgsql' ? $pg : ($driver === 'sqlsrv' ? $ss : []);
        if (empty($map)) {
            return $mysqlFormat;
        }

        return str_replace(array_keys($map), array_values($map), $mysqlFormat);
    }

    /**
     * 跨库等价的 JSON_CONTAINS(col, ?, path) 片段。绑定值需 json_encode 后传入。
     */
    public static function jsonContains(string $column, string $mysqlPath, string $placeholder = '?'): string
    {
        $driver = DB::connection()->getDriverName();
        switch ($driver) {
            case 'pgsql':
                $key = self::jsonPathToKey($mysqlPath);

                return '(' . $column . '->\'' . $key . '\')::jsonb @> ' . $placeholder . '::jsonb';
            case 'sqlsrv':
                return 'JSON_VALUE(' . $column . ', \'' . $mysqlPath . '\') = ' . $placeholder;
            case 'mysql':
            default:
                return 'JSON_CONTAINS(' . $column . ', ' . $placeholder . ', \'' . $mysqlPath . '\')';
        }
    }

    private static function jsonPathToKey(string $mysqlPath): string
    {
        $key = preg_replace('/^\$\.?/', '', $mysqlPath);
        $key = preg_replace('/\.[^.]+$/', '', $key);

        return $key;
    }
}
