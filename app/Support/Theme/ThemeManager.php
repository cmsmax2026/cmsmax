<?php

namespace App\Support\Theme;

use App\Support\PackageManifest\PackageManifest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Main\Models\Modules;

/**
 * 主题管理器。
 *
 * 主题与模块是两套独立体系：模块自己带视图（命名空间 `bbs::home.*`），
 * 主题只负责 `public/views/themes/<id>/` 下的模板与资源。
 * 主题需要模块数据时，走 ThemeDataRegistry 动态调用（见 ThemeDataRegistry）。
 *
 * 职责：
 * - 当前主题解析与缓存
 * - 视图名解析（支持 parent 继承链 + default 兜底）
 * - 主题资源 URL / 路径
 * - 升级槽位路径
 */
class ThemeManager
{
    /**
     * 兜底主题：任何主题缺失的文件都会回退到这里。
     */
    public const FALLBACK_THEME = 'default';

    /**
     * 升级槽位目录名（主题根目录下）。
     */
    public const SLOT_DIR = '_updates';

    /**
     * 继承链最大深度，防止 parent 配置成环。
     */
    const MAX_CHAIN_DEPTH = 8;

    /**
     * 预览主题（仅当次请求有效，不写缓存）。
     *
     * 后台预览一个「未启用」的主题时，必须把当前主题临时切过去，
     * 否则 @themeAsset() / @themeInclude() 仍然按已启用主题的继承链解析，
     * 被预览主题的静态资源会全部 404。
     *
     * @var string|null
     */
    private static $previewTheme = null;

    /**
     * 当前主题标识。
     */
    public static function current(): string
    {
        if (self::$previewTheme !== null && self::$previewTheme !== '') {
            return self::$previewTheme;
        }

        if (Cache::has('theme')) {
            $theme = (string) Cache::get('theme');
            if ($theme !== '') {
                return $theme;
            }
        }

        $theme = self::resolveActiveTheme();
        Cache::forever('theme', $theme);

        return $theme;
    }

    /**
     * 临时把当前主题切换成指定主题（后台预览用），传 null 取消。
     */
    public static function asTheme(?string $theme): void
    {
        $theme = $theme === null ? null : trim($theme);
        self::$previewTheme = ($theme === null || $theme === '') ? null : $theme;
    }

    public static function previewTheme(): ?string
    {
        return self::$previewTheme;
    }

    /**
     * 在指定主题上下文里执行一段逻辑，结束后恢复。
     */
    public static function runAs(string $theme, callable $callback, ...$args)
    {
        $previous = self::$previewTheme;
        self::asTheme($theme);

        try {
            return $callback(...$args);
        } finally {
            self::$previewTheme = $previous;
        }
    }

    /**
     * 从数据库解析启用中的主题，拿不到时回退 default。
     */
    public static function resolveActiveTheme(): string
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('themes')) {
                return self::FALLBACK_THEME;
            }
            $theme = DB::table('themes')->where('status', 1)->value('identification');
        } catch (\Throwable $exception) {
            $theme = null;
        }

        return $theme ? (string) $theme : self::FALLBACK_THEME;
    }

    /**
     * 主题清单（config.json 经 PackageManifest 归一化）。
     */
    public static function manifest(?string $theme = null): array
    {
        static $cache = [];

        $theme = $theme ?: self::current();
        if (array_key_exists($theme, $cache)) {
            return $cache[$theme];
        }

        try {
            $manifest = PackageManifest::load($theme, Modules::Theme);
        } catch (\Throwable $exception) {
            $manifest = null;
        }

        return $cache[$theme] = is_array($manifest) ? $manifest : [];
    }

    /**
     * 主题是否启用短标签解析（config.json 里 `"shorttags": false` 可关闭）。
     */
    public static function shortTagsEnabled(?string $theme = null): bool
    {
        $manifest = self::manifest($theme);
        if (!array_key_exists('shorttags', $manifest)) {
            return true;
        }

        return (bool) $manifest['shorttags'];
    }

    /**
     * 主题根目录绝对路径。
     */
    public static function path(?string $theme = null, string $sub = ''): string
    {
        $theme = $theme ?: self::current();
        $base = rtrim((string) public_path('views/themes/' . $theme), '\\/');
        if ($sub === '') {
            return $base;
        }

        return $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($sub, '\\/'));
    }

    /**
     * 主题继承链：自身 -> parent -> ... -> default。
     *
     * config.json 里声明 `"parent": "default"` 即可复用父主题文件，
     * 子主题只需要放差异文件。
     */
    public static function chain(?string $theme = null): array
    {
        static $cache = [];

        $theme = $theme ?: self::current();
        if (isset($cache[$theme])) {
            return $cache[$theme];
        }

        $chain = [];
        $cursor = $theme;
        $depth = 0;

        while ($depth < self::MAX_CHAIN_DEPTH) {
            $depth++;
            if ($cursor === '' || in_array($cursor, $chain, true)) {
                break;
            }
            $chain[] = $cursor;

            $parent = (string) (self::manifest($cursor)['parent'] ?? '');
            if ($parent === '' || strcasecmp($parent, $cursor) === 0) {
                break;
            }
            $cursor = $parent;
        }

        // 兜底主题始终放在最后，保证任何文件都找得到
        if (!in_array(self::FALLBACK_THEME, $chain, true) && self::fallbackEnabled($theme)) {
            $chain[] = self::FALLBACK_THEME;
        }

        return $cache[$theme] = $chain;
    }

    /**
     * 把点号路径解析成实际存在的视图名，找不到时返回 null。
     *
     * @param string $path 主题内的相对视图路径，如 `public.head`、`model.overwrite.list`
     */
    public static function resolveView(string $path, ?string $theme = null): ?string
    {
        $path = self::normalizeViewPath($path);
        if ($path === '') {
            return null;
        }

        foreach (self::chain($theme) as $candidate) {
            // 必须只在「某一个主题目录内」判断，不能用 fileExists()：
            // 后者本身会再遍历一次继承链，会导致命中父主题时错返回子主题的视图名。
            if (self::viewFileExistsIn($path, $candidate)) {
                return 'themes.' . $candidate . '.' . $path;
            }
        }

        return null;
    }

    /**
     * 只在指定主题目录内判断视图文件是否存在（不查 parent）。
     */
    public static function viewFileExistsIn(string $path, string $theme): bool
    {
        $base = str_replace('.', '/', self::normalizeViewPath($path));
        if ($base === '') {
            return false;
        }

        return is_file(self::path($theme, $base . '.blade.php'))
            || is_file(self::path($theme, $base . '.php'));
    }

    /**
     * 解析视图名，找不到时仍返回「当前主题」的视图名，让 Laravel 抛标准异常。
     */
    public static function viewName(string $path, ?string $theme = null): string
    {
        return self::resolveView($path, $theme)
            ?: 'themes.' . ($theme ?: self::current()) . '.' . self::normalizeViewPath($path);
    }

    /**
     * 主题内视图文件是否存在（允许 `.blade.php` / `.php`）。
     */
    public static function viewFileExists(string $path, ?string $theme = null): bool
    {
        // Blade 用点号表示目录层级：public.head -> public/head.blade.php
        $base = str_replace('.', '/', self::normalizeViewPath($path));
        if ($base === '') {
            return false;
        }

        return self::fileExists($base . '.blade.php', $theme)
            || self::fileExists($base . '.php', $theme);
    }

    /**
     * 只在指定主题目录内判断文件是否存在（不查 parent 链）。
     */
    public static function fileExistsIn(string $relativePath, string $theme): bool
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        return $relativePath !== '' && is_file(self::path($theme, $relativePath));
    }

    /**
     * 沿继承链查找文件是否存在（传主题内的相对路径，如 `public/head.blade.php`）。
     */
    public static function fileExists(string $relativePath, ?string $theme = null): bool
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if ($relativePath === '') {
            return false;
        }

        foreach (self::chain($theme) as $candidate) {
            if (is_file(self::path($candidate, $relativePath))) {
                return true;
            }
        }

        return false;
    }

    /**
     * 返回第一个命中的主题文件的绝对路径，没找到返回 null。
     */
    public static function findFile(string $relativePath, ?string $theme = null): ?string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if ($relativePath === '') {
            return null;
        }

        foreach (self::chain($theme) as $candidate) {
            $full = self::path($candidate, $relativePath);
            if (is_file($full)) {
                return $full;
            }
        }

        return null;
    }

    /**
     * 返回第一个命中的主题名（用于资源回退），没找到返回 null。
     */
    public static function findThemeOf(string $relativePath, ?string $theme = null): ?string
    {
        foreach (self::chain($theme) as $candidate) {
            if (self::fileExistsIn($relativePath, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * 主题资源 URL，如 `css/app.css` -> `http://site/views/themes/<id>/assets/css/app.css`。
     *
     * 资源缺失时沿继承链回退，子主题没放资源也能用父主题的。
     */
    public static function asset(string $path = '', ?string $theme = null): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if ($path === '') {
            return rtrim((string) asset('views/themes/' . ($theme ?: self::current())), '/') . '/';
        }

        $target = $theme ?: self::current();
        $relative = 'assets/' . $path;
        foreach (self::chain($theme) as $candidate) {
            if (is_file(self::path($candidate, $relative))) {
                $target = $candidate;
                break;
            }
        }

        return asset('views/themes/' . $target . '/' . $relative);
    }

    /**
     * 升级槽位根目录。
     *
     * 主题升级时，命中「本地已修改」的文件不会覆盖原文件，
     * 新版本写入 `themes/<id>/_updates/<version>/<原相对路径>`，供人工比对合并。
     */
    public static function slotPath(?string $theme = null, string $version = ''): string
    {
        $base = self::path($theme, self::SLOT_DIR);
        if ($version === '') {
            return $base;
        }

        $version = self::sanitizeVersion($version);

        return $base . DIRECTORY_SEPARATOR . $version;
    }

    public static function slotManifestPath(?string $theme = null, string $version = ''): string
    {
        return self::slotPath($theme, $version) . DIRECTORY_SEPARATOR . '_manifest.json';
    }

    /**
     * 版本号只允许安全字符，避免路径穿越。
     */
    public static function sanitizeVersion(string $version): string
    {
        $version = preg_replace('/[^A-Za-z0-9._-]+/', '-', trim($version)) ?: '';

        return trim($version, '.-') !== '' ? trim($version, '.-') : 'latest';
    }

    /**
     * 主题是否启用 default 兜底。
     *
     * config.json 里 `"fallback": false` 可关掉，用于做完全独立、不允许回退的主题。
     */
    public static function fallbackEnabled(?string $theme = null): bool
    {
        $manifest = self::manifest($theme);
        if (!array_key_exists('fallback', $manifest)) {
            return true;
        }

        return (bool) $manifest['fallback'];
    }

    /**
     * 规范化视图路径：去掉前后点号与斜杠，统一 `.` 分隔。
     */
    private static function normalizeViewPath(string $path): string
    {
        $path = str_replace(['\\', '/'], '.', trim($path));
        $path = preg_replace('/\.(blade\.php|php)$/i', '', $path) ?: '';
        $path = trim($path, '.');

        // 防止越界到主题目录之外
        if ($path === '' || strpos($path, '..') !== false) {
            return '';
        }

        return $path;
    }
}
