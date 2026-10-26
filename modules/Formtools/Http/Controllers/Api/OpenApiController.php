<?php

namespace Modules\Formtools\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Formtools\Models\FormModel;
use Modules\Formtools\Models\OpenApiApp;
use Modules\Formtools\Support\FormOpenApiService;
use Modules\ModulesController;

class OpenApiController extends ModulesController
{
    public function meta(string $accessIdentification)
    {
        $model = $this->service()->findModelByAccessIdentification($accessIdentification);
        if (!$model) {
            return returnArr(404, '模型不存在');
        }

        $globalSettings = $this->service()->globalSettings();
        $modelConfig = $this->service()->modelConfig($model);
        if ((int) $globalSettings['enabled'] !== 1 || (int) $modelConfig['enabled'] !== 1) {
            return returnArr(403, '当前模型未启用开放 API');
        }

        return returnArr(200, '获取成功', $this->service()->buildMetaDocument($model));
    }

    public function submit(string $accessIdentification)
    {
        $requestId = $this->service()->makeRequestId();
        $clientIp = (string) $this->request->ip();
        $input = $this->request->all();
        $appId = trim((string) ($this->request->header('X-Formtools-AppId', $input['app_id'] ?? '')));
        $appSecret = trim((string) ($this->request->header('X-Formtools-AppSecret', $input['app_secret'] ?? '')));
        $idempotencyKey = trim((string) ($this->request->header('X-Formtools-Idempotency-Key', $input['idempotency_key'] ?? '')));
        $payload = $this->service()->parseBodyPayload($input);
        $payloadHash = '';

        $model = $this->service()->findModelByAccessIdentification($accessIdentification);
        if (!$model) {
            $payloadHash = $this->service()->payloadHash($payload);
            return $this->respondAndLog([
                'request_id' => $requestId,
                'channel' => 'openapi',
                'access_identification' => $accessIdentification,
                'client_ip' => $clientIp,
                'idempotency_key' => $idempotencyKey,
                'payload_hash' => $payloadHash,
            ], returnArr(404, '模型不存在'));
        }

        $globalSettings = $this->service()->globalSettings();
        $modelConfig = $this->service()->modelConfig($model);
        $app = OpenApiApp::query()
            ->where('model_id', $model->id)
            ->where('app_id', $appId)
            ->first();

        $logContext = [
            'request_id' => $requestId,
            'channel' => 'openapi',
            'model_id' => $model->id,
            'app_id' => $appId,
            'access_identification' => $accessIdentification,
            'client_ip' => $clientIp,
            'idempotency_key' => $idempotencyKey,
            'payload_hash' => '',
        ];

        if ((int) $globalSettings['enabled'] !== 1) {
            return $this->respondAndLog($logContext, returnArr(403, '开放 API 已停用'));
        }
        if ((int) $modelConfig['enabled'] !== 1) {
            return $this->respondAndLog($logContext, returnArr(403, '当前模型未启用开放 API'));
        }
        if ($appId === '' || $appSecret === '') {
            return $this->respondAndLog($logContext, returnArr(422, '缺少鉴权请求头'));
        }
        if (!$app) {
            return $this->respondAndLog($logContext, returnArr(403, '开放应用不存在'));
        }
        if ((int) $app->status !== 1) {
            return $this->respondAndLog($logContext, returnArr(403, '开放应用已停用'));
        }
        if (!$this->service()->ipAllowed($clientIp, (string) $globalSettings['ip_whitelist'], (string) $modelConfig['ip_whitelist'], (string) ($app->ip_whitelist ?? ''))) {
            return $this->respondAndLog($logContext, returnArr(403, '当前 IP 不在白名单内'));
        }

        $limit = min(max(1, (int) $app->rate_limit), max(1, (int) $globalSettings['rate_limit']));
        if (!$this->service()->consumeRateLimit((string) $app->app_id, $limit)) {
            return $this->respondAndLog($logContext, returnArr(429, '调用频率过高，请稍后重试'));
        }

        $secret = $this->service()->decryptSecret($app);
        if ($secret === '' || !hash_equals((string) $secret, (string) $appSecret)) {
            return $this->respondAndLog($logContext, returnArr(403, '密钥校验失败'));
        }
        if (!$this->service()->reserveIdempotency((string) $app->app_id, $accessIdentification, $idempotencyKey, (int) $globalSettings['idempotent_ttl'])) {
            return $this->respondAndLog($logContext, returnArr(409, '幂等键已使用，请勿重复提交'));
        }

        $payloadHash = $this->service()->payloadHash($payload);
        $logContext['payload_hash'] = $payloadHash;

        [$insertData, $errors] = $this->buildInsertData($model, $payload, $modelConfig);
        if ($errors !== []) {
            return $this->respondAndLog($logContext, returnArr(422, implode('；', $errors)));
        }

        try {
            $tableName = $this->service()->tableName($model);
            $idMeta = $this->service()->idColumnMeta($tableName);
            $needManualId = !empty($idMeta['is_primary']) && empty($idMeta['is_auto_increment']) && $idMeta['type'] !== '' && !str_contains((string) $idMeta['type'], 'int');
            if ($needManualId && (!array_key_exists('id', $insertData) || trim((string) $insertData['id']) === '')) {
                $insertData['id'] = $this->service()->makeRecordId();
            }

            if ($needManualId) {
                if (!array_key_exists('id', $insertData) || trim((string) $insertData['id']) === '') {
                    throw new \RuntimeException('数据表主键异常，无法生成主键');
                }
                DB::table($tableName)->insert($insertData);
                $recordId = $insertData['id'];
            } else {
                $recordId = DB::table($tableName)->insertGetId($insertData);
            }
        } catch (\Throwable $exception) {
            return $this->respondAndLog($logContext, returnArr(500, '写入失败：' . $exception->getMessage()));
        }

        $app->forceFill([
            'last_used_at' => date('Y-m-d H:i:s'),
            'last_ip' => $clientIp,
            'updated_at' => date('Y-m-d H:i:s'),
        ])->save();

        return $this->respondAndLog($logContext, returnArr(200, '提交成功', [
            'id' => $recordId,
            'detail_url' => model_detail_url($accessIdentification, $recordId),
        ]));
    }

    private function buildInsertData(FormModel $model, array $payload, array $modelConfig): array
    {
        $tableName = $this->service()->tableName($model);
        if (!Schema::hasTable($tableName)) {
            return [[], ['模型数据表不存在']];
        }

        $tableColumns = Schema::getColumnListing($tableName);
        $insertData = [];
        $errors = [];
        $acceptedCount = 0;

        foreach ($this->service()->contentFields($model) as $field) {
            $identification = (string) ($field['identification'] ?? '');
            if ($identification === 'id') {
                continue;
            }
            $existsInPayload = array_key_exists($identification, $payload);
            $rawValue = $payload[$identification] ?? null;
            $value = $this->normalizeFieldValue($rawValue);

            if (($field['required'] ?? false) && $this->isBlankValue($rawValue)) {
                $errors[] = ($field['name'] ?? $identification) . ' 不能为空';
                continue;
            }

            if (!$existsInPayload || $this->isBlankValue($rawValue)) {
                continue;
            }

            $validationError = $this->validateFieldValue($field, $rawValue);
            if ($validationError !== null) {
                $errors[] = $validationError;
                continue;
            }

            if (in_array($identification, $tableColumns, true)) {
                $insertData[$identification] = $value;
                $acceptedCount++;
            }
        }

        if ((int) ($modelConfig['allow_seo'] ?? 1) === 1) {
            foreach (['seo_title', 'seo_keywords', 'seo_description'] as $column) {
                if (in_array($column, $tableColumns, true) && array_key_exists($column, $payload) && !$this->isBlankValue($payload[$column])) {
                    $insertData[$column] = $this->normalizeFieldValue($payload[$column]);
                }
            }
        }

        if ($acceptedCount === 0) {
            $errors[] = '没有可写入的模型字段';
        }

        $now = date('Y-m-d H:i:s');
        if (in_array('status', $tableColumns, true)) {
            $insertData['status'] = in_array((int) ($modelConfig['default_status'] ?? 1), [0, 1, 2], true)
                ? (int) $modelConfig['default_status']
                : 1;
        }
        if (in_array('uid', $tableColumns, true)) {
            $insertData['uid'] = 0;
        }
        if (in_array('created_at', $tableColumns, true)) {
            $insertData['created_at'] = $now;
        }
        if (in_array('updated_at', $tableColumns, true)) {
            $insertData['updated_at'] = $now;
        }

        return [$insertData, $errors];
    }

    private function validateFieldValue(array $field, $rawValue): ?string
    {
        $name = (string) ($field['name'] ?? $field['identification'] ?? '字段');
        $formtype = strtolower(trim((string) ($field['formtype'] ?? 'text')));
        $fieldtype = strtolower(trim((string) ($field['fieldtype'] ?? '')));

        if ((in_array($formtype, ['number'], true) || in_array($fieldtype, ['integer', 'biginteger', 'float', 'double', 'decimal'], true)) && !is_numeric(is_array($rawValue) ? implode(',', $rawValue) : $rawValue)) {
            return $name . ' 必须为数字';
        }

        $value = $this->normalizeFieldValue($rawValue);
        $maxlength = (int) ($field['maxlength'] ?? 0);
        if ($maxlength > 0 && mb_strlen($value) > $maxlength) {
            return $name . ' 长度不能超过 ' . $maxlength;
        }

        $options = array_column((array) ($field['datas'] ?? []), 'value');
        if ($options !== []) {
            if (is_array($rawValue)) {
                foreach ($rawValue as $item) {
                    if (!in_array((string) $item, array_map('strval', $options), true)) {
                        return $name . ' 存在未定义的选项值';
                    }
                }
            } elseif (!in_array($value, array_map('strval', $options), true)) {
                return $name . ' 选项值不合法';
            }
        }

        return null;
    }

    private function normalizeFieldValue($value): string
    {
        if (is_array($value)) {
            return implode(',', array_map(function ($item) {
                return is_scalar($item) ? (string) $item : json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }, $value));
        }

        return is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function isBlankValue($value): bool
    {
        if (is_array($value)) {
            return $value === [];
        }

        return trim((string) $value) === '';
    }

    private function respondAndLog(array $context, array $response): array
    {
        $this->service()->writeLog(array_merge($context, [
            'http_method' => $this->request->method(),
            'status_code' => (int) ($response['status'] ?? 0),
            'status_text' => (int) ($response['status'] ?? 0) === 200 ? 'success' : 'failed',
            'message' => (string) ($response['msg'] ?? ''),
            'request_payload' => $this->request->all(),
            'response_payload' => $response,
        ]));

        return $response;
    }

    private function service(): FormOpenApiService
    {
        return app(FormOpenApiService::class);
    }
}
