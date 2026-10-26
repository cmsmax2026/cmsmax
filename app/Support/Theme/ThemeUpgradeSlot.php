<?php

namespace App\Support\Theme;

/**
 * 主题升级槽位。
 *
 * 背景：在线升级主题是「解压覆盖」，用户改过的 blade / css / js 会被静默覆盖，
 * 既没有快照也没有提示。这里改成：
 *
 *   本地文件被改过  -> 不覆盖，新版本写进 themes/<id>/_updates/<version>/<原路径>
 *   本地文件没改过  -> 正常覆盖
 *
 * 用户可以在后台「主题 - 更新槽位」里逐文件对比、选择性应用或丢弃。
 * 插件可以监听 ThemeUpgradeFile 改变某个文件的处置方式。
 */
class ThemeUpgradeSlot
{
    /**
     * 处置方式。
     */
    public const ACTION_OVERWRITE = 'overwrite';
    public const ACTION_SLOT = 'slot';

    /**
     * mtime 容差（秒），避免文件系统时间戳精度造成误判。
     */
    const MTIME_TOLERANCE = 2;

    /**
     * 决定一个文件该怎么处置。
     *
     * @return array{action:string,reason:string}
     */
    public static function decide(string $theme, string $relativePath): array
    {
        $relativePath = self::normalizeRelative($relativePath);

        // 1. 主题显式声明的保护路径（config.json 的 protection.protected_paths）
        if (self::isProtected($theme, $relativePath)) {
            return [
                'action' => self::ACTION_SLOT,
                'reason' => '主题声明为保护路径',
            ];
        }

        // 2. 槽位目录自身永远不参与覆盖，也不该被判定成"本地改动"
        if ($relativePath === ThemeManager::SLOT_DIR || strpos($relativePath, ThemeManager::SLOT_DIR . '/') === 0) {
            return [
                'action' => self::ACTION_OVERWRITE,
                'reason' => '槽位目录',
            ];
        }

        // 3. 本地不存在 -> 新文件，直接写入
        $localFile = ThemeManager::path($theme, $relativePath);
        if (!is_file($localFile)) {
            return [
                'action' => self::ACTION_OVERWRITE,
                'reason' => '新增文件',
            ];
        }

        // 4. 本地改过 -> 进槽位
        $baseline = self::baseline($theme);
        $mtime = (int) @filemtime($localFile);
        if ($baseline > 0 && $mtime > $baseline + self::MTIME_TOLERANCE) {
            return [
                'action' => self::ACTION_SLOT,
                'reason' => '本地已修改',
            ];
        }

        // 5. 插件最后一次介入机会
        foreach ((array) hook('ThemeUpgradeFile', [
            'theme' => $theme,
            'path' => $relativePath,
            'baseline' => $baseline,
            'mtime' => $mtime,
        ]) as $decision) {
            if (is_array($decision) && isset($decision['action'])) {
                return [
                    'action' => (string) $decision['action'] === self::ACTION_SLOT ? self::ACTION_SLOT : self::ACTION_OVERWRITE,
                    'reason' => (string) ($decision['reason'] ?? '插件决定'),
                ];
            }
        }

        return [
            'action' => self::ACTION_OVERWRITE,
            'reason' => '未检测到本地改动',
        ];
    }

    /**
     * 把新版本文件写进槽位。
     */
    public static function write(string $theme, string $version, string $relativePath, string $content, string $reason = ''): bool
    {
        $relativePath = self::normalizeRelative($relativePath);
        $version = ThemeManager::sanitizeVersion($version);
        if ($relativePath === '' || strpos($relativePath, '..') !== false) {
            return false;
        }

        $target = ThemeManager::slotPath($theme, $version) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $dir = dirname($target);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }

        if (@file_put_contents($target, $content) === false) {
            return false;
        }

        return self::appendManifest($theme, $version, [
            'path' => $relativePath,
            'reason' => $reason,
            'size' => (int) @filesize($target),
            'local_mtime' => (int) @filemtime(ThemeManager::path($theme, $relativePath)),
            'written_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 把一个已存在的文件复制进槽位（升级时用：新版本文件落槽）。
     */
    public static function put(string $theme, string $version, string $relativePath, string $sourceFile, string $reason = ''): bool
    {
        $relativePath = self::normalizeRelative($relativePath);
        if ($relativePath === '' || strpos($relativePath, '..') !== false || !is_file($sourceFile)) {
            return false;
        }

        $version = ThemeManager::sanitizeVersion($version);
        $target = ThemeManager::slotPath($theme, $version) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $dir = dirname($target);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }

        if (!@copy($sourceFile, $target)) {
            return false;
        }
        @chmod($target, 0755);

        return self::appendManifest($theme, $version, [
            'path' => $relativePath,
            'reason' => $reason,
            'size' => (int) @filesize($target),
            'local_mtime' => (int) @filemtime(ThemeManager::path($theme, $relativePath)),
            'written_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 读取某个版本的槽位清单。
     */
    public static function manifest(string $theme, string $version): array
    {
        $version = ThemeManager::sanitizeVersion($version);
        $file = ThemeManager::slotManifestPath($theme, $version);
        if (!is_file($file)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($file), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * 列出主题下所有槽位版本（新的在前）。
     *
     * @return array<int, array{version:string,path:string,count:int,created_at:string}>
     */
    public static function versions(string $theme): array
    {
        $root = ThemeManager::slotPath($theme);
        if (!is_dir($root)) {
            return [];
        }

        $items = [];
        foreach (array_values(array_diff((array) scandir($root), ['.', '..'])) as $entry) {
            if (!is_dir($root . DIRECTORY_SEPARATOR . $entry)) {
                continue;
            }
            $manifest = self::manifest($theme, $entry);
            $items[] = [
                'version' => $entry,
                'path' => $root . DIRECTORY_SEPARATOR . $entry,
                'count' => count((array) ($manifest['files'] ?? [])),
                'created_at' => (string) ($manifest['created_at'] ?? ''),
            ];
        }

        // 版本号要按数字比较，否则 1.10 会被排到 1.9 前面
        usort($items, static function (array $a, array $b) {
            return version_compare((string) $b['version'], (string) $a['version']);
        });

        return $items;
    }

    /**
     * 应用槽位文件到主题（覆盖本地文件）。
     *
     * @param array<int, string> $files 指定要应用的文件，空数组表示全部
     */
    public static function apply(string $theme, string $version, array $files = []): array
    {
        $manifest = self::manifest($theme, $version);
        $slotRoot = ThemeManager::slotPath($theme, $version);

        $applied = [];
        $failed = [];
        foreach ((array) ($manifest['files'] ?? []) as $item) {
            $relative = (string) ($item['path'] ?? '');
            if ($relative === '') {
                continue;
            }
            if ($files !== [] && !in_array($relative, $files, true)) {
                continue;
            }

            $source = $slotRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $target = ThemeManager::path($theme, $relative);
            if (!is_file($source)) {
                $failed[] = $relative;
                continue;
            }

            $dir = dirname($target);
            if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
                $failed[] = $relative;
                continue;
            }

            if (@copy($source, $target)) {
                @chmod($target, 0755);
                $applied[] = $relative;
            } else {
                $failed[] = $relative;
            }
        }

        if ($applied !== []) {
            self::touchBaseline($theme);
        }

        return ['applied' => $applied, 'failed' => $failed];
    }

    /**
     * 丢弃某个版本的槽位。
     */
    public static function discard(string $theme, string $version): bool
    {
        $root = ThemeManager::slotPath($theme, $version);
        if (!is_dir($root)) {
            return false;
        }

        return self::removeDirectory($root);
    }

    /**
     * 读取槽位中某个文件的内容。
     */
    public static function read(string $theme, string $version, string $relativePath): ?string
    {
        $relativePath = self::normalizeRelative($relativePath);
        if ($relativePath === '' || strpos($relativePath, '..') !== false) {
            return null;
        }

        $file = ThemeManager::slotPath($theme, $version) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        return is_file($file) ? (string) file_get_contents($file) : null;
    }

    /**
     * 站点内所有待处理的槽位，供后台概览。
     */
    public static function pending(?string $theme = null): array
    {
        $themes = $theme !== null ? [$theme] : array_values(array_diff(
            (array) @scandir(rtrim((string) public_path('views/themes'), '\\/')),
            ['.', '..']
        ));

        $pending = [];
        foreach ($themes as $item) {
            $item = (string) $item;
            if ($item === '' || !is_dir(public_path('views/themes/' . $item))) {
                continue;
            }
            foreach (self::versions($item) as $version) {
                if ((int) $version['count'] <= 0) {
                    continue;
                }
                $pending[] = array_merge($version, ['theme' => $item]);
            }
        }

        return $pending;
    }

    /**
     * 本地改动基线：优先 lock 文件，其次 config.json，最后取包内最早文件时间。
     */
    public static function baseline(string $theme): int
    {
        $root = ThemeManager::path($theme);
        if (!is_dir($root)) {
            return 0;
        }

        $lock = $root . DIRECTORY_SEPARATOR . 'lock';
        if (is_file($lock)) {
            return (int) @filemtime($lock);
        }

        $config = $root . DIRECTORY_SEPARATOR . 'config.json';
        if (is_file($config)) {
            return (int) @filemtime($config);
        }

        $earliest = 0;
        foreach (self::listFiles($root) as $file) {
            $mtime = (int) @filemtime($file);
            if ($earliest === 0 || $mtime < $earliest) {
                $earliest = $mtime;
            }
        }

        return $earliest;
    }

    /**
     * 刷新基线（升级/应用槽位后调用），让后续改动能被正确识别。
     */
    public static function touchBaseline(string $theme): void
    {
        $root = ThemeManager::path($theme);
        if (!is_dir($root)) {
            return;
        }

        @file_put_contents($root . DIRECTORY_SEPARATOR . 'lock', date('Y-m-d H:i:s'));
    }

    /**
     * 主题是否在 config.json 里把该路径声明为保护路径。
     */
    public static function isProtected(string $theme, string $relativePath): bool
    {
        $manifest = ThemeManager::manifest($theme);
        $paths = (array) ($manifest['protection']['protected_paths'] ?? []);
        if ($paths === []) {
            return false;
        }

        foreach ($paths as $pattern) {
            $pattern = self::normalizeRelative((string) $pattern);
            if ($pattern === '') {
                continue;
            }
            if ($pattern === $relativePath) {
                return true;
            }
            if (fnmatch($pattern, $relativePath)) {
                return true;
            }
        }

        return false;
    }

    private static function appendManifest(string $theme, string $version, array $entry): bool
    {
        $root = ThemeManager::slotPath($theme, $version);
        if (!is_dir($root) && !@mkdir($root, 0755, true) && !is_dir($root)) {
            return false;
        }

        $manifest = self::manifest($theme, $version);
        $manifest['theme'] = $theme;
        $manifest['version'] = ThemeManager::sanitizeVersion($version);
        $manifest['created_at'] = $manifest['created_at'] ?? date('Y-m-d H:i:s');
        $manifest['files'] = $manifest['files'] ?? [];

        // 同路径重复写入时覆盖旧记录
        $manifest['files'] = array_values(array_filter(
            (array) $manifest['files'],
            static function ($item) use ($entry) {
                return (string) ($item['path'] ?? '') !== $entry['path'];
            }
        ));
        $manifest['files'][] = $entry;

        return @file_put_contents(
            ThemeManager::slotManifestPath($theme, $version),
            json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        ) !== false;
    }

    private static function listFiles(string $root): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $files[] = $fileInfo->getPathname();
            }
        }

        return $files;
    }

    private static function removeDirectory(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }

        foreach (array_values(array_diff((array) @scandir($path), ['.', '..'])) as $entry) {
            $target = $path . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($target) && !is_link($target)) {
                self::removeDirectory($target);
                continue;
            }
            @chmod($target, 0777);
            @unlink($target);
        }

        return @rmdir($path);
    }

    private static function normalizeRelative(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));

        return trim(preg_replace('#/{2,}#', '/', $path) ?: '', '/');
    }
}
