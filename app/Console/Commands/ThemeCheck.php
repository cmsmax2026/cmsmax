<?php

namespace App\Console\Commands;

use App\Support\Theme\ShortTagParser;
use App\Support\Theme\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * theme:check —— 主题体检。
 *
 * 面向主题开发者 / 运维，一键检查主题是否合规、会不会在线上踩坑：
 *   - config.json 是否存在、合法、必填字段齐全
 *   - 继承链 parent 是否指向已存在主题（且非自身）
 *   - 首页视图 index/index.blade.php 是否齐备（缺失会回退，仅警告）
 *   - assets/ 资源目录是否齐备
 *   - 短标签语法：未知标签名（警告）、成对标签未闭合（错误）
 *   - 资源引用：是否写死主题路径（应改用 {mx:asset} / ThemeManager::asset）
 *   - Blade 编译：模板是否有语法错误（@php 未闭合等）
 *
 * 用法：
 *   php artisan theme:check               检查全部已安装主题
 *   php artisan theme:check --theme=mxzcms 只检查指定主题
 */
class ThemeCheck extends Command
{
    protected $signature = 'theme:check
        {--theme= : 只检查指定主题标识，缺省检查全部}
        {--dir= : 主题根目录，默认 public/views/themes}';

    protected $description = '主题体检：config.json / 继承链 / 视图 / 短标签 / 资源引用 / Blade 编译';

    /**
     * 既支持自闭合也支持成对写法的标签。
     * @var string[]
     */
    private const PAIRED_TAGS = ['list', 'detail', 'tree', 'chart', 'data', 'nav', 'menu', 'include', 'hook', 'field'];

    public function handle(): int
    {
        $base = $this->option('dir') ?: public_path('views/themes');
        $only = $this->option('theme');

        if ($only) {
            $themes = [$only];
        } else {
            if (!is_dir($base)) {
                $this->error("主题目录不存在：{$base}");

                return 1;
            }
            $themes = [];
            foreach (array_filter(glob($base . '/*'), 'is_dir') as $dir) {
                $themes[] = basename($dir);
            }
        }

        if ($themes === []) {
            $this->warn('未发现任何主题。');

            return 0;
        }

        $knownTags = $this->knownTags();
        $rows = [];
        $errors = 0;
        $warns = 0;

        foreach ($themes as $theme) {
            foreach ($this->checkTheme($theme, $knownTags) as $issue) {
                if ($issue['level'] === 'ERROR') {
                    $errors++;
                } elseif ($issue['level'] === 'WARN') {
                    $warns++;
                }
                $rows[] = [$theme, $issue['check'], $issue['level'], $issue['message']];
            }
        }

        if ($rows === []) {
            $this->info('所有主题检查通过，无问题。');
        } else {
            $this->table(['主题', '检查项', '级别', '说明'], $rows);
        }
        $this->line('');
        $this->info("体检完成：ERROR={$errors}  WARN={$warns}");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * 已知短标签名（内置 catalog + 插件通过 ThemeShortTagCatalog 补充）。
     */
    private function knownTags(): array
    {
        $tags = [];
        foreach (ShortTagParser::catalog() as $item) {
            $tags[$item['tag']] = true;
        }
        if (function_exists('hook')) {
            foreach ((array) hook('ThemeShortTagCatalog', []) as $extra) {
                if (!is_array($extra)) {
                    continue;
                }
                foreach ($extra as $e) {
                    if (isset($e['tag'])) {
                        $tags[$e['tag']] = true;
                    }
                }
            }
        }

        return $tags;
    }

    private function checkTheme(string $theme, array $knownTags): array
    {
        $issues = [];
        $path = ThemeManager::path($theme);

        if (!is_dir($path)) {
            $issues[] = ['check' => '目录', 'level' => 'ERROR', 'message' => "主题目录不存在：{$path}"];

            return $issues;
        }

        // A. config.json
        $manifest = $this->checkConfig($theme, $path, $issues);

        // B. 继承链 parent
        $parent = (string) ($manifest['parent'] ?? '');
        if ($parent !== '') {
            if (strcasecmp($parent, $theme) === 0) {
                $issues[] = ['check' => '继承链', 'level' => 'ERROR', 'message' => 'parent 不能指向自身'];
            } elseif (!is_dir(ThemeManager::path($parent))) {
                $issues[] = ['check' => '继承链', 'level' => 'WARN', 'message' => "parent 主题不存在：{$parent}"];
            }
        }

        // C. 首页视图
        if (!ThemeManager::viewFileExists('index.index', $theme)) {
            $issues[] = ['check' => '视图', 'level' => 'WARN', 'message' => 'index/index.blade.php 缺失（将回退父主题/default）'];
        }

        // D. assets 目录
        if (!is_dir($path . DIRECTORY_SEPARATOR . 'assets')) {
            $issues[] = ['check' => '资源', 'level' => 'WARN', 'message' => 'assets/ 目录缺失'];
        }

        // E/F/G 扫描 blade
        foreach ($this->collectBlade($path) as $file) {
            $content = file_get_contents($file);
            $rel = str_replace($path . DIRECTORY_SEPARATOR, '', $file);

            // E. 短标签语法
            foreach ($this->checkShortTags($rel, $content, $knownTags) as $sub) {
                $issues[] = $sub;
            }

            // F. 写死主题资源路径
            if (preg_match('#views/themes/[A-Za-z0-9_-]+/#', $content)) {
                $issues[] = ['check' => '资源引用', 'level' => 'WARN', 'message' => "{$rel} 中疑似写死主题路径，建议用 {mx:asset} / ThemeManager::asset()"];
            }

            // G. PHP 块配对 + Blade 编译兜底
            $phpOpen = preg_match_all('/@php\b/', $content);
            $phpClose = preg_match_all('/@endphp\b/', $content);
            if ($phpOpen !== $phpClose) {
                $issues[] = [
                    'check' => 'Blade编译',
                    'level' => 'ERROR',
                    'message' => "{$rel} @php 块未闭合（@php={$phpOpen} / @endphp={$phpClose}）",
                ];
            }
            try {
                app('blade.compiler')->compileString($content);
            } catch (\Throwable $e) {
                $issues[] = ['check' => 'Blade编译', 'level' => 'ERROR', 'message' => "{$rel} 编译失败：" . $this->brief($e->getMessage())];
            }
        }

        return $issues;
    }

    private function checkConfig(string $theme, string $path, array &$issues): array
    {
        $cfgPath = $path . DIRECTORY_SEPARATOR . 'config.json';
        if (!is_file($cfgPath)) {
            $issues[] = ['check' => 'config.json', 'level' => 'ERROR', 'message' => '缺少 config.json'];

            return [];
        }

        $manifest = json_decode(file_get_contents($cfgPath), true);
        if (!is_array($manifest)) {
            $issues[] = ['check' => 'config.json', 'level' => 'ERROR', 'message' => 'config.json 不是合法 JSON'];

            return [];
        }

        foreach (['identification', 'name'] as $field) {
            if (!isset($manifest[$field]) || $manifest[$field] === '') {
                $issues[] = ['check' => 'config.json', 'level' => 'ERROR', 'message' => "缺少必填字段：{$field}"];
            }
        }

        return $manifest;
    }

    private function checkShortTags(string $rel, string $content, array $knownTags): array
    {
        $issues = [];

        // 未知标签名
        if (preg_match_all('#\{mx:([a-zA-Z_][\w-]*)\b#', $content, $m)) {
            foreach (array_unique($m[1]) as $tag) {
                if (!isset($knownTags[$tag])) {
                    $issues[] = ['check' => '短标签', 'level' => 'WARN', 'message' => "{$rel} 未知短标签 {mx:{$tag}}"];
                }
            }
        }

        // 成对标签未闭合
        foreach (self::PAIRED_TAGS as $tag) {
            $openAll = preg_match_all('#\{mx:' . $tag . '(?:\s[^}]*)?\}#', $content);
            $selfClose = preg_match_all('#\{mx:' . $tag . '(?:\s[^}]*)?/\}#', $content);
            $openPaired = $openAll - $selfClose;
            $close = preg_match_all('#\{/mx:' . $tag . '\}#', $content);

            if ($openPaired > $close) {
                $issues[] = [
                    'check' => '短标签',
                    'level' => 'ERROR',
                    'message' => "{$rel} 成对标签 {mx:{$tag}} 未闭合（开放 {$openPaired} / 闭合 {$close}）",
                ];
            }
        }

        return $issues;
    }

    /**
     * 递归收集主题目录下所有 .blade.php。
     * @return string[]
     */
    private function collectBlade(string $path): array
    {
        $files = [];
        $rii = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($rii as $f) {
            if ($f->isFile() && preg_match('/\.blade\.php$/', $f->getFilename())) {
                $files[] = $f->getPathname();
            }
        }

        return $files;
    }

    private function brief(string $msg): string
    {
        return trim(preg_replace('/\s+/', ' ', $msg));
    }
}
