<?php

namespace App\Support\Env;

class EnvSyncService
{
    public function syncFromExample(array $envOverrides = [], array $exampleOverrides = []): array
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        if (!is_file($examplePath)) {
            return [
                'status' => 0,
                'msg' => '.env.example 不存在，无法同步环境配置',
                'data' => [
                    'env_created' => false,
                    'env_added_keys' => [],
                    'env_updated_keys' => [],
                    'example_updated_keys' => [],
                ],
            ];
        }

        $exampleOriginal = (string) file_get_contents($examplePath);
        $exampleUpdateResult = $this->applyKeyValueUpdates($exampleOriginal, $exampleOverrides);
        $exampleContent = $exampleUpdateResult['content'];

        if ($exampleContent !== $exampleOriginal) {
            file_put_contents($examplePath, $exampleContent);
        }

        $envCreated = false;
        if (is_file($envPath)) {
            $envOriginal = (string) file_get_contents($envPath);
        } else {
            $envOriginal = '';
            $envCreated = true;
        }

        $envWorking = $envCreated ? $exampleContent : $envOriginal;
        $envUpdateResult = $this->applyKeyValueUpdates($envWorking, $envOverrides);
        $envMergedResult = $this->appendMissingEntriesFromExample($envUpdateResult['content'], $exampleContent);
        $envContent = $envMergedResult['content'];

        if ($envCreated || $envContent !== $envOriginal) {
            file_put_contents($envPath, $envContent);
        }

        $addedKeys = $envCreated
            ? array_values(array_unique(array_keys($this->parseEnvAssignments($exampleContent))))
            : $envMergedResult['added_keys'];

        return [
            'status' => 200,
            'msg' => $this->buildSummaryMessage($envCreated, $addedKeys, $envUpdateResult['updated_keys'], $exampleUpdateResult['updated_keys']),
            'data' => [
                'env_created' => $envCreated,
                'env_added_keys' => $addedKeys,
                'env_updated_keys' => $envUpdateResult['updated_keys'],
                'example_updated_keys' => $exampleUpdateResult['updated_keys'],
            ],
        ];
    }

    private function applyKeyValueUpdates(string $content, array $updates): array
    {
        $updatedKeys = [];
        if (empty($updates)) {
            return [
                'content' => $content,
                'updated_keys' => [],
            ];
        }

        foreach ($updates as $key => $value) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            $replacement = $key . '=' . $this->normalizeValue($value);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
            if (preg_match($pattern, $content)) {
                $newContent = (string) preg_replace($pattern, $replacement, $content, 1);
                if ($newContent !== $content) {
                    $updatedKeys[] = $key;
                    $content = $newContent;
                }
                continue;
            }

            $content = rtrim($content) . PHP_EOL . $replacement . PHP_EOL;
            $updatedKeys[] = $key;
        }

        return [
            'content' => $content,
            'updated_keys' => array_values(array_unique($updatedKeys)),
        ];
    }

    private function appendMissingEntriesFromExample(string $envContent, string $exampleContent): array
    {
        $envKeys = $this->parseEnvAssignments($envContent);
        $exampleLines = preg_split("/\r\n|\n|\r/", $exampleContent);
        $pendingLines = [];
        $additions = [];
        $addedKeys = [];

        foreach ($exampleLines as $line) {
            $parsed = $this->parseAssignmentLine($line);
            if ($parsed === null) {
                $pendingLines[] = $line;
                continue;
            }

            $key = $parsed['key'];
            if (isset($envKeys[$key])) {
                $pendingLines = [];
                continue;
            }

            if ($pendingLines) {
                foreach ($pendingLines as $pendingLine) {
                    if ($pendingLine === '' && (!empty($additions) && end($additions) === '')) {
                        continue;
                    }
                    $additions[] = $pendingLine;
                }
            }

            $additions[] = $line;
            $addedKeys[] = $key;
            $envKeys[$key] = true;
            $pendingLines = [];
        }

        if (!$additions) {
            return [
                'content' => $envContent,
                'added_keys' => [],
            ];
        }

        $normalizedAdditions = implode(PHP_EOL, $this->trimTrailingBlankLines($additions));
        $separator = $envContent === '' ? '' : PHP_EOL;
        $content = rtrim($envContent) . $separator . PHP_EOL . '# Synced from .env.example' . PHP_EOL . $normalizedAdditions . PHP_EOL;

        return [
            'content' => $content,
            'added_keys' => array_values(array_unique($addedKeys)),
        ];
    }

    private function parseEnvAssignments(string $content): array
    {
        $keys = [];
        foreach (preg_split("/\r\n|\n|\r/", $content) as $line) {
            $parsed = $this->parseAssignmentLine($line);
            if ($parsed !== null) {
                $keys[$parsed['key']] = true;
            }
        }

        return $keys;
    }

    private function parseAssignmentLine(string $line): ?array
    {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            return null;
        }

        if (!preg_match('/^([A-Za-z_][A-Za-z0-9_]*)=(.*)$/', $line, $matches)) {
            return null;
        }

        return [
            'key' => trim((string) ($matches[1] ?? '')),
            'value' => (string) ($matches[2] ?? ''),
        ];
    }

    private function normalizeValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if ($value === null) {
            return '';
        }

        $value = (string) $value;
        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"/', $value)) {
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
        }

        return $value;
    }

    private function trimTrailingBlankLines(array $lines): array
    {
        while (!empty($lines) && trim((string) end($lines)) === '') {
            array_pop($lines);
        }

        return $lines;
    }

    private function buildSummaryMessage(bool $envCreated, array $addedKeys, array $envUpdatedKeys, array $exampleUpdatedKeys): string
    {
        $parts = [];
        if ($envCreated) {
            $parts[] = '已创建 .env';
        }
        if ($addedKeys) {
            $parts[] = '.env 已补充缺失配置 ' . count($addedKeys) . ' 项';
        }
        if ($envUpdatedKeys) {
            $parts[] = '.env 已更新配置 ' . implode('、', $envUpdatedKeys);
        }
        if ($exampleUpdatedKeys) {
            $parts[] = '.env.example 已更新配置 ' . implode('、', $exampleUpdatedKeys);
        }

        return $parts ? implode('；', $parts) : '.env 与 .env.example 无需同步';
    }
}
