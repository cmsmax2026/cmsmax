<?php

namespace Modules\Formtools\Support;

class FormTemplateResolver
{
    public const DEFAULT_LIST_TEMPLATE = 'list';
    public const DEFAULT_DETAIL_TEMPLATE = 'detail';
    public const DEFAULT_HANDLE_TEMPLATE = 'handle';
    public const DEFAULT_LIST_PAGE_TEMPLATE = 'center';

    public static function normalizeHomeConfig(array $config = []): array
    {
        $customListTemplate = self::sanitizeTemplateName($config['custom_list_template'] ?? '');
        $customDetailTemplate = self::sanitizeTemplateName($config['custom_detail_template'] ?? '');
        $listTemplate = self::sanitizeTemplateName($config['list_template'] ?? '');
        $detailTemplate = self::sanitizeTemplateName($config['detail_template'] ?? '');

        $config['custom_list_template'] = $customListTemplate;
        $config['custom_detail_template'] = $customDetailTemplate;
        $config['list_template'] = $listTemplate ?: $customListTemplate ?: self::DEFAULT_LIST_TEMPLATE;
        $config['detail_template'] = $detailTemplate ?: $customDetailTemplate ?: self::DEFAULT_DETAIL_TEMPLATE;
        $config['page_num'] = max(0, (int) ($config['page_num'] ?? 20));

        $pageTemplate = strtolower(trim((string) ($config['list_page_template'] ?? self::DEFAULT_LIST_PAGE_TEMPLATE)));
        if (!in_array($pageTemplate, ['center', 'left', 'right'], true)) {
            $pageTemplate = self::DEFAULT_LIST_PAGE_TEMPLATE;
        }
        $config['list_page_template'] = $pageTemplate;
        $config['url_suffix'] = strtolower(trim((string) ($config['url_suffix'] ?? 'html'))) === 'none' ? 'none' : 'html';

        $frontDocMode = strtolower(trim((string) ($config['front_doc_mode'] ?? 'off')));
        if (!in_array($frontDocMode, ['off', 'on'], true)) {
            $frontDocMode = 'off';
        }
        $config['front_doc_mode'] = $frontDocMode;
        $config['front_doc_title'] = trim((string) ($config['front_doc_title'] ?? ''));
        $config['front_doc_desc'] = trim((string) ($config['front_doc_desc'] ?? ''));
        $frontDocPriceMode = strtolower(trim((string) ($config['front_doc_price_mode'] ?? 'inherit')));
        if (!in_array($frontDocPriceMode, ['inherit', 'free_only', 'paid_only'], true)) {
            $frontDocPriceMode = 'inherit';
        }
        $config['front_doc_price_mode'] = $frontDocPriceMode;

        return $config;
    }

    public static function normalizeModelData(array $model): array
    {
        $model['home_config'] = self::normalizeHomeConfig((array) ($model['home_config'] ?? []));
        $model['list_template'] = $model['home_config']['list_template'];
        $model['detail_template'] = $model['home_config']['detail_template'];
        $model['page_num'] = $model['home_config']['page_num'];
        $model['list_page_template'] = $model['home_config']['list_page_template'];
        return $model;
    }

    public static function resolveFrontendTemplate(string $theme, string $template, string $fallback = self::DEFAULT_LIST_TEMPLATE): array
    {
        $template = self::sanitizeTemplateName($template) ?: $fallback;
        $fallback = self::sanitizeTemplateName($fallback) ?: self::DEFAULT_LIST_TEMPLATE;

        // 场景子目录：list / detail / form。找不到时回退到扁平位置（兼容老主题平铺在 model/overwrite/ 下的文件）。
        $scene = \Modules\Formtools\Support\FormTemplateCatalog::sceneOf($template);

        if ($scene !== '') {
            if (self::themeTemplateExists($theme, $scene . '/' . $template)) {
                return [
                    'template' => "themes.{$theme}.model.overwrite.{$scene}.{$template}",
                    'name' => $template,
                    'source' => 'theme',
                ];
            }
        }

        if (self::themeTemplateExists($theme, $template)) {
            return [
                'template' => "themes.{$theme}.model.overwrite.{$template}",
                'name' => $template,
                'source' => 'theme',
            ];
        }

        if ($scene !== '') {
            if (self::builtinTemplateExists($scene . '/' . $template)) {
                return [
                    'template' => "model.{$scene}.{$template}",
                    'name' => $template,
                    'source' => 'builtin',
                ];
            }
        }

        if (self::builtinTemplateExists($template)) {
            return [
                'template' => "model.{$template}",
                'name' => $template,
                'source' => 'builtin',
            ];
        }

        if ($template !== $fallback) {
            return self::resolveFrontendTemplate($theme, $fallback, self::DEFAULT_LIST_TEMPLATE);
        }

        return [
            'template' => "model." . self::DEFAULT_LIST_TEMPLATE,
            'name' => self::DEFAULT_LIST_TEMPLATE,
            'source' => 'builtin',
        ];
    }

    public static function sanitizeTemplateName(?string $template): string
    {
        $template = trim((string) $template);
        if ($template === '') {
            return '';
        }
        $template = str_replace('\\', '/', $template);
        $template = preg_replace('/\.blade\.php$/i', '', $template);
        $template = preg_replace('/\.php$/i', '', $template);
        $template = trim($template, '/');
        if ($template === '' || strpos($template, '..') !== false) {
            return '';
        }
        if (!preg_match('/^[A-Za-z0-9_\/-]+$/', $template)) {
            return '';
        }
        return $template;
    }

    private static function themeTemplateExists(string $theme, string $template): bool
    {
        return file_exists(public_path('views/themes/' . $theme . '/model/overwrite/' . $template . '.blade.php'));
    }

    private static function builtinTemplateExists(string $template): bool
    {
        return file_exists(base_path('public/views/model/' . $template . '.blade.php'));
    }
}
