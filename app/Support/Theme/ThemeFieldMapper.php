<?php

namespace App\Support\Theme;

/**
 * 主题字段映射器 —— 解决「主题模板」与「Formtools 模型字段」的耦合问题。
 *
 * 问题：主题模板里写死 `$row['title']`、`$row['content']`，换个模型字段名不一样就全崩。
 * 解决：模板一律用「别名」取值，映射器负责把别名翻译成当前模型的真实字段。
 *
 *     $map = ThemeFieldMapper::make($modelFields);   // ['title' => 'title', 'cover' => 'thumb', ...]
 *     echo ThemeFieldMapper::pick($row, $map, 'title');
 *
 * 别名表优先取 Formtools 的 FormTemplateCatalog::fieldAliases()，模块不在时回落内置表，
 * 保证主题不硬依赖模块。
 */
class ThemeFieldMapper
{
    /**
     * 内置别名表（与 Formtools 保持一致）。
     *
     * @return array<string, array<int, string>>
     */
    public static function aliases(): array
    {
        if (class_exists(\Modules\Formtools\Support\FormTemplateCatalog::class)) {
            try {
                $aliases = \Modules\Formtools\Support\FormTemplateCatalog::fieldAliases();
                if (is_array($aliases) && $aliases !== []) {
                    return $aliases;
                }
            } catch (\Throwable $exception) {
                // 模块异常时回落内置表
            }
        }

        return [
            'title' => ['title', 'name'],
            'subtitle' => ['subtitle', 'sub_title', 'slogan'],
            'summary' => ['summary', 'describe', 'description', 'intro', 'remark'],
            'content' => ['content', 'body', 'detail'],
            'cover' => ['cover', 'image', 'thumb', 'pic', 'logo'],
            'gallery' => ['images', 'gallery', 'photos', 'albums'],
            'video' => ['video', 'video_url', 'media_url', 'video_src'],
            'file' => ['file', 'file_url', 'attachment', 'download', 'download_url'],
            'date' => ['date', 'publish_time', 'created_at', 'updated_at'],
            'pid' => ['pid', 'parent_id'],
            'price' => ['price', 'amount', 'money', 'sale_price', 'shop_price'],
            'origin_price' => ['origin_price', 'market_price', 'original_price', 'old_price'],
            'stock' => ['stock', 'inventory', 'quantity', 'stock_num'],
            'spec' => ['spec', 'specification', 'specs', 'sku', 'model'],
            'tag' => ['tag', 'tags', 'keyword', 'keywords', 'label'],
            'position' => ['position', 'job', 'job_title', 'duty', 'title_name'],
            'phone' => ['phone', 'tel', 'mobile'],
            'email' => ['email', 'mail'],
            'address' => ['address', 'company_address'],
            'link' => ['url', 'link', 'href'],
        ];
    }

    /**
     * 由模型字段定义构建「别名 => 真实字段名」映射。
     *
     * @param array $modelFields Formtools 的 fields 数组，形如 [['identification'=>'title','name'=>'标题'], ...]
     * @param array $extraFields 额外参与匹配的字段名（如系统固定列 id/created_at）
     */
    public static function make(array $modelFields, array $extraFields = []): array
    {
        $names = [];
        foreach ($modelFields as $field) {
            if (is_array($field)) {
                $name = (string) ($field['identification'] ?? '');
            } else {
                $name = (string) $field;
            }
            if ($name !== '') {
                $names[] = $name;
            }
        }

        foreach (['id', 'pid', 'uid', 'price', 'status', 'created_at', 'updated_at'] as $system) {
            $names[] = $system;
        }
        foreach ($extraFields as $name) {
            $names[] = (string) $name;
        }

        $names = array_values(array_unique(array_filter($names)));

        $map = [];
        foreach (self::aliases() as $alias => $candidates) {
            $alias = self::stripAliasSuffix((string) $alias);
            $hit = self::match($names, (array) $candidates);
            if ($hit !== '') {
                $map[$alias] = $hit;
            }
        }

        return $map;
    }

    /**
     * 按别名从一行数据里取值。
     */
    public static function pick($row, array $map, string $alias, $default = '')
    {
        $row = self::toArray($row);
        $field = $map[$alias] ?? $alias;

        if (array_key_exists($field, $row)) {
            return $row[$field];
        }

        // 映射不到就退化成直接按别名取，兼容主题作者手写的字段名
        return $row[$alias] ?? $default;
    }

    /**
     * 取字段的显示名（后台配置的 name），用于表头 / 图表轴标签。
     */
    public static function label(array $modelFields, string $field, string $default = ''): string
    {
        foreach ($modelFields as $item) {
            if (!is_array($item)) {
                continue;
            }
            if ((string) ($item['identification'] ?? '') === $field) {
                return (string) ($item['name'] ?? $default) ?: $default;
            }
        }

        return $default !== '' ? $default : $field;
    }

    /**
     * 把原始行数据按映射整理成主题模板能直接用的结构。
     *
     * @return array{title:string,summary:string,content:string,cover:string,date:string,link:string,raw:array}
     */
    public static function normalize($row, array $map, string $access = ''): array
    {
        $raw = self::toArray($row);

        return [
            'id' => (int) ($raw['id'] ?? 0),
            'title' => (string) self::pick($raw, $map, 'title', ''),
            'summary' => (string) self::pick($raw, $map, 'summary', ''),
            'content' => (string) self::pick($raw, $map, 'content', ''),
            'cover' => (string) self::pick($raw, $map, 'cover', ''),
            'date' => (string) self::pick($raw, $map, 'date', ''),
            'link' => self::buildLink($access, (int) ($raw['id'] ?? 0)),
            'raw' => $raw,
            'map' => $map,
        ];
    }

    public static function buildLink(string $access, int $id): string
    {
        if ($access === '' || $id <= 0) {
            return '';
        }

        if (function_exists('model_detail_url')) {
            try {
                return (string) model_detail_url($access, $id);
            } catch (\Throwable $exception) {
                // 继续走默认拼接
            }
        }

        return url('detail/' . $access . '/' . $id . '.html');
    }

    private static function match(array $names, array $candidates): string
    {
        foreach ($candidates as $candidate) {
            $candidate = strtolower(trim((string) $candidate));
            if ($candidate === '') {
                continue;
            }
            foreach ($names as $name) {
                if (strtolower((string) $name) === $candidate) {
                    return (string) $name;
                }
            }
        }

        return '';
    }

    /**
     * `title_alias` -> `title`
     */
    private static function stripAliasSuffix(string $alias): string
    {
        return preg_replace('/_alias$/i', '', $alias) ?: $alias;
    }

    private static function toArray($row): array
    {
        if (is_array($row)) {
            return $row;
        }
        if ($row instanceof \Illuminate\Contracts\Support\Arrayable) {
            return $row->toArray();
        }
        if (is_object($row)) {
            return get_object_vars($row);
        }

        return [];
    }
}
