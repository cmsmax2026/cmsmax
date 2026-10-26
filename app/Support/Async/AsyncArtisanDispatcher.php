<?php

namespace App\Support\Async;

use App\Support\Update\UpdateLogger;
use Illuminate\Support\Facades\Artisan;

class AsyncArtisanDispatcher
{
    public function dispatch(string $actionName, array $arguments, string $moduleName, array $context = []): array
    {
        $taskId = $context['async_id'] ?? $this->generateTaskId($moduleName, $actionName);
        $requestId = $context['requestid'] ?? (request()->requestid ?? '');
        $payload = [
            'actionName' => trim($actionName),
            'arguments' => $arguments,
            'moduleName' => trim($moduleName),
            'requestid' => $requestId,
            'async_id' => $taskId,
            'context' => $context,
        ];

        try {
            app(AsyncTaskStore::class)->init($payload);
            $trigger = curl_request_ms(url("api/asynCall"), $payload, 2000);
            $triggerError = $this->resolveTriggerError($trigger);
            if ($triggerError !== '') {
                app(AsyncTaskStore::class)->update($taskId, [
                    'status_code' => 0,
                    'error' => $triggerError,
                    'reason_code' => 'async_trigger_failed',
                ]);
                UpdateLogger::log('async.dispatch_trigger_failed', [
                    'async_id' => $taskId,
                    'module' => $payload['moduleName'],
                    'action' => $payload['actionName'],
                    'arguments' => $arguments,
                    'context' => $context,
                    'requestid' => $requestId,
                    'trigger' => $trigger,
                    'error' => $triggerError,
                ]);

                if (in_array(trim($actionName), ['migrate', 'mxz:datamanage:migrate-fix'], true)) {
                    return $this->fallbackSyncMigrate($payload);
                }

                throw new \RuntimeException($triggerError);
            }
            UpdateLogger::log('async.dispatch_queued', [
                'async_id' => $taskId,
                'module' => $payload['moduleName'],
                'action' => $payload['actionName'],
                'arguments' => $arguments,
                'context' => $context,
                'requestid' => $requestId,
                'trigger' => $trigger,
            ]);

            return [
                'status' => 'queued',
                'async_id' => $taskId,
                'requestid' => $requestId,
            ];
        } catch (\Throwable $exception) {
            app(AsyncTaskStore::class)->update($taskId, [
                'status_code' => 0,
                'error' => $exception->getMessage(),
            ]);
            UpdateLogger::log('async.dispatch_failed', [
                'async_id' => $taskId,
                'module' => $payload['moduleName'],
                'action' => $payload['actionName'],
                'arguments' => $arguments,
                'context' => $context,
                'requestid' => $requestId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function resolveTriggerError($trigger): string
    {
        if (!is_array($trigger)) {
            return '无法触发异步调用：curl_request_ms 返回值异常';
        }

        $httpCode = (int) ($trigger['http_code'] ?? 0);
        $errno = (int) ($trigger['errno'] ?? 0);
        $error = trim((string) ($trigger['error'] ?? ''));
        $response = trim((string) ($trigger['response'] ?? ''));

        if ($errno !== 0) {
            return '无法触发异步调用：' . ($error !== '' ? $error : ('curl_errno=' . $errno));
        }

        if ($httpCode >= 400) {
            $msg = '';
            $decoded = json_decode($response, true);
            if (is_array($decoded) && isset($decoded['msg'])) {
                $msg = trim((string) $decoded['msg']);
            }
            return '无法触发异步调用：HTTP ' . $httpCode . ($msg !== '' ? (' ' . $msg) : '');
        }

        return '';
    }

    private function fallbackSyncMigrate(array $payload): array
    {
        $startedAt = microtime(true);
        app(AsyncTaskStore::class)->update((string) ($payload['async_id'] ?? ''), [
            'status_code' => 102,
            'started_at' => date('c'),
            'reason_code' => 'fallback_sync_migrate',
        ]);

        try {
            $exitCode = Artisan::call((string) ($payload['actionName'] ?? ''), (array) ($payload['arguments'] ?? []));
            $output = trim((string) Artisan::output());
            if ($output !== '' && strlen($output) > 2000) {
                $output = substr($output, 0, 2000);
            }
            $durationMs = (int) ((microtime(true) - $startedAt) * 1000);
            $status = $exitCode === 0 ? 200 : 0;

            app(AsyncTaskStore::class)->update((string) ($payload['async_id'] ?? ''), array_merge($payload, [
                'status_code' => $status,
                'exit_code' => $exitCode,
                'duration_ms' => $durationMs,
                'output' => $output,
                'finished_at' => date('c'),
            ]));
            UpdateLogger::log('async.fallback_sync_migrate_finished', array_merge($payload, [
                'status' => $status,
                'exit_code' => $exitCode,
                'duration_ms' => $durationMs,
            ]));

            return [
                'status' => 'sync_fallback',
                'async_id' => (string) ($payload['async_id'] ?? ''),
                'requestid' => (string) ($payload['requestid'] ?? ''),
            ];
        } catch (\Throwable $exception) {
            $durationMs = (int) ((microtime(true) - $startedAt) * 1000);
            app(AsyncTaskStore::class)->update((string) ($payload['async_id'] ?? ''), array_merge($payload, [
                'status_code' => 0,
                'duration_ms' => $durationMs,
                'error' => $exception->getMessage(),
                'finished_at' => date('c'),
            ]));
            UpdateLogger::log('async.fallback_sync_migrate_exception', array_merge($payload, [
                'status' => 0,
                'duration_ms' => $durationMs,
                'error' => $exception->getMessage(),
            ]));

            throw $exception;
        }
    }

    private function generateTaskId(string $moduleName, string $actionName): string
    {
        return strtolower(trim($moduleName)) . '_' . trim($actionName) . '_' . uniqid();
    }
}
