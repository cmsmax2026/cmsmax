<?php

namespace Modules\System\Http\Controllers\Admin;

use App\Support\Security\FrontTrafficGuard;
use App\Support\Security\IpAccessRuleService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\Main\Models\Modules;
use Modules\System\Models\IpAccessRule;
use Modules\System\Models\SystemIpBlockLog;
use Modules\System\Http\Controllers\Common\CommonController;
use Illuminate\Http\Request;
use Modules\System\Services\ServiceModel;
use Modules\System\Models\Setting;

class SecureController extends CommonController {

    public function __construct(Request $request) {
        parent::__construct($request);
    }

    //前台 IP 规则
    public function ipRules(Request $request) {
        $pageData = [
            'subtitle' => '安全与工具',
            'title' => '前台 IP 规则',
            'controller' => 'Secure',
            'action' => 'ipRules',
        ];

        $scope = trim((string) $request->input('scope', ''));
        $keyword = trim((string) $request->input('keyword', ''));

        $query = IpAccessRule::query();
        if ($scope !== '' && in_array($scope, IpAccessRuleService::scopes(), true)) {
            $query->where('scope', $scope);
        }
        if ($keyword !== '') {
            $query->where('rule_value', 'like', '%' . $keyword . '%');
        }

        $rules = $query->orderByRaw(
            "CASE WHEN scope = ? THEN 0 WHEN scope = ? THEN 1 ELSE 2 END",
            [IpAccessRuleService::SCOPE_FRONT, IpAccessRuleService::SCOPE_ALL]
        )->orderByDesc('id')->paginate(20)->appends($request->query());

        return $this->adminView('secure.ipRules', [
            'pageData' => $pageData,
            'rules' => $rules,
            'scope' => $scope,
            'keyword' => $keyword,
            'guardEnabled' => FrontTrafficGuard::enabled(),
            'guardSettings' => [
                'rate_limit' => FrontTrafficGuard::setting(FrontTrafficGuard::SETTING_RATE_LIMIT),
                'violation_limit' => FrontTrafficGuard::setting(FrontTrafficGuard::SETTING_VIOLATION_LIMIT),
                'ban_minutes' => FrontTrafficGuard::setting(FrontTrafficGuard::SETTING_BAN_MINUTES),
                'violation_window' => FrontTrafficGuard::setting(FrontTrafficGuard::SETTING_VIOLATION_WINDOW),
            ],
            'stats' => [
                'total' => IpAccessRule::query()->count(),
                'active' => IpAccessRule::query()
                    ->where('status', 1)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })->count(),
                'auto' => IpAccessRule::query()->where('source_type', IpAccessRuleService::SOURCE_AUTO)->count(),
                'expired' => IpAccessRule::query()
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now())
                    ->count(),
            ],
        ]);
    }

    //添加 IP 规则（支持单条或批量）
    public function addIpRule(Request $request) {
        $scope = trim((string) $request->input('scope', ''));
        $ruleType = trim((string) $request->input('rule_type', ''));
        $ruleValue = trim((string) $request->input('rule_value', ''));
        $batchValues = trim((string) $request->input('batch_values', ''));
        $minutes = (int) $request->input('minutes', 0);
        $reason = trim((string) $request->input('reason', ''));

        if (!in_array($scope, IpAccessRuleService::scopes(), true)) {
            return returnArr(0, '作用域不合法');
        }
        if (!in_array($ruleType, [IpAccessRuleService::RULE_ALLOW, IpAccessRuleService::RULE_DENY], true)) {
            return returnArr(0, '规则类型不合法');
        }

        if ($batchValues !== '') {
            return $this->batchAddIpRules($scope, $ruleType, $batchValues, $minutes, $reason);
        }

        if ($ruleValue === '') {
            return returnArr(0, '请填写 IP / 网段');
        }

        $rule = IpAccessRuleService::addRule([
            'scope' => $scope,
            'rule_type' => $ruleType,
            'match_type' => IpAccessRuleService::detectMatchType($ruleValue),
            'rule_value' => $ruleValue,
            'reason' => $reason !== '' ? $reason : '后台手动添加',
            'expires_at' => $minutes > 0 ? now()->addMinutes($minutes)->format('Y-m-d H:i:s') : '',
        ]);

        if (!$rule) {
            return returnArr(0, '规则表不存在，添加失败');
        }

        return returnArr(200, '规则已添加', ['id' => $rule->id]);
    }

    private function batchAddIpRules(string $scope, string $ruleType, string $batchValues, int $minutes, string $reason): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $batchValues) ?: [];
        $success = 0;
        $failed = [];
        $skipped = 0;
        $defaultReason = $reason !== '' ? $reason : '后台批量导入';
        $expiresAt = $minutes > 0 ? now()->addMinutes($minutes)->format('Y-m-d H:i:s') : '';

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '//') === 0) {
                $skipped++;
                continue;
            }

            if (!IpAccessRuleService::isValidRuleValue($line)) {
                $failed[] = $line;
                continue;
            }

            $rule = IpAccessRuleService::addRule([
                'scope' => $scope,
                'rule_type' => $ruleType,
                'match_type' => IpAccessRuleService::detectMatchType($line),
                'rule_value' => $line,
                'reason' => $defaultReason,
                'expires_at' => $expiresAt,
            ]);

            if ($rule) {
                $success++;
            } else {
                $failed[] = $line;
            }
        }

        if ($success === 0 && count($failed) === 0) {
            return returnArr(0, '没有可导入的规则，请检查输入');
        }

        $msg = sprintf('批量导入完成：成功 %d 条', $success);
        if ($skipped > 0) {
            $msg .= sprintf('，跳过 %d 条空行/注释', $skipped);
        }
        if (count($failed) > 0) {
            $msg .= sprintf('，失败 %d 条（%s）', count($failed), implode('、', array_slice($failed, 0, 5)));
        }

        return returnArr(200, $msg, ['success' => $success, 'skipped' => $skipped, 'failed' => $failed]);
    }

    //移除 IP 规则（解封）
    public function removeIpRule(Request $request) {
        $id = (int) $request->input('id', 0);
        if ($id <= 0) {
            return returnArr(0, '参数有误');
        }

        return IpAccessRuleService::removeRule($id)
            ? returnArr(200, '已移除')
            : returnArr(0, '规则不存在');
    }

    //清理过期规则
    public function clearExpiredIpRules() {
        $count = IpAccessRuleService::clearExpired();

        return returnArr(200, '已清理 ' . $count . ' 条过期规则');
    }

    //IP 拦截日志
    public function ipBlockLogs(Request $request) {
        $pageData = [
            'subtitle' => '安全与工具',
            'title' => 'IP 拦截日志',
            'controller' => 'Secure',
            'action' => 'ipBlockLogs',
        ];

        $ip = trim((string) $request->input('ip', ''));
        $source = trim((string) $request->input('source', ''));

        $hasTable = class_exists(SystemIpBlockLog::class) && \Illuminate\Support\Facades\Schema::hasTable(SystemIpBlockLog::TABLE_NAME);
        $logs = null;
        if ($hasTable) {
            $query = SystemIpBlockLog::query();
            if ($ip !== '') {
                $query->where('ip', 'like', '%' . $ip . '%');
            }
            if ($source !== '') {
                $query->where('source', $source);
            }
            $logs = $query->orderByDesc('id')->paginate(20)->appends($request->query());
        }

        $sources = ['database_rule' => '数据库规则', 'blacklist_setting' => '系统黑名单', 'whitelist_only_mode' => '仅白名单模式', 'auto_ban' => '自动封禁', 'rate_limit' => '频率超限'];

        $topIps = [];
        $hourly = [];
        $daily = [];
        if ($hasTable) {
            $topIps = SystemIpBlockLog::query()
                ->select('ip', DB::raw('count(*) as total'))
                ->groupBy('ip')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            $hourlyMap = [];
            for ($i = 23; $i >= 0; $i--) {
                $hourlyMap[now()->subHours($i)->format('Y-m-d H:00')] = 0;
            }
            $hourRows = SystemIpBlockLog::query()
                ->where('created_at', '>=', now()->subHours(24)->format('Y-m-d H:i:s'))
                ->orderBy('created_at')
                ->get(['created_at']);
            foreach ($hourRows as $row) {
                $hour = substr((string) $row->created_at, 0, 13) . ':00';
                if (array_key_exists($hour, $hourlyMap)) {
                    $hourlyMap[$hour]++;
                }
            }
            $hourly = $hourlyMap;

            $dailyMap = [];
            for ($i = 6; $i >= 0; $i--) {
                $dailyMap[now()->subDays($i)->format('Y-m-d')] = 0;
            }
            $dayRows = SystemIpBlockLog::query()
                ->where('created_at', '>=', now()->subDays(7)->format('Y-m-d H:i:s'))
                ->orderBy('created_at')
                ->get(['created_at']);
            foreach ($dayRows as $row) {
                $day = substr((string) $row->created_at, 0, 10);
                if (array_key_exists($day, $dailyMap)) {
                    $dailyMap[$day]++;
                }
            }
            $daily = $dailyMap;
        }

        return $this->adminView('secure.ipBlockLogs', [
            'pageData' => $pageData,
            'logs' => $logs,
            'ip' => $ip,
            'source' => $source,
            'sources' => $sources,
            'hasTable' => $hasTable,
            'topIps' => $topIps,
            'hourly' => $hourly,
            'daily' => $daily,
        ]);
    }

    //安全设置
    public function secureConfig(Request $request) {
        $pageData = [
            'subtitle' => '安全与工具',
            'title' => '安全设置',
            'controller' => 'Secure',
            'action' => 'secureConfig',
        ];


        return $this->adminView('secure.secureConfig', [
            'pageData' => $pageData,
        ]);
    }


    //上传设置
    public function uploadsConfig(Request $request) {
        $pageData = [
            'subtitle' => '安全与工具',
            'title' => '上传设置',
            'controller' => 'Secure',
            'action' => 'uploadsConfig',
        ];

        //获取当前所有的上传插件
        $plugin_upload_lists = hook("GetUploadPluginList");

        $local_start_plugin_list = [];
        foreach ($plugin_upload_lists as $plugin) {
            if ($plugin) {
                $local_start_plugin_list[] = $plugin;
            }
        }

        $currentUploadDriver = (string) __E('upload_driver');
        $currentUploadDriverLabel = '本地存储';
        if ($currentUploadDriver !== '' && $currentUploadDriver !== 'local') {
            foreach ($local_start_plugin_list as $plugin) {
                if (($plugin['identification'] ?? '') === $currentUploadDriver) {
                    $currentUploadDriverLabel = $plugin['name'] ?? $currentUploadDriver;
                    break;
                }
            }
        }

        $uploadOverview = [
            [
                'name' => '当前上传驱动',
                'value' => $currentUploadDriverLabel,
                'desc' => '当前文件上传优先使用的存储来源',
            ],
            [
                'name' => '可选上传来源',
                'value' => count($local_start_plugin_list) + 1,
                'desc' => '包含本地存储和已接入的上传插件',
            ],
            [
                'name' => '单文件大小限制',
                'value' => (__E('upload_limit') ?: '0') . ' KB',
                'desc' => '超过限制的文件将无法上传',
            ],
            [
                'name' => '缩略图',
                'value' => __E('thumb_auto') == 1 ? '已开启' : '未开启',
                'desc' => '图片上传后是否自动生成缩略图',
            ],
            [
                'name' => '水印类型',
                'value' => __E('watermark_type') == 'img' ? '图片水印' : (__E('watermark_type') == 'text' ? '文字水印' : '未开启'),
                'desc' => '上传图片时按设置自动打水印（仅本地驱动）',
            ],
            [
                'name' => '上传状态',
                'value' => __E('upload_status') == 1 ? '已开启' : '已关闭',
                'desc' => '关闭后将停止文件上传能力',
            ],
        ];

        return $this->adminView('secure.uploadsConfig', [
            'pageData' => $pageData,
            'plugin_list' => $local_start_plugin_list,
            'uploadOverview' => $uploadOverview,
            'currentUploadDriver' => $currentUploadDriver ?: 'local',
        ]);
    }

    //缓存配置
    public function cacheConfig(Request $request) {
        $pageData = [
            'subtitle' => '安全与工具',
            'title' => '缓存配置',
            'controller' => 'Secure',
            'action' => 'cacheConfig',
        ];

        $cacheDriver = (string) env('CACHE_DRIVER', 'file');
        if ($cacheDriver === '') {
            $cacheDriver = 'file';
        }

        $driverOptions = [
            [
                'value' => 'file',
                'name' => 'File',
                'desc' => '默认本地文件缓存，部署简单，适合单机环境。',
            ],
            [
                'value' => 'redis',
                'name' => 'Redis',
                'desc' => '适合高并发和多节点场景，推荐生产环境优先使用。',
            ],
            [
                'value' => 'memcached',
                'name' => 'Memcached',
                'desc' => '轻量内存缓存，适合追求简单高性能的独立缓存服务。',
            ],
            [
                'value' => 'database',
                'name' => 'Database',
                'desc' => '使用数据库保存缓存，便于统一维护但性能较保守。',
            ],
            [
                'value' => 'apc',
                'name' => 'APC',
                'desc' => '依赖扩展环境，适用于已安装 APC 的特定服务器。',
            ],
            [
                'value' => 'array',
                'name' => 'Array',
                'desc' => '仅当前请求有效，通常用于调试或临时开发环境。',
            ],
        ];

        $driverMetaMap = [];
        foreach ($driverOptions as $option) {
            $driverMetaMap[$option['value']] = $option;
        }

        $currentDriverMeta = $driverMetaMap[$cacheDriver] ?? $driverMetaMap['file'];
        $connectionTarget = '当前驱动无需额外连接配置';
        if ($cacheDriver === 'redis') {
            $connectionTarget = (env('REDIS_HOST') ?: '127.0.0.1') . ':' . (env('REDIS_PORT') ?: '6379');
        } elseif ($cacheDriver === 'memcached') {
            $connectionTarget = (env('MEMCACHED_HOST') ?: '127.0.0.1') . ':' . (env('MEMCACHED_PORT') ?: '11211');
        }

        $cacheOverview = [
            [
                'name' => '当前缓存驱动',
                'value' => $currentDriverMeta['name'],
                'desc' => $currentDriverMeta['desc'],
            ],
            [
                'name' => '缓存前缀',
                'value' => env('CACHE_PREFIX') ?: '未设置',
                'desc' => '用于区分站点缓存键，避免与其它项目相互污染。',
            ],
            [
                'name' => '连接目标',
                'value' => $connectionTarget,
                'desc' => '仅在 Redis 或 Memcached 驱动下需要重点确认。',
            ],
            [
                'name' => '可选驱动',
                'value' => count($driverOptions),
                'desc' => '支持本地、数据库、内存服务等多种缓存方式。',
            ],
        ];

        $cacheQuickActions = [
            [
                'title' => '清理系统缓存',
                'desc' => '保存缓存配置后，可直接清理视图、配置、路由等缓存，确保新设置立即生效。',
                'action' => 'clear-cache',
                'button' => '立即清理',
            ],
            [
                'title' => '检查驱动连接',
                'desc' => '切换到 Redis 或 Memcached 时，请先确认服务地址、端口和认证信息是否正确。',
                'action' => 'check-driver',
                'button' => '查看当前连接',
            ],
        ];

        return $this->adminView('secure.cacheConfig', [
            'pageData' => $pageData,
            'cacheOverview' => $cacheOverview,
            'cacheDriverOptions' => $driverOptions,
            'currentCacheDriver' => $cacheDriver,
            'cacheQuickActions' => $cacheQuickActions,
            'currentCacheDriverMeta' => $currentDriverMeta,
            'cacheConnectionTarget' => $connectionTarget,
        ]);
    }

    //提交处理
    public function toolSubmit() {

        if ($this->request->ismethod('post')) {

            $all = $this->request->all();

            switch ($all['form']) {
                case "safe":

                    $sessionCookie = trim((string) ($all['SESSION_COOKIE'] ?? ''));
                    if ($sessionCookie === '') {
                        $sessionCookie = Str::slug(env('APP_NAME', 'laravel'), '_').'_session';
                    }

                    $env['SESSION_COOKIE'] = $sessionCookie;
                    // Keep the legacy key in sync so old deployments do not drift.
                    $env['COOKIE_NAME'] = $sessionCookie;
                    $env['SESSION_DOMAIN'] = $all['SESSION_DOMAIN'];

                    if (array_key_exists('SESSION_DRIVER', $all)) {
                        $env['SESSION_DRIVER'] = $all['SESSION_DRIVER'];
                    }

                    if (array_key_exists('SESSION_LIFETIME', $all)) {
                        $env['SESSION_LIFETIME'] = $all['SESSION_LIFETIME'];
                    }

                    if (array_key_exists('SESSION_ENCRYPT', $all)) {
                        $env['SESSION_ENCRYPT'] = $all['SESSION_ENCRYPT'];
                    }


                    modifyEnv($env);
                    $settings = [
                        'limit_count' => $this->normalizePositiveInteger($all['limit_count'] ?? 0),
                        'limit_time' => $this->normalizePositiveInteger($all['limit_time'] ?? 0),
                        'blacklist_ip' => $this->normalizeIpRules($all['blacklist_ip'] ?? ''),
                        'whitelist_ip' => $this->normalizeIpRules($all['whitelist_ip'] ?? ''),
                        'ip_auto_ban_threshold' => $this->normalizePositiveInteger($all['ip_auto_ban_threshold'] ?? 0),
                        'ip_auto_ban_minutes' => $this->normalizePositiveInteger($all['ip_auto_ban_minutes'] ?? 0),
                        'admin_login_entrance' => $this->normalizeLoginEntrance($all['admin_login_entrance'] ?? ''),
                        'password_key' => trim((string) ($all['password_key'] ?? 'mxz_')) ?: 'mxz_',
                        'admin_login_code' => intval($all['admin_login_code'] ?? 0) === 1 ? '1' : '0',
                        'home_submit_code' => intval($all['home_submit_code'] ?? 0) === 1 ? '1' : '0',
                        'admin_ip_access_mode' => $this->normalizeAdminIpAccessMode($all['admin_ip_access_mode'] ?? 'allow_all'),
                        'admin_google_authenticator_mode' => $this->normalizeGoogleAuthenticatorMode($all['admin_google_authenticator_mode'] ?? 'off'),
                        'admin_google_authenticator_issuer' => trim((string) ($all['admin_google_authenticator_issuer'] ?? env('APP_NAME', 'MxzCMS Admin'))) ?: env('APP_NAME', 'MxzCMS Admin'),
                        // 前台流量防护（全局中间件 FrontIpGuard 使用）
                        'front_guard_enabled' => intval($all['front_guard_enabled'] ?? 0) === 1 ? '1' : '0',
                        'front_guard_rate_limit' => $this->normalizePositiveInteger($all['front_guard_rate_limit'] ?? 120),
                        'front_guard_violation_limit' => $this->normalizePositiveInteger($all['front_guard_violation_limit'] ?? 5),
                        'front_guard_ban_minutes' => $this->normalizePositiveInteger($all['front_guard_ban_minutes'] ?? 30),
                        'front_guard_violation_window' => $this->normalizePositiveInteger($all['front_guard_violation_window'] ?? 10),
                    ];

                    foreach ($settings as $key => $value) {
                        DB::table(Setting::TABLE_NAME)->updateOrInsert(
                            [
                                'module' => 'Main',
                                'type' => 'safe',
                                'key' => $key,
                            ],
                            [
                                'value' => (string) $value,
                            ]
                        );
                    }

                    //更新缓存
                    cacheGlobalSettings(2);
                    break;

                case "cache":

                    $env['CACHE_PREFIX'] = $all['CACHE_PREFIX'];

                    $env['CACHE_DRIVER'] = $all['CACHE_DRIVER'];

                    $env['REDIS_HOST'] = $all['REDIS_HOST'];

                    $env['REDIS_PASSWORD'] = $all['REDIS_PASSWORD'];

                    $env['REDIS_PORT'] = $all['REDIS_PORT'];

                    $env['MEMCACHED_HOST'] = $all['MEMCACHED_HOST'];

                    $env['MEMCACHED_USERNAME'] = $all['MEMCACHED_USERNAME'];

                    $env['MEMCACHED_PASSWORD'] = $all['MEMCACHED_PASSWORD'];

                    $env['MEMCACHED_PORT'] = $all['MEMCACHED_PORT'];

                    modifyEnv($env);


                    break;

                case "upload":

                    $in_database = [
                        'upload_status',
                        'upload_limit',
                        'upload_format',
                        'upload_driver',
                        'thumb_auto',
                        'thumb_method',
                        'thumb_width',
                        'thumb_height',
                        'watermark_type',
                        'watermark_position',
                        'watermark_text',
                        'watermark_text_size',
                        'watermark_text_angle',
                        'watermark_text_color',
                        'watermark_upload_format',
                        'watermark_img',
                    ];

                    $settings = [];

                    //水印图片文件上传（不走自动水印/缩略图处理）
                    if ($this->request->hasFile('watermark_img')) {
                        $watermarkImgPath = UploadFile(
                            $this->request,
                            'watermark_img',
                            "website/watermark/" . date("Y/m/d/") . uniqid(),
                            "png,jpg,jpeg,gif,webp",
                            "local",
                            false, 0, 0,
                            null, "", false, false, false
                        );
                        if ($watermarkImgPath) {
                            $settings[] = ['key' => 'watermark_img', 'value' => $watermarkImgPath];
                        }
                    }

                    foreach ($all as $key => $value) {
                        if (in_array($key, $in_database, true) && is_string($value)) {
                            array_push($settings, ['key' => $key, 'value' => $value]);
                        }
                    }

                    //逐键 updateOrInsert：兼容老站点缺少新设置行的情况
                    foreach ($settings as $item) {
                        DB::table(Setting::TABLE_NAME)->updateOrInsert(
                            [
                                'module' => 'Main',
                                'type' => 'upload',
                                'key' => $item['key'],
                            ],
                            [
                                'value' => (string) $item['value'],
                            ]
                        );
                    }

                    //更新缓存
                    cacheGlobalSettings(2);

                    break;

                default :

                    return ["status" => 40000, "msg" => "Method does not exist"];
            }


            return ["status" => 200, "msg" => "保存成功"];

        } else {
            return ["status" => 40000, "msg" => "method error,must post method"];
        }
    }

    private function normalizeIpRules($value): string
    {
        $tokens = preg_split('/[\r\n,;|]+/', (string) $value) ?: [];
        $tokens = array_map('trim', $tokens);
        $tokens = array_values(array_unique(array_filter($tokens, static function ($item) {
            return $item !== '';
        })));

        return implode("\n", $tokens);
    }

    private function normalizePositiveInteger($value): string
    {
        return (string) max(0, intval($value));
    }

    private function normalizeLoginEntrance($value): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', trim((string) $value)) ?: '';
    }

    private function normalizeAdminIpAccessMode($value): string
    {
        return trim((string) $value) === 'whitelist_only' ? 'whitelist_only' : 'allow_all';
    }

    private function normalizeGoogleAuthenticatorMode($value): string
    {
        $value = trim((string) $value);
        if (in_array($value, ['optional', 'required'], true)) {
            return $value;
        }

        return 'off';
    }



}
