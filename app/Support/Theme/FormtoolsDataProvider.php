<?php

namespace App\Support\Theme;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Formtools 数据提供者 —— 主题调用 Formtools 数据的标准通道。
 *
 * 主题不应该直接写 SQL，也不应该依赖 Formtools 的内部类（模块可能未安装），
 * 统一通过 theme_data('formtools.list', [...]) 取数。
 *
 * 约定参数：
 *   model    模型标识（access_identification，也接受 identification）
 *   limit    条数，0 表示不限制
 *   page     页码，配合 limit 使用
 *   order    排序，如 `id desc`
 *   where    条件，支持 ['status'=>1] 或 [['price','>',0]]
 *   pid      按父级过滤（树形模型）
 *   keyword  关键词，配合 search 字段
 *   search   参与搜索的字段名数组
 *   fields   只返回这些字段
 *   cache    缓存秒数（覆盖 config theme.data_cache_ttl）：>=0 生效，0 表示不缓存；
 *            不传则使用全局配置（默认 0 = 关闭）
 *
 * 提供的 key：
 *   formtools.list     列表（支持分页）
 *   formtools.detail   单条
 *   formtools.tree     树形结构
 *   formtools.count    计数
 *   formtools.chart    图表聚合（分组统计）
 *   formtools.fields   模型字段定义
 *   formtools.models   可用于前台的模型列表
 *   formtools.map      字段别名映射
 *
 * 性能：所有查询都经过 run() —— ① 同一请求内相同 (方法+参数) 只查一次库；
 * ② 跨请求结果按 config('theme.data_cache_ttl') 或短标签 cache 属性缓存；
 * ③ 内容变更时调 bump($access) 让该模型缓存立即失效。
 */
class FormtoolsDataProvider
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $modelCache = [];

    /**
     * 请求内查询缓存：[method][key] => 结果（含 null）。
     * @var array<string, array<string, mixed>>
     */
    private static array $queryCache = [];

    /**
     * 请求内版本号缓存：[access] => 版本戳。
     * @var array<string, string>
     */
    private static array $versionCache = [];

    /**
     * 未命中哨兵（与合法返回值 null 区分）。
     */
    private const MISS = "\0__MISS__\0";

    /**
     * 跨请求缓存值的包装键（用于区分「缓存了 null」与「未命中」）。
     */
    private const CACHE_TAG = 'v';

    /**
     * @return array<string, callable>
     */
    public static function providers(): array
    {
        return [
            'formtools.list' => [self::class, 'list'],
            'formtools.detail' => [self::class, 'detail'],
            'formtools.tree' => [self::class, 'tree'],
            'formtools.count' => [self::class, 'count'],
            'formtools.chart' => [self::class, 'chart'],
            'formtools.fields' => [self::class, 'fields'],
            'formtools.models' => [self::class, 'models'],
            'formtools.map' => [self::class, 'map'],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* 缓存层（请求内去重 + 跨请求 TTL + 版本号失效）                       */
    /* ------------------------------------------------------------------ */

    /**
     * 统一入口：查缓存 → 命中返回；未命中执行 $real() 并写回。
     */
    private static function run(string $method, array $params, callable $real)
    {
        $hit = self::lookup($method, $params);
        if ($hit !== self::MISS) {
            return $hit;
        }

        return self::store($method, $params, $real());
    }

    /**
     * 查找缓存：先请求内，再跨请求。返回 MISS 表示未命中。
     * @return mixed
     */
    private static function lookup(string $method, array $params)
    {
        $key = self::cacheKey($method, $params);

        if (isset(self::$queryCache[$method]) && array_key_exists($key, self::$queryCache[$method])) {
            return self::$queryCache[$method][$key];
        }

        $ttl = self::ttlFor($params);
        if ($ttl > 0) {
            $boxed = Cache::get($key);
            if (is_array($boxed) && array_key_exists(self::CACHE_TAG, $boxed)) {
                self::$queryCache[$method][$key] = $boxed[self::CACHE_TAG];

                return $boxed[self::CACHE_TAG];
            }
        }

        return self::MISS;
    }

    /**
     * 写回缓存：请求内必写，跨请求按 TTL 写。
     * @param mixed $value
     * @return mixed
     */
    private static function store(string $method, array $params, $value)
    {
        $key = self::cacheKey($method, $params);
        self::$queryCache[$method][$key] = $value;

        $ttl = self::ttlFor($params);
        if ($ttl > 0) {
            Cache::put($key, [self::CACHE_TAG => $value], $ttl);
        }

        return $value;
    }

    /**
     * 缓存 key：含模型版本号，使内容变更后可整体失活。
     */
    private static function cacheKey(string $method, array $params): string
    {
        $access = trim((string) ($params['model'] ?? ''));

        return 'theme:data:' . self::version($access) . ':' . $method . ':'
            . md5(json_encode($params, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 跨请求缓存 TTL：短标签 cache 参数优先（>=0 生效），否则取全局配置。
     */
    private static function ttlFor(array $params): int
    {
        $explicit = $params['cache'] ?? null;
        if (is_numeric($explicit) && (int) $explicit >= 0) {
            return (int) $explicit;
        }

        return (int) Config::get('theme.data_cache_ttl', 0);
    }

    /**
     * 模型版本号：每次 bump 后变化，使旧缓存 key 自然失活。
     */
    private static function version(string $access): string
    {
        if ($access === '') {
            return '0';
        }
        if (array_key_exists($access, self::$versionCache)) {
            return self::$versionCache[$access];
        }

        $k = 'theme:data:version:' . $access;
        $v = Cache::get($k);
        if ($v === null) {
            $v = (string) time();
            Cache::forever($k, $v);
        }

        return self::$versionCache[$access] = (string) $v;
    }

    /**
     * 内容变更后调用：让该模型的全部数据查询缓存立即失效。
     * 例：Formtools 保存/删除某模型数据时 → FormtoolsDataProvider::bump($access)。
     */
    public static function bump(string $access): void
    {
        $access = trim($access);
        unset(self::$versionCache[$access]);
        if ($access !== '') {
            Cache::forget('theme:data:version:' . $access);
        }
        self::$queryCache = [];
    }

    /**
     * 清空本请求内全部查询缓存（跨请求缓存靠 TTL 自然过期）。
     */
    public static function clearRequestCache(): void
    {
        self::$queryCache = [];
        self::$versionCache = [];
    }

    /* ------------------------------------------------------------------ */
    /* 公开方法（包装 run，逻辑在 *Impl 中）                               */
    /* ------------------------------------------------------------------ */

    public static function list(array $params = []): array
    {
        return self::run('list', $params, static fn () => self::listImpl($params));
    }

    public static function detail(array $params = []): ?array
    {
        return self::run('detail', $params, static fn () => self::detailImpl($params));
    }

    public static function tree(array $params = []): array
    {
        return self::run('tree', $params, static fn () => self::treeImpl($params));
    }

    public static function count(array $params = []): int
    {
        return self::run('count', $params, static fn () => self::countImpl($params));
    }

    public static function chart(array $params = []): array
    {
        return self::run('chart', $params, static fn () => self::chartImpl($params));
    }

    public static function fields(array $params = []): array
    {
        return self::run('fields', $params, static fn () => self::fieldsImpl($params));
    }

    public static function models(array $params = []): array
    {
        return self::run('models', $params, static fn () => self::modelsImpl($params));
    }

    public static function map(array $params = []): array
    {
        return self::run('map', $params, static fn () => self::mapImpl($params));
    }

    /* ------------------------------------------------------------------ */
    /* 内部实现                                                            */
    /* ------------------------------------------------------------------ */

    private static function listImpl(array $params = []): array
    {
        $model = self::model($params['model'] ?? '');
        if ($model === null) {
            return self::emptyPage();
        }

        $table = self::table($model);
        if ($table === '') {
            return self::emptyPage();
        }

        $query = DB::table($table);
        self::applyStatus($query, $table);
        self::applyWhere($query, $table, $params);
        self::applyPid($query, $table, $params);
        self::applyKeyword($query, $table, $params);
        self::applyOrder($query, $table, $params);

        $limit = max(0, (int) ($params['limit'] ?? 10));
        $page = max(1, (int) ($params['page'] ?? 1));
        $offset = max(0, (int) ($params['offset'] ?? 0));
        $paginate = (bool) ($params['paginate'] ?? ($limit > 0));

        if ($paginate && $limit > 0 && $offset === 0) {
            $paginator = $query->paginate($limit, self::selectColumns($table, $params), 'page', $page);
            $items = $paginator->items();

            return [
                'items' => $items,
                'rows' => self::normalizeRows($items, $model),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'paginator' => $paginator,
                'map' => self::mapOf($model),
                'model' => $model['identification'] ?? '',
                'access' => $model['access_identification'] ?? '',
            ];
        }

        if ($offset > 0) {
            $query->skip($offset);
        }

        if ($limit > 0) {
            $query->limit($limit);
            if ($page > 1) {
                $query->offset(($page - 1) * $limit);
            }
        }

        $items = $query->get()->all();

        return [
            'items' => $items,
            'rows' => self::normalizeRows($items, $model),
            'total' => count($items),
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => 1,
            'paginator' => null,
            'map' => self::mapOf($model),
            'model' => $model['identification'] ?? '',
            'access' => $model['access_identification'] ?? '',
        ];
    }

    private static function detailImpl(array $params = []): ?array
    {
        $model = self::model($params['model'] ?? '');
        if ($model === null) {
            return null;
        }

        $table = self::table($model);
        if ($table === '') {
            return null;
        }

        $query = DB::table($table);
        self::applyStatus($query, $table);

        $id = (int) ($params['id'] ?? 0);
        if ($id > 0) {
            $query->where('id', $id);
        } else {
            self::applyWhere($query, $table, $params);
        }

        $row = $query->first();
        if ($row === null) {
            return null;
        }

        $map = self::mapOf($model);

        return array_merge(ThemeFieldMapper::normalize($row, $map, (string) ($model['access_identification'] ?? '')), [
            'prev' => self::neighbour($table, $model, (int) $row->id, 'prev'),
            'next' => self::neighbour($table, $model, (int) $row->id, 'next'),
        ]);
    }

    private static function treeImpl(array $params = []): array
    {
        $model = self::model($params['model'] ?? '');
        if ($model === null) {
            return [];
        }

        $table = self::table($model);
        if ($table === '' || !Schema::hasColumn($table, 'pid')) {
            return [];
        }

        $query = DB::table($table);
        self::applyStatus($query, $table);
        self::applyOrder($query, $table, $params);
        $rows = $query->get()->all();

        $map = self::mapOf($model);
        $access = (string) ($model['access_identification'] ?? '');

        $items = [];
        foreach ($rows as $row) {
            $row = (array) $row;
            $items[] = array_merge(ThemeFieldMapper::normalize($row, $map, $access), [
                'pid' => (int) ($row['pid'] ?? 0),
            ]);
        }

        return self::buildTree($items, (int) ($params['pid'] ?? 0));
    }

    private static function countImpl(array $params = []): int
    {
        $model = self::model($params['model'] ?? '');
        if ($model === null) {
            return 0;
        }

        $table = self::table($model);
        if ($table === '') {
            return 0;
        }

        $query = DB::table($table);
        self::applyStatus($query, $table);
        self::applyWhere($query, $table, $params);
        self::applyPid($query, $table, $params);
        self::applyKeyword($query, $table, $params);

        return (int) $query->count();
    }

    private static function chartImpl(array $params = []): array
    {
        $model = self::model($params['model'] ?? '');
        if ($model === null) {
            return ['labels' => [], 'values' => [], 'items' => []];
        }

        $table = self::table($model);
        if ($table === '') {
            return ['labels' => [], 'values' => [], 'items' => []];
        }

        $map = self::mapOf($model);
        $fields = (array) ($model['fields'] ?? []);

        $groupField = (string) ($params['group'] ?? '');
        if ($groupField === '') {
            return ['labels' => [], 'values' => [], 'items' => []];
        }
        // 允许传别名
        $groupField = $map[$groupField] ?? $groupField;
        if (!Schema::hasColumn($table, $groupField)) {
            return ['labels' => [], 'values' => [], 'items' => []];
        }

        $type = strtolower((string) ($params['type'] ?? 'count'));
        if (!in_array($type, ['count', 'sum', 'avg', 'max', 'min'], true)) {
            $type = 'count';
        }

        $valueField = (string) ($params['value'] ?? '');
        if ($valueField !== '') {
            $valueField = $map[$valueField] ?? $valueField;
        }
        if ($type !== 'count' && ($valueField === '' || !Schema::hasColumn($table, $valueField))) {
            $type = 'count';
        }

        $query = DB::table($table);
        self::applyStatus($query, $table);
        self::applyWhere($query, $table, $params);

        $aggregate = $type === 'count'
            ? DB::raw('COUNT(*) as agg_value')
            : DB::raw(strtoupper($type) . '(' . self::quoteColumn($valueField) . ') as agg_value');

        $query->select($groupField . ' as label', $aggregate)
            ->groupBy($groupField)
            ->orderBy('agg_value', strtolower((string) ($params['order'] ?? 'desc')) === 'asc' ? 'asc' : 'desc');

        $limit = max(0, (int) ($params['limit'] ?? 0));
        if ($limit > 0) {
            $query->limit($limit);
        }

        $rows = $query->get()->all();

        $labels = [];
        $values = [];
        $items = [];
        foreach ($rows as $row) {
            $row = (array) $row;
            $label = (string) ($row['label'] ?? '');
            $value = is_numeric($row['agg_value'] ?? null) ? (float) $row['agg_value'] : 0;
            $labels[] = $label;
            $values[] = $value;
            $items[] = [
                'label' => $label,
                'label_text' => $label !== '' ? $label : '未分组',
                'value' => $value,
                'field_label' => ThemeFieldMapper::label($fields, $groupField, $groupField),
            ];
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'items' => $items,
            'group' => $groupField,
            'type' => $type,
        ];
    }

    private static function fieldsImpl(array $params = []): array
    {
        $model = self::model($params['model'] ?? '');
        if ($model === null) {
            return [];
        }

        return (array) ($model['fields'] ?? []);
    }

    private static function modelsImpl(array $params = []): array
    {
        if (!self::modelsTableReady()) {
            return [];
        }

        $query = DB::table('module_formtools_models');
        if (!empty($params['home_only'])) {
            $query->where('show_home_page', 'yes');
        }

        $rows = $query->get()->all();
        $items = [];
        foreach ($rows as $row) {
            $row = (array) $row;
            $items[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => (string) ($row['name'] ?? ''),
                'identification' => (string) ($row['identification'] ?? ''),
                'access' => (string) ($row['access_identification'] ?? ($row['identification'] ?? '')),
                'type' => (string) ($row['type'] ?? 'multi'),
            ];
        }

        return $items;
    }

    private static function mapImpl(array $params = []): array
    {
        $model = self::model($params['model'] ?? '');
        if ($model === null) {
            return [];
        }

        return self::mapOf($model);
    }

    /* ------------------------------------------------------------------ */
    /* 模型元信息                                                          */
    /* ------------------------------------------------------------------ */

    private static function model(string $access): ?array
    {
        $access = trim($access);
        if ($access === '' || !self::modelsTableReady()) {
            return null;
        }

        if (isset(self::$modelCache[$access])) {
            return self::$modelCache[$access];
        }

        $row = DB::table('module_formtools_models')
            ->where('access_identification', $access)
            ->orWhere('identification', $access)
            ->first();

        if ($row === null) {
            return self::$modelCache[$access] = null;
        }

        $model = (array) $row;
        foreach (['fields', 'home_config', 'other_config'] as $jsonField) {
            if (isset($model[$jsonField]) && is_string($model[$jsonField])) {
                $model[$jsonField] = json_decode($model[$jsonField], true) ?: [];
            }
        }
        $model['fields'] = (array) ($model['fields'] ?? []);

        return self::$modelCache[$access] = $model;
    }

    private static function table(array $model): string
    {
        $identification = (string) ($model['identification'] ?? '');
        if ($identification === '') {
            return '';
        }

        $table = 'module_formtools_' . $identification;

        return Schema::hasTable($table) ? $table : '';
    }

    private static function modelsTableReady(): bool
    {
        try {
            return Schema::hasTable('module_formtools_models');
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private static function mapOf(array $model): array
    {
        return ThemeFieldMapper::make((array) ($model['fields'] ?? []));
    }

    private static function applyStatus($query, string $table): void
    {
        if (Schema::hasColumn($table, 'status')) {
            $query->where($table . '.status', 1);
        }
    }

    private static function applyWhere($query, string $table, array $params): void
    {
        $where = $params['where'] ?? [];
        if (!is_array($where) || $where === []) {
            return;
        }

        foreach ($where as $key => $value) {
            if (is_array($value)) {
                // [field, op, value] 或 [field, value]
                $field = (string) ($value[0] ?? '');
                $operator = (string) ($value[1] ?? '=');
                $target = $value[2] ?? ($value[1] ?? null);
                if (is_numeric($operator) && count($value) === 2) {
                    $operator = '=';
                    $target = $value[1];
                }
                if ($field === '' || !Schema::hasColumn($table, $field)) {
                    continue;
                }
                $query->where($table . '.' . $field, $operator, $target);
                continue;
            }

            if (is_numeric($key) || !Schema::hasColumn($table, (string) $key)) {
                continue;
            }
            $query->where($table . '.' . (string) $key, $value);
        }
    }

    private static function applyPid($query, string $table, array $params): void
    {
        if (!array_key_exists('pid', $params) || $params['pid'] === null || $params['pid'] === '') {
            return;
        }
        if (!Schema::hasColumn($table, 'pid')) {
            return;
        }

        $query->where($table . '.pid', (int) $params['pid']);
    }

    private static function applyKeyword($query, string $table, array $params): void
    {
        $keyword = trim((string) ($params['keyword'] ?? ''));
        if ($keyword === '') {
            return;
        }

        $search = (array) ($params['search'] ?? []);
        if ($search === []) {
            $search = ['title', 'name', 'content'];
        }

        $usable = array_values(array_filter($search, static function ($field) use ($table) {
            return Schema::hasColumn($table, (string) $field);
        }));

        if ($usable === []) {
            return;
        }

        $query->where(static function ($sub) use ($table, $usable, $keyword) {
            foreach ($usable as $field) {
                $sub->orWhere($table . '.' . $field, 'like', '%' . $keyword . '%');
            }
        });
    }

    private static function applyOrder($query, string $table, array $params): void
    {
        $order = trim((string) ($params['order'] ?? ''));
        if ($order === '') {
            $order = Schema::hasColumn($table, 'id') ? 'id desc' : '';
        }
        if ($order === '') {
            return;
        }

        foreach (explode(',', $order) as $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }
            $parts = preg_split('/\s+/', $segment) ?: [];
            $field = (string) ($parts[0] ?? '');
            $direction = strtolower((string) ($parts[1] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
            if ($field === '' || !Schema::hasColumn($table, $field)) {
                continue;
            }
            $query->orderBy($table . '.' . $field, $direction);
        }
    }

    private static function selectColumns(string $table, array $params): array
    {
        $fields = (array) ($params['fields'] ?? []);
        if ($fields === []) {
            return ['*'];
        }

        $columns = array_values(array_filter($fields, static function ($field) use ($table) {
            return Schema::hasColumn($table, (string) $field);
        }));

        return $columns === [] ? ['*'] : $columns;
    }

    private static function normalizeRows(array $rows, array $model): array
    {
        $map = self::mapOf($model);
        $access = (string) ($model['access_identification'] ?? '');

        $result = [];
        foreach ($rows as $row) {
            $result[] = ThemeFieldMapper::normalize($row, $map, $access);
        }

        return $result;
    }

    private static function neighbour(string $table, array $model, int $id, string $direction): ?array
    {
        if ($id <= 0 || !Schema::hasColumn($table, 'id')) {
            return null;
        }

        $query = DB::table($table);
        self::applyStatus($query, $table);
        $query->where('id', $direction === 'prev' ? '<' : '>', $id)
            ->orderBy('id', $direction === 'prev' ? 'desc' : 'asc');

        $row = $query->first();
        if ($row === null) {
            return null;
        }

        return ThemeFieldMapper::normalize($row, self::mapOf($model), (string) ($model['access_identification'] ?? ''));
    }

    private static function buildTree(array $items, int $pid, int $depth = 0, int $maxDepth = 5): array
    {
        $branch = [];
        foreach ($items as $item) {
            if ((int) ($item['pid'] ?? 0) !== $pid) {
                continue;
            }
            $children = $depth + 1 < $maxDepth ? self::buildTree($items, (int) $item['id'], $depth + 1, $maxDepth) : [];
            $item['children'] = $children;
            $item['depth'] = $depth;
            $branch[] = $item;
        }

        return $branch;
    }

    private static function quoteColumn(string $column): string
    {
        return '`' . str_replace('`', '', $column) . '`';
    }

    private static function emptyPage(): array
    {
        return [
            'items' => [],
            'rows' => [],
            'total' => 0,
            'per_page' => 0,
            'current_page' => 1,
            'last_page' => 1,
            'paginator' => null,
            'map' => [],
            'model' => '',
            'access' => '',
        ];
    }
}
