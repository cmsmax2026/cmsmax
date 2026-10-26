<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;


class Group extends Model {
    //设置表名
    const TABLE_NAME = "module_auth_group";
    const primaryKey = "group_id";
    protected $table = self::TABLE_NAME;
    protected $primaryKey = self::primaryKey;//主键
    public $timestamps = false;

    // SQLite 不支持 JSON_LENGTH，权限项数在 PHP 层按 role_json 顶层元素数计算（等价 JSON_LENGTH）
    public function getPermissionCountAttribute(): int
    {
        $decoded = json_decode((string) ($this->attributes['role_json'] ?? ''), true);
        return is_array($decoded) ? count($decoded) : 0;
    }

}