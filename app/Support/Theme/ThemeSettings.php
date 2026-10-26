<?php

namespace App\Support\Theme;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * 主题配置读写。
 *
 * 主题在 config.json 里用 settings.schema 声明可调项：
 *
 *   "settings": {
 *     "schema": [
 *       {"key":"primary_color","label":"主色","type":"color","default":"#2563eb"},
 *       {"key":"logo","label":"Logo","type":"image","default":""},
 *       {"key":"layout","label":"列表布局","type":"select","options":{"grid":"网格","list":"列表"},"default":"grid"}
 *     ]
 *   }
 *
 * 用户填的值存在 themes.form（JSON，按主题隔离），不会像旧的 diy 配置那样
 * 所有主题共用一个 Setting key 互相污染。
 */
class ThemeSettings
{
    /**
     * 读取主题声明的配置项定义。
     *
     * @return array<int, array<string, mixed>>
     */
    public static function schema(?string $theme = null): array
    {
        $manifest = ThemeManager::manifest($theme);
        $schema = $manifest['settings']['schema'] ?? [];

        if (!is_array($schema)) {
            return [];
        }

        $items = [];
        foreach ($schema as $item) {
            if (!is_array($item) || ($item['key'] ?? '') === '') {
                continue;
            }
            $items[] = [
                'key' => (string) $item['key'],
                'label' => (string) ($item['label'] ?? $item['key']),
                'type' => (string) ($item['type'] ?? 'text'),
                'default' => $item['default'] ?? '',
                'options' => (array) ($item['options'] ?? []),
                'description' => (string) ($item['description'] ?? ''),
                'group' => (string) ($item['group'] ?? '基本'),
            ];
        }

        // 插件可以补充配置项（监听 ThemeSettingSchema）
        $theme = $theme ?: ThemeManager::current();
        foreach ((array) hook('ThemeSettingSchema', ['theme' => $theme, 'schema' => $items]) as $extra) {
            if (is_array($extra)) {
                $items = array_merge($items, $extra);
            }
        }

        return $items;
    }

    /**
     * 取全部已保存的值（缺失的用 schema 默认值补齐）。
     */
    public static function values(?string $theme = null): array
    {
        $theme = $theme ?: ThemeManager::current();

        $saved = self::saved($theme);
        $values = self::defaults($theme);

        foreach ($saved as $key => $value) {
            $values[$key] = $value;
        }

        return $values;
    }

    /**
     * 取 schema 里定义的默认值。
     */
    public static function defaults(?string $theme = null): array
    {
        $defaults = [];
        foreach (self::schema($theme) as $item) {
            $defaults[$item['key']] = $item['default'];
        }

        return $defaults;
    }

    /**
     * 取单个配置值。
     */
    public static function get(string $key, $default = null, ?string $theme = null)
    {
        if ($key === '') {
            return $default;
        }

        $values = self::values($theme);
        if (array_key_exists($key, $values) && $values[$key] !== '' && $values[$key] !== null) {
            return $values[$key];
        }

        return $default;
    }

    /**
     * 批量保存（只更新传入的 key，未传的保持原样）。
     */
    public static function save(array $values, ?string $theme = null): bool
    {
        $theme = $theme ?: ThemeManager::current();

        $merged = array_merge(self::saved($theme), $values);
        // 清掉空数组/占位，保持 JSON 干净
        foreach ($merged as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                unset($merged[$key]);
            }
        }

        try {
            // 不能用 update() 的返回值判断成败：不同数据库对「值未变化」返回的
            // 受影响行数不一致（MySQL 可能返回 0），会把成功保存误判成失败。
            $exists = DB::table('themes')->where('identification', $theme)->exists();
            if (!$exists) {
                return false;
            }

            DB::table('themes')
                ->where('identification', $theme)
                ->update([
                    'form' => json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        } catch (\Throwable $exception) {
            return false;
        }

        Cache::forget(self::cacheKey($theme));

        return true;
    }

    /**
     * 输出 CSS 变量，供 head 里注入。
     *
     * schema 里 type=color / size / radius / font 的项会转成 `--mx-变量名`。
     */
    public static function cssVariables(?string $theme = null): array
    {
        $variables = [];
        foreach (self::schema($theme) as $item) {
            if (!in_array($item['type'], ['color', 'size', 'radius', 'font', 'shadow'], true)) {
                continue;
            }
            $value = self::get($item['key'], $item['default'], $theme);
            if ($value === '' || $value === null) {
                continue;
            }
            $variables['--mx-' . str_replace('_', '-', $item['key'])] = (string) $value;
        }

        foreach ((array) hook('ThemeCssVariables', ['theme' => $theme ?: ThemeManager::current(), 'variables' => $variables]) as $extra) {
            if (is_array($extra)) {
                $variables = array_merge($variables, $extra);
            }
        }

        return $variables;
    }

    /**
     * 把 CSS 变量渲染成 <style> 片段，主题 head 里 `{!! theme_css_variables() !!}` 即可。
     */
    public static function cssVariablesStyle(?string $theme = null): string
    {
        $variables = self::cssVariables($theme);
        if ($variables === []) {
            return '';
        }

        $body = '';
        foreach ($variables as $name => $value) {
            $body .= '  ' . $name . ': ' . $value . ';' . PHP_EOL;
        }

        return '<style id="mx-theme-variables">' . PHP_EOL . ':root {' . PHP_EOL . $body . '}' . PHP_EOL . '</style>';
    }

    /**
     * 原始保存值（不含默认值补齐）。
     */
    public static function saved(?string $theme = null): array
    {
        $theme = $theme ?: ThemeManager::current();

        return Cache::rememberForever(self::cacheKey($theme), static function () use ($theme) {
            try {
                $form = DB::table('themes')->where('identification', $theme)->value('form');
            } catch (\Throwable $exception) {
                return [];
            }

            if (!is_string($form) || $form === '') {
                return [];
            }

            $decoded = json_decode($form, true);

            return is_array($decoded) ? $decoded : [];
        });
    }

    public static function forgetCache(?string $theme = null): void
    {
        Cache::forget(self::cacheKey($theme ?: ThemeManager::current()));
    }

    private static function cacheKey(string $theme): string
    {
        return 'theme_settings.' . $theme;
    }
}
