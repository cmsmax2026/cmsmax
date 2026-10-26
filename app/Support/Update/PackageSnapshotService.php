<?php

namespace App\Support\Update;

use ZipArchive;

/**
 * 包目录快照服务。
 *
 * 在线安装 / 在线更新会直接用解压覆盖包目录，除 Config/config.php 外的本地改动
 * （改过的 Libs、Listeners、InitController 等）会被静默删掉且没有退路。
 * 这里在解压前把整个包目录打成 zip 存到 storage/backup/packages，
 * 解压失败时整包回滚，成功时保留最近若干份以便人工比对。
 *
 * 与 UpdateSnapshotService 的区别：后者是「主程序升级」的整站快照，
 * 这里是「单个模块/插件」的轻量快照，随每次在线更新自动生成。
 */
class PackageSnapshotService
{
    /**
     * 每个包保留的快照份数。
     */
    const KEEP = 5;

    /**
     * 快照存放根目录：storage/backup/packages
     */
    public static function basePath(): string
    {
        return storage_path('backup' . DIRECTORY_SEPARATOR . 'packages');
    }

    /**
     * 给目标包目录打快照。
     *
     * @return string|null 快照文件路径；目录不存在、ZipArchive 不可用或打包失败时返回 null
     */
    public static function create(string $cloudType, string $identification, string $targetPath): ?string
    {
        $targetPath = rtrim((string) $targetPath, '\\/');
        if ($targetPath === '' || !is_dir($targetPath) || !class_exists(ZipArchive::class)) {
            return null;
        }

        $parentPath = dirname($targetPath);
        $basename = basename($targetPath);
        $snapshotDir = self::basePath();
        if (!is_dir($snapshotDir) && !@mkdir($snapshotDir, 0755, true) && !is_dir($snapshotDir)) {
            return null;
        }

        $snapshotPath = $snapshotDir . DIRECTORY_SEPARATOR . self::buildFileName($cloudType, $identification);

        $zip = new ZipArchive();
        if ($zip->open($snapshotPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        $added = self::addDirectory($zip, $targetPath, $basename);
        $closed = $zip->close();

        if (!$added || !$closed || !is_file($snapshotPath)) {
            @unlink($snapshotPath);
            return null;
        }

        self::prune($cloudType, $identification);

        return $snapshotPath;
    }

    /**
     * 用快照恢复包目录。
     *
     * 先把目标目录清空，再把快照整体解压回去，保证不留半新半旧的中间态。
     */
    public static function restore(?string $snapshotPath, string $targetPath): bool
    {
        if (!is_string($snapshotPath) || $snapshotPath === '' || !is_file($snapshotPath)) {
            return false;
        }
        if (!class_exists(ZipArchive::class)) {
            return false;
        }

        $targetPath = rtrim((string) $targetPath, '\\/');
        if ($targetPath === '') {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($snapshotPath) !== true) {
            return false;
        }

        self::clearDirectory($targetPath);
        $extracted = $zip->extractTo(dirname($targetPath));
        $zip->close();

        return (bool) $extracted;
    }

    /**
     * 检测包目录里「安装之后又被改过」的文件。
     *
     * 基准取安装器写入的 lock 文件时间（没有 lock 就取包内最早的文件时间）。
     * Config/config.php 不计入：它由后台配置页改写，且在线更新时会自动保留其 value。
     *
     * @return array{total:int,samples:array}
     */
    public static function inspectLocalModifications(string $targetPath, int $sample = 5): array
    {
        $targetPath = rtrim((string) $targetPath, '\\/');
        if ($targetPath === '' || !is_dir($targetPath)) {
            return ['total' => 0, 'samples' => []];
        }

        $lockFile = $targetPath . DIRECTORY_SEPARATOR . 'lock';
        $baseline = is_file($lockFile) ? (int) @filemtime($lockFile) : 0;

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($targetPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            if (!$fileInfo->isFile()) {
                continue;
            }

            $path = $fileInfo->getPathname();
            $relative = ltrim(str_replace('\\', '/', substr($path, strlen($targetPath))), '/');
            // Config/config.php 由后台配置页改写、config.json 是主题清单，
            // 两者在解压后都会自动合并保留，不计入「本地改动」统计。
            if (in_array($relative, ['lock', 'Config/config.php', 'config.json'], true)) {
                continue;
            }

            $files[$relative] = (int) @filemtime($path);
        }

        if ($files === []) {
            return ['total' => 0, 'samples' => []];
        }

        if ($baseline <= 0) {
            $baseline = (int) min($files);
        }

        $modified = [];
        foreach ($files as $relative => $mtime) {
            if ($mtime > $baseline + 2) {
                $modified[] = $relative;
            }
        }

        return [
            'total' => count($modified),
            'samples' => array_slice($modified, 0, $sample),
        ];
    }

    /**
     * 列出某个包的历史快照（新的在前）。
     */
    public static function list(string $cloudType, string $identification): array
    {
        $prefix = self::fileNamePrefix($cloudType, $identification);
        $files = glob(self::basePath() . DIRECTORY_SEPARATOR . $prefix . '*.zip') ?: [];
        rsort($files);

        return array_values($files);
    }

    /**
     * 只保留最近 $keep 份快照。
     */
    public static function prune(string $cloudType, string $identification, int $keep = self::KEEP): int
    {
        $removed = 0;
        foreach (self::list($cloudType, $identification) as $index => $file) {
            if ($index < $keep) {
                continue;
            }
            if (@unlink($file)) {
                $removed++;
            }
        }

        return $removed;
    }

    private static function addDirectory(ZipArchive $zip, string $directory, string $relativeRoot): bool
    {
        $added = false;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            $path = $fileInfo->getPathname();
            $relative = $relativeRoot . '/' . ltrim(str_replace('\\', '/', substr($path, strlen($directory))), '/');

            if ($fileInfo->isDir()) {
                $zip->addEmptyDir(rtrim($relative, '/'));
                continue;
            }

            if (!$fileInfo->isFile()) {
                continue;
            }

            if ($zip->addFile($path, $relative)) {
                $added = true;
            }
        }

        return $added;
    }

    private static function buildFileName(string $cloudType, string $identification): string
    {
        // 同一秒内可能连续产生多份快照（重试、多包连更），补随机串避免互相覆盖
        $unique = substr(md5(uniqid('', true)), 0, 6);

        return self::fileNamePrefix($cloudType, $identification) . date('YmdHis') . '-' . $unique . '.zip';
    }

    private static function fileNamePrefix(string $cloudType, string $identification): string
    {
        $safeType = preg_replace('/[^a-z0-9]+/i', '', $cloudType) ?: 'package';
        $safeName = preg_replace('/[^a-z0-9]+/i', '', $identification) ?: 'unknown';

        return strtolower($safeType) . '-' . $safeName . '-';
    }

    private static function clearDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $target = $path . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($target) && !is_link($target)) {
                self::clearDirectory($target);
                @rmdir($target);
                continue;
            }

            @chmod($target, 0777);
            @unlink($target);
        }
    }
}
