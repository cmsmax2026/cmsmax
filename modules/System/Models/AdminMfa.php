<?php

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Model;

class AdminMfa extends Model
{
    public const TABLE_NAME = 'system_admin_mfa';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'uid',
        'provider',
        'secret',
        'status',
        'bound_at',
        'last_verified_at',
    ];
}
