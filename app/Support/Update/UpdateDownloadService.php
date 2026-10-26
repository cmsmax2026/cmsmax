<?php

namespace App\Support\Update;

class UpdateDownloadService
{
    private const DEFAULT_CONNECT_TIMEOUT = 20;
    private const DEFAULT_DOWNLOAD_STALL_TIMEOUT = 1800;

    public function download(string $remoteFileUrl, string $localFilePath, int $chunkSize = 3145728): array
    {
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '2560M');

        if (file_exists($localFilePath)) {
            @unlink($localFilePath);
        }

        mk_dir(dirname($localFilePath));
        if (!is_writable(dirname($localFilePath))) {
            chmod($localFilePath, 0777);
            if (!is_writable(dirname($localFilePath))) {
                return [
                    "status" => 500,
                    "msg" => "下载目录不可写",
                    "data" => []
                ];
            }
        }

        file_put_contents($localFilePath, "");
        chmod($localFilePath, 0777);
        $localFile = fopen($localFilePath, 'w+b');
        if (!$localFile) {
            return [
                "status" => 500,
                "msg" => "本地文件获取失败",
                "reason_code" => "local_file_open_failed",
                "data" => [
                    'timeout_seconds' => $this->downloadTimeoutSeconds(),
                ]
            ];
        }

        $ch = curl_init($remoteFileUrl);
        $progressState = [
            'last_bytes' => 0,
            'last_progress_at' => time(),
            'timed_out' => false,
        ];
        curl_setopt($ch, CURLOPT_FILE, $localFile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeoutSeconds());
        curl_setopt($ch, CURLOPT_BUFFERSIZE, max(8192, $chunkSize));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MXZCMS-Updater/1.0');
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $downloadSize, $downloadedBytes) use (&$progressState) {
            if ($downloadedBytes > $progressState['last_bytes']) {
                $progressState['last_bytes'] = (int) $downloadedBytes;
                $progressState['last_progress_at'] = time();
                return 0;
            }

            if ((time() - (int) $progressState['last_progress_at']) > $this->downloadStallTimeoutSeconds()) {
                $progressState['timed_out'] = true;
                return 1;
            }

            return 0;
        });

        $execResult = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        fflush($localFile);
        fclose($localFile);

        if ($execResult === false || $errno !== 0 || $httpCode >= 400) {
            $this->cleanupFailedDownload($localFilePath);

            $reasonCode = 'download_failed';
            $msg = '更新包下载失败';
            if ($progressState['timed_out']) {
                $reasonCode = 'download_stall_timeout';
                $msg = '更新包长时间无下载进度，已判定超时，请重试';
            } elseif ($errno === CURLE_OPERATION_TIMEDOUT) {
                $reasonCode = 'download_timeout';
                $msg = '更新包下载超时，请重试或调大超时设置';
            } elseif ($errno !== 0) {
                $reasonCode = 'download_curl_failed';
                $msg = '更新包下载失败：' . ($error !== '' ? $error : ('curl errno ' . $errno));
            } elseif ($httpCode >= 400) {
                $reasonCode = 'download_http_' . $httpCode;
                $msg = '更新包下载失败，远程返回 HTTP ' . $httpCode;
            }

            return [
                "status" => 500,
                "msg" => $msg,
                "reason_code" => $reasonCode,
                "data" => [
                    'timeout_seconds' => $this->downloadStallTimeoutSeconds(),
                    'timeout_mode' => 'stall',
                    'connect_timeout_seconds' => $this->connectTimeoutSeconds(),
                    'curl_errno' => $errno,
                    'curl_error' => $error,
                    'http_code' => $httpCode,
                    'downloaded_bytes' => $progressState['last_bytes'],
                ]
            ];
        }

        clearstatcache(true, $localFilePath);
        $validated = $this->validateDownloadedPackage($localFilePath, [
            'http_code' => $httpCode,
            'curl_errno' => $errno,
            'curl_error' => $error,
        ]);
        if (($validated['status'] ?? 0) !== 200) {
            $this->cleanupFailedDownload($localFilePath);
            return $validated;
        }

        return [
            "status" => 200,
            "msg" => "下载完成",
            "data" => [
                'timeout_seconds' => $this->downloadStallTimeoutSeconds(),
                'timeout_mode' => 'stall',
                'connect_timeout_seconds' => $this->connectTimeoutSeconds(),
            ]
        ];
    }

    public function fileSize(string $localFilePath): array
    {
        if (!file_exists($localFilePath)) {
            return UpdateResponseFactory::success('等待下载开始', [
                'size' => 0,
                'not_ready' => 1,
            ]);
        }

        return UpdateResponseFactory::downloadedFileSize((int) filesize($localFilePath));
    }

    private function cleanupFailedDownload(string $localFilePath): void
    {
        clearstatcache(true, $localFilePath);
        if (file_exists($localFilePath)) {
            @unlink($localFilePath);
        }
    }

    private function connectTimeoutSeconds(): int
    {
        return max(5, (int) env('MXZ_UPDATE_DOWNLOAD_CONNECT_TIMEOUT', self::DEFAULT_CONNECT_TIMEOUT));
    }

    private function downloadStallTimeoutSeconds(): int
    {
        return max(60, (int) env('MXZ_UPDATE_DOWNLOAD_STALL_TIMEOUT', env('MXZ_UPDATE_DOWNLOAD_TIMEOUT', self::DEFAULT_DOWNLOAD_STALL_TIMEOUT)));
    }

    private function validateDownloadedPackage(string $localFilePath, array $context = []): array
    {
        if (!file_exists($localFilePath)) {
            return [
                'status' => 500,
                'msg' => '更新包下载失败：本地文件不存在',
                'reason_code' => 'download_missing_file',
                'data' => $context,
            ];
        }

        $size = @filesize($localFilePath);
        if (!is_int($size) || $size <= 0) {
            return [
                'status' => 500,
                'msg' => '更新包下载失败：文件大小异常',
                'reason_code' => 'download_empty_file',
                'data' => array_merge($context, [
                    'size' => is_int($size) ? $size : null,
                ]),
            ];
        }

        $head = $this->readFileHead($localFilePath, 512);
        $headPreview = $this->normalizePreview($head);
        if ($this->looksLikeZip($head)) {
            return [
                'status' => 200,
                'msg' => 'ok',
                'data' => [
                    'size' => $size,
                ],
            ];
        }

        if ($this->looksLikeHtml($head)) {
            return [
                'status' => 500,
                'msg' => '更新包内容异常（疑似返回了 HTML 页面），请重试',
                'reason_code' => 'download_invalid_html',
                'data' => array_merge($context, [
                    'size' => $size,
                    'head' => $headPreview,
                ]),
            ];
        }

        if ($this->looksLikeJson($head)) {
            return [
                'status' => 500,
                'msg' => '更新包内容异常（疑似返回了 JSON 错误信息），请重试',
                'reason_code' => 'download_invalid_json',
                'data' => array_merge($context, [
                    'size' => $size,
                    'head' => $headPreview,
                ]),
            ];
        }

        return [
            'status' => 500,
            'msg' => '更新包内容异常（非 ZIP 格式），请重试',
            'reason_code' => 'download_invalid_content',
            'data' => array_merge($context, [
                'size' => $size,
                'head' => $headPreview,
            ]),
        ];
    }

    private function readFileHead(string $path, int $length): string
    {
        $fp = @fopen($path, 'rb');
        if (!$fp) {
            return '';
        }
        $data = (string) @fread($fp, $length);
        @fclose($fp);
        return $data;
    }

    private function looksLikeZip(string $head): bool
    {
        if ($head === '') {
            return false;
        }
        return strncmp($head, "PK\x03\x04", 4) === 0
            || strncmp($head, "PK\x05\x06", 4) === 0
            || strncmp($head, "PK\x07\x08", 4) === 0;
    }

    private function looksLikeHtml(string $head): bool
    {
        $t = ltrim($this->normalizePreview($head));
        if ($t === '') {
            return false;
        }
        return stripos($t, '<!doctype') === 0
            || stripos($t, '<html') === 0
            || stripos($t, '<head') === 0
            || stripos($t, '<body') === 0;
    }

    private function looksLikeJson(string $head): bool
    {
        $t = ltrim($this->normalizePreview($head));
        if ($t === '') {
            return false;
        }
        $first = $t[0] ?? '';
        return $first === '{' || $first === '[';
    }

    private function normalizePreview(string $text): string
    {
        $text = str_replace(["\r\n", "\r", "\n", "\t"], ' ', (string) $text);
        $text = preg_replace('/\s+/', ' ', $text) ?: $text;
        return mb_substr($text, 0, 240);
    }
}
