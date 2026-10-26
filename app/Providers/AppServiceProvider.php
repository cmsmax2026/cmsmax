<?php

namespace App\Providers;

use Doctrine\DBAL\DriverManager;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Laravel 11/12 移除了 Connection::getDoctrineSchemaManager()，但项目多处
        // （Formtools/DataManage 的表结构同步）仍依赖 doctrine 对象式 API。这里用同名
        // 宏桥接，行为与旧版一致。
        //
        // 注意：doctrine/dbal 3.x 的 PDO 驱动 connect() 会忽略传入的 pdo 参数、按
        // path/dbname 重新建连，因此必须用 Laravel 连接的实际配置构造 doctrine 连接，
        // 否则 SQLite 会打开空库（listTables 返回 0）。同时必须显式指定 driverClass。
        if (!Connection::hasMacro('getDoctrineSchemaManager')) {
            Connection::macro('getDoctrineSchemaManager', function () {
                /** @var Connection $this */
                $driverMap = [
                    'mysql'  => \Doctrine\DBAL\Driver\PDO\MySQL\Driver::class,
                    'sqlite' => \Doctrine\DBAL\Driver\PDO\SQLite\Driver::class,
                    'pgsql'  => \Doctrine\DBAL\Driver\PDO\PgSQL\Driver::class,
                    'sqlsrv' => \Doctrine\DBAL\Driver\PDO\SQLSrv\Driver::class,
                ];
                $driverName = $this->getDriverName();
                $driverClass = $driverMap[$driverName] ?? \Doctrine\DBAL\Driver\PDO\SQLite\Driver::class;
                $config = (array) $this->getConfig(null);

                switch ($driverName) {
                    case 'sqlite':
                        $database = (string) $this->getDatabaseName();
                        $params = $database === ':memory:'
                            ? ['memory' => true]
                            : ['path' => $database];
                        break;
                    case 'mysql':
                        $params = [
                            'host'       => $this->getConfig('host'),
                            'port'       => $this->getConfig('port'),
                            'dbname'     => $this->getDatabaseName(),
                            'user'       => $this->getConfig('username'),
                            'password'   => $this->getConfig('password'),
                            'charset'    => $config['charset'] ?? 'utf8mb4',
                            'unix_socket' => $config['unix_socket'] ?? null,
                        ];
                        break;
                    case 'pgsql':
                    case 'sqlsrv':
                    default:
                        $params = [
                            'host'   => $this->getConfig('host'),
                            'port'   => $this->getConfig('port'),
                            'dbname' => $this->getDatabaseName(),
                            'user'   => $this->getConfig('username'),
                            'password' => $this->getConfig('password'),
                        ];
                        break;
                }

                $params['driverClass'] = $driverClass;

                return DriverManager::getConnection($params)->createSchemaManager();
            });
        }

        // 兼容「二级目录」与「一级目录」部署：Laravel 的 Request::root() 只有
        // scheme://host，不含子目录路径，二级目录下 url()/asset() 会丢掉前缀导致 404。
        // 这里直接从「当前请求」推断子目录（getBaseUrl 形如 /cms/index.php → /cms），
        // 不依赖 APP_URL 配置。一级目录时 getBaseUrl 为 /index.php → 前缀为空，行为不变。
        $request = request();
        if ($request) {
            // getBaseUrl() 已包含子目录（如 /cms），并自动剥离 /index.php
            $base = str_replace('\\', '/', (string) $request->getBaseUrl());
            $base = preg_replace('#/index\.php$#i', '', $base);
            if ($base === '' || $base === '/') {
                $base = '';
            }
            \Illuminate\Support\Facades\URL::forceRootUrl(
                $request->getSchemeAndHttpHost() . $base
            );
        }

        $this->bootPluginVendorAutoload();
        $this->bootThemeDirectives();
    }

    /**
     * 注册主题 Blade 指令。
     *
     * 目的：主题模板里不要再出现 `@include("themes.default.public.head")` 这种写死主题名的写法，
     * 否则主题一复制改名就全部指向旧主题。统一改成：
     *
     *     @themeInclude('public.head')
     *     <link rel="stylesheet" href="@themeAsset('css/app.css')">
     *     @themeSetting('primary_color', '#2563eb')
     */
    private function bootThemeDirectives(): void
    {
        // @themeInclude('public.head')  /  @themeInclude('public.head', ['model' => $model])
        Blade::directive('themeInclude', static function ($expression) {
            return "<?php echo theme_include({$expression}); ?>";
        });

        // @themeAsset('css/app.css')
        Blade::directive('themeAsset', static function ($expression) {
            return "<?php echo \\App\\Support\\Theme\\ThemeManager::asset({$expression}); ?>";
        });

        // @themeSetting('primary_color', '#2563eb')
        Blade::directive('themeSetting', static function ($expression) {
            return "<?php echo e(\\App\\Support\\Theme\\ThemeSettings::get({$expression})); ?>";
        });

        // @themeData('bbs.latest', ['limit' => 5]) 结果放进 $__themeData
        Blade::directive('themeData', static function ($expression) {
            return "<?php \$__themeData = \\App\\Support\\Theme\\ThemeDataRegistry::get({$expression}); ?>";
        });

        // @shorttags ... @endshorttags —— 局部开启短标签解析
        Blade::directive('shorttags', static function () {
            return '<?php ob_start(); ?>';
        });
        Blade::directive('endshorttags', static function () {
            return '<?php echo \\App\\Support\\Theme\\ShortTagParser::render(ob_get_clean()); ?>';
        });

        // @shorttag('{mx:list model="news" limit="6" /}')
        Blade::directive('shorttag', static function ($expression) {
            return "<?php echo \\App\\Support\\Theme\\ShortTagParser::render({$expression}); ?>";
        });
    }

    /**
     * 加载「已启用插件」的私有 composer 依赖。
     *
     * 与 ModulesController::__construct() 对模块 vendor/autoload.php 的处理保持对称：
     * 插件允许在 plugins/<X>/composer.json 声明第三方依赖并安装到 plugins/<X>/vendor/，
     * 否则插件里 use 的第三方类（如 OSS\OssClient）会 Class not found。
     *
     * 注意：框架只为插件自动注册 Listeners，不存在插件级的 ServiceProvider，
     * 因此这一层只能由框架全局补上。
     */
    private function bootPluginVendorAutoload(): void
    {
        if (!function_exists('plugins_base_path')) {
            return;
        }

        $pluginRoot = plugins_base_path();
        if (!is_dir($pluginRoot)) {
            return;
        }

        foreach ($this->resolveActivePluginIdentifications($pluginRoot) as $identification) {
            $autoload = $pluginRoot . DIRECTORY_SEPARATOR . $identification
                . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
            if (is_file($autoload)) {
                require_once $autoload;
            }
        }
    }

    /**
     * 取启用插件的标识列表。
     *
     * 优先读 plugins-active 缓存；缓存为空（首次安装或刚清过缓存）时退化为扫描目录，
     * 保证这一请求不会因为缺缓存而漏掉依赖加载。
     */
    private function resolveActivePluginIdentifications(string $pluginRoot): array
    {
        $cached = \cache(\Mxzcms\Modules\cache\CacheKey::PluginsActive);
        if (is_array($cached) && $cached !== []) {
            $identifications = [];
            foreach ($cached as $plugin) {
                if (empty($plugin['status'])) {
                    continue;
                }
                $identification = trim((string) ($plugin['identification'] ?? ''));
                if ($identification !== '') {
                    $identifications[] = $identification;
                }
            }

            return $identifications;
        }

        $identifications = [];
        foreach ((array) (function_exists('getDirContent') ? getDirContent($pluginRoot) : []) as $directory) {
            $directory = trim((string) $directory);
            if ($directory === '' || $directory === '.' || $directory === '..') {
                continue;
            }
            if (is_dir($pluginRoot . DIRECTORY_SEPARATOR . $directory)) {
                $identifications[] = $directory;
            }
        }

        return $identifications;
    }


}
