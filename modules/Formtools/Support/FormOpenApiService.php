<?php

namespace Modules\Formtools\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Formtools\Models\FormModel;
use Modules\Formtools\Models\OpenApiApp;
use Modules\Formtools\Models\OpenApiLog;

class FormOpenApiService
{
    private const GLOBAL_DEFAULTS = [
        'enabled' => 1,
        'idempotent_ttl' => 600,
        'rate_limit' => 60,
        'ip_whitelist' => '',
    ];

    private const MODEL_DEFAULTS = [
        'enabled' => 0,
        'allow_seo' => 1,
        'default_status' => 1,
        'ip_whitelist' => '',
        'remark' => '',
    ];

    private const RESERVED_REQUEST_KEYS = [
        'app_id',
        'app_secret',
        'payload',
        'idempotency_key',
    ];

    public function globalSettings(): array
    {
        $enabled = cacheGlobalSettingsByKey('openapi_enabled', 'Formtools');
        $idempotentTtl = cacheGlobalSettingsByKey('openapi_idempotent_ttl', 'Formtools');
        $rateLimit = cacheGlobalSettingsByKey('openapi_rate_limit', 'Formtools');

        return [
            'enabled' => $enabled === '' || $enabled === null ? self::GLOBAL_DEFAULTS['enabled'] : (int) $enabled,
            'idempotent_ttl' => max(60, $idempotentTtl === '' || $idempotentTtl === null ? self::GLOBAL_DEFAULTS['idempotent_ttl'] : (int) $idempotentTtl),
            'rate_limit' => max(1, $rateLimit === '' || $rateLimit === null ? self::GLOBAL_DEFAULTS['rate_limit'] : (int) $rateLimit),
            'ip_whitelist' => trim((string) cacheGlobalSettingsByKey('openapi_ip_whitelist', 'Formtools')),
        ];
    }

    public function modelConfig(FormModel $model): array
    {
        $otherConfig = json_decode((string) ($model->other_config ?: '[]'), true) ?: [];
        $openApiConfig = is_array($otherConfig['openapi'] ?? null) ? $otherConfig['openapi'] : [];

        return [
            'enabled' => (int) ($openApiConfig['enabled'] ?? self::MODEL_DEFAULTS['enabled']),
            'allow_seo' => (int) ($openApiConfig['allow_seo'] ?? self::MODEL_DEFAULTS['allow_seo']),
            'default_status' => (int) ($openApiConfig['default_status'] ?? self::MODEL_DEFAULTS['default_status']),
            'ip_whitelist' => trim((string) ($openApiConfig['ip_whitelist'] ?? self::MODEL_DEFAULTS['ip_whitelist'])),
            'remark' => trim((string) ($openApiConfig['remark'] ?? self::MODEL_DEFAULTS['remark'])),
        ];
    }

    public function saveModelConfig(FormModel $model, array $input): void
    {
        $otherConfig = json_decode((string) ($model->other_config ?: '[]'), true) ?: [];
        $otherConfig['openapi'] = [
            'enabled' => !empty($input['enabled']) ? 1 : 0,
            'allow_seo' => !empty($input['allow_seo']) ? 1 : 0,
            'default_status' => in_array((int) ($input['default_status'] ?? 1), [0, 1, 2], true) ? (int) $input['default_status'] : 1,
            'ip_whitelist' => trim((string) ($input['ip_whitelist'] ?? '')),
            'remark' => trim((string) ($input['remark'] ?? '')),
        ];

        $payload = json_encode($otherConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        FormModel::query()->where('id', $model->id)->update([
            'other_config' => $payload,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $model->other_config = $payload;
        $model->updated_at = date('Y-m-d H:i:s');
    }

    public function findModelByAccessIdentification(string $accessIdentification): ?FormModel
    {
        $accessIdentification = trim($accessIdentification);
        if ($accessIdentification === '') {
            return null;
        }

        return FormModel::query()
            ->withoutReserved()
            ->where('access_identification', $accessIdentification)
            ->first();
    }

    public function tableName(FormModel $model): string
    {
        return 'module_formtools_' . $model->identification;
    }

    public function contentFields(FormModel $model): array
    {
        $tableColumns = $this->getTableColumns($this->tableName($model));
        $fields = json_decode((string) ($model->fields ?: '[]'), true) ?: [];
        $normalized = [];

        foreach ($fields as $field) {
            $identification = trim((string) ($field['identification'] ?? ''));
            if ($identification === '' || !in_array($identification, $tableColumns, true)) {
                continue;
            }
            if (in_array($identification, ['status', 'remark', 'seo_title', 'seo_keywords', 'seo_description'], true)) {
                continue;
            }

            $field['name'] = trim((string) ($field['name'] ?? $field['remark'] ?? $identification));
            $field['remark'] = trim((string) ($field['remark'] ?? ''));
            $field['formtype'] = trim((string) ($field['formtype'] ?? 'text')) ?: 'text';
            $field['required'] = $this->normalizeFieldRequired($field['required'] ?? null);
            $field['maxlength'] = (int) ($field['maxlength'] ?? 0);
            $field['datas'] = $this->normalizeFieldOptions($field['datas'] ?? []);
            $normalized[] = $field;
        }

        return $normalized;
    }

    public function metaFields(FormModel $model, ?array $modelConfig = null): array
    {
        $modelConfig = $modelConfig ?? $this->modelConfig($model);
        $tableName = $this->tableName($model);
        $fields = [];

        if ((int) ($modelConfig['allow_seo'] ?? 1) === 1 && $this->hasTableColumn($tableName, 'seo_title')) {
            $fields[] = [
                'identification' => 'seo_title',
                'name' => 'SEO 标题',
                'formtype' => 'text',
                'required' => false,
                'remark' => '选填，不传时按模型详情页规则回退。',
            ];
        }
        if ((int) ($modelConfig['allow_seo'] ?? 1) === 1 && $this->hasTableColumn($tableName, 'seo_keywords')) {
            $fields[] = [
                'identification' => 'seo_keywords',
                'name' => 'SEO 关键词',
                'formtype' => 'text',
                'required' => false,
                'remark' => '选填，支持逗号分隔关键词。',
            ];
        }
        if ((int) ($modelConfig['allow_seo'] ?? 1) === 1 && $this->hasTableColumn($tableName, 'seo_description')) {
            $fields[] = [
                'identification' => 'seo_description',
                'name' => 'SEO 描述',
                'formtype' => 'textarea',
                'required' => false,
                'remark' => '选填，建议控制在 255 字以内。',
            ];
        }

        return $fields;
    }

    public function buildMetaDocument(FormModel $model): array
    {
        $modelConfig = $this->modelConfig($model);
        $metaUrl = url('api/formtools/open/' . $model->access_identification . '/meta');
        $submitUrl = url('api/formtools/open/' . $model->access_identification . '/submit');

        return [
            'model' => [
                'id' => $model->id,
                'name' => $model->name,
                'identification' => $model->identification,
                'access_identification' => $model->access_identification,
            ],
            'config' => $modelConfig,
            'endpoints' => [
                'meta' => [
                    'method' => 'GET',
                    'url' => $metaUrl,
                ],
                'submit' => [
                    'method' => 'POST',
                    'url' => $submitUrl,
                ],
            ],
            'authentication' => [
                'type' => 'app_secret',
                'headers' => [
                    'X-Formtools-AppId',
                    'X-Formtools-AppSecret',
                    'X-Formtools-Idempotency-Key',
                ],
                'note' => 'submit 需在请求头携带 AppId + AppSecret（明文比对）；Idempotency-Key 用于幂等，建议必带。',
            ],
            'fields' => $this->contentFields($model),
            'meta_fields' => $this->metaFields($model, $modelConfig),
        ];
    }

    public function createApp(FormModel $model, array $input): array
    {
        $secret = $this->generateSecret();
        $app = OpenApiApp::query()->create([
            'model_id' => $model->id,
            'app_name' => trim((string) ($input['app_name'] ?? '')) ?: ($model->name . '开放应用'),
            'app_id' => $this->generateUniqueAppId(),
            'app_secret' => Crypt::encryptString($secret),
            'secret_hint' => $this->secretHint($secret),
            'status' => !empty($input['status']) ? 1 : 0,
            'rate_limit' => max(1, (int) ($input['rate_limit'] ?? $this->globalSettings()['rate_limit'])),
            'ip_whitelist' => trim((string) ($input['ip_whitelist'] ?? '')),
            'remark' => trim((string) ($input['remark'] ?? '')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return [$app, $secret];
    }

    public function rotateSecret(OpenApiApp $app): string
    {
        $secret = $this->generateSecret();
        $app->forceFill([
            'app_secret' => Crypt::encryptString($secret),
            'secret_hint' => $this->secretHint($secret),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->save();

        return $secret;
    }

    public function decryptSecret(?OpenApiApp $app): string
    {
        if (!$app || trim((string) $app->app_secret) === '') {
            return '';
        }

        try {
            return Crypt::decryptString((string) $app->app_secret);
        } catch (\Throwable $exception) {
            return '';
        }
    }

    public function payloadHash(array $payload): string
    {
        $encoded = json_encode($this->canonicalize($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return hash('sha256', (string) $encoded);
    }

    public function payloadHashUnsorted(array $payload): string
    {
        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return hash('sha256', (string) $encoded);
    }

    public function makeRecordId(): string
    {
        return 'ftc_' . strtolower(substr(bin2hex(random_bytes(12)), 0, 24));
    }

    public function idColumnMeta(string $tableName): array
    {
        $driver = Schema::getConnection()->getDriverName();
        $prefix = DB::connection()->getTablePrefix();
        $physicalTableName = $prefix !== '' ? ($prefix . $tableName) : $tableName;

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA table_info('{$physicalTableName}')");
            foreach ($rows as $row) {
                if ((string) ($row->name ?? '') !== 'id') {
                    continue;
                }
                $type = strtolower((string) ($row->type ?? ''));
                $isPrimary = (int) ($row->pk ?? 0) === 1;
                return [
                    'driver' => $driver,
                    'type' => $type,
                    'is_primary' => $isPrimary,
                    'is_auto_increment' => $isPrimary && str_contains($type, 'int'),
                ];
            }

            return [
                'driver' => $driver,
                'type' => '',
                'is_primary' => false,
                'is_auto_increment' => false,
            ];
        }

        $rows = DB::select("SHOW COLUMNS FROM `{$physicalTableName}` LIKE 'id'");
        if (!$rows) {
            return [
                'driver' => $driver,
                'type' => '',
                'is_primary' => false,
                'is_auto_increment' => false,
            ];
        }

        $column = $rows[0];
        $type = strtolower((string) ($column->Type ?? ''));
        $key = strtoupper((string) ($column->Key ?? ''));
        $extra = strtolower((string) ($column->Extra ?? ''));

        return [
            'driver' => $driver,
            'type' => $type,
            'is_primary' => $key === 'PRI',
            'is_auto_increment' => str_contains($extra, 'auto_increment'),
        ];
    }

    public function parseBodyPayload(array $input): array
    {
        $payload = $input['payload'] ?? null;
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        if (is_array($payload)) {
            return $payload;
        }

        foreach (self::RESERVED_REQUEST_KEYS as $key) {
            unset($input[$key]);
        }

        return $input;
    }

    public function reserveIdempotency(string $appId, string $accessIdentification, string $idempotencyKey, int $ttlSeconds): bool
    {
        if ($appId === '' || $accessIdentification === '' || $idempotencyKey === '') {
            return true;
        }

        $cacheKey = $this->idempotencyCacheKey($appId, $accessIdentification, $idempotencyKey);
        return Cache::add($cacheKey, 1, now()->addSeconds($ttlSeconds));
    }

    public function consumeRateLimit(string $appId, int $limitPerMinute): bool
    {
        $limitPerMinute = max(1, $limitPerMinute);
        $cacheKey = 'formtools:openapi:rate:' . $appId . ':' . date('YmdHi');
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, 0, now()->addSeconds(70));
        }

        $current = (int) Cache::increment($cacheKey);
        return $current <= $limitPerMinute;
    }

    public function ipAllowed(?string $clientIp, string ...$whitelists): bool
    {
        $clientIp = trim((string) $clientIp);
        if ($clientIp === '') {
            return false;
        }

        foreach ($whitelists as $whitelist) {
            $rules = $this->parseWhitelist($whitelist);
            if ($rules === []) {
                continue;
            }
            if (!$this->matchWhitelist($clientIp, $rules)) {
                return false;
            }
        }

        return true;
    }

    public function makeRequestId(): string
    {
        return 'foa_' . strtolower(substr(bin2hex(random_bytes(12)), 0, 24));
    }

    public function writeLog(array $data): void
    {
        OpenApiLog::query()->create([
            'model_id' => $data['model_id'] ?? null,
            'app_id' => $data['app_id'] ?? null,
            'access_identification' => $data['access_identification'] ?? null,
            'request_id' => $data['request_id'] ?? null,
            'channel' => $data['channel'] ?? 'openapi',
            'http_method' => strtoupper((string) ($data['http_method'] ?? 'POST')),
            'status_code' => (int) ($data['status_code'] ?? 0),
            'status_text' => trim((string) ($data['status_text'] ?? 'failed')) ?: 'failed',
            'message' => $this->truncateText($data['message'] ?? ''),
            'client_ip' => trim((string) ($data['client_ip'] ?? '')),
            'nonce' => trim((string) ($data['nonce'] ?? '')),
            'idempotency_key' => trim((string) ($data['idempotency_key'] ?? '')),
            'payload_hash' => trim((string) ($data['payload_hash'] ?? '')),
            'request_payload' => $this->truncateText($data['request_payload'] ?? '', 18000),
            'response_payload' => $this->truncateText($data['response_payload'] ?? '', 18000),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function generateUniqueAppId(): string
    {
        do {
            $appId = 'fta_' . Str::lower(Str::random(24));
        } while (OpenApiApp::query()->where('app_id', $appId)->exists());

        return $appId;
    }

    private function generateSecret(): string
    {
        return 'fts_' . Str::lower(Str::random(48));
    }

    private function secretHint(string $secret): string
    {
        return '...' . substr($secret, -6);
    }

    private function normalizeFieldRequired($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'required', 'yes', 'y', 'on'], true);
    }

    private function normalizeFieldOptions($datas): array
    {
        if (is_string($datas) && trim($datas) !== '') {
            $decoded = json_decode($datas, true);
            if (is_array($decoded)) {
                $datas = $decoded;
            }
        }

        if (!is_array($datas)) {
            return [];
        }

        $normalized = [];
        foreach ($datas as $key => $item) {
            if (is_array($item)) {
                $normalized[] = [
                    'name' => (string) ($item['name'] ?? $item['label'] ?? $item['value'] ?? ''),
                    'value' => (string) ($item['value'] ?? $key),
                ];
                continue;
            }

            $normalized[] = [
                'name' => (string) $item,
                'value' => is_string($key) ? $key : (string) $item,
            ];
        }

        return $normalized;
    }

    private function getTableColumns(string $tableName): array
    {
        if (!Schema::hasTable($tableName)) {
            return [];
        }

        return Schema::getColumnListing($tableName);
    }

    private function hasTableColumn(string $tableName, string $column): bool
    {
        return Schema::hasTable($tableName) && Schema::hasColumn($tableName, $column);
    }

    private function canonicalize(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->canonicalize($value);
            }
        }

        ksort($payload);
        return $payload;
    }

    private function idempotencyCacheKey(string $appId, string $accessIdentification, string $idempotencyKey): string
    {
        return 'formtools:openapi:idempotent:' . md5($appId . '|' . $accessIdentification . '|' . $idempotencyKey);
    }

    private function parseWhitelist(string $whitelist): array
    {
        $items = preg_split('/[\r\n,，;；]+/', $whitelist) ?: [];
        $rules = [];
        foreach ($items as $item) {
            $item = trim((string) $item);
            if ($item !== '') {
                $rules[] = $item;
            }
        }
        return array_values(array_unique($rules));
    }

    private function matchWhitelist(string $clientIp, array $rules): bool
    {
        foreach ($rules as $rule) {
            if ($rule === '*') {
                return true;
            }
            if ($rule === $clientIp) {
                return true;
            }
            if (str_contains($rule, '/') && $this->matchCidr($clientIp, $rule)) {
                return true;
            }
        }

        return false;
    }

    private function matchCidr(string $clientIp, string $cidr): bool
    {
        [$subnet, $mask] = array_pad(explode('/', $cidr, 2), 2, null);
        $mask = (int) $mask;
        $ipLong = ip2long($clientIp);
        $subnetLong = ip2long((string) $subnet);
        if ($ipLong === false || $subnetLong === false || $mask < 0 || $mask > 32) {
            return false;
        }

        if ($mask === 0) {
            return true;
        }

        $maskLong = -1 << (32 - $mask);
        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    private function truncateText($value, int $limit = 5000): string
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return mb_substr((string) $value, 0, $limit);
    }
}
