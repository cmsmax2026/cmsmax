<?php

namespace Modules\Formtools\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Formtools\Models\FormModel;
use Modules\Formtools\Models\FormModelLayout;
use Modules\Formtools\Models\FormModelTemplateMap;
use Modules\Formtools\Models\FormTemplateRegistry;

class FormModelTemplateManager
{
    public static function getTemplatesByScene(string $scene): array
    {
        $templates = [];
        if (Schema::hasTable('module_formtools_template_registry')) {
            $templates = FormTemplateRegistry::query()
                ->where('scene', $scene)
                ->where('status', 1)
                ->orderBy('sort')
                ->orderBy('id')
                ->get()
                ->map(static function (FormTemplateRegistry $item) {
                    return [
                        'scene' => $item->scene,
                        'template_key' => $item->template_key,
                        'template_name' => $item->template_name,
                        'description' => $item->description ?: '',
                        'required_fields' => $item->required_fields ?: [],
                        'recommended_fields' => $item->recommended_fields ?: [],
                        'supported_model_type' => $item->supported_model_type ?: ['single', 'multi'],
                        'sort' => (int) $item->sort,
                    ];
                })
                ->toArray();
        }

        return $templates ?: FormTemplateCatalog::templatesByScene($scene);
    }

    public static function buildTemplatePanels(array $modelData): array
    {
        $fieldMeta = self::extractFieldMeta($modelData);
        $fieldMap = self::detectFieldMap($fieldMeta['names']);
        $currentMaps = self::resolveModelSceneMaps($modelData);
        $panels = [];

        foreach (FormTemplateCatalog::scenes() as $scene => $label) {
            $templates = [];
            foreach (self::getTemplatesByScene($scene) as $template) {
                $templates[] = self::buildTemplateMatch($template, $fieldMap, $fieldMeta['names'], (string) ($modelData['type'] ?? 'multi'));
            }

            $panels[$scene] = [
                'label' => $label,
                'templates' => $templates,
                'current' => $currentMaps[$scene] ?? self::defaultSceneMap($scene, $modelData),
            ];
        }

        return [
            'field_meta' => $fieldMeta,
            'field_map' => $fieldMap,
            'scene_meta' => FormTemplateCatalog::sceneMeta(),
            'model_meta' => [
                'access_identification' => trim((string) ($modelData['access_identification'] ?? '')),
                'show_home_page' => (string) ($modelData['show_home_page'] ?? 'no'),
            ],
            'panels' => $panels,
        ];
    }

    public static function resolveModelSceneMaps(array $modelData): array
    {
        $defaults = [
            FormTemplateCatalog::SCENE_LIST => self::defaultSceneMap(FormTemplateCatalog::SCENE_LIST, $modelData),
            FormTemplateCatalog::SCENE_DETAIL => self::defaultSceneMap(FormTemplateCatalog::SCENE_DETAIL, $modelData),
            FormTemplateCatalog::SCENE_HOME_SECTION => self::defaultSceneMap(FormTemplateCatalog::SCENE_HOME_SECTION, $modelData),
        ];

        $modelId = trim((string) ($modelData['id'] ?? ''));
        if ($modelId === '' || !Schema::hasTable('module_formtools_model_template_maps')) {
            return $defaults;
        }

        $records = FormModelTemplateMap::query()
            ->where('model_id', $modelId)
            ->get()
            ->keyBy('scene');

        foreach ($records as $scene => $record) {
            $defaults[$scene] = [
                'scene' => $scene,
                'mode' => $record->mode ?: $defaults[$scene]['mode'],
                'template_key' => $record->template_key ?: $defaults[$scene]['template_key'],
                'custom_template' => $record->custom_template ?: '',
                'field_map' => $record->field_map ?: [],
            ];
        }

        return $defaults;
    }

    public static function resolveSceneMapForFrontend(array $modelData, string $scene): array
    {
        $maps = self::resolveModelSceneMaps($modelData);
        return $maps[$scene] ?? self::defaultSceneMap($scene, $modelData);
    }

    public static function resolveSceneTemplate(array $modelData, string $scene): string
    {
        $map = self::resolveSceneMapForFrontend($modelData, $scene);
        if (($map['mode'] ?? 'preset') === 'custom') {
            $customTemplate = FormTemplateResolver::sanitizeTemplateName((string) ($map['custom_template'] ?? ''));
            if ($customTemplate !== '') {
                return $customTemplate;
            }
        }

        return FormTemplateResolver::sanitizeTemplateName((string) ($map['template_key'] ?? ''));
    }

    public static function resolveModelLayout(?string $modelId, string $scene): ?FormModelLayout
    {
        if ($modelId === null || $modelId === '' || !Schema::hasTable('module_formtools_model_layouts')) {
            return null;
        }

        return FormModelLayout::query()
            ->where('model_id', $modelId)
            ->where('scene', $scene)
            ->first();
    }

    public static function renderHomeSection($model): string
    {
        return self::renderSceneLayout($model, FormTemplateCatalog::SCENE_HOME_SECTION);
    }

    public static function renderSceneLayout($model, string $scene, array $context = []): string
    {
        $modelData = self::normalizeModelArray($model);
        if ($modelData === []) {
            return '';
        }

        $sceneMap = self::resolveSceneMapForFrontend($modelData, $scene);
        if (($sceneMap['mode'] ?? 'preset') !== 'visual') {
            return '';
        }

        $layout = self::resolveModelLayout(trim((string) ($modelData['id'] ?? '')), $scene);
        if (!$layout || trim((string) $layout->layout_schema) === '') {
            return '';
        }

        return PageSchemaRenderer::render($layout->layout_schema, array_merge([
            'page_model_identification' => trim((string) ($modelData['access_identification'] ?? '')),
            'query' => request()->query(),
            'device' => self::detectDevice(),
            'scene' => $scene,
        ], $context));
    }

    public static function syncModelTemplateMaps(FormModel $model, array $payload): void
    {
        if (!Schema::hasTable('module_formtools_model_template_maps')) {
            return;
        }

        $fieldMeta = self::extractFieldMeta($payload);
        $fieldMap = self::detectFieldMap($fieldMeta['names']);
        $templateMaps = isset($payload['template_maps']) && is_array($payload['template_maps']) ? $payload['template_maps'] : [];

        foreach (FormTemplateCatalog::scenes() as $scene => $label) {
            $scenePayload = isset($templateMaps[$scene]) && is_array($templateMaps[$scene]) ? $templateMaps[$scene] : [];
            $sceneMap = self::defaultSceneMap($scene, $payload);
            $sceneMap['mode'] = self::normalizeSceneMode($scene, (string) ($scenePayload['mode'] ?? $sceneMap['mode']));
            $sceneMap['template_key'] = FormTemplateResolver::sanitizeTemplateName((string) ($scenePayload['template_key'] ?? $sceneMap['template_key']));
            $sceneMap['custom_template'] = FormTemplateResolver::sanitizeTemplateName((string) ($scenePayload['custom_template'] ?? ''));

            $matchSummary = self::buildSceneMatchSummary($scene, $sceneMap['template_key'], $fieldMap, $fieldMeta['names'], (string) ($payload['type'] ?? 'multi'));

            FormModelTemplateMap::query()->updateOrCreate([
                'model_id' => (string) $model->id,
                'scene' => $scene,
            ], [
                'mode' => $sceneMap['mode'],
                'template_key' => $sceneMap['template_key'],
                'custom_template' => $sceneMap['custom_template'],
                'field_map' => $fieldMap,
                'match_summary' => $matchSummary,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public static function ensureHomeSectionLayout(FormModel $model, array $modelData): void
    {
        self::ensureSceneLayouts($model, $modelData);
    }

    public static function ensureSceneLayouts(FormModel $model, array $modelData): void
    {
        if (!Schema::hasTable('module_formtools_model_layouts')) {
            return;
        }

        foreach (array_keys(FormTemplateCatalog::scenes()) as $scene) {
            FormModelLayout::query()->firstOrCreate([
                'model_id' => (string) $model->id,
                'scene' => $scene,
            ], [
                'status' => 1,
                'layout_schema' => FormTemplateCatalog::defaultSceneSchema(
                    $scene,
                    (string) ($modelData['access_identification'] ?? $model->access_identification),
                    (string) data_get($modelData, 'home_config.home_page_title', ''),
                    (string) data_get($modelData, 'home_config.home_page_describe', '')
                ),
                'theme_tokens' => [],
                'version' => 1,
                'published_at' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public static function syncHomeConfigWithTemplateMaps(array $payload): array
    {
        $homeConfig = FormTemplateResolver::normalizeHomeConfig((array) ($payload['home_config'] ?? []));
        $templateMaps = isset($payload['template_maps']) && is_array($payload['template_maps']) ? $payload['template_maps'] : [];

        $listScene = isset($templateMaps[FormTemplateCatalog::SCENE_LIST]) && is_array($templateMaps[FormTemplateCatalog::SCENE_LIST])
            ? $templateMaps[FormTemplateCatalog::SCENE_LIST]
            : [];
        $detailScene = isset($templateMaps[FormTemplateCatalog::SCENE_DETAIL]) && is_array($templateMaps[FormTemplateCatalog::SCENE_DETAIL])
            ? $templateMaps[FormTemplateCatalog::SCENE_DETAIL]
            : [];

        $listMode = self::normalizeSceneMode(FormTemplateCatalog::SCENE_LIST, (string) ($listScene['mode'] ?? 'preset'));
        $detailMode = self::normalizeSceneMode(FormTemplateCatalog::SCENE_DETAIL, (string) ($detailScene['mode'] ?? 'preset'));
        $listTemplateKey = FormTemplateResolver::sanitizeTemplateName((string) ($listScene['template_key'] ?? $homeConfig['list_template']));
        $detailTemplateKey = FormTemplateResolver::sanitizeTemplateName((string) ($detailScene['template_key'] ?? $homeConfig['detail_template']));
        $listCustom = FormTemplateResolver::sanitizeTemplateName((string) ($listScene['custom_template'] ?? ''));
        $detailCustom = FormTemplateResolver::sanitizeTemplateName((string) ($detailScene['custom_template'] ?? ''));

        $homeConfig['custom_list_template'] = $listMode === 'custom' ? $listCustom : '';
        $homeConfig['custom_detail_template'] = $detailMode === 'custom' ? $detailCustom : '';
        $homeConfig['list_template'] = $listMode === 'custom'
            ? ($listCustom ?: ($listTemplateKey ?: FormTemplateResolver::DEFAULT_LIST_TEMPLATE))
            : ($listTemplateKey ?: FormTemplateResolver::DEFAULT_LIST_TEMPLATE);
        $homeConfig['detail_template'] = $detailMode === 'custom'
            ? ($detailCustom ?: ($detailTemplateKey ?: FormTemplateResolver::DEFAULT_DETAIL_TEMPLATE))
            : ($detailTemplateKey ?: FormTemplateResolver::DEFAULT_DETAIL_TEMPLATE);

        $payload['home_config'] = FormTemplateResolver::normalizeHomeConfig($homeConfig);
        return $payload;
    }

    public static function buildTemplateGuideHtml(array $templateState): string
    {
        $fieldMeta = $templateState['field_meta'] ?? ['labels' => [], 'names' => []];
        $sceneMeta = $templateState['scene_meta'] ?? [];
        $modelMeta = $templateState['model_meta'] ?? [];
        $accessIdentification = trim((string) ($modelMeta['access_identification'] ?? ''));
        $showHomePage = (string) ($modelMeta['show_home_page'] ?? 'no');
        $chips = [];
        foreach (($fieldMeta['labels'] ?? []) as $label) {
            $chips[] = '<span style="display:inline-flex;align-items:center;height:26px;padding:0 10px;border-radius:999px;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;font-size:12px;margin:0 8px 8px 0;">' . e($label) . '</span>';
        }

        $cards = [];
        foreach (($templateState['panels'] ?? []) as $scene => $panel) {
            $meta = $sceneMeta[$scene] ?? [];
            $route = trim((string) ($meta['route'] ?? ''));
            $summary = trim((string) ($meta['summary'] ?? ''));
            $templateScope = trim((string) ($meta['template_scope'] ?? ''));
            $current = $panel['current'] ?? [];
            $currentMode = trim((string) ($current['mode'] ?? 'preset'));
            $currentTemplate = trim((string) ($current['template_key'] ?? ''));
            $currentCustomTemplate = trim((string) ($current['custom_template'] ?? ''));
            $currentRender = self::describeSceneCurrentSelection($scene, $currentMode, $currentTemplate, $currentCustomTemplate);
            $effectText = self::describeSceneEffect($scene, $accessIdentification, $showHomePage);
            $items = [];
            foreach (($panel['templates'] ?? []) as $template) {
                $required = implode(' / ', $template['required_fields'] ?? []);
                $recommended = implode(' / ', $template['recommended_fields'] ?? []);
                $renderSummary = trim((string) ($template['render_summary'] ?? ''));
                $statusColor = match ($template['match_level'] ?? 'warn') {
                    'perfect' => '#16a34a',
                    'basic' => '#2563eb',
                    default => '#f59e0b',
                };
                $items[] = '<div style="padding:14px;border:1px solid #e5e7eb;border-radius:16px;background:#fff;">'
                    . '<div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;">'
                    . '<div><div style="font-weight:700;color:#0f172a;">' . e($template['template_name']) . '</div>'
                    . '<div style="margin-top:6px;color:#64748b;line-height:1.7;">' . e($template['description']) . '</div></div>'
                    . '<span style="display:inline-flex;min-width:70px;justify-content:center;padding:4px 10px;border-radius:999px;background:' . $statusColor . '12;border:1px solid ' . $statusColor . '33;color:' . $statusColor . ';font-size:12px;">' . e($template['match_label']) . '</span>'
                    . '</div>'
                    . '<div style="margin-top:10px;color:#475569;font-size:12px;line-height:1.8;">'
                    . '<div><strong>必需：</strong>' . e($required !== '' ? $required : '无') . '</div>'
                    . '<div><strong>推荐：</strong>' . e($recommended !== '' ? $recommended : '无') . '</div>'
                    . '<div><strong>命中：</strong>' . e(implode('，', $template['matched_aliases'] ?? []) ?: '无') . '</div>'
                    . '<div><strong>渲染：</strong>' . e($renderSummary !== '' ? $renderSummary : '未识别') . '</div>'
                    . '</div>'
                    . '</div>';
            }

            $cards[] = '<div style="padding:18px;border:1px solid #dbe2ea;border-radius:18px;background:#f8fafc;margin-bottom:16px;">'
                . '<div style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:8px;">' . e($panel['label'] ?? $scene) . '</div>'
                . '<div style="color:#334155;line-height:1.8;font-size:13px;margin-bottom:10px;">'
                . '<div><strong>对应页面：</strong>' . e($route !== '' ? $route : '-') . '</div>'
                . '<div><strong>页面作用：</strong>' . e($summary !== '' ? $summary : '-') . '</div>'
                . '<div><strong>主题链路：</strong>' . e($templateScope !== '' ? $templateScope : '-') . '</div>'
                . '<div><strong>当前生效：</strong>' . e($currentRender) . '</div>'
                . '<div><strong>当前联动：</strong>' . e($effectText) . '</div>'
                . '</div>'
                . implode('', $items)
                . '</div>';
        }

        return '<div style="line-height:1.8;">'
            . '<div style="margin-bottom:10px;color:#475569;">当前模型已识别字段：</div>'
            . '<div style="margin-bottom:14px;">' . ($chips ? implode('', $chips) : '<span style="color:#94a3b8;">还没有自定义字段，先去字段管理补齐。</span>') . '</div>'
            . implode('', $cards)
            . '</div>';
    }

    public static function describeScenePresetTemplate(string $scene, string $templateKey): string
    {
        return self::describePresetTemplateRender($scene, $templateKey);
    }

    private static function buildSceneMatchSummary(string $scene, string $templateKey, array $fieldMap, array $fieldNames, string $modelType): array
    {
        $template = null;
        foreach (self::getTemplatesByScene($scene) as $definition) {
            if (($definition['template_key'] ?? '') === $templateKey) {
                $template = $definition;
                break;
            }
        }

        if (!$template) {
            return [];
        }

        return self::buildTemplateMatch($template, $fieldMap, $fieldNames, $modelType);
    }

    private static function buildTemplateMatch(array $template, array $fieldMap, array $fieldNames, string $modelType): array
    {
        $scene = (string) ($template['scene'] ?? '');
        $templateKey = (string) ($template['template_key'] ?? '');
        $required = (array) ($template['required_fields'] ?? []);
        $recommended = (array) ($template['recommended_fields'] ?? []);
        $supportedModelType = (array) ($template['supported_model_type'] ?? ['single', 'multi']);
        $matchedAliases = [];
        $missingRequired = [];
        $missingRecommended = [];

        foreach ($required as $alias) {
            if (!empty($fieldMap[$alias])) {
                $matchedAliases[] = $alias . ' -> ' . $fieldMap[$alias];
            } else {
                $missingRequired[] = $alias;
            }
        }
        foreach ($recommended as $alias) {
            if (!empty($fieldMap[$alias])) {
                $matchedAliases[] = $alias . ' -> ' . $fieldMap[$alias];
            } else {
                $missingRecommended[] = $alias;
            }
        }

        $matchLevel = 'warn';
        $matchLabel = '有缺口';
        if ($missingRequired === [] && count($recommended) > 0 && count($missingRecommended) === 0) {
            $matchLevel = 'perfect';
            $matchLabel = '完全匹配';
        } elseif ($missingRequired === []) {
            $matchLevel = 'basic';
            $matchLabel = '基本匹配';
        }

        if (!in_array($modelType, $supportedModelType, true)) {
            $matchLevel = 'warn';
            $matchLabel = '类型不匹配';
        }

        $template['missing_required'] = $missingRequired;
        $template['missing_recommended'] = $missingRecommended;
        $template['matched_aliases'] = array_values(array_unique($matchedAliases));
        $template['match_level'] = $matchLevel;
        $template['match_label'] = $matchLabel;
        $template['field_names'] = $fieldNames;
        $template['render_summary'] = self::describePresetTemplateRender($scene, $templateKey);

        return $template;
    }

    private static function extractFieldMeta(array $modelData): array
    {
        $fields = $modelData['fields'] ?? [];
        if (is_string($fields)) {
            $fields = json_decode($fields, true) ?: [];
        }
        if (!is_array($fields)) {
            $fields = [];
        }

        $names = [];
        $labels = [];
        foreach ($fields as $field) {
            $identification = trim((string) ($field['identification'] ?? ''));
            if ($identification === '') {
                continue;
            }
            $names[] = $identification;
            $labels[] = trim((string) ($field['name'] ?? $identification)) . ' [' . $identification . ']';
        }

        return [
            'names' => array_values(array_unique($names)),
            'labels' => $labels,
        ];
    }

    private static function detectFieldMap(array $fieldNames): array
    {
        $fieldNames = array_values(array_unique(array_filter(array_map(static function ($item) {
            return trim((string) $item);
        }, $fieldNames))));

        $map = [];
        foreach (FormTemplateCatalog::fieldAliases() as $alias => $candidates) {
            foreach ($candidates as $candidate) {
                if (in_array($candidate, $fieldNames, true)) {
                    $map[$alias] = $candidate;
                    break;
                }
            }
        }

        return $map;
    }

    private static function defaultSceneMap(string $scene, array $modelData): array
    {
        $homeConfig = FormTemplateResolver::normalizeHomeConfig((array) ($modelData['home_config'] ?? []));
        $templateKey = match ($scene) {
            FormTemplateCatalog::SCENE_DETAIL => $homeConfig['detail_template'] ?? FormTemplateResolver::DEFAULT_DETAIL_TEMPLATE,
            FormTemplateCatalog::SCENE_HOME_SECTION => $homeConfig['list_template'] ?? FormTemplateResolver::DEFAULT_LIST_TEMPLATE,
            default => $homeConfig['list_template'] ?? FormTemplateResolver::DEFAULT_LIST_TEMPLATE,
        };

        return [
            'scene' => $scene,
            'mode' => $scene === FormTemplateCatalog::SCENE_HOME_SECTION ? 'preset' : 'preset',
            'template_key' => FormTemplateResolver::sanitizeTemplateName((string) $templateKey),
            'custom_template' => $scene === FormTemplateCatalog::SCENE_DETAIL
                ? FormTemplateResolver::sanitizeTemplateName((string) ($homeConfig['custom_detail_template'] ?? ''))
                : FormTemplateResolver::sanitizeTemplateName((string) ($homeConfig['custom_list_template'] ?? '')),
            'field_map' => [],
        ];
    }

    private static function normalizeSceneMode(string $scene, string $mode): string
    {
        $mode = trim($mode);
        $allowed = ['preset', 'visual', 'custom'];

        return in_array($mode, $allowed, true) ? $mode : 'preset';
    }

    private static function normalizeModelArray($model): array
    {
        if ($model instanceof FormModel) {
            $model = $model->toArray();
        } elseif (is_object($model) && method_exists($model, 'toArray')) {
            $model = $model->toArray();
        } elseif (is_object($model)) {
            $model = get_object_vars($model);
        }

        if (!is_array($model)) {
            return [];
        }

        foreach (['home_config', 'other_config', 'home_seo_config', 'home_seo_detail_config', 'admin_config'] as $field) {
            if (isset($model[$field]) && is_string($model[$field])) {
                $model[$field] = json_decode($model[$field], true) ?: [];
            }
        }

        return $model;
    }

    private static function detectDevice(): string
    {
        $userAgent = strtolower((string) request()->userAgent());
        if (str_contains($userAgent, 'ipad') || str_contains($userAgent, 'tablet')) {
            return 'tablet';
        }
        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android') || str_contains($userAgent, 'iphone')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private static function describeSceneEffect(string $scene, string $accessIdentification, string $showHomePage): string
    {
        return match ($scene) {
            FormTemplateCatalog::SCENE_LIST => '控制列表页样式；访问 ' . ($accessIdentification !== '' ? ('/list/' . $accessIdentification) : '/list/{access}') . ' 时生效，并继续参与分页。',
            FormTemplateCatalog::SCENE_DETAIL => '控制详情页样式；访问 ' . ($accessIdentification !== '' ? ('/detail/' . $accessIdentification . '/{id}') : '/detail/{access}/{id}') . ' 时生效，只渲染单条内容。',
            FormTemplateCatalog::SCENE_HOME_SECTION => $showHomePage === 'yes'
                ? '控制首页 / 里的该模型聚合区块；当前模型已开启首页展示，会直接汇总到首页。'
                : '控制首页 / 里的该模型聚合区块；当前模型还没开启首页展示，开启后才会汇总到首页。',
            default => '当前场景说明暂未配置。',
        };
    }

    private static function describeSceneCurrentSelection(string $scene, string $mode, string $templateKey, string $customTemplate): string
    {
        if ($mode === 'visual') {
            return match ($scene) {
                FormTemplateCatalog::SCENE_LIST => '当前为可视化布局模式，列表页直接渲染 layout_schema，并继续承接当前分页结果。',
                FormTemplateCatalog::SCENE_DETAIL => '当前为可视化布局模式，详情页直接渲染 layout_schema，并继续承接当前详情数据。',
                default => '当前为可视化布局模式，首页区块直接渲染 layout_schema，不再读取主题预设模板。',
            };
        }

        if ($mode === 'custom') {
            return $customTemplate !== ''
                ? '当前为自定义模板模式，读取自定义模板：' . $customTemplate
                : '当前为自定义模板模式，但还没有填写模板文件名。';
        }

        if ($templateKey === '') {
            return '当前还没有选定预设模板。';
        }

        return self::describePresetTemplateRender($scene, $templateKey);
    }

    private static function describePresetTemplateRender(string $scene, string $templateKey): string
    {
        $templateKey = trim($templateKey);
        if ($templateKey === '') {
            return '';
        }

        $activeTheme = self::activeTheme();
        if (in_array($scene, [FormTemplateCatalog::SCENE_LIST, FormTemplateCatalog::SCENE_DETAIL], true)) {
            if (self::themeModelTemplateExists($activeTheme, $templateKey)) {
                return '当前主题 `' . $activeTheme . '` 已覆盖 `model/overwrite/' . $templateKey . '.blade.php`。';
            }
            if (self::builtinModelTemplateExists($templateKey)) {
                return '当前主题未覆盖，前台会回退到系统通用模板 `public/views/model/' . $templateKey . '.blade.php`。';
            }

            return '当前主题和系统通用模板都未找到 `' . $templateKey . '`。';
        }

        if (self::themeHomeSectionTemplateExists($activeTheme, $templateKey)) {
            return '首页区块会走当前主题 `' . $activeTheme . '` 的 `index/templates/' . $templateKey . '.blade.php`。';
        }
        if ($activeTheme !== 'default' && self::themeHomeSectionTemplateExists('default', $templateKey)) {
            return '当前主题未提供该首页区块模板，前台会回退到 `default` 主题的 `index/templates/' . $templateKey . '.blade.php`。';
        }

        return '当前主题和 default 都没有首页区块模板 `' . $templateKey . '`。';
    }

    private static function activeTheme(): string
    {
        if (cache()->has('theme')) {
            return (string) cache()->get('theme');
        }

        if (Schema::hasTable('themes')) {
            $theme = (string) DB::table('themes')->where('status', '1')->value('identification');
            if ($theme !== '') {
                return $theme;
            }
        }

        return 'default';
    }

    private static function themeModelTemplateExists(string $theme, string $template): bool
    {
        return file_exists(public_path('views/themes/' . $theme . '/model/overwrite/' . $template . '.blade.php'));
    }

    private static function builtinModelTemplateExists(string $template): bool
    {
        return file_exists(base_path('public/views/model/' . $template . '.blade.php'));
    }

    private static function themeHomeSectionTemplateExists(string $theme, string $template): bool
    {
        return file_exists(public_path('views/themes/' . $theme . '/index/templates/' . $template . '.blade.php'));
    }
}
