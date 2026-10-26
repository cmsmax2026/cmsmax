<?php

namespace Modules\Main\Http\Controllers\Admin;

use App\Support\Theme\ShortTagParser;
use App\Support\Theme\ThemeHooks;
use App\Support\Theme\ThemeManager;
use App\Support\Theme\ThemeSettings;
use App\Support\Theme\ThemeUpgradeSlot;
use App\Support\Telemetry\StatisticReporter;
use Illuminate\Support\Facades\Cache;
use Modules\Main\Models\Modules;
use Modules\Main\Services\ServiceModel;
use Modules\ModulesController;
use Modules\System\Models\Setting;

class ThemeController extends ModulesController {

    public function diy() {

        if ($this->request->isMethod("POST")){

            $all = $this->request->all();
            unset($all['_token'],$all['m']);

            if($_FILES['home_screen_image']['size']>0){
                $all['home_screen_image'] = UploadFile($this->request,"home_screen_image","home/screen/".md5(time().rand(100,999)));
            }

            foreach ($all as $key=>$value){
                ServiceModel::SettingInsertOrUpdate("Main","theme",$key,$value);
            }

            Cache::forget("settings");
            return [
                "status"=>200,
                "msg"=>"更新成功"
            ];
        }

        $pageData = getURIByRoute($this->request);
        $pageData['title'] = "开发者助手，快速创建本地应用";
        $pageData['subtitle'] = "开发者助手，子标题";
        $themeIdentification = $this->request->query('m', Cache::get('theme', 'default'));
        StatisticReporter::reportSuccess('Usage', $themeIdentification, Modules::Theme, [
            'entry' => 'admin_theme_diy',
        ]);

        return view("admin.func.diy",[
            "pageData" =>$pageData
        ]);

    }

    /**
     * 主题配置：按 config.json 的 settings.schema 自动生成表单，值存 themes.form。
     */
    public function themeSettings()
    {
        $theme = $this->resolveTheme();

        if ($this->request->isMethod('POST')) {
            $all = $this->request->all();
            unset($all['_token'], $all['m'], $all['_method']);

            // 只接受 schema 里声明过的 key，避免被塞进任意字段
            $allowed = array_column(ThemeSettings::schema($theme), 'key');
            $values = array_intersect_key($all, array_flip($allowed));

            $saved = ThemeSettings::save($values, $theme);

            return [
                'status' => $saved ? 200 : 0,
                'msg' => $saved ? '主题配置已保存' : '保存失败，请确认主题已安装',
            ];
        }

        return view('admin.func.themeSettings', [
            'pageData' => $this->buildPageData('主题配置'),
            'theme' => $theme,
            'schema' => ThemeSettings::schema($theme),
            'values' => ThemeSettings::values($theme),
            'themes' => $this->installedThemes(),
        ]);
    }

    /**
     * 更新槽位：列出被保护下来的新版本文件，支持逐项应用 / 丢弃。
     */
    public function themeSlots()
    {
        $theme = $this->resolveTheme();

        if ($this->request->isMethod('POST')) {
            $version = trim((string) $this->request->input('version', ''));
            $action = trim((string) $this->request->input('act', ''));
            $files = (array) $this->request->input('files', []);

            if ($version === '') {
                return ['status' => 0, 'msg' => '缺少版本号'];
            }

            if ($action === 'discard') {
                $ok = ThemeUpgradeSlot::discard($theme, $version);
                return [
                    'status' => $ok ? 200 : 0,
                    'msg' => $ok ? '槽位已丢弃' : '丢弃失败',
                ];
            }

            $result = ThemeUpgradeSlot::apply($theme, $version, $files);
            $applied = count($result['applied']);

            return [
                'status' => $applied > 0 ? 200 : 0,
                'msg' => $applied > 0
                    ? "已应用 {$applied} 个文件到主题（原文件已被新版本覆盖）"
                    : '没有文件被应用',
                'data' => $result,
            ];
        }

        $versions = [];
        foreach (ThemeUpgradeSlot::versions($theme) as $version) {
            $versions[] = array_merge($version, [
                'manifest' => ThemeUpgradeSlot::manifest($theme, $version['version']),
            ]);
        }

        return view('admin.func.themeSlots', [
            'pageData' => $this->buildPageData('更新槽位'),
            'theme' => $theme,
            'versions' => $versions,
            'pending' => ThemeUpgradeSlot::pending(),
            'themes' => $this->installedThemes(),
            'baseline' => ThemeUpgradeSlot::baseline($theme),
        ]);
    }

    /**
     * 查看槽位里某个文件的内容（用于左右对比）。
     */
    public function themeSlotFile()
    {
        $theme = $this->resolveTheme();
        $version = trim((string) $this->request->input('version', ''));
        $path = trim((string) $this->request->input('path', ''));

        return [
            'status' => 200,
            'data' => [
                'new' => ThemeUpgradeSlot::read($theme, $version, $path),
                'current' => is_file(ThemeManager::path($theme, $path))
                    ? (string) file_get_contents(ThemeManager::path($theme, $path))
                    : null,
            ],
        ];
    }

    /**
     * 短标签生成器：列出可用标签，可实时预览解析结果。
     */
    public function themeShorttags()
    {
        $theme = $this->resolveTheme();
        $preview = '';

        if ($this->request->isMethod('POST')) {
            $tag = (string) $this->request->input('tag', '');
            $preview = ShortTagParser::render($tag);

            return [
                'status' => 200,
                'msg' => 'ok',
                'data' => ['html' => $preview],
            ];
        }

        return view('admin.func.themeShorttags', [
            'pageData' => $this->buildPageData('短标签'),
            'theme' => $theme,
            'catalog' => ShortTagParser::catalog(),
            'dataKeys' => array_column(\App\Support\Theme\ThemeDataRegistry::all(), 'key'),
            'themes' => $this->installedThemes(),
        ]);
    }

    /**
     * 主题钩子说明。
     */
    public function themeHooks()
    {
        $theme = $this->resolveTheme();

        return view('admin.func.themeHooks', [
            'pageData' => $this->buildPageData('主题钩子'),
            'theme' => $theme,
            'declared' => ThemeHooks::forTheme($theme),
            'catalog' => ThemeHooks::available(),
            'themes' => $this->installedThemes(),
        ]);
    }

    /**
     * 在线模板编辑器：左侧文件树，右侧 CodeMirror 编辑主题模板/资源。
     */
    public function templateEditor()
    {
        $theme = $this->resolveTheme();
        $tree = $this->templateTree($theme);
        $file = trim((string) $this->request->query('file', ''));

        // AJAX 读取单文件内容（raw=1 时只返回 JSON，不渲染整页）
        if ($this->request->query('raw') !== null && $file !== '') {
            $content = $this->readTemplate($theme, $file);

            return [
                'status' => $content !== null ? 200 : 0,
                'data' => ['content' => $content ?? '', 'file' => $file],
            ];
        }

        $content = $file !== '' ? ($this->readTemplate($theme, $file) ?? '') : '';

        return view('admin.func.themeTemplateEditor', [
            'pageData' => $this->buildPageData('模板编辑'),
            'theme' => $theme,
            'tree' => $tree,
            'currentFile' => $file,
            'content' => $content,
            'catalog' => ShortTagParser::catalog(),
            'themes' => $this->installedThemes(),
        ]);
    }

    /**
     * 保存模板/资源文件，并清空视图编译缓存。
     */
    public function templateSave()
    {
        $theme = $this->resolveTheme();
        $file = trim((string) $this->request->input('file', ''));
        $content = (string) $this->request->input('content', '');

        $path = $this->resolveTemplatePath($theme, $file);
        if ($path === null) {
            return ['status' => 0, 'msg' => '非法文件路径（禁止越权写入）'];
        }

        try {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($path, $content);
            // 改了 blade 后必须清编译缓存，否则前端仍读旧的编译结果
            \Illuminate\Support\Facades\Artisan::call('view:clear');

            return ['status' => 200, 'msg' => '已保存：' . $file];
        } catch (\Throwable $e) {
            return ['status' => 0, 'msg' => '保存失败：' . $e->getMessage()];
        }
    }

    /**
     * 递归收集主题目录下可编辑的文本文件，构造成树。
     */
    private function templateTree(string $theme): array
    {
        $base = ThemeManager::path($theme);
        if (!is_dir($base)) {
            return [];
        }
        $editable = ['blade.php', 'php', 'css', 'js', 'json', 'html', 'txt', 'md', 'yml', 'yaml'];

        return $this->scanTree($base, $base, $editable);
    }

    private function scanTree(string $base, string $dir, array $editable): array
    {
        $nodes = [];
        foreach (array_filter(glob($dir . '/*'), 'is_dir') as $sub) {
            $rel = ltrim(str_replace($base, '', $sub), '/\\');
            $children = $this->scanTree($base, $sub, $editable);
            if ($children !== []) {
                $nodes[] = ['type' => 'dir', 'name' => basename($sub), 'path' => $rel, 'children' => $children];
            }
        }
        foreach (glob($dir . '/*.*') as $f) {
            if (!is_file($f)) {
                continue;
            }
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if (!in_array($ext, $editable, true)) {
                continue;
            }
            $rel = ltrim(str_replace($base, '', $f), '/\\');
            $nodes[] = ['type' => 'file', 'name' => basename($f), 'path' => $rel];
        }
        usort($nodes, static function ($a, $b) {
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'dir' ? -1 : 1;
            }

            return strcasecmp($a['name'], $b['name']);
        });

        return $nodes;
    }

    /**
     * 把相对路径安全地解析成主题内的绝对路径，越权返回 null。
     */
    private function resolveTemplatePath(string $theme, string $file): ?string
    {
        if ($file === '' || strpos($file, '..') !== false || strpos($file, "\0") !== false) {
            return null;
        }
        $base = ThemeManager::path($theme);
        $realBase = realpath($base);
        if ($realBase === false) {
            return null;
        }
        $candidate = $base . DIRECTORY_SEPARATOR . str_replace('\\', '/', ltrim($file, '/'));
        $real = realpath($candidate);
        if ($real === false) {
            // 可能是新建文件，校验父目录在主题内即可
            $parent = realpath(dirname($candidate));
            if ($parent === false || strpos($parent, $realBase) !== 0) {
                return null;
            }

            return $candidate;
        }
        if (strpos($real, $realBase) !== 0) {
            return null;
        }

        return $real;
    }

    /**
     * 读取模板文件内容，越权或不存在返回 null。
     */
    private function readTemplate(string $theme, string $file): ?string
    {
        $path = $this->resolveTemplatePath($theme, $file);
        if ($path === null || !is_file($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    /**
     * 当前操作的主题：优先取 ?m=，否则取启用中的主题。
     */
    private function resolveTheme(): string
    {
        $theme = trim((string) $this->request->input('m', ''));
        if ($theme !== '') {
            return $theme;
        }

        return ThemeManager::current();
    }

    /**
     * 已安装的主题列表，供页面顶部切换。
     */
    private function installedThemes(): array
    {
        try {
            return \Illuminate\Support\Facades\DB::table('themes')
                ->orderBy('status', 'asc')
                ->get()
                ->map(static function ($item) {
                    return [
                        'identification' => (string) $item->identification,
                        'name' => (string) $item->name,
                        'status' => (int) $item->status,
                    ];
                })
                ->all();
        } catch (\Throwable $exception) {
            return [];
        }
    }

    private function buildPageData(string $title): array
    {
        $pageData = getURIByRoute($this->request);
        $pageData['title'] = $title;
        $pageData['subtitle'] = $title;

        return $pageData;
    }
}
