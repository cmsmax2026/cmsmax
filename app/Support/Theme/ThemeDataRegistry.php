<?php

namespace App\Support\Theme;

/**
 * 主题数据注册表 —— 主题与模块之间的数据桥。
 *
 * 背景：主题和模块是两套独立体系，主题不能直接依赖模块内部类，
 * 否则模块一换/一删主题就崩。主题要拿模块数据，统一走这里：
 *
 *      $rows = theme_data('bbs.latest', ['limit' => 5]);
 *
 * 数据来源按优先级：
 *   1. 运行时注册（ThemeDataRegistry::register，插件在 ServiceProvider 里调）
 *   2. 模块约定类 Modules\<Name>\Theme\DataProvider::providers()
 *   3. 模块 Config/config.php 的 theme_data 段
 *   4. hook('ThemeData') —— 插件最后一次介入机会
 *
 * 内置 formtools.* 一系列提供者（Formtools 是主题的主要数据来源）。
 */
class ThemeDataRegistry
{
    /**
     * @var array<string, callable|string>
     */
    private static array $providers = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $resolvedProviders = [];

    private static bool $discovered = false;

    /**
     * 注册一个数据提供者。
     *
     * @param callable|string $provider callable，或 `Class@method` / 可调用类名
     */
    public static function register(string $key, $provider): void
    {
        $key = self::normalizeKey($key);
        if ($key === '') {
            return;
        }

        self::$providers[$key] = $provider;
        unset(self::$resolvedProviders[$key]);
    }

    /**
     * 批量注册。
     */
    public static function registerMany(array $providers): void
    {
        foreach ($providers as $key => $provider) {
            self::register((string) $key, $provider);
        }
    }

    /**
     * 取数据。
     *
     * @param string $key    形如 `formtools.list`、`bbs.latest`
     * @param array  $params 查询参数，各提供者自行约定
     * @return mixed 取不到返回 null
     */
    public static function get(string $key, array $params = [])
    {
        $key = self::normalizeKey($key);
        if ($key === '') {
            return null;
        }

        self::discover();

        $provider = self::resolveProvider($key);
        if ($provider === null) {
            // 最后一次机会交给插件：监听 ThemeData，返回 null 表示自己不处理
            foreach ((array) hook('ThemeData', ['key' => $key, 'params' => $params]) as $result) {
                if ($result !== null) {
                    return $result;
                }
            }

            return null;
        }

        try {
            return $provider($params, $key);
        } catch (\Throwable $exception) {
            if (function_exists('config') && config('app.debug')) {
                throw $exception;
            }

            return null;
        }
    }

    /**
     * 判断某个 key 是否有对应提供者。
     */
    public static function has(string $key): bool
    {
        self::discover();

        return self::resolveProvider(self::normalizeKey($key)) !== null;
    }

    /**
     * 已注册的 key 列表（供后台「数据标签」下拉用）。
     *
     * @return array<int, array{key:string,source:string,description:string}>
     */
    public static function all(): array
    {
        self::discover();

        $items = [];
        foreach (array_keys(self::$providers) as $key) {
            $items[$key] = [
                'key' => $key,
                'source' => 'runtime',
                'description' => '',
            ];
        }

        foreach (self::$resolvedProviders as $key => $meta) {
            $items[$key] = [
                'key' => $key,
                'source' => (string) ($meta['source'] ?? 'unknown'),
                'description' => (string) ($meta['description'] ?? ''),
            ];
        }

        return array_values($items);
    }

    /**
     * 解析 key 对应的可调用体。
     */
    private static function resolveProvider(string $key): ?callable
    {
        if ($key === '') {
            return null;
        }

        if (isset(self::$providers[$key])) {
            return self::wrap(self::$providers[$key]);
        }

        if (isset(self::$resolvedProviders[$key]['provider'])) {
            return self::wrap(self::$resolvedProviders[$key]['provider']);
        }

        return null;
    }

    /**
     * 把各种写法统一成 callable。
     */
    private static function wrap($provider): ?callable
    {
        if (is_callable($provider)) {
            return $provider;
        }

        if (is_string($provider) && strpos($provider, '@') !== false) {
            [$class, $method] = explode('@', $provider, 2);
            if (class_exists($class) && method_exists($class, $method)) {
                return [app($class), $method];
            }
        }

        if (is_string($provider) && class_exists($provider)) {
            $instance = app($provider);
            if (is_callable($instance)) {
                return $instance;
            }
            if (method_exists($instance, 'handle')) {
                return [$instance, 'handle'];
            }
        }

        return null;
    }

    /**
     * 扫描模块/插件声明的数据提供者。
     */
    private static function discover(): void
    {
        if (self::$discovered) {
            return;
        }
        self::$discovered = true;

        // 内置：Formtools 是主题的主要数据来源。
        // 只补位、不覆盖 —— 外部（插件 ServiceProvider）已经注册过的同名 key 优先级更高。
        foreach (FormtoolsDataProvider::providers() as $key => $provider) {
            $key = self::normalizeKey((string) $key);
            if ($key === '' || isset(self::$providers[$key])) {
                continue;
            }
            self::register($key, $provider);
        }

        foreach (self::packageRoots() as $type => $root) {
            if (!is_dir($root)) {
                continue;
            }

            foreach (array_values(array_diff((array) scandir($root), ['.', '..'])) as $name) {
                if (!is_dir($root . DIRECTORY_SEPARATOR . $name)) {
                    continue;
                }
                self::discoverPackage($type, $name);
            }
        }
    }

    /**
     * 扫描单个包：约定类优先，配置声明兜底。
     */
    private static function discoverPackage(string $type, string $name): void
    {
        $namespace = $type === 'plugin' ? 'Plugins\\' . $name : 'Modules\\' . $name;

        $conventionClass = $namespace . '\\Theme\\DataProvider';
        if (class_exists($conventionClass) && method_exists($conventionClass, 'providers')) {
            try {
                $providers = (array) $conventionClass::providers();
                foreach ($providers as $key => $provider) {
                    self::$resolvedProviders[self::normalizeKey((string) $key)] = [
                        'provider' => $provider,
                        'source' => $type . ':' . $name,
                        'description' => '',
                    ];
                }
            } catch (\Throwable $exception) {
                // 约定类写坏了不能拖垮整站
            }
        }

        $config = self::loadPackageConfig($type, $name);
        foreach ((array) ($config['theme_data'] ?? []) as $key => $provider) {
            $key = self::normalizeKey((string) $key);
            if ($key === '') {
                continue;
            }
            self::$resolvedProviders[$key] = [
                'provider' => $provider,
                'source' => $type . ':' . $name,
                'description' => is_array($provider) ? (string) ($provider['description'] ?? '') : '',
            ];
        }
    }

    private static function loadPackageConfig(string $type, string $name): array
    {
        $root = self::packageRoots()[$type] ?? '';
        if ($root === '') {
            return [];
        }

        $file = $root . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'config.php';
        if (!is_file($file)) {
            return [];
        }

        try {
            $config = include $file;
        } catch (\Throwable $exception) {
            return [];
        }

        return is_array($config) ? $config : [];
    }

    /**
     * @return array<string, string>
     */
    private static function packageRoots(): array
    {
        static $roots = null;
        if ($roots !== null) {
            return $roots;
        }

        $roots = [
            'module' => function_exists('modules_base_path') ? rtrim((string) modules_base_path(), '\\/') : rtrim((string) base_path('modules'), '\\/'),
            'plugin' => function_exists('plugins_base_path') ? rtrim((string) plugins_base_path(), '\\/') : rtrim((string) base_path('plugins'), '\\/'),
        ];

        return $roots;
    }

    private static function normalizeKey(string $key): string
    {
        return strtolower(trim($key));
    }

    /**
     * 重置（测试或重新扫描用）。
     */
    public static function flush(): void
    {
        self::$providers = [];
        self::$resolvedProviders = [];
        self::$discovered = false;
    }
}
