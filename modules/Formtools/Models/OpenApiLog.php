<?php

namespace Modules\Formtools\Models;

use Illuminate\Database\Eloquent\Model;

class OpenApiLog extends Model
{
    const TABLE_NAME = 'module_formtools_openapi_logs';

    protected $table = self::TABLE_NAME;
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'model_id',
        'app_id',
        'access_identification',
        'request_id',
        'channel',
        'http_method',
        'status_code',
        'status_text',
        'message',
        'client_ip',
        'nonce',
        'idempotency_key',
        'payload_hash',
        'request_payload',
        'response_payload',
        'created_at',
    ];

    protected $casts = [
        'model_id' => 'string',
        'status_code' => 'integer',
    ];

    public function model()
    {
        return $this->belongsTo(FormModel::class, 'model_id', 'id');
    }
}
