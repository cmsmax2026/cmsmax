<?php

namespace Modules\Formtools\Models;

use Illuminate\Database\Eloquent\Model;

class FormModel extends Model {
    //设置表名
    const TABLE_NAME = 'module_formtools_models';
    /**
     * formtools 模块自带的内部表（不是用户动态模型），不参与"从数据表获取模型结构"功能
     * 注意：这些 identification 对应的物理表实际是 module_formtools_{identification}
     */
    const RESERVED_IDENTIFICATIONS = [
        // 模型元数据
        'models',
        'model_layouts',
        'model_template_maps',
        'dynamic_tables',
        // 页面构建
        'pages',
        'page_categories',
        'template_registry',
        // OpenAPI
        'openapi_apps',
        'openapi_logs',
        // 支付
        'paid_records',
    ];

    protected $table = self::TABLE_NAME;
    protected $primaryKey = 'id';
    public $timestamps = false;

    public static function reservedIdentifications(): array
    {
        return self::RESERVED_IDENTIFICATIONS;
    }

    public static function isReservedIdentification(?string $identification): bool
    {
        return in_array(strtolower(trim((string) $identification)), self::RESERVED_IDENTIFICATIONS, true);
    }

    public static function isReservedTableName(?string $tableName): bool
    {
        $normalized = trim((string) $tableName);
        if ($normalized === '') {
            return false;
        }

        if (str_starts_with($normalized, env('DB_PREFIX', ''))) {
            $normalized = substr($normalized, strlen(env('DB_PREFIX', '')));
        }

        if ($normalized === self::TABLE_NAME) {
            return true;
        }

        if (!str_starts_with($normalized, 'module_formtools_')) {
            return false;
        }

        return self::isReservedIdentification(substr($normalized, strlen('module_formtools_')));
    }

    public function scopeWithoutReserved($query)
    {
        return $query->whereNotIn('identification', self::reservedIdentifications());
    }
}
