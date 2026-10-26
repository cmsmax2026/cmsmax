<?php

namespace App\Support\Theme;

/**
 * 短标签解析器 —— 让不懂 PHP 的人也能在模板 / 内容里"插入一段数据"。
 *
 * 语法（两种）：
 *   {mx:list model="news" limit="6" /}                  自闭合，用主题 partials 或默认样式渲染
 *   {mx:list model="news" limit="6"}<li>{title}</li>{/mx:list}   成对，内部当行模板循环输出
 *
 * 成对标签内部用 `{字段名}` 占位，可用：id / title / summary / content / cover / date / link
 * 以及当前模型的任意真实字段名（如 `{author}`）。
 *
 * 字面量转义：写成 `\{mx:list ...}` 可原样输出。
 */
class ShortTagParser
{
    /**
     * 标签前缀。
     */
    public const PREFIX = 'mx';

    /**
     * 行模板占位符找不到对应字段时的兜底。
     */
    const PLACEHOLDER_FALLBACK = '';

    /**
     * 当前渲染视图的数据，供 {mx:field} 取用。
     *
     * @var array<string, mixed>
     */
    private static array $context = [];

    /**
     * 设置解析上下文（ThemeView 渲染前调用），返回旧的上下文以便嵌套恢复。
     */
    public static function withContext(array $data): array
    {
        $previous = self::$context;
        self::$context = $data;

        return $previous;
    }

    public static function setContext(array $data): void
    {
        self::$context = $data;
    }

    /**
     * 解析一段内容里的所有短标签。
     */
    public static function render(string $content): string
    {
        if ($content === '' || strpos($content, '{' . self::PREFIX . ':') === false) {
            return $content;
        }

        // 成对标签优先，避免被自闭合规则抢先吃掉
        $pattern = '/\\\\?\{' . self::PREFIX . ':([a-zA-Z][\w-]*)((?:\s+[\w:-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s}]+))*)\s*(\/)?\}(.*?)\{\/' . self::PREFIX . ':\1\}/s';
        $content = (string) preg_replace_callback($pattern, static function (array $matches) {
            return self::handleMatch($matches, true);
        }, $content);

        $pattern = '/\\\\?\{' . self::PREFIX . ':([a-zA-Z][\w-]*)((?:\s+[\w:-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s}]+))*)\s*(\/)?\}/s';
        $content = (string) preg_replace_callback($pattern, static function (array $matches) {
            return self::handleMatch($matches, false);
        }, $content);

        // 还原被转义的字面量
        return str_replace('\\{' . self::PREFIX . ':', '{' . self::PREFIX . ':', $content);
    }

    /**
     * 属性字符串解析成 kv 数组。
     */
    public static function parseAttrs(string $text): array
    {
        $attrs = [];
        if (trim($text) === '') {
            return $attrs;
        }

        preg_match_all('/([\w:-]+)\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s]+))/', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = strtolower($match[1]);
            $value = $match[3] ?? ($match[4] ?? ($match[5] ?? ''));
            $attrs[$key] = self::decodeValue($value);
        }

        return $attrs;
    }

    /**
     * 已注册的标签及其说明，供后台「短标签生成器」展示。
     *
     * @return array<int, array{tag:string,label:string,attrs:array<int,string>,example:string,description:string}>
     */
    public static function catalog(): array
    {
        $catalog = [
            ['tag' => 'list', 'label' => '内容列表', 'attrs' => ['model', 'limit', 'order', 'where', 'pid', 'keyword', 'offset', 'cache', 'type', 'tpl', 'cols', 'empty', 'page'], 'example' => '{mx:list model="news" limit="6" order="id desc" /}', 'description' => '取 Formtools 模型列表。type 用内置渲染器（card / product / video / gallery / text），tpl 指向主题 partials/list-{tpl}.blade.php；offset 跳过前 N 条；empty 是无数据时的提示；cache 设缓存秒数'],
            ['tag' => 'detail', 'label' => '单条详情', 'attrs' => ['model', 'id', 'cache', 'tpl'], 'example' => '{mx:detail model="news" id="12" /}', 'description' => '取一条内容，常用于首页固定位展示；cache 设缓存秒数'],
            ['tag' => 'tree', 'label' => '树形目录', 'attrs' => ['model', 'pid', 'cache', 'tpl'], 'example' => '{mx:tree model="doc" /}', 'description' => '按 pid 组织成树，适合文档 / 帮助中心；cache 设缓存秒数'],
            ['tag' => 'count', 'label' => '数量统计', 'attrs' => ['model', 'where', 'cache'], 'example' => '{mx:count model="news" /}', 'description' => '输出符合条件的记录数；cache 设缓存秒数'],
            ['tag' => 'chart', 'label' => '图表数据', 'attrs' => ['model', 'group', 'value', 'type', 'limit', 'cache', 'tpl'], 'example' => '{mx:chart model="order" group="month" value="amount" type="sum" /}', 'description' => '分组聚合，输出可直接喂给 ECharts 的结构；cache 设缓存秒数'],
            ['tag' => 'slider', 'label' => '图片轮播', 'attrs' => ['model', 'limit', 'height', 'interval', 'cache'], 'example' => '{mx:slider model="banner" limit="3" height="380" /}', 'description' => '取模型的封面图轮播，自动切换；interval 是切换间隔毫秒，0 表示不自动播放'],
            ['tag' => 'gallery', 'label' => '图集相册', 'attrs' => ['model', 'limit', 'cols', 'cache'], 'example' => '{mx:gallery model="album" limit="9" cols="3" /}', 'description' => '多列图片瀑布，点击弹窗放大；主题带 components.js 时自动支持灯箱'],
            ['tag' => 'video', 'label' => '视频播放', 'attrs' => ['model', 'id', 'src', 'cover', 'cache'], 'example' => '{mx:video model="course" id="3" /}', 'description' => '内嵌播放器；给 src 直接播指定地址，给 model+id 取模型里的视频字段，支持 mp4 与主流平台'],
            ['tag' => 'related', 'label' => '相关内容', 'attrs' => ['limit', 'tpl', 'cache'], 'example' => '{mx:related limit="5" /}', 'description' => '详情页里输出同模型的其他内容，自动排除当前这条'],
            ['tag' => 'breadcrumb', 'label' => '面包屑', 'attrs' => ['home', 'separator'], 'example' => '{mx:breadcrumb /}', 'description' => '输出首页 / 栏目 / 当前内容的层级导航，详情页自动带标题'],
            ['tag' => 'search', 'label' => '搜索框', 'attrs' => ['model', 'placeholder', 'button'], 'example' => '{mx:search model="news" placeholder="搜索内容" /}', 'description' => '输出一个指向模型列表页的 GET 搜索表单'],
            ['tag' => 'paginate', 'label' => '分页导航', 'attrs' => ['total', 'per_page', 'page'], 'example' => '{mx:paginate total="100" per_page="10" /}', 'description' => '按当前请求地址输出分页链接，配合自己渲染的列表使用'],
            ['tag' => 'data', 'label' => '通用数据', 'attrs' => ['key', 'limit'], 'example' => '{mx:data key="bbs.latest" limit="5" /}', 'description' => '调用任意模块注册的数据提供者，模块与主题解耦的关键'],
            ['tag' => 'field', 'label' => '当前字段', 'attrs' => ['name', 'default'], 'example' => '{mx:field name="title" /}', 'description' => '在详情页输出当前记录的字段'],
            ['tag' => 'setting', 'label' => '主题配置', 'attrs' => ['key', 'default'], 'example' => '{mx:setting key="primary_color" /}', 'description' => '输出主题 config.json settings 里保存的值'],
            ['tag' => 'site', 'label' => '站点配置', 'attrs' => ['key', 'default'], 'example' => '{mx:site key="webname" /}', 'description' => '输出后台站点设置项'],
            ['tag' => 'asset', 'label' => '主题资源', 'attrs' => ['path'], 'example' => '{mx:asset path="css/app.css" /}', 'description' => '输出当前主题 assets 下资源的完整 URL'],
            ['tag' => 'nav', 'label' => '导航菜单', 'attrs' => ['position', 'tpl'], 'example' => '{mx:nav position="top" /}', 'description' => '渲染前台菜单，position 取 top / bottom / footer'],
            ['tag' => 'include', 'label' => '主题片段', 'attrs' => ['view'], 'example' => '{mx:include view="public.header" /}', 'description' => '引入主题内的 blade 片段，支持继承链回退'],
            ['tag' => 'hook', 'label' => '插件钩子', 'attrs' => ['name'], 'example' => '{mx:hook name="ThemeHead" /}', 'description' => '触发主题钩子，插件可在此注入 HTML'],
        ];

        // 插件可以补充自己的标签说明
        foreach ((array) hook('ThemeShortTagCatalog', ['catalog' => $catalog]) as $extra) {
            if (is_array($extra)) {
                $catalog = array_merge($catalog, $extra);
            }
        }

        return $catalog;
    }

    /* ------------------------------------------------------------------ */
    /* 内部实现                                                            */
    /* ------------------------------------------------------------------ */

    private static function handleMatch(array $matches, bool $paired): string
    {
        $raw = (string) $matches[0];
        // 转义写法原样保留，最后统一去掉反斜杠
        if (strpos($raw, '\\{') === 0) {
            return $raw;
        }

        $tag = strtolower((string) $matches[1]);
        $attrs = self::parseAttrs((string) ($matches[2] ?? ''));
        $inner = $paired ? (string) ($matches[4] ?? '') : '';

        try {
            return (string) self::dispatch($tag, $attrs, $inner, $paired);
        } catch (\Throwable $exception) {
            if (function_exists('config') && config('app.debug')) {
                return '<!-- shorttag error: ' . e($exception->getMessage()) . ' -->';
            }

            return '';
        }
    }

    private static function dispatch(string $tag, array $attrs, string $inner, bool $paired)
    {
        switch ($tag) {
            case 'list':
                return self::renderList($attrs, $inner, $paired);
            case 'detail':
                return self::renderDetail($attrs, $inner, $paired);
            case 'tree':
                return self::renderTree($attrs, $inner, $paired);
            case 'count':
                return (string) self::resolveData('formtools.count', $attrs);
            case 'chart':
                return self::renderChart($attrs);
            case 'slider':
                return self::renderSlider($attrs);
            case 'gallery':
                return self::renderGallery($attrs);
            case 'video':
                return self::renderVideo($attrs);
            case 'related':
                return self::renderRelated($attrs, $inner, $paired);
            case 'breadcrumb':
                return self::renderBreadcrumb($attrs);
            case 'search':
                return self::renderSearch($attrs);
            case 'paginate':
                return self::renderPaginate($attrs);
            case 'data':
                return self::renderGenericData($attrs, $inner, $paired);
            case 'field':
                return (string) self::currentField((string) ($attrs['name'] ?? ''), $attrs['default'] ?? '');
            case 'setting':
                return (string) theme_setting((string) ($attrs['key'] ?? ''), $attrs['default'] ?? '');
            case 'site':
                return (string) self::siteValue((string) ($attrs['key'] ?? ''), $attrs['default'] ?? '');
            case 'asset':
                return (string) ThemeManager::asset((string) ($attrs['path'] ?? ''));
            case 'nav':
            case 'menu':
                return self::renderNav($attrs);
            case 'include':
                return self::renderInclude($attrs);
            case 'hook':
                return self::renderHook($attrs);
        }

        // 交给插件
        $result = null;
        foreach ((array) hook('ThemeShortTag', ['tag' => $tag, 'attrs' => $attrs, 'inner' => $inner]) as $item) {
            if ($item !== null) {
                $result = $item;
                break;
            }
        }

        return $result ?? '';
    }

    private static function resolveData(string $key, array $attrs)
    {
        $params = $attrs;
        unset($params['tpl'], $params['key']);

        return ThemeDataRegistry::get($key, $params);
    }

    private static function renderList(array $attrs, string $inner, bool $paired): string
    {
        $result = self::resolveData('formtools.list', $attrs);
        $rows = (array) ($result['rows'] ?? []);
        if ($rows === []) {
            return isset($attrs['empty']) ? e((string) $attrs['empty']) : '';
        }

        // 统一整理成 theme_record 标准结构（cover_url / price / gallery / video 都已算好），
        // 这样主题 partials 和内置渲染器拿到的都是可直接用的字段。
        $access = (string) ($result['access'] ?? ($attrs['model'] ?? ''));
        $map = (array) ($result['map'] ?? []);
        $rows = array_map(static function ($row) use ($access, $map) {
            return theme_record($row['raw'] ?? $row, $access, $map);
        }, $rows);

        // type 是 tpl 的同义词：先找主题 partials/list-{tpl}.blade.php，
        // 找不到再退化到内置渲染器（card / product / video / gallery / text）。
        $tpl = (string) ($attrs['tpl'] ?? $attrs['type'] ?? '');
        if ($tpl !== '') {
            $view = ThemeManager::resolveView('partials.list-' . $tpl);
            if ($view !== null) {
                return (string) view($view, [
                    'rows' => $rows,
                    'items' => $result['items'] ?? [],
                    'result' => $result,
                    'attrs' => $attrs,
                    'map' => $result['map'] ?? [],
                ])->render();
            }

            $renderer = 'renderer' . \Illuminate\Support\Str::studly(strtolower($tpl));
            if (method_exists(self::class, $renderer)) {
                return (string) self::{$renderer}($rows, $attrs);
            }
        }

        $html = '';
        foreach ($rows as $row) {
            $html .= self::applyRowTemplate($inner, $row, $paired);
        }

        return $html !== '' ? $html : self::defaultListHtml($rows, $attrs);
    }

    /* ------------------------------------------------------------------ */
    /* 内置渲染器：主题没放 partials/list-*.blade.php 时的兜底              */
    /* ------------------------------------------------------------------ */

    /**
     * type="card" / tpl="card" —— 封面 + 标题 + 摘要的卡片网格。
     */
    private static function rendererCard(array $rows, array $attrs): string
    {
        $cols = max(1, min(6, (int) ($attrs['cols'] ?? 3)));
        $html = '<div class="mx-shorttag-cards mx-card-grid" style="grid-template-columns:repeat(' . $cols . ',minmax(0,1fr));">';
        foreach ($rows as $row) {
            $title = (string) ($row['title'] ?? ('#' . ($row['id'] ?? '')));
            $link = (string) ($row['link'] ?? '');
            $cover = (string) ($row['cover_url'] ?? '');
            $summary = (string) ($row['summary'] ?? '');
            $html .= '<article class="mx-card">';
            if ($cover !== '') {
                $html .= '<a href="' . e($link) . '" class="mx-card__thumb"><img src="' . e($cover) . '" alt="' . e($title) . '" loading="lazy"></a>';
            }
            $html .= '<h3 class="mx-card__title"><a href="' . e($link) . '">' . e($title) . '</a></h3>';
            $html .= $summary !== '' ? '<p class="mx-card__desc">' . e(\Illuminate\Support\Str::limit($summary, 90)) . '</p>' : '';
            $html .= self::rowMeta($row, ['date']);
            $html .= '</article>';
        }

        return $html . '</div>';
    }

    /**
     * type="product" —— 价格卡片网格，模型有 price 字段时显示价格。
     */
    private static function rendererProduct(array $rows, array $attrs): string
    {
        $cols = max(2, min(6, (int) ($attrs['cols'] ?? 4)));
        $html = '<div class="mx-product-grid" style="grid-template-columns:repeat(' . $cols . ',minmax(0,1fr));">';
        foreach ($rows as $row) {
            $title = (string) ($row['title'] !== '' ? $row['title'] : ('#' . ($row['id'] ?? '')));
            $link = (string) ($row['link'] ?? '');
            $cover = (string) ($row['cover_url'] ?? '');
            $price = (string) ($row['price'] ?? '');
            $origin = (string) ($row['origin_price'] ?? '');
            $stock = $row['stock'] ?? null;
            $tags = (array) ($row['tags'] ?? []);
            $spec = (string) ($row['spec'] ?? '');
            $html .= '<article class="mx-product">';
            $html .= '<a href="' . e($link) . '" class="mx-product__media">';
            $html .= $cover !== ''
                ? '<img src="' . e($cover) . '" alt="' . e($title) . '" loading="lazy">'
                : '<span class="mx-product__placeholder"><i class="fa fa-picture-o"></i></span>';
            if ($stock === 0) {
                $html .= '<span class="mx-product__badge mx-product__badge--off">已售罄</span>';
            } elseif ($origin !== '' && $price !== '') {
                $html .= '<span class="mx-product__badge">促销中</span>';
            }
            $html .= '</a>';
            $html .= '<div class="mx-product__body">';
            if ($tags !== []) {
                $html .= '<div class="mx-tag-row">';
                foreach (array_slice($tags, 0, 3) as $tag) {
                    $html .= '<span class="mx-tag">' . e((string) $tag) . '</span>';
                }
                $html .= '</div>';
            }
            $html .= '<h3 class="mx-product__title"><a href="' . e($link) . '">' . e($title) . '</a></h3>';
            if ($spec !== '') {
                $html .= '<p class="mx-product__spec">' . e($spec) . '</p>';
            }
            $html .= '<div class="mx-product__price">';
            $html .= $price !== ''
                ? '<strong class="mx-price">' . e($price) . '</strong>'
                : '<strong class="mx-price mx-price--quote">面议</strong>';
            if ($origin !== '') {
                $html .= '<del class="mx-price-origin">' . e($origin) . '</del>';
            }
            if ($stock !== null) {
                $html .= '<span class="mx-product__stock">库存 ' . e((string) $stock) . '</span>';
            }
            $html .= '</div>';
            $html .= '<a class="mx-product__action" href="' . e($link) . '">查看详情</a>';
            $html .= '</div></article>';
        }

        return $html . '</div>';
    }

    /**
     * type="video" —— 带播放按钮的视频卡片，点击弹窗播放。
     */
    private static function rendererVideo(array $rows, array $attrs): string
    {
        $cols = max(1, min(6, (int) ($attrs['cols'] ?? 3)));
        $html = '<div class="mx-video-grid" style="grid-template-columns:repeat(' . $cols . ',minmax(0,1fr));">';
        foreach ($rows as $row) {
            $title = (string) ($row['title'] !== '' ? $row['title'] : ('#' . ($row['id'] ?? '')));
            $cover = (string) ($row['cover_url'] ?? '');
            $videoType = (string) ($row['video_type'] ?? '');
            $videoSrc = (string) ($row['video_url'] ?? '');
            $link = (string) ($row['link'] ?? '');
            $html .= '<article class="mx-video-card">';
            $html .= '<button type="button" class="mx-video-card__media" data-mx-video'
                . ' data-video-type="' . e($videoType) . '"'
                . ' data-video-src="' . e($videoSrc) . '"'
                . ' data-video-title="' . e($title) . '"'
                . ($videoSrc === '' ? ' disabled' : '') . '>';
            $html .= $cover !== ''
                ? '<img src="' . e($cover) . '" alt="' . e($title) . '" loading="lazy">'
                : '<span class="mx-product__placeholder"><i class="fa fa-film"></i></span>';
            $html .= '<span class="mx-video-play"><i class="fa fa-play"></i></span></button>';
            $html .= '<div class="mx-video-card__body"><h3 class="mx-video-card__title">';
            $html .= $link !== '' ? '<a href="' . e($link) . '">' . e($title) . '</a>' : e($title);
            $html .= '</h3></div></article>';
        }

        return $html . '</div>';
    }

    /**
     * type="gallery" —— 图片瀑布，点击放大（复用主题的灯箱）。
     */
    private static function rendererGallery(array $rows, array $attrs): string
    {
        $cols = max(1, min(6, (int) ($attrs['cols'] ?? 3)));
        $group = 'mx-st-gallery-' . substr(md5(json_encode($attrs)), 0, 8);
        $html = '<div class="mx-gallery" style="--mx-gallery-columns:' . $cols . ';" data-mx-lightbox-group="' . e($group) . '">';
        foreach ($rows as $row) {
            $record = theme_record($row['raw'] ?? $row, (string) ($attrs['model'] ?? ''), (array) ($row['map'] ?? []));
            $images = $record['gallery'];
            if ($images === []) {
                continue;
            }
            $caption = (string) ($record['title'] ?? '');
            foreach ($images as $image) {
                $html .= '<a href="' . e($image) . '" class="mx-gallery__item" data-mx-lightbox data-caption="' . e($caption) . '">'
                    . '<img src="' . e($image) . '" alt="' . e($caption) . '" loading="lazy"></a>';
            }
        }

        return $html . '</div>';
    }

    /**
     * type="text" —— 纯标题链接列表。
     */
    private static function rendererText(array $rows, array $attrs): string
    {
        return self::defaultListHtml($rows, $attrs);
    }

    /**
     * 卡片里那一行元信息（日期等），没有可用字段时返回空串。
     */
    private static function rowMeta(array $row, array $keys): string
    {
        $parts = [];
        foreach ($keys as $key) {
            $value = (string) ($row[$key] ?? '');
            if ($value !== '') {
                $parts[] = '<span>' . e($value) . '</span>';
            }
        }

        return $parts === [] ? '' : '<div class="mx-card__meta">' . implode('', $parts) . '</div>';
    }

    private static function renderDetail(array $attrs, string $inner, bool $paired): string
    {
        $result = self::resolveData('formtools.detail', $attrs);
        if (!is_array($result)) {
            return '';
        }

        $tpl = (string) ($attrs['tpl'] ?? '');
        if ($tpl !== '') {
            $view = ThemeManager::resolveView('partials.detail-' . $tpl);
            if ($view !== null) {
                return (string) view($view, ['row' => $result, 'attrs' => $attrs])->render();
            }
        }

        // 自闭合写法没有行模板，必须给出默认渲染，否则整段输出空白
        if (!$paired) {
            return self::defaultDetailHtml($result);
        }

        return self::applyRowTemplate($inner, $result, $paired);
    }

    private static function renderTree(array $attrs, string $inner, bool $paired): string
    {
        $items = (array) self::resolveData('formtools.tree', $attrs);
        if ($items === []) {
            return '';
        }

        $tpl = (string) ($attrs['tpl'] ?? '');
        if ($tpl !== '') {
            $view = ThemeManager::resolveView('partials.tree-' . $tpl);
            if ($view !== null) {
                return (string) view($view, ['items' => $items, 'attrs' => $attrs])->render();
            }
        }

        // 自闭合时行模板为空，用默认项渲染，避免输出一串空 <li>
        return self::treeHtml($items, $inner, $paired);
    }

    /**
     * 单条记录的默认渲染（自闭合 {mx:detail /} 用）。
     */
    private static function defaultDetailHtml(array $row): string
    {
        $title = (string) ($row['title'] ?? '');
        $summary = (string) ($row['summary'] ?? '');
        $link = (string) ($row['link'] ?? '');

        // 没有摘要字段时，从正文里截一段，避免单页模型渲染出一个空的壳
        if ($summary === '' && ($row['content'] ?? '') !== '') {
            $summary = \Illuminate\Support\Str::limit(trim(strip_tags((string) $row['content'])), 120);
        }

        $html = '<div class="mx-shorttag-detail">';
        $html .= $title !== '' ? '<h3>' . ($link !== '' ? '<a href="' . e($link) . '">' . e($title) . '</a>' : e($title)) . '</h3>' : '';
        $html .= $summary !== '' ? '<p>' . e($summary) . '</p>' : '';
        $html .= '</div>';

        return $html;
    }

    private static function renderChart(array $attrs): string
    {
        $data = (array) self::resolveData('formtools.chart', $attrs);

        $tpl = (string) ($attrs['tpl'] ?? '');
        if ($tpl !== '') {
            $view = ThemeManager::resolveView('partials.chart-' . $tpl);
            if ($view !== null) {
                return (string) view($view, ['chart' => $data, 'attrs' => $attrs])->render();
            }
        }

        $view = ThemeManager::resolveView('partials.chart');
        if ($view !== null) {
            return (string) view($view, ['chart' => $data, 'attrs' => $attrs])->render();
        }

        // 没有主题模板时输出一个带 data-* 的容器，主题自己接 ECharts
        return '<div class="mx-chart" data-chart-type="' . e((string) ($data['type'] ?? 'count')) . '" '
            . 'data-chart-labels="' . e((string) json_encode($data['labels'] ?? [], JSON_UNESCAPED_UNICODE)) . '" '
            . 'data-chart-values="' . e((string) json_encode($data['values'] ?? [], JSON_UNESCAPED_UNICODE)) . '"></div>';
    }

    /**
     * {mx:slider model="banner" limit="3" height="380" interval="4000" /}
     *
     * 取模型的封面图做轮播。只输出结构，切换逻辑由主题的 components.js 接管；
     * 主题没带脚本时至少能显示第一张，不会留白。
     */
    private static function renderSlider(array $attrs): string
    {
        $result = self::resolveData('formtools.list', $attrs);
        $rows = (array) ($result['rows'] ?? []);
        if ($rows === []) {
            return isset($attrs['empty']) ? e((string) $attrs['empty']) : '';
        }

        $height = (int) ($attrs['height'] ?? 380);
        $interval = (int) ($attrs['interval'] ?? 4000);
        $id = 'mx-slider-' . substr(md5(json_encode($attrs)), 0, 8);

        $slides = '';
        $dots = '';
        foreach ($rows as $index => $row) {
            $cover = (string) ($row['cover'] ?? '');
            if ($cover === '') {
                continue;
            }
            $title = (string) ($row['title'] ?? '');
            $link = (string) ($row['link'] ?? '');
            $tag = $link !== '' ? 'a' : 'div';
            $href = $link !== '' ? ' href="' . e($link) . '"' : '';

            $slides .= '<' . $tag . $href . ' class="mx-slider__slide' . ($index === 0 ? ' is-active' : '') . '">'
                . '<img src="' . e($cover) . '" alt="' . e($title) . '" loading="' . ($index === 0 ? 'eager' : 'lazy') . '">'
                . ($title !== '' ? '<div class="mx-slider__caption">' . e($title) . '</div>' : '')
                . '</' . $tag . '>';
            $dots .= '<button type="button" class="mx-slider__dot' . ($index === 0 ? ' is-active' : '') . '" data-mx-slider-dot="' . $index . '" aria-label="第 ' . ($index + 1) . ' 张"></button>';
        }

        if ($slides === '') {
            return '';
        }

        $controls = substr_count($slides, 'mx-slider__slide') > 1
            ? '<button type="button" class="mx-slider__arrow mx-slider__arrow--prev" data-mx-slider-prev aria-label="上一张">&#10094;</button>'
            . '<button type="button" class="mx-slider__arrow mx-slider__arrow--next" data-mx-slider-next aria-label="下一张">&#10095;</button>'
            . '<div class="mx-slider__dots">' . $dots . '</div>'
            : '';

        return '<div class="mx-slider" id="' . e($id) . '" data-mx-slider data-interval="' . max(0, $interval) . '" style="--mx-slider-height:' . max(120, $height) . 'px;">'
            . '<div class="mx-slider__track">' . $slides . '</div>'
            . $controls
            . '</div>';
    }

    /**
     * {mx:gallery model="album" limit="9" cols="3" /}
     *
     * 与 list 的 type="gallery" 渲染器等价，独立成标签是为了读起来更直白。
     */
    private static function renderGallery(array $attrs): string
    {
        $result = self::resolveData('formtools.list', $attrs);
        $rows = (array) ($result['rows'] ?? []);
        if ($rows === []) {
            return isset($attrs['empty']) ? e((string) $attrs['empty']) : '';
        }

        return self::rendererGallery($rows, $attrs);
    }

    /**
     * {mx:video model="course" id="3" /} 或 {mx:video src="/upload/a.mp4" cover="..." /}
     */
    private static function renderVideo(array $attrs): string
    {
        $src = trim((string) ($attrs['src'] ?? ''));
        $cover = trim((string) ($attrs['cover'] ?? ''));

        if ($src === '' && !empty($attrs['model'])) {
            $result = self::resolveData('formtools.detail', $attrs);
            if (!is_array($result)) {
                return '';
            }
            $record = theme_record($result['raw'] ?? $result, (string) ($attrs['model'] ?? ''), (array) ($result['map'] ?? []));
            $src = $record['video'];
            $cover = $cover !== '' ? $cover : $record['cover'];
        }

        if ($src === '') {
            return '';
        }

        $source = theme_video_source($src);
        if ($source['type'] === 'iframe') {
            $inner = '<iframe src="' . e($source['src']) . '" allowfullscreen frameborder="0" title="' . e((string) ($attrs['title'] ?? '')) . '"></iframe>';
        } else {
            $inner = '<video controls preload="metadata" playsinline' . ($cover !== '' ? ' poster="' . e(theme_media_url($cover)) . '"' : '') . '>'
                . '<source src="' . e($source['src']) . '">您的浏览器不支持在线播放该视频。</video>';
        }

        return '<div class="mx-video-detail__player mx-shorttag-video">' . $inner . '</div>';
    }

    /**
     * {mx:related limit="5" /} —— 详情页里输出同模型的其他内容。
     *
     * 模型标识与当前记录 id 都从渲染上下文里取（$param['model'] 与 $detailRecord / $data），
     * 所以只在列表/详情页里有效，首页用会输出空。
     */
    private static function renderRelated(array $attrs, string $inner, bool $paired): string
    {
        $access = self::contextAccess();
        $currentId = self::contextRecordId();
        if ($access === '') {
            return '';
        }

        $params = $attrs;
        unset($params['tpl']);
        $params['model'] = $access;
        // 多取一条，用来补上被排除的当前记录
        $params['limit'] = max(1, (int) ($params['limit'] ?? 5)) + ($currentId > 0 ? 1 : 0);

        $result = self::resolveData('formtools.list', $params);
        $rows = (array) ($result['rows'] ?? []);
        $rows = array_values(array_filter($rows, static function ($row) use ($currentId) {
            return $currentId <= 0 || (int) ($row['id'] ?? 0) !== $currentId;
        }));
        $rows = array_slice($rows, 0, max(1, (int) ($attrs['limit'] ?? 5)));

        if ($rows === []) {
            return isset($attrs['empty']) ? e((string) $attrs['empty']) : '';
        }

        $tpl = (string) ($attrs['tpl'] ?? '');
        if ($tpl !== '') {
            $view = ThemeManager::resolveView('partials.list-' . $tpl);
            if ($view !== null) {
                return (string) view($view, ['rows' => $rows, 'attrs' => $attrs, 'map' => $result['map'] ?? []])->render();
            }
        }

        $html = '';
        foreach ($rows as $row) {
            $html .= self::applyRowTemplate($inner, $row, $paired);
        }

        return $html !== '' ? $html : self::defaultListHtml($rows, $attrs);
    }

    /**
     * {mx:breadcrumb /} —— 首页 / 栏目 / 当前内容。
     */
    private static function renderBreadcrumb(array $attrs): string
    {
        $homeText = (string) ($attrs['home'] ?? '首页');
        $separator = (string) ($attrs['separator'] ?? '/');
        $access = self::contextAccess();
        $modelName = self::contextModelName();
        $title = self::contextTitle();

        $items = ['<a href="' . e(url('/')) . '">' . e($homeText) . '</a>'];
        if ($access !== '') {
            $items[] = '<a href="' . e((string) model_list_url($access)) . '">' . e($modelName) . '</a>';
        }
        if ($title !== '') {
            $items[] = '<em>' . e($title) . '</em>';
        }

        return '<nav class="mx-breadcrumb" aria-label="面包屑导航">' . implode('<span class="mx-breadcrumb__sep">' . e($separator) . '</span>', $items) . '</nav>';
    }

    /**
     * {mx:search model="news" placeholder="搜索内容" /}
     */
    private static function renderSearch(array $attrs): string
    {
        $access = trim((string) ($attrs['model'] ?? '')) ?: self::contextAccess();
        if ($access === '') {
            return '';
        }

        $placeholder = (string) ($attrs['placeholder'] ?? '输入关键词搜索');
        $buttonText = (string) ($attrs['button'] ?? '搜索');
        $keyword = (string) request()->input('keyword', '');

        return '<form method="get" action="' . e((string) model_list_url($access)) . '" class="mx-search-inline">'
            . '<input type="text" name="keyword" value="' . e($keyword) . '" placeholder="' . e($placeholder) . '">'
            . '<button type="submit">' . e($buttonText) . '</button>'
            . '</form>';
    }

    /**
     * {mx:paginate total="100" per_page="10" /} —— 自己渲染列表时补一个分页。
     */
    private static function renderPaginate(array $attrs): string
    {
        $total = max(0, (int) ($attrs['total'] ?? 0));
        if ($total <= 0) {
            return '';
        }

        $perPage = max(1, (int) ($attrs['per_page'] ?? 10));
        $lastPage = (int) ceil($total / $perPage);
        if ($lastPage <= 1) {
            return '';
        }

        $current = max(1, min($lastPage, (int) ($attrs['page'] ?? request()->input('page', 1))));
        $query = request()->except('page');

        $link = static function ($page, $label, $class = '') use ($query) {
            $url = url()->current() . ($query ? '?' . http_build_query($query) . '&' : '?') . 'page=' . $page;

            return '<a href="' . e($url) . '" class="mx-pagination__item ' . $class . '">' . e((string) $label) . '</a>';
        };

        $html = '<div class="mx-pagination mx-shorttag-pagination">';
        $html .= $current > 1 ? $link($current - 1, '上一页') : '<span class="mx-pagination__item is-disabled">上一页</span>';

        $start = max(1, $current - 2);
        $end = min($lastPage, $start + 4);
        $start = max(1, $end - 4);
        for ($page = $start; $page <= $end; $page += 1) {
            $html .= $link($page, $page, $page === $current ? 'is-active' : '');
        }

        $html .= $current < $lastPage ? $link($current + 1, '下一页') : '<span class="mx-pagination__item is-disabled">下一页</span>';
        $html .= '</div>';

        return $html;
    }

    /**
     * 当前渲染上下文里的模型访问标识。
     */
    private static function contextAccess(): string
    {
        $param = (array) (self::$context['param'] ?? []);

        return trim((string) ($param['model'] ?? self::$context['access'] ?? ''));
    }

    /**
     * 当前记录 id（详情页有效）。
     */
    private static function contextRecordId(): int
    {
        foreach (['detailRecord', 'data', 'item', 'row'] as $key) {
            $value = self::$context[$key] ?? null;
            if ($value === null || is_scalar($value)) {
                continue;
            }
            $array = is_array($value) ? $value : (is_object($value) ? get_object_vars($value) : []);

            return (int) ($array['id'] ?? 0);
        }

        return 0;
    }

    /**
     * 当前模型的显示名：优先用控制器传进来的 $model['name']，其次查模型表。
     */
    private static function contextModelName(): string
    {
        $model = (array) (self::$context['model'] ?? []);
        $name = trim((string) ($model['name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $access = self::contextAccess();
        if ($access === '') {
            return '';
        }

        foreach ((array) self::resolveData('formtools.models', []) as $item) {
            $item = (array) $item;
            if ((string) ($item['access'] ?? '') === $access) {
                return (string) ($item['name'] ?? $access);
            }
        }

        return $access;
    }

    /**
     * 当前记录标题（详情页有效）。
     */
    private static function contextTitle(): string
    {
        return trim((string) self::currentField('title', self::currentField('name', '')));
    }

    private static function renderGenericData(array $attrs, string $inner, bool $paired): string
    {
        $key = (string) ($attrs['key'] ?? '');
        if ($key === '') {
            return '';
        }

        $data = self::resolveData($key, $attrs);
        if ($data === null || $data === '') {
            return '';
        }

        if (is_string($data)) {
            return $data;
        }

        $rows = is_array($data) ? ($data['rows'] ?? $data) : $data;
        if (!is_array($rows)) {
            return (string) $rows;
        }

        // 只有一层结构时按 [['k'=>v]] 处理
        $first = reset($rows);
        if (!is_array($first)) {
            return self::applyRowTemplate($inner, ['raw' => $rows], $paired);
        }

        $html = '';
        foreach ($rows as $row) {
            $html .= self::applyRowTemplate($inner, is_array($row) ? $row : ['value' => $row], $paired);
        }

        return $html;
    }

    private static function renderNav(array $attrs): string
    {
        $position = (string) ($attrs['position'] ?? 'top');

        $menus = [];
        if (function_exists('hook')) {
            $menus = (array) (hook('GetHomeMenu', ['moduleName' => 'System', 'position' => $position])[0] ?? []);
        }

        $view = ThemeManager::resolveView((string) ($attrs['tpl'] ?? '') !== ''
            ? 'partials.nav-' . $attrs['tpl']
            : 'public.menuItem');

        if ($view !== null) {
            return (string) view($view, ['menus' => $menus, 'position' => $position, 'attrs' => $attrs])->render();
        }

        return self::defaultNavHtml($menus, $position);
    }

    private static function renderInclude(array $attrs): string
    {
        $view = ThemeManager::resolveView((string) ($attrs['view'] ?? ''));
        if ($view === null) {
            return '';
        }

        return (string) view($view, \Illuminate\Support\Arr::except($attrs, ['view']))->render();
    }

    private static function renderHook(array $attrs): string
    {
        $name = (string) ($attrs['name'] ?? '');
        if ($name === '') {
            return '';
        }

        $html = '';
        foreach ((array) hook($name, ['theme' => ThemeManager::current(), 'attrs' => $attrs]) as $item) {
            if ($item !== null) {
                $html .= (string) $item;
            }
        }

        return $html;
    }

    /**
     * 行模板占位替换。
     *
     *   {title}                  转义输出（默认，安全）
     *   {content|raw}            原样输出，富文本正文用这个，否则 HTML 会被转义成实体
     *   {content|strip|limit:80} 先去标签再截 80 字（过滤器按书写顺序执行）
     *   {date|date:Y-m-d}        日期格式化，Y/m/d 等任意 date() 格式
     *   {title|default:未命名}   值为空时的兜底文案
     *   {raw.author}             直接取原始字段（同样支持过滤器）
     *
     * 可用过滤器：raw / strip / limit:N / words:N / date:FMT / upper / lower / nl / default:X
     */
    private static function applyRowTemplate(string $template, array $row, bool $paired): string
    {
        if (!$paired || trim($template) === '') {
            return '';
        }

        $raw = (array) ($row['raw'] ?? $row);

        return (string) preg_replace_callback('/\{([\w.]+)((?:\|[\w]+(?::[^}|]*)?)*)\}/', static function (array $matches) use ($row, $raw) {
            $token = (string) ($matches[1] ?? '');
            if ($token === '') {
                return (string) $matches[0];
            }

            $filters = self::parseFilters((string) ($matches[2] ?? ''));

            if (strpos($token, 'raw.') === 0) {
                $value = $raw[substr($token, 4)] ?? null;
            } elseif (array_key_exists($token, $row)) {
                $value = $row[$token];
            } else {
                $value = $raw[$token] ?? null;
            }

            if ($value !== null && !is_scalar($value)) {
                return self::PLACEHOLDER_FALLBACK;
            }

            return self::applyFilters((string) $value, $filters);
        }, $template);
    }

    /**
     * 解析 `|raw|limit:30` 这样的过滤器串。
     *
     * @return array<int, array{name:string,arg:string}>
     */
    private static function parseFilters(string $text): array
    {
        $filters = [];
        if (trim($text, '| ') === '') {
            return $filters;
        }

        foreach (explode('|', trim($text, '|')) as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }
            $parts = explode(':', $chunk, 2);
            $filters[] = [
                'name' => strtolower(trim((string) $parts[0])),
                'arg' => isset($parts[1]) ? trim((string) $parts[1]) : '',
            ];
        }

        return $filters;
    }

    /**
     * 按书写顺序执行过滤器，最后统一决定要不要转义。
     */
    private static function applyFilters(string $value, array $filters): string
    {
        $escape = true;

        foreach ($filters as $filter) {
            $length = max(1, (int) $filter['arg'] ?: ($filter['name'] === 'limit' ? 100 : 30));

            switch ($filter['name']) {
                case 'raw':
                    $escape = false;
                    break;
                case 'strip':
                    $value = strip_tags($value);
                    break;
                case 'limit':
                    $value = \Illuminate\Support\Str::limit($value, $filter['arg'] !== '' ? $length : 100);
                    break;
                case 'words':
                    $value = \Illuminate\Support\Str::words($value, $filter['arg'] !== '' ? $length : 30);
                    break;
                case 'date':
                    $format = $filter['arg'] !== '' ? $filter['arg'] : 'Y-m-d';
                    $timestamp = is_numeric($value) ? (int) $value : strtotime((string) $value);
                    $value = $timestamp > 0 ? date($format, $timestamp) : $value;
                    break;
                case 'upper':
                    $value = mb_strtoupper($value);
                    break;
                case 'lower':
                    $value = mb_strtolower($value);
                    break;
                case 'nl':
                    $value = nl2br(e($value));
                    $escape = false;
                    break;
                case 'default':
                    if (trim($value) === '') {
                        $value = $filter['arg'];
                    }
                    break;
            }
        }

        return $escape ? e($value) : $value;
    }

    private static function defaultListHtml(array $rows, array $attrs): string
    {
        $html = '<ul class="mx-shorttag-list">';
        foreach ($rows as $row) {
            $title = (string) ($row['title'] ?? '');
            if ($title === '') {
                // 单页模型常没有标题字段，兜底显示编号，避免出现空链接
                $title = '#' . (string) ($row['id'] ?? '');
            }
            $link = (string) ($row['link'] ?? '');
            $html .= '<li>' . ($link !== '' ? '<a href="' . e($link) . '">' . e($title) . '</a>' : e($title)) . '</li>';
        }

        return $html . '</ul>';
    }

    private static function defaultNavHtml(array $menus, string $position): string
    {
        $html = '<ul class="mx-shorttag-nav mx-nav-' . e($position) . '">';
        foreach ($menus as $menu) {
            $menu = (array) $menu;
            $title = (string) ($menu['title'] ?? $menu['name'] ?? '');
            $url = (string) ($menu['url'] ?? $menu['link'] ?? '#');
            if ($title === '') {
                continue;
            }
            $html .= '<li><a href="' . e($url) . '">' . e($title) . '</a></li>';
        }

        return $html . '</ul>';
    }

    private static function treeHtml(array $items, string $inner, bool $paired): string
    {
        $html = '<ul class="mx-shorttag-tree">';
        foreach ($items as $item) {
            $item = (array) $item;
            $html .= '<li>' . self::treeItemHtml($item, $inner, $paired);
            if (!empty($item['children'])) {
                $html .= self::treeHtml((array) $item['children'], $inner, $paired);
            }
            $html .= '</li>';
        }

        return $html . '</ul>';
    }

    /**
     * 树节点的默认渲染（自闭合 {mx:tree /} 或未提供行模板时用）。
     */
    private static function treeItemHtml(array $item, string $inner, bool $paired): string
    {
        if ($paired && trim($inner) !== '') {
            return self::applyRowTemplate($inner, $item, $paired);
        }

        $title = (string) ($item['title'] ?? '');
        if ($title === '') {
            $title = '#' . (string) ($item['id'] ?? '');
        }
        $link = (string) ($item['link'] ?? '');

        return $link !== '' ? '<a href="' . e($link) . '">' . e($title) . '</a>' : e($title);
    }

    /**
     * 详情页上下文里取当前记录字段。
     *
     * 注意：控制器 `view($tpl, ['detailRecord' => $row])` 传的数据不在
     * `View::getShared()` 里，所以由 ThemeView 在渲染前把视图数据灌进 self::$context，
     * 这里优先从 $context 取，取不到再看 shared / 当前记录。
     */
    private static function currentField(string $name, $default = '')
    {
        if ($name === '') {
            return $default;
        }

        $sources = [];

        // 1. 当前渲染视图的数据（ThemeView::render 注入）
        if (self::$context !== []) {
            $sources[] = self::$context;
        }

        // 2. View::share() 共享的数据
        $sources[] = (array) app('view')->getShared();

        foreach ($sources as $source) {
            foreach (['detailRecord', 'data', 'item', 'row'] as $key) {
                $value = $source[$key] ?? null;
                if ($value === null || is_scalar($value)) {
                    continue;
                }
                $array = is_array($value) ? $value : (is_object($value) ? get_object_vars($value) : []);
                if (array_key_exists($name, $array)) {
                    return $array[$name];
                }
            }
        }

        return $default;
    }

    private static function siteValue(string $key, $default = '')
    {
        if ($key === '' || !function_exists('cacheGlobalSettingsByKey')) {
            return $default;
        }

        try {
            $value = cacheGlobalSettingsByKey($key);
        } catch (\Throwable $exception) {
            $value = null;
        }

        return ($value === null || $value === '') ? $default : $value;
    }

    private static function decodeValue(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
