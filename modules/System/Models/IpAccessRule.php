<?php

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Model;

class IpAccessRule extends Model
{
    const TABLE_NAME = 'system_ip_access_rules';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'scope',
        'rule_type',
        'match_type',
        'rule_value',
        'source_type',
        'status',
        'reason',
        'expires_at',
        'hit_count',
        'ban_count',
        'last_hit_at',
    ];
}
