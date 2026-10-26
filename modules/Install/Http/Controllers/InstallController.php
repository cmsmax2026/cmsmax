<?php

namespace Modules\Install\Http\Controllers;

use App\Support\Installer\DatabasePreflight;
use App\Support\Installer\ExtensionInspector;
use App\Support\Installer\InstallLogger;
use App\Support\Installer\InstallerInspector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Main\Http\Controllers\Admin\FuncController;
use Modules\Main\Models\Member;
use Modules\Main\Services\ServiceModel;
use Modules\ModulesController;
use Modules\System\Models\Setting;

class InstallController extends ModulesController {

    public function __construct(Request $request) {
        //安装向导语言切换（需在 checkInstall 之前处理，保证已安装状态下切换语言依然生效）
        $installLang = (string) $request->get('lang');
        if (in_array($installLang, ['zh-CN', 'zh-TW', 'en'], true)) {
            session(['install_current_language' => $installLang]);
        }
        parent::__construct($request);
        //判断是否安装过)
        if (self::checkInstall()) {
            throw new \Exception(installTrans('install_already_installed'));
        }

        view()->share([
            'cms_name' => config('app.name'),
            'cms_url' => config('app.url'),
        ]);
    }

    //检查是否安装
    public static function checkInstall() {
        $lockPath = public_path('install.lock');
        if (is_file($lockPath)) {
            return true;
        }

        try {
            if (!is_file(base_path('.env'))) {
                return false;
            }

            $driver = strtolower(trim((string) env('DB_CONNECTION', 'mysql')));
            if ($driver === 'sqlite') {
                $sqliteDatabase = trim((string) env('DB_SQLITE_DATABASE', ''));
                if ($sqliteDatabase === '') {
                    return false;
                }
            } else {
                $dbHost = (string) env('DB_HOST');
                $dbDatabase = (string) env('DB_DATABASE');
                $dbUsername = (string) env('DB_USERNAME');
                if ($dbHost === '' || $dbDatabase === '' || $dbUsername === '') {
                    return false;
                }
            }

            $installed = Schema::hasTable('modules')
                || Schema::hasTable('members')
                || Schema::hasTable('settings');

            if (!$installed) {
                return false;
            }

            // @file_put_contents($lockPath, date('Y-m-d H:i:s'));
            // return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    //事件目录列表
    public static function listenersDirList() {
        $array = [];

        if (self::checkInstall()) {
            $pluginList = \cache()->get(\Mxzcms\Modules\cache\CacheKey::PluginsActive) ?: [];
            foreach ($pluginList as $plugin) {
                if ($plugin['status']) {
                    $array[] = plugins_base_path($plugin['identification'] . '/Listeners');
                }
            }
            $moduleList = \cache()->get(\Mxzcms\Modules\cache\CacheKey::ModulesActive) ?: [];
            foreach ($moduleList as $module) {
                if ($module['status']) {
                    $array[] = modules_base_path($module['identification'] . '/Listeners');
                }
            }
        } else {
            $array = [
                modules_base_path('System/Listeners'),
                modules_base_path('Auth/Listeners'),
                modules_base_path('Formtools/Listeners'),
            ];
        }
        return $array;
    }

    //安装引导页面
    public function index(Request $request) {
        $params = $request->all();
        $params['install'] = isset($params['install']) ? $params['install'] : 1;
        switch (intval($params['install'])) {
            case 2: //检测环境
                $data = InstallerInspector::inspectForLegacyView();
                view()->share([
                    'data' => $data,
                    'installCheckSummary' => $this->buildInstallerCheckSummary($data['checks'] ?? []),
                ]);
                break;
            case 3: //创建数据
                view()->share([
                    'installDefaults' => $this->getInstallFormDefaults(),
                    'installDbDefaults' => $this->getInstallDatabaseDefaults(),
                    'installDbDrivers' => ExtensionInspector::databaseDrivers(),
                ]);
                break;
            case 4: //创建数据 - 提交

                //仅允许选择当前环境已支持的数据库类型
                $availableDbDrivers = array_values(array_filter(ExtensionInspector::databaseDrivers(), static fn(array $item) => $item['available']));
                $availableDrivers = array_column($availableDbDrivers, 'driver');
                $requestedDriver = $this->normalizeInstallDatabaseDriver($params['dbdriver'] ?? '');
                if (!in_array($requestedDriver, $availableDrivers, true)) {
                    $availableLabels = implode(' / ', array_column($availableDbDrivers, 'label'));
                    return back()->with('error', installTrans('install_driver_not_supported', [
                        'labels' => $availableLabels !== '' ? $availableLabels : installTrans('install_no_available_drivers'),
                    ]));
                }

                /**
                 * 设置数据库配置
                 */
                $env = $this->buildInstallDatabaseEnv($params);
                if (isset($env)) {
                    $row = $this->saveDBInfo($request);
                    if ($row['status'] != 200) return back()->with('error', $row['msg']);
                    $install['env'] = $env;
                }

                /**
                 * 网站SEO
                 */
                $settings = [];
                $in_database = [
                    'website_name',
                    'website_keys',
                    'website_desc',
                ];
                foreach ($params as $key => $value) {
                    if (in_array($key, $in_database)) {
                        if ($key == 'website_reg_rqstd') {
                            $value = implode(",", $value);
                        }
                        array_push($settings, ['key' => $key, 'value' => $value]);
                    }
                }
                $install['settings'] = $settings;
                $install['settings'][] = [
                    'key' => 'admin_login_entrance',
                    'value' => $this->getInstallFormDefaults()['admin_login_entrance'],
                ];

                /**
                 * 设置管理员信息
                 */
                $install['admin'] = [
                    'username' => $params['manager'],
                    'email' => $params['manager_email'],
                    'password' => $params['manager_pwd'],
                ];
                session()->put('install_form_defaults', [
                    'manager' => $params['manager'],
                    'manager_email' => $params['manager_email'],
                    'manager_pwd' => $params['manager_pwd'],
                    'admin_login_entrance' => $this->getInstallFormDefaults()['admin_login_entrance'],
                    'dbdriver' => $env['DB_CONNECTION'] ?? 'mysql',
                    'dbsqlite' => $env['DB_SQLITE_DATABASE'] ?? '',
                ]);

                /**
                 * SQL文件处理
                 */
                try {
                    $this->prepareInstallDatabase($env);
                } catch (\Throwable $e) {
                    InstallLogger::log('install_prepare_database_failed', [
                        'driver' => $env['DB_CONNECTION'] ?? '',
                        'error' => $e->getMessage(),
                    ]);
                    return back()->with('error', $e->getMessage());
                }

                session()->put('install', $install);
                break;
            default:
                # code...
                break;
        }
        return view('install/index/install' . $params['install']);
    }

    //测试数据库
    public function checkDbPwd(Request $request) {
        if ($request->isMethod('POST')) {
            return DatabasePreflight::check($request->all());
        } else {
            return ['msg' => installTrans('install_invalid_request'), 'status' => 40000];
        }
    }

    public function checkRewrite(Request $request) {
        $requestUri = (string) $request->server('REQUEST_URI', '');
        $scriptName = (string) $request->server('SCRIPT_NAME', '');
        $path = trim((string) $request->path(), '/');
        $containsIndexPhp = stripos($requestUri, 'index.php') !== false;
        $supported = $path === 'install/rewrite-check' && !$containsIndexPhp;
        $serverMeta = $this->detectWebServer($request);

        return response()->json([
            'status' => $supported ? 200 : 40000,
            'msg' => $supported
                ? installTrans('install_rewrite_ok')
                : installTrans('install_rewrite_not_effective'),
            'data' => [
                'supported' => $supported,
                'request_uri' => $requestUri,
                'path' => $path,
                'script_name' => $scriptName,
                'server_type' => $serverMeta['type'],
                'server_software' => $serverMeta['software'],
            ],
        ]);
    }

    public function agreement(Request $request)
    {
        $cacheKey = 'install_agreement_html_v1';
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return response()->json([
                'status' => 200,
                'msg' => 'ok',
                'data' => [
                    'html' => $cached,
                    'from_cache' => true,
                ],
            ]);
        }

        $url = 'https://www.mxzcloud.com/api/cloud/getAgrees';
        $html = '';
        $error = '';

        try {
            if (extension_loaded('curl')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_TIMEOUT => 5,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_USERAGENT => 'MxzCMS-Installer/1.0',
                ]);
                $response = curl_exec($ch);
                if ($response === false) {
                    $error = curl_error($ch) ?: 'curl_error';
                } else {
                    $html = (string) $response;
                }
                curl_close($ch);
            } else {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 5,
                    ],
                    'https' => [
                        'timeout' => 5,
                    ],
                ]);
                $response = @file_get_contents($url, false, $context);
                if ($response === false) {
                    $error = 'file_get_contents_failed';
                } else {
                    $html = (string) $response;
                }
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $html = trim($html);
        if ($html !== '') {
            Cache::put($cacheKey, $html, now()->addHours(12));
            return response()->json([
                'status' => 200,
                'msg' => 'ok',
                'data' => [
                    'html' => $html,
                    'from_cache' => false,
                ],
            ]);
        }

        InstallLogger::log('install_agreement_fetch_failed', [
            'error' => $error ?: 'empty_response',
        ]);

        return response()->json([
            'status' => 40000,
            'msg' => installTrans('install_agreement_network_error_retry'),
            'data' => [
                'html' => '<div class="text-muted">' . installTrans('install_agreement_load_failed_online') . '</div>',
                'fallback_url' => $url,
            ],
        ]);
    }

    //保存数据库信息
    public function saveDBInfo(Request $request) {
        $env = $this->buildInstallDatabaseEnv($request->all());
        try {
            modifyEnv($env);
            return ['msg' => installTrans('install_save_db_success'), 'status' => 200];
        } catch (\Exception $exception) {
            return ['msg' => $exception->getMessage(), 'status' => 0];
        }
    }

    public function start(Request $request) {
        if (self::checkInstall()) {
            InstallLogger::log('install_start_blocked', [
                'reason' => 'already_installed',
            ]);
            return ['msg' => installTrans('install_already_installed'), 'status' => 40000];
        }

        try {
            InstallLogger::log('install_start', [
                'db_host' => env('DB_HOST'),
                'db_database' => env('DB_DATABASE'),
            ]);
            $this->applyInstallDatabaseConfig((array) session('install.env', []));
            $migrate = Artisan::call('migrate', [
                '--path' => modules_relative_path('Install/Database/Migrations/install'),
                '--force' => 1,
            ]);
            Artisan::call('db:seed', [
                '--class' => "Modules\Install\Database\Seeders\DatabaseSeeder",
                '--force' => 1,
            ]);
            $installError = session('install.error');
            InstallLogger::log('install_success', [
                'install_error' => $installError,
            ]);
            return ['status' => 200, 'msg' => installTrans('install_install_complete'), 'data' => ['done' => 1, 'error' => $installError]];

        } catch (\Exception $exception) {
            InstallLogger::log('install_fail', [
                'error' => $exception->getMessage(),
            ]);
            return ['msg' => $exception->getMessage(), 'status' => 40000];
        }

    }


    public function setDbConfig() {
        $env = session('install.env');
        self::writeDatabaseConfig($env);
        return ['msg' => installTrans('install_db_config_written'), 'status' => 200];
    }

    //安装模块
    public function installModule() {
        //安装模块
        $request = \request();
        //$request->merge(['m' => 'System']);
        $request->merge(['cloud_type' => \Modules\Main\Models\Modules::Module]);
        $request->merge(['return_type' => 'api']);
        $func = new FuncController($request);
        $res = $func->install($request);
        if ($res['status'] == 200) {
            return ['msg' => installTrans('install_module_success', ['module' => $request->m]), 'status' => 200];
        } else {
            return ['msg' => installTrans('install_module_failed', ['module' => $request->m, 'msg' => $res['msg']]), 'status' => 0];
        }
    }

    //安装数据库
    public function installModuleDB() {
        $request = \request();
        $m = $request->m;
        try {
            return ['status' => 200, 'msg' => installTrans('install_module_db_success', ['module' => $m])];
        } catch (\Exception $exception) {
            return ['msg' => $exception->getMessage(), 'status' => 40000];
        }
    }

    public function setSite() {
        try {
            $admin = session('install.admin');
            $find = Member::query()->where('username', $admin['username'])->first();
            $add["uid"] = 1;
            $add["avatar"] = "avatar/avatar.jpg";
            $add['username'] = $admin['username'];
            $add['nickname'] = $admin['username'];
            $add['password'] = $this->hashInstallAdminPassword((string) $admin['password']);
            $add['status'] = 1;
            $add['email'] = $admin['email'];
            if ($find) {
                ServiceModel::whereUpdate(Member::TABLE_NAME, ['uid' => $find['uid']], $add);
            } else {
                $uid = ServiceModel::add(Member::TABLE_NAME, $add);
            }
            write_lock_file(public_path(), '', 'install.lock');
            if (!is_file(public_path('install.lock'))) {
                throw new \RuntimeException(installTrans('install_lock_write_failed'));
            }

            foreach (session('install')['settings'] as $setting) {
                $key = (string) ($setting['key'] ?? '');
                if ($key === '') {
                    continue;
                }

                $value = (string) ($setting['value'] ?? '');
                $updated = Setting::query()
                    ->where('module', 'Main')
                    ->where('key', $key)
                    ->update(['value' => $value]);

                if (!$updated) {
                    Setting::query()->create([
                        'module' => 'Main',
                        'type' => 'install',
                        'key' => $key,
                        'value' => $value,
                    ]);
                }
            }
            Cache::forget('settings');

        } catch (\Exception $e) {
            return ['msg' => installTrans('install_site_create_failed') . $e->getMessage(), 'status' => 40000];
        }
        return ['msg' => installTrans('install_site_create_success'), 'status' => 200];
    }

    /**
     * 数据库配置项重写
     */
    private function writeDatabaseConfig($params = []) {
        $limit_unique = [
            'DB_CONNECTION', 'DB_PREFIX', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
            'DB_PORT', 'DB_HOST', 'DB_CHARSET', 'DB_SQLITE_DATABASE'
        ];
        $update_env = [];
        foreach ($params as $key => $value) {
            if (in_array($key, $limit_unique)) $update_env[$key] = $value;
        }
        modifyEnv($update_env);//更新ENV配置文件中的数据库配置项
    }

    private function buildInstallerCheckSummary(array $checks): array
    {
        $summary = [
            'total' => 0,
            'passed' => 0,
            'failed_required' => 0,
            'failed_optional' => 0,
            'failed_items' => [],
        ];

        foreach ($checks as $group => $items) {
            foreach (($items ?: []) as $item) {
                $summary['total']++;
                if (($item['status'] ?? '') === 'pass') {
                    $summary['passed']++;
                    continue;
                }

                if (($item['required'] ?? true)) {
                    $summary['failed_required']++;
                } else {
                    $summary['failed_optional']++;
                }

                $summary['failed_items'][] = [
                    'group' => $group,
                    'title' => $item['title'] ?? $item['key'] ?? 'unknown',
                    'message' => $item['message'] ?? installTrans('install_check_fail'),
                    'required' => $item['required'] ?? true,
                ];
            }
        }

        return $summary;
    }

    private function getInstallFormDefaults(): array
    {
        $defaults = session('install_form_defaults') ?: [];
        if (empty($defaults['manager_pwd'])) {
            $defaults['manager_pwd'] = $this->generateRandomPassword();
        }

        if (empty($defaults['manager'])) {
            $defaults['manager'] = 'admin';
        }

        if (!array_key_exists('manager_email', $defaults)) {
            $defaults['manager_email'] = '';
        }

        if (empty($defaults['admin_login_entrance'])) {
            $defaults['admin_login_entrance'] = strtolower($this->generateRandomPassword(10));
        }

        session()->put('install_form_defaults', $defaults);

        return $defaults;
    }

    private function getInstallDatabaseDefaults(): array
    {
        $defaults = session('install_form_defaults') ?: [];
        $driver = $this->resolveInstallDatabaseDriver($defaults['dbdriver'] ?? env('DB_CONNECTION', 'mysql'));
        $sqliteDatabase = $defaults['dbsqlite'] ?? env('DB_SQLITE_DATABASE', database_path('database.sqlite'));

        return [
            'dbdriver' => $driver,
            'dbhost' => env('DB_HOST', '127.0.0.1'),
            'dbport' => env('DB_PORT', 3306),
            'dbuser' => env('DB_USERNAME', ''),
            'dbpw' => env('DB_PASSWORD', ''),
            'dbname' => env('DB_DATABASE', ''),
            'dbprefix' => env('DB_PREFIX', 'mxz_'),
            'dbcharset' => env('DB_CHARSET', 'utf8mb4'),
            'dbsqlite' => $this->normalizeSqliteDatabasePath((string) $sqliteDatabase),
        ];
    }

    private function buildInstallDatabaseEnv(array $all): array
    {
        $driver = $this->normalizeInstallDatabaseDriver($all['dbdriver'] ?? $all['db_connection'] ?? env('DB_CONNECTION', 'mysql'));
        $env = [
            'DB_CONNECTION' => $driver,
            'DB_PREFIX' => trim((string) ($all['dbprefix'] ?? env('DB_PREFIX', 'mxz_'))),
        ];

        if ($driver === 'sqlite') {
            $env['DB_SQLITE_DATABASE'] = $this->normalizeSqliteDatabasePath((string) ($all['dbsqlite'] ?? $all['sqlite_database'] ?? ''));
            $env['DB_HOST'] = '';
            $env['DB_PORT'] = '';
            $env['DB_USERNAME'] = '';
            $env['DB_PASSWORD'] = '';
            $env['DB_DATABASE'] = '';
            $env['DB_CHARSET'] = '';

            return $env;
        }

        $defaultPort = [
            'mysql' => '3306',
            'pgsql' => '5432',
            'sqlsrv' => '1433',
        ][$driver] ?? '3306';
        $env['DB_HOST'] = trim((string) ($all['dbhost'] ?? env('DB_HOST', '127.0.0.1')));
        $env['DB_PORT'] = trim((string) ($all['dbport'] ?? env('DB_PORT', $defaultPort)));
        $env['DB_USERNAME'] = trim((string) ($all['dbuser'] ?? env('DB_USERNAME', '')));
        $env['DB_PASSWORD'] = (string) ($all['dbpw'] ?? env('DB_PASSWORD', ''));
        $env['DB_DATABASE'] = trim((string) ($all['dbname'] ?? env('DB_DATABASE', '')));
        // 仅 MySQL 需要显式字符集；pgsql/sqlsrv 使用框架连接默认字符集，不写入 DB_CHARSET。
        if ($driver === 'mysql') {
            $env['DB_CHARSET'] = trim((string) ($all['dbcharset'] ?? env('DB_CHARSET', 'utf8mb4'))) ?: 'utf8mb4';
        }
        $env['DB_SQLITE_DATABASE'] = '';

        return $env;
    }

    private function normalizeInstallDatabaseDriver(?string $driver): string
    {
        $driver = strtolower(trim((string) $driver));

        return in_array($driver, ['mysql', 'sqlite', 'pgsql', 'sqlsrv'], true) ? $driver : 'mysql';
    }

    /**
     * 将安装表单默认的数据库类型解析为当前环境已支持的类型：
     * 若历史值（如 env 中的 DB_CONNECTION）对应扩展未开启，则回退到第一个可用类型。
     */
    private function resolveInstallDatabaseDriver(?string $driver): string
    {
        $driver = $this->normalizeInstallDatabaseDriver($driver);
        $available = array_column(array_filter(ExtensionInspector::databaseDrivers(), static fn(array $item) => $item['available']), 'driver');
        if (in_array($driver, $available, true)) {
            return $driver;
        }

        return $available[0] ?? $driver;
    }

    private function normalizeSqliteDatabasePath(string $database): string
    {
        $database = trim($database);
        if ($database === '') {
            $database = database_path('database.sqlite');
        }

        if (!preg_match('#^(?:[A-Za-z]:[\\\\/]|/)#', $database)) {
            $database = base_path($database);
        }

        return str_replace('\\', '/', $database);
    }

    private function prepareInstallDatabase(array $env): void
    {
        $driver = $this->normalizeInstallDatabaseDriver($env['DB_CONNECTION'] ?? 'mysql');
        if ($driver === 'sqlite') {
            if (!extension_loaded('pdo_sqlite') || !extension_loaded('sqlite3')) {
                $missing = [];
                if (!extension_loaded('pdo_sqlite')) {
                    $missing[] = 'pdo_sqlite';
                }
                if (!extension_loaded('sqlite3')) {
                    $missing[] = 'sqlite3';
                }
                throw new \RuntimeException(installTrans('install_sqlite_driver_unavailable', ['ext' => implode(installTrans('install_list_separator'), $missing)]));
            }

            $database = $this->normalizeSqliteDatabasePath((string) ($env['DB_SQLITE_DATABASE'] ?? ''));
            $directory = dirname($database);
            if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new \RuntimeException(installTrans('install_sqlite_dir_create_failed', ['dir' => $directory]));
            }
            if (!is_file($database) && @file_put_contents($database, '') === false) {
                throw new \RuntimeException(installTrans('install_sqlite_file_create_failed', ['file' => $database]));
            }

            return;
        }

        $extension = [
            'mysql' => 'pdo_mysql',
            'pgsql' => 'pdo_pgsql',
            'sqlsrv' => 'pdo_sqlsrv',
        ][$driver] ?? 'pdo_mysql';
        if (!extension_loaded($extension)) {
            throw new \RuntimeException(installTrans('install_driver_ext_missing', ['driver' => $driver, 'ext' => $extension]));
        }

        $host = (string) ($env['DB_HOST'] ?? '127.0.0.1');
        $port = (string) ($env['DB_PORT'] ?? (['mysql' => '3306', 'pgsql' => '5432', 'sqlsrv' => '1433'][$driver] ?? '3306'));
        $username = (string) ($env['DB_USERNAME'] ?? '');
        $password = (string) ($env['DB_PASSWORD'] ?? '');
        $database = str_replace('`', '', (string) ($env['DB_DATABASE'] ?? ''));

        if ($driver === 'mysql') {
            $charset = trim((string) ($env['DB_CHARSET'] ?? 'utf8mb4')) ?: 'utf8mb4';
            $dsn = sprintf('mysql:host=%s;port=%s', $host, $port);
            $pdo = new \PDO($dsn, $username, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET {$charset}");

            return;
        }

        if ($driver === 'pgsql') {
            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=postgres', $host, $port);
            $pdo = new \PDO($dsn, $username, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $exists = $pdo->query("SELECT 1 FROM pg_database WHERE datname = " . $pdo->quote($database))->fetchColumn();
            if (!$exists) {
                $pdo->exec('CREATE DATABASE "' . str_replace('"', '""', $database) . '"');
            }

            return;
        }

        if ($driver === 'sqlsrv') {
            $dsn = sprintf('sqlsrv:Server=%s,%s;Database=master', $host, $port);
            $pdo = new \PDO($dsn, $username, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $exists = $pdo->query("SELECT name FROM sys.databases WHERE name = " . $pdo->quote($database))->fetchColumn();
            if ($exists === false) {
                $pdo->exec('CREATE DATABASE [' . str_replace(']', ']]', $database) . ']');
            }

            return;
        }
    }

    /**
     * 在真正执行迁移前，把用户在安装向导里填写的数据库配置覆盖到当前进程的内存 config 中。
     *
     * modifyEnv() 只负责把配置写进 .env 文件，并不会刷新已经加载到内存的 config；
     * 若部署时存在配置缓存（config:cache）或首请求加载的是初始 .env，直接 migrate 可能连错库。
     * 这里以会话中保存的 install.env 为准，确保迁移落在使用者选定的数据库上。
     */
    private function applyInstallDatabaseConfig(array $env): void
    {
        if (empty($env)) {
            return;
        }

        $driver = $this->normalizeInstallDatabaseDriver($env['DB_CONNECTION'] ?? null);

        if ($driver === 'sqlite') {
            $database = $this->normalizeSqliteDatabasePath((string) ($env['DB_SQLITE_DATABASE'] ?? ''));
            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => $database,
                'database.connections.sqlite.prefix' => $env['DB_PREFIX'] ?? '',
            ]);
            DB::purge('sqlite');
            return;
        }

        $connectionKey = $driver; // 'mysql' | 'pgsql' | 'sqlsrv'
        $config = [
            'database.default' => $connectionKey,
            "database.connections.{$connectionKey}.host" => $env['DB_HOST'] ?? '127.0.0.1',
            "database.connections.{$connectionKey}.port" => $env['DB_PORT'] ?? (['mysql' => '3306', 'pgsql' => '5432', 'sqlsrv' => '1433'][$driver] ?? '3306'),
            "database.connections.{$connectionKey}.database" => $env['DB_DATABASE'] ?? '',
            "database.connections.{$connectionKey}.username" => $env['DB_USERNAME'] ?? '',
            "database.connections.{$connectionKey}.password" => $env['DB_PASSWORD'] ?? '',
            "database.connections.{$connectionKey}.prefix" => $env['DB_PREFIX'] ?? '',
        ];
        if ($driver === 'mysql') {
            $config['database.connections.mysql.charset'] = $env['DB_CHARSET'] ?: 'utf8mb4';
        }
        config($config);
        DB::purge($connectionKey);
    }

    private function generateRandomPassword(int $length = 12): string
    {
        $raw = Str::random($length + 4);
        $password = preg_replace('/[^A-Za-z0-9]/', '', $raw) ?: Str::random($length);

        return substr($password, 0, $length);
    }

    private function hashInstallAdminPassword(string $password): string
    {
        $passwordKey = $this->getInstallPasswordKey();

        return md5($passwordKey . md5($password));
    }

    private function getInstallPasswordKey(): string
    {
        $passwordKey = (string) Setting::query()
            ->where('module', 'Main')
            ->where('key', 'password_key')
            ->value('value');

        return $passwordKey !== '' ? $passwordKey : 'mxz_';
    }

    private function detectWebServer(Request $request): array
    {
        $software = trim((string) $request->server('SERVER_SOFTWARE', ''));
        $softwareLower = strtolower($software);
        $type = 'unknown';

        if ($softwareLower !== '') {
            if (str_contains($softwareLower, 'openresty')) {
                $type = 'nginx';
            } elseif (str_contains($softwareLower, 'nginx')) {
                $type = 'nginx';
            } elseif (str_contains($softwareLower, 'apache')) {
                $type = 'apache';
            } elseif (str_contains($softwareLower, 'microsoft-iis') || str_contains($softwareLower, 'iis')) {
                $type = 'iis';
            } elseif (str_contains($softwareLower, 'caddy')) {
                $type = 'caddy';
            }
        }

        return [
            'type' => $type,
            'software' => $software,
        ];
    }
}
