<?php

namespace Modules\Formtools\Models;

use Illuminate\Database\Eloquent\Model;

class OpenApiApp extends Model
{
    const TABLE_NAME = 'module_formtools_openapi_apps';

    protected $table = self::TABLE_NAME;
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'model_id',
        'app_name',
        'app_id',
        'app_secret',
        'secret_hint',
        'status',
        'rate_limit',
        'ip_whitelist',
        'remark',
        'last_used_at',
        'last_ip',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'model_id' => 'string',
        'status' => 'integer',
        'rate_limit' => 'integer',
    ];

    public function model()
    {
        return $this->belongsTo(FormModel::class, 'model_id', 'id');
    }
}
