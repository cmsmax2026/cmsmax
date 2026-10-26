<?php

namespace App\Support\Async;

class AsyncTaskStore
{
    private function baseDir(): string
    {
        return storage_path('async/tasks');
    }

    private function taskPath(string $asyncId): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $asyncId) ?: 'task';
        return $this->baseDir() . DIRECTORY_SEPARATOR . $safe . '.json';
    }

    private function ensureDir(): void
    {
        $dir = $this->baseDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    public function init(array $payload): void
    {
        $asyncId = trim((string) ($payload['async_id'] ?? ''));
        if ($asyncId === '') {
            return;
        }

        $this->ensureDir();
        $now = date('c');
        $data = [
            'async_id' => $asyncId,
            'requestid' => (string) ($payload['requestid'] ?? ''),
            'moduleName' => (string) ($payload['moduleName'] ?? ''),
            'actionName' => (string) ($payload['actionName'] ?? ''),
            'arguments' => is_array($payload['arguments'] ?? null) ? $payload['arguments'] : [],
            'context' => is_array($payload['context'] ?? null) ? $payload['context'] : [],
            'status_code' => 102,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $path = $this->taskPath($asyncId);
        if (is_file($path)) {
            return;
        }

        $this->writeJsonAtomic($path, $data);
    }

    public function update(string $asyncId, array $fields): void
    {
        $asyncId = trim($asyncId);
        if ($asyncId === '') {
            return;
        }

        $this->ensureDir();
        $path = $this->taskPath($asyncId);
        $data = $this->readJson($path) ?: [
            'async_id' => $asyncId,
            'created_at' => date('c'),
        ];

        foreach ($fields as $key => $value) {
            $data[$key] = $value;
        }
        $data['updated_at'] = date('c');

        if (isset($data['output']) && is_string($data['output']) && strlen($data['output']) > 2000) {
            $data['output'] = substr($data['output'], 0, 2000);
        }
        if (isset($data['error']) && is_string($data['error']) && strlen($data['error']) > 500) {
            $data['error'] = substr($data['error'], 0, 500);
        }

        $this->writeJsonAtomic($path, $data);
    }

    public function get(string $asyncId): ?array
    {
        $asyncId = trim($asyncId);
        if ($asyncId === '') {
            return null;
        }

        $path = $this->taskPath($asyncId);
        return $this->readJson($path);
    }

    private function readJson(string $path): ?array
    {
        if (!is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function writeJsonAtomic(string $path, array $data): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $tmp = $path . '.tmp.' . bin2hex(random_bytes(6));
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($payload)) {
            return;
        }

        file_put_contents($tmp, $payload);
        @chmod($tmp, 0644);
        @rename($tmp, $path);
    }
}

