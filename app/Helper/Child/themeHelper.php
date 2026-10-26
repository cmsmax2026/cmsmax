<?php

/**
 * 主题助手函数。
 *
 * 主题与模块是两套独立体系：主题只管 `public/views/themes/<id>/` 下的模板与资源，
 * 模块自己带视图。主题需要模块数据时，通过 theme_data() 动态调用，不要直接 new 模块的类。
 *
 * 本文件由 app/Helper/functions.php 自动 include。
 */

use App\Support\Theme\FormtoolsDataProvider;
use App\Support\Theme\ShortTagParser;
use App\Support\Theme\ThemeDataRegistry;
use App\Support\Theme\ThemeFieldMapper;
use App\Support\Theme\ThemeManager;
use App\Support\Theme\ThemeSettings;
use App\Support\Theme\ThemeUpgradeSlot;
use App\Support\Theme\ThemeView;

if (!function_exists('theme_current')) {
    /**
     * 当前主题标识。
     */
    function theme_current(): string
    {
        return \App\Support\Theme\ThemeManager::current();
    }
}

if (!function_exists('theme_view')) {
    /**
     * 解析主题内的视图名（支持 parent 继承链 + default 兜底）。
     *
     * 主题模板里用这个函数，就再也不用把主题名写死成 `themes.default.public.head`。
     */
    function theme_view(string $path, ?string $theme = null): string
    {
        return ThemeManager::viewName($path, $theme);
    }
}

if (!function_exists('theme_include')) {
    /**
     * 渲染主题内的一个片段，找不到就输出空串而不是报错。
     *
     * @param string $path 主题内相对视图路径，如 `public.head`
     */
    function theme_include(string $path, array $data = [], ?string $theme = null): string
    {
        $view = ThemeManager::resolveView($path, $theme);
        if ($view === null) {
            // 缺失片段静默跳过，避免主题少放一个文件就整站 500；
            // 开发模式下给出注释，方便定位。
            if (function_exists('config') && config('app.debug')) {
                return '<!-- theme fragment not found: ' . e($path) . ' -->';
            }

            return '';
        }

        return (string) view($view, $data)->render();
    }
}

if (!function_exists('theme_asset')) {
    /**
     * 主题资源 URL。
     *
     * theme_asset('css/app.css') -> http://site/views/themes/<id>/assets/css/app.css
     * 子主题没放资源时自动回退到父主题的。
     */
    function theme_asset(string $path = '', ?string $theme = null): string
    {
        return ThemeManager::asset($path, $theme);
    }
}

if (!function_exists('theme_file')) {
    /**
     * 主题内文件的绝对路径（沿继承链查找）。
     */
    function theme_file(string $relativePath, ?string $theme = null): ?string
    {
        return ThemeManager::findFile($relativePath, $theme);
    }
}

if (!function_exists('theme_has')) {
    /**
     * 主题内文件是否存在。
     */
    function theme_has(string $relativePath, ?string $theme = null): bool
    {
        return ThemeManager::fileExists($relativePath, $theme);
    }
}

if (!function_exists('theme_setting')) {
    /**
     * 读取主题配置值（config.json settings.schema 声明 + themes.form 保存的值）。
     */
    function theme_setting(string $key, $default = null, ?string $theme = null)
    {
        return ThemeSettings::get($key, $default, $theme);
    }
}

if (!function_exists('theme_settings')) {
    /**
     * 读取主题全部配置值。
     */
    function theme_settings(?string $theme = null): array
    {
        return ThemeSettings::values($theme);
    }
}

if (!function_exists('theme_css_variables')) {
    /**
     * 输出主题 CSS 变量的 <style> 片段，主题 head 里放 `{!! theme_css_variables() !!}`。
     */
    function theme_css_variables(?string $theme = null): string
    {
        return ThemeSettings::cssVariablesStyle($theme);
    }
}

if (!function_exists('theme_data')) {
    /**
     * 动态调用模块数据 —— 主题与模块解耦的关键入口。
     *
     *     theme_data('formtools.list', ['model' => 'news', 'limit' => 6]);
     *     theme_data('bbs.latest', ['limit' => 5]);   // 由 Bbs 模块/插件注册
     */
    function theme_data(string $key, array $params = [])
    {
        return ThemeDataRegistry::get($key, $params);
    }
}

if (!function_exists('theme_data_has')) {
    function theme_data_has(string $key): bool
    {
        return ThemeDataRegistry::has($key);
    }
}

if (!function_exists('theme_rows')) {
    /**
     * 快捷取 Formtools 列表的规范化行数据（含 title/summary/cover/link 等）。
     */
    function theme_rows(string $model, array $params = []): array
    {
        $result = ThemeDataRegistry::get('formtools.list', array_merge(['model' => $model], $params));

        return (array) ($result['rows'] ?? []);
    }
}

if (!function_exists('theme_row')) {
    /**
     * 快捷取 Formtools 单条。
     */
    function theme_row(string $model, array $params = []): ?array
    {
        return ThemeDataRegistry::get('formtools.detail', array_merge(['model' => $model], $params));
    }
}

if (!function_exists('theme_chart')) {
    /**
     * 快捷取图表聚合数据，返回 labels / values / items。
     */
    function theme_chart(string $model, array $params = []): array
    {
        return (array) ThemeDataRegistry::get('formtools.chart', array_merge(['model' => $model], $params));
    }
}

if (!function_exists('theme_map')) {
    /**
     * 取模型的「别名 => 真实字段」映射，模板里用别名取值就不怕字段改名。
     */
    function theme_map(string $model): array
    {
        return (array) ThemeDataRegistry::get('formtools.map', ['model' => $model]);
    }
}

if (!function_exists('theme_field')) {
    /**
     * 按别名从一行数据里取值。
     *
     *     echo theme_field($row, 'title');      // 自动找 title / name / ...
     */
    function theme_field($row, string $alias, $default = '', ?array $map = null)
    {
        if ($map === null) {
            $map = [];
        }

        return ThemeFieldMapper::pick($row, $map, $alias, $default);
    }
}

if (!function_exists('theme_normalize')) {
    /**
     * 把一行原始数据整理成主题模板用的标准结构。
     */
    function theme_normalize($row, array $map = [], string $access = ''): array
    {
        return ThemeFieldMapper::normalize($row, $map, $access);
    }
}

if (!function_exists('theme_shorttag')) {
    /**
     * 解析短标签：{mx:list model="news" limit="6" /}
     */
    function theme_shorttag(string $content): string
    {
        return ShortTagParser::render($content);
    }
}

if (!function_exists('theme_shorttag_catalog')) {
    /**
     * 可用短标签清单，供后台「短标签生成器」展示。
     */
    function theme_shorttag_catalog(): array
    {
        return ShortTagParser::catalog();
    }
}

if (!function_exists('theme_slots')) {
    /**
     * 主题的更新槽位列表（升级时被保护下来的新版本文件）。
     */
    function theme_slots(?string $theme = null): array
    {
        return ThemeUpgradeSlot::versions($theme ?: theme_current());
    }
}

if (!function_exists('theme_slot_manifest')) {
    function theme_slot_manifest(string $version, ?string $theme = null): array
    {
        return ThemeUpgradeSlot::manifest($theme ?: theme_current(), $version);
    }
}

if (!function_exists('theme_slot_apply')) {
    /**
     * 把槽位里的文件应用到主题。
     */
    function theme_slot_apply(string $version, array $files = [], ?string $theme = null): array
    {
        return ThemeUpgradeSlot::apply($theme ?: theme_current(), $version, $files);
    }
}

if (!function_exists('theme_slot_discard')) {
    function theme_slot_discard(string $version, ?string $theme = null): bool
    {
        return ThemeUpgradeSlot::discard($theme ?: theme_current(), $version);
    }
}

if (!function_exists('theme_hook')) {
    /**
     * 触发一个主题钩子并把返回值拼成 HTML。
     *
     * 模板里：{!! theme_hook('ThemeHead') !!}
     */
    function theme_hook(string $name, array $params = [], ?string $theme = null): string
    {
        return \App\Support\Theme\ThemeHooks::render($name, $params, $theme);
    }
}

if (!function_exists('theme_hooks')) {
    /**
     * 当前主题声明的钩子列表（config.json 的 hooks 段，没声明则取系统内置列表）。
     */
    function theme_hooks(?string $theme = null): array
    {
        return \App\Support\Theme\ThemeHooks::forTheme($theme);
    }
}

if (!function_exists('theme_hook_catalog')) {
    /**
     * 系统内置主题钩子点清单（含说明），供后台「钩子说明」页展示。
     */
    function theme_hook_catalog(): array
    {
        return \App\Support\Theme\ThemeHooks::available();
    }
}

if (!function_exists('theme_view_object')) {
    /**
     * 包装一个 View，渲染后自动跑短标签解析。
     */
    function theme_view_object($view, bool $parseShortTags = true): ThemeView
    {
        return new ThemeView($view, $parseShortTags);
    }
}

if (!function_exists('theme_media_url')) {
    /**
     * 把字段值（可能是相对路径或完整 URL）转成可直接给 img / video / a 用的地址。
     *
     * 已经带协议或是 data URI 的值原样返回，其余交给 GetUrlByPath() 处理，
     * 这样后台上传字段和手填外链都能正常显示。
     */
    function theme_media_url($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        if (preg_match('#^(https?:)?//#i', $value) || str_starts_with($value, 'data:')) {
            return $value;
        }

        return function_exists('GetUrlByPath') ? (string) GetUrlByPath($value) : $value;
    }
}

if (!function_exists('theme_gallery_urls')) {
    /**
     * 把图集字段整理成 URL 数组。
     *
     * Formtools 的多图字段可能是 JSON 数组、逗号/换行分隔的字符串，
     * 也可能是 [['url'=>...]] 这种结构，这里统一拍平成一维 URL 数组。
     */
    function theme_gallery_urls($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                $value = (array) preg_split('/[\r\n,|]+/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
            }
        }

        if (!is_array($value)) {
            return [];
        }

        $urls = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                $item = $item['url'] ?? ($item['path'] ?? ($item['src'] ?? ''));
            }
            $url = theme_media_url($item);
            if ($url !== '') {
                $urls[] = $url;
            }
        }

        return array_values(array_unique($urls));
    }
}

if (!function_exists('theme_price')) {
    /**
     * 格式化价格：数字按整数/两位小数输出，非数字（面议、询价）原样返回。
     */
    function theme_price($value, string $prefix = '¥'): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        if (!is_numeric($value)) {
            return $value;
        }

        $number = (float) $value;
        $formatted = abs($number - round($number)) < 0.000001
            ? number_format($number, 0, '.', '')
            : number_format($number, 2, '.', '');

        return $prefix . $formatted;
    }
}

if (!function_exists('theme_video_source')) {
    /**
     * 判断视频字段该怎么播放。
     *
     * @return array{type:string,src:string} type 取 mp4 / iframe / unknown
     */
    function theme_video_source($value): array
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return ['type' => 'unknown', 'src' => ''];
        }

        $url = theme_media_url($raw);

        // 本地/直链视频文件
        if (preg_match('/\.(mp4|m4v|webm|ogg|ogv|mov)(\?|#|$)/i', $url)) {
            return ['type' => 'mp4', 'src' => $url];
        }

        // 主流视频平台的分享地址转成可直接 iframe 的播放器地址
        if (preg_match('#(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]+)#i', $url, $m)) {
            return ['type' => 'iframe', 'src' => 'https://www.youtube.com/embed/' . $m[1]];
        }
        if (preg_match('#vimeo\.com/(\d+)#i', $url, $m)) {
            return ['type' => 'iframe', 'src' => 'https://player.vimeo.com/video/' . $m[1]];
        }
        if (preg_match('#bilibili\.com/video/(?:av\d+|(BV[\w]+))#i', $url, $m)) {
            $bvid = $m[1] ?? '';
            return ['type' => 'iframe', 'src' => 'https://player.bilibili.com/player.html?bvid=' . $bvid . '&autoplay=0'];
        }
        if (preg_match('#player\.(bilibili|qq|youku)\.com#i', $url)) {
            return ['type' => 'iframe', 'src' => $url];
        }

        // 其余外链按 iframe 处理，本地路径按直链处理
        return ['type' => preg_match('#^https?://#i', $url) ? 'iframe' : 'mp4', 'src' => $url];
    }
}

if (!function_exists('theme_record')) {
    /**
     * 把一行 Formtools 原始数据整理成模板能直接用的标准结构。
     *
     * 模板里写死 `$row['title']` 换个模型字段名就崩，这里统一按别名取值，
     * 同时把封面/图集/视频/文件/价格这些"要二次加工"的字段提前算好：
     *
     *     @php($it = theme_record($row, $param['model'], $map))
     *     <img src="{{ $it['cover_url'] }}">
     *
     * @param mixed  $row    原始行（数组 / 模型对象皆可）
     * @param string $access 模型访问标识，用于生成详情链接、按需取字段映射
     * @param array|null $map 别名映射，省略时按 $access 现取
     * @return array<string, mixed>
     */
    function theme_record($row, string $access = '', ?array $map = null): array
    {
        if (is_array($row)) {
            $raw = $row;
        } elseif ($row instanceof \Illuminate\Contracts\Support\Arrayable) {
            $raw = $row->toArray();
        } elseif (is_object($row)) {
            $raw = get_object_vars($row);
        } else {
            $raw = [];
        }

        if ($map === null && $access !== '') {
            $map = theme_map($access);
        }
        $map = (array) $map;

        $pick = static function (string $alias, $default = '') use ($raw, $map) {
            return \App\Support\Theme\ThemeFieldMapper::pick($raw, $map, $alias, $default);
        };

        $id = (int) ($raw['id'] ?? 0);
        $content = (string) $pick('content', '');
        $summary = trim((string) $pick('summary', ''));
        if ($summary === '' && $content !== '') {
            $summary = \Illuminate\Support\Str::limit(trim(strip_tags($content)), 120);
        }

        $cover = trim((string) $pick('cover', ''));
        $gallery = theme_gallery_urls($pick('gallery', ''));
        if ($gallery === [] && $cover !== '') {
            $gallery = [$cover];
        }

        $video = trim((string) $pick('video', ''));
        $videoSource = theme_video_source($video);

        $file = trim((string) $pick('file', ''));
        $priceRaw = trim((string) $pick('price', ''));
        $originRaw = trim((string) $pick('origin_price', ''));
        $stock = $pick('stock', null);

        $tags = $pick('tag', '');
        if (is_string($tags)) {
            $tags = (array) preg_split('/[,，、|\r\n]+/', trim($tags), -1, PREG_SPLIT_NO_EMPTY);
        }

        $link = '';
        if ($access !== '' && $id > 0 && function_exists('model_detail_url')) {
            $link = (string) model_detail_url($access, $id);
        }

        return [
            'id' => $id,
            'title' => trim((string) $pick('title', '')),
            'subtitle' => trim((string) $pick('subtitle', '')),
            'summary' => $summary,
            'content' => $content,
            'cover' => $cover,
            'cover_url' => theme_media_url($cover),
            'gallery' => $gallery,
            'video' => $video,
            'video_url' => $videoSource['src'],
            'video_type' => $videoSource['type'],
            'file' => $file,
            'file_url' => theme_media_url($file),
            'file_name' => $file !== '' ? basename(parse_url(theme_media_url($file), PHP_URL_PATH) ?: $file) : '',
            'date' => (string) $pick('date', ''),
            'link' => $link,
            'price_raw' => $priceRaw,
            'price' => theme_price($priceRaw),
            'origin_price_raw' => $originRaw,
            'origin_price' => theme_price($originRaw),
            'stock' => ($stock === null || $stock === '') ? null : (int) $stock,
            'spec' => trim((string) $pick('spec', '')),
            'tags' => array_values(array_filter((array) $tags)),
            'position' => trim((string) $pick('position', '')),
            'phone' => trim((string) $pick('phone', '')),
            'email' => trim((string) $pick('email', '')),
            'address' => trim((string) $pick('address', '')),
            'raw' => $raw,
            'map' => $map,
        ];
    }
}
