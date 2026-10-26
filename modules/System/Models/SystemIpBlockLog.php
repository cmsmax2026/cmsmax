<?php

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Model;

class SystemIpBlockLog extends Model
{
    const TABLE_NAME = 'system_ip_block_logs';

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $guarded = [];
}
