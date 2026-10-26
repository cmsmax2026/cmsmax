@include(moduleAdminTemplate($moduleName)."public.header")
@php($sessionCookie = env('SESSION_COOKIE', env('COOKIE_NAME')))
@php($sessionDriver = env('SESSION_DRIVER', 'file'))
@php($sessionDomain = env('SESSION_DOMAIN'))
@php($sessionLifetime = env('SESSION_LIFETIME'))
@php($sessionEncrypt = filter_var(env('SESSION_ENCRYPT', false), FILTER_VALIDATE_BOOLEAN))
@php($adminLoginEntrance = (string) cacheGlobalSettingsByKey('admin_login_entrance'))
@php($adminLoginCode = (int) cacheGlobalSettingsByKey('admin_login_code'))
@php($homeSubmitCode = (int) cacheGlobalSettingsByKey('home_submit_code'))
@php($adminIpAccessMode = (string) cacheGlobalSettingsByKey('admin_ip_access_mode'))
@php($googleAuthenticatorMode = (string) cacheGlobalSettingsByKey('admin_google_authenticator_mode'))
@php($googleAuthenticatorIssuer = (string) cacheGlobalSettingsByKey('admin_google_authenticator_issuer'))
@php($blacklistIp = (string) cacheGlobalSettingsByKey('blacklist_ip'))
@php($whitelistIp = (string) cacheGlobalSettingsByKey('whitelist_ip'))
@php($autoBanThreshold = (int) cacheGlobalSettingsByKey('ip_auto_ban_threshold'))
@php($autoBanMinutes = (int) cacheGlobalSettingsByKey('ip_auto_ban_minutes'))
@php($frontGuardEnabled = (int) cacheGlobalSettingsByKey('front_guard_enabled') === 1)
@php($frontGuardRateLimit = (int) cacheGlobalSettingsByKey('front_guard_rate_limit') ?: 120)
@php($frontGuardWindow = (int) cacheGlobalSettingsByKey('front_guard_violation_window') ?: 10)
@php($frontGuardViolationLimit = (int) cacheGlobalSettingsByKey('front_guard_violation_limit') ?: 5)
@php($frontGuardBanMinutes = (int) cacheGlobalSettingsByKey('front_guard_ban_minutes') ?: 30)
@php($blacklistItems = array_filter(array_map('trim', preg_split('/[\r\n,;|]+/', $blacklistIp) ?: [])))
@php($currentAdminPayload = session(\Modules\System\Http\Controllers\Common\SessionKey::AdminInfo))
@php($currentAdminInfo = is_array($currentAdminPayload) ? $currentAdminPayload : ((is_object($currentAdminPayload) && method_exists($currentAdminPayload, 'toArray')) ? $currentAdminPayload->toArray() : []))
@php($currentAdminHasGoogleAuthenticator = !empty($currentAdminInfo) ? \App\Support\Security\AdminMfaService::hasEnabledBinding((int) ($currentAdminInfo['uid'] ?? 0)) : false)
@php($googleAuthenticatorManageUrl = url('admin/login/google-authenticator/manage' . ($adminLoginEntrance ? '/' . $adminLoginEntrance : '')))
@php($activeAutoBanCount = class_exists(\Modules\System\Models\IpAccessRule::class) && \Illuminate\Support\Facades\Schema::hasTable(\Modules\System\Models\IpAccessRule::TABLE_NAME) ? \Modules\System\Models\IpAccessRule::query()->where('scope', 'admin')->where('rule_type', 'deny')->where('source_type', 'auto')->where('status', 1)->where(function ($query) { $query->whereNull('expires_at')->orWhere('expires_at', '>', now()); })->count() : 0)
@php($reservedConfigCount = 1)
@php($effectiveConfigCount = 12)
<style>
    .secure-config-hero {
        margin-bottom: 20px;
        padding: 22px 24px;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
    }

    .secure-config-hero__title {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #111827;
    }

    .secure-config-hero__desc {
        margin-top: 8px;
        max-width: 880px;
        color: #6b7280;
        line-height: 1.8;
    }

    .secure-config-overview {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 18px;
    }

    .secure-config-card,
    .secure-config-sidecard {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 10px 32px rgba(15, 23, 42, 0.05);
    }

    .secure-config-overview__item {
        padding: 16px 18px;
    }

    .secure-config-overview__name {
        font-size: 13px;
        color: #6b7280;
    }

    .secure-config-overview__value {
        margin-top: 10px;
        font-size: 24px;
        line-height: 1.2;
        font-weight: 700;
        color: #111827;
        word-break: break-word;
    }

    .secure-config-overview__desc {
        margin-top: 10px;
        min-height: 42px;
        color: #6b7280;
        line-height: 1.6;
    }

    .secure-config-layout {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
        gap: 20px;
        align-items: start;
    }

    .secure-config-card {
        padding: 20px;
    }

    .secure-config-section + .secure-config-section {
        margin-top: 18px;
    }

    .secure-config-section {
        padding: 18px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
    }

    .secure-config-section__title {
        margin: 0 0 6px;
        font-size: 16px;
        font-weight: 700;
        color: #111827;
    }

    .secure-config-section__desc {
        margin-bottom: 16px;
        color: #6b7280;
        line-height: 1.7;
    }

    .secure-config-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .secure-config-field--full {
        grid-column: 1 / -1;
    }

    .secure-config-field label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .secure-config-field input,
    .secure-config-field textarea,
    .secure-config-field select {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        background: #fff;
    }

    .secure-config-field input,
    .secure-config-field select {
        height: 42px;
        padding: 8px 12px;
    }

    .secure-config-field textarea {
        min-height: 120px;
        padding: 10px 12px;
        resize: vertical;
    }

    .secure-config-help {
        margin-top: 6px;
        color: #6b7280;
        line-height: 1.6;
    }

    .secure-config-help.is-warning {
        color: #b45309;
    }

    .secure-config-inline {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .secure-config-inline__prefix,
    .secure-config-inline__suffix {
        color: #6b7280;
        white-space: nowrap;
    }

    .secure-config-inline__field {
        min-width: 180px;
        flex: 1;
    }

    .secure-config-actions {
        margin-top: 20px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .secure-config-sidecard {
        padding: 18px;
    }

    .secure-config-sidecard + .secure-config-sidecard {
        margin-top: 18px;
    }

    .secure-config-sidecard__title {
        margin: 0 0 8px;
        font-size: 15px;
        font-weight: 700;
        color: #111827;
    }

    .secure-config-sidecard__desc,
    .secure-config-sidecard__meta {
        color: #6b7280;
        line-height: 1.7;
        word-break: break-word;
    }

    .secure-config-sidecard__meta strong {
        color: #111827;
    }

    .secure-config-radio-group {
        display: flex;
        align-items: center;
        gap: 22px;
        flex-wrap: wrap;
        min-height: 48px;
    }

    .secure-config-radio-group label {
        margin: 0;
        font-weight: 400;
    }

    .secure-config-radio-group .radio-inline {
        display: inline-flex;
        align-items: center;
        padding-left: 0;
        margin-right: 6px;
        font-size: 15px;
        line-height: 1.4;
        overflow: visible;
    }

    .secure-config-radio-group .styled,
    .secure-config-radio-group input[type="radio"] {
        width: 14px;
        height: 14px;
        margin: 0 12px 0 0;
        vertical-align: middle;
        transform: scale(1.2);
        transform-origin: center;
    }

    .secure-config-radio-group .h-span-val {
        display: inline-block;
        font-size: 15px;
        line-height: 1.4;
        white-space: nowrap;
        margin-left: 20px;
    }

    .secure-config-chip {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .secure-config-chip.is-active {
        color: #166534;
        background: #dcfce7;
    }

    .secure-config-chip.is-pending {
        color: #b45309;
        background: #fef3c7;
    }

    @media (max-width: 1200px) {
        .secure-config-overview,
        .secure-config-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .secure-config-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .secure-config-overview,
        .secure-config-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<body>

@include(moduleAdminTemplate($moduleName)."public.nav")

<div class="page-container">
    <div class="page-content">
        @include(moduleAdminTemplate($moduleName)."public.left")

        <div class="content-wrapper">
            <div class="page-header">
                @include(moduleAdminTemplate($moduleName)."public.page", ['breadcrumb'=>[$pageData['title'],$pageData['subtitle']]])
            </div>

            <div class="content" style="margin-top: 1rem;">
                <div class="secure-config-hero">
                    <h2 class="secure-config-hero__title">安全配置工作台</h2>
                    <div class="secure-config-hero__desc">
                        这页负责后台入口、会话参数、验证码和基础访问控制。已接入运行时的配置会直接影响全局行为，预留项会明确标注，避免“页面能改但系统没吃到”。
                    </div>

                    <div class="secure-config-overview">
                        <div class="secure-config-card secure-config-overview__item">
                            <div class="secure-config-overview__name">Session 驱动</div>
                            <div class="secure-config-overview__value">{{ strtoupper($sessionDriver) }}</div>
                            <div class="secure-config-overview__desc">当前 Laravel 会话层实际使用的存储驱动。</div>
                        </div>
                        <div class="secure-config-card secure-config-overview__item">
                            <div class="secure-config-overview__name">后台安全入口</div>
                            <div class="secure-config-overview__value">{{ $adminLoginEntrance ?: '未设置' }}</div>
                            <div class="secure-config-overview__desc">未设置时使用默认后台登录地址，设置后需通过专用入口访问。</div>
                        </div>
                        <div class="secure-config-card secure-config-overview__item">
                            <div class="secure-config-overview__name">IP 黑名单</div>
                            <div class="secure-config-overview__value">{{ count($blacklistItems) + $activeAutoBanCount }} 条</div>
                            <div class="secure-config-overview__desc">静态黑名单与动态自动封禁合计条数，命中后会被统一拦截。</div>
                        </div>
                        <div class="secure-config-card secure-config-overview__item">
                            <div class="secure-config-overview__name">后台登录验证码</div>
                            <div class="secure-config-overview__value">{{ $adminLoginCode === 1 ? '开启' : '关闭' }}</div>
                            <div class="secure-config-overview__desc">影响后台登录页的验证码显示与校验流程。</div>
                        </div>
                        <div class="secure-config-card secure-config-overview__item">
                            <div class="secure-config-overview__name">已接入项</div>
                            <div class="secure-config-overview__value">{{ $effectiveConfigCount }} 项</div>
                            <div class="secure-config-overview__desc">当前已确认真实影响运行时行为的安全配置数量。</div>
                        </div>
                        <div class="secure-config-card secure-config-overview__item">
                            <div class="secure-config-overview__name">预留项</div>
                            <div class="secure-config-overview__value">{{ $reservedConfigCount }} 项</div>
                            <div class="secure-config-overview__desc">仍保留在页面中，但尚未完整接入统一策略链路。</div>
                        </div>
                    </div>
                </div>

                <div class="secure-config-layout">
                    <div class="secure-config-card">
                        <form id="myForm" method="post" enctype="multipart/form-data">
                            {{csrf_field()}}
                            <input type="hidden" name="form" value="safe">

                            <div class="secure-config-section">
                                <h3 class="secure-config-section__title">会话与登录入口</h3>
                                <div class="secure-config-section__desc">这组配置决定会话 Cookie、会话驱动和后台登录入口，是优先级最高的安全基础项。</div>

                                <div class="secure-config-grid">
                                    <div class="secure-config-field">
                                        <label>Session Cookie</label>
                                        <input type="text" name="SESSION_COOKIE" value="{{$sessionCookie}}" class="form-control">
                                        <div class="secure-config-help">Laravel 实际读取的是 `SESSION_COOKIE`，这里控制前后台 Session Cookie 名称。</div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>Session 域名</label>
                                        <input type="text" name="SESSION_DOMAIN" value="{{$sessionDomain}}" class="form-control" placeholder=".example.com">
                                        <div class="secure-config-help">跨子域共享登录状态时可配置统一域名，否则通常留空。</div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>Session 驱动</label>
                                        <select name="SESSION_DRIVER" class="select-search">
                                            <option value="file" @if($sessionDriver=='file') selected @endif>File</option>
                                            <option value="redis" @if($sessionDriver=='redis') selected @endif>Redis</option>
                                            <option value="memcached" @if($sessionDriver=='memcached') selected @endif>Memcached</option>
                                            <option value="database" @if($sessionDriver=='database') selected @endif>Database</option>
                                            <option value="apc" @if($sessionDriver=='apc') selected @endif>Apc</option>
                                            <option value="array" @if($sessionDriver=='array') selected @endif>Array</option>
                                        </select>
                                        <div class="secure-config-help">建议与缓存驱动和部署环境保持一致，避免会话漂移。</div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>Session 时长</label>
                                        <div class="secure-config-inline">
                                            <input type="number" name="SESSION_LIFETIME" value="{{$sessionLifetime}}" class="form-control secure-config-inline__field">
                                            <span class="secure-config-inline__suffix">分钟</span>
                                        </div>
                                        <div class="secure-config-help">超时后用户需要重新登录，后台建议按管理场景设置合适时长。</div>
                                    </div>

                                    <div class="secure-config-field secure-config-field--full">
                                        <label>Session 加密</label>
                                        <div class="secure-config-radio-group">
                                            <label class="radio-inline">
                                                <input type="radio" name="SESSION_ENCRYPT" class="styled h-radio" value="false" @if(!$sessionEncrypt) checked @endif>
                                                <span class="h-span-val">不加密</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="SESSION_ENCRYPT" class="styled h-radio" value="true" @if($sessionEncrypt) checked @endif>
                                                <span class="h-span-val">加密</span>
                                            </label>
                                        </div>
                                        <div class="secure-config-help">现在已真正同步到 Laravel Session 配置，修改后建议清理配置缓存。</div>
                                    </div>

                                    <div class="secure-config-field secure-config-field--full">
                                        <label>后台登录入口</label>
                                        <div class="secure-config-inline">
                                            <span class="secure-config-inline__prefix">{{ url('admin/login') }}/</span>
                                            <input type="text" id="adminLoginEntrance" name="admin_login_entrance" value="{{$adminLoginEntrance}}" class="form-control secure-config-inline__field" placeholder="字母、数字、短横线">
                                            <button type="button" class="btn btn-default" id="generateAdminLoginEntrance">随机生成</button>
                                        </div>
                                        <div class="secure-config-help">设置后只能通过该专用入口进入后台，建议使用易记但不直白的路径。</div>
                                        <div class="secure-config-help">当前预览：<span id="adminLoginEntrancePreview">{{ $adminLoginEntrance ? url('admin/login/' . $adminLoginEntrance) : url('admin/login') }}</span></div>
                                    </div>

                                    <div class="secure-config-field secure-config-field--full">
                                        <label>密码加密 KEY</label>
                                        <input type="text" name="password_key" value="{{cacheGlobalSettingsByKey('password_key')}}" class="form-control">
                                        <div class="secure-config-help">当前加密规则：`md5(key + md5(password))`。修改后会影响密码校验一致性，建议谨慎操作。</div>
                                    </div>
                                </div>
                            </div>

                            <div class="secure-config-section">
                                <h3 class="secure-config-section__title">访问控制与过滤</h3>
                                <div class="secure-config-section__desc">这里包含后台 IP 访问模式、白名单、静态黑名单、动态自动封禁，以及后台账号锁定策略。动态封禁记录落到 `system_ip_access_rules` 表。</div>

                                <div class="secure-config-grid">
                                    <div class="secure-config-field secure-config-field--full">
                                        <label>后台 IP 访问模式</label>
                                        <div class="secure-config-radio-group">
                                            <label class="radio-inline">
                                                <input type="radio" name="admin_ip_access_mode" class="styled h-radio" value="allow_all" @if($adminIpAccessMode !== 'whitelist_only') checked @endif>
                                                <span class="h-span-val">允许所有 IP</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="admin_ip_access_mode" class="styled h-radio" value="whitelist_only" @if($adminIpAccessMode === 'whitelist_only') checked @endif>
                                                <span class="h-span-val">仅白名单可访问</span>
                                            </label>
                                        </div>
                                        <div class="secure-config-help">开启“仅白名单可访问”后，未命中白名单或数据库 allow 规则的其它 IP 会直接被后台入口拒绝。</div>
                                    </div>

                                    <div class="secure-config-field secure-config-field--full">
                                        <label>IP 白名单</label>
                                        <textarea placeholder="127.0.0.1,192.168.1.0/24" name="whitelist_ip" rows="3">{{$whitelistIp}}</textarea>
                                        <div class="secure-config-help">白名单优先生效，支持单 IP、CIDR 网段、IP 段，分隔符可用换行、逗号、分号或 `|`。</div>
                                    </div>

                                    <div class="secure-config-field secure-config-field--full">
                                        <label>IP 黑名单</label>
                                        <textarea placeholder="127.0.0.1,192.168.1.1" name="blacklist_ip" rows="4">{{$blacklistIp}}</textarea>
                                        <div class="secure-config-help">支持单 IP、CIDR 网段、IP 段，分隔符可用换行、逗号、分号或 `|`。该配置会与 `system_ip_access_rules` 动态规则一起生效。</div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>账号锁定阈值</label>
                                        <div class="secure-config-inline">
                                            <span class="secure-config-inline__prefix">连续失败</span>
                                            <input type="number" name="limit_count" value="{{cacheGlobalSettingsByKey('limit_count')}}" class="form-control secure-config-inline__field">
                                            <span class="secure-config-inline__suffix">次</span>
                                        </div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>锁定时长</label>
                                        <div class="secure-config-inline">
                                            <span class="secure-config-inline__prefix">锁定</span>
                                            <input type="number" name="limit_time" value="{{cacheGlobalSettingsByKey('limit_time')}}" class="form-control secure-config-inline__field">
                                            <span class="secure-config-inline__suffix">分钟</span>
                                        </div>
                                    </div>

                                    <div class="secure-config-field secure-config-field--full">
                                        <div class="secure-config-help">账号锁定已接入后台登录失败计数逻辑，按“账号 + IP”维度累计失败次数，登录成功后会自动清零。</div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>自动封禁阈值</label>
                                        <div class="secure-config-inline">
                                            <span class="secure-config-inline__prefix">同一 IP 连续失败</span>
                                            <input type="number" name="ip_auto_ban_threshold" value="{{$autoBanThreshold}}" class="form-control secure-config-inline__field">
                                            <span class="secure-config-inline__suffix">次</span>
                                        </div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>自动封禁时长</label>
                                        <div class="secure-config-inline">
                                            <span class="secure-config-inline__prefix">封禁</span>
                                            <input type="number" name="ip_auto_ban_minutes" value="{{$autoBanMinutes}}" class="form-control secure-config-inline__field">
                                            <span class="secure-config-inline__suffix">分钟</span>
                                        </div>
                                    </div>

                                    <div class="secure-config-field secure-config-field--full">
                                        <div class="secure-config-help">自动封禁命中后会写入 `system_ip_access_rules`，并记录命中次数与最后命中时间。白名单 IP 不会被自动封禁。</div>
                                    </div>
                                </div>
                            </div>

                            <div class="secure-config-section">
                                <h3 class="secure-config-section__title">前台流量防护</h3>
                                <div class="secure-config-section__desc">对所有模块的前台请求生效（后台、API 除外规则见下方说明）。超限的 IP 会被自动写入 `system_ip_access_rules` 并封禁一段时间。</div>

                                <div class="secure-config-grid">
                                    <div class="secure-config-field secure-config-field--full">
                                        <label>启用前台防护</label>
                                        <div class="secure-config-radio-group">
                                            <label class="radio-inline">
                                                <input type="radio" name="front_guard_enabled" class="styled h-radio" value="0" @if(!$frontGuardEnabled) checked @endif>
                                                <span class="h-span-val">关闭</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="front_guard_enabled" class="styled h-radio" value="1" @if($frontGuardEnabled) checked @endif>
                                                <span class="h-span-val">开启</span>
                                            </label>
                                        </div>
                                        <div class="secure-config-help">开启后，单 IP 每分钟请求数超过阈值会被限流；在统计窗口内反复超限，会自动封禁。</div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>每分钟请求上限</label>
                                        <div class="secure-config-inline">
                                            <span class="secure-config-inline__prefix">单 IP 每分钟最多</span>
                                            <input type="number" name="front_guard_rate_limit" value="{{ $frontGuardRateLimit }}" class="form-control secure-config-inline__field">
                                            <span class="secure-config-inline__suffix">次</span>
                                        </div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>违规统计窗口</label>
                                        <div class="secure-config-inline">
                                            <span class="secure-config-inline__prefix">在</span>
                                            <input type="number" name="front_guard_violation_window" value="{{ $frontGuardWindow }}" class="form-control secure-config-inline__field">
                                            <span class="secure-config-inline__suffix">分钟内累计</span>
                                        </div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>自动封禁阈值</label>
                                        <div class="secure-config-inline">
                                            <span class="secure-config-inline__prefix">窗口内违规</span>
                                            <input type="number" name="front_guard_violation_limit" value="{{ $frontGuardViolationLimit }}" class="form-control secure-config-inline__field">
                                            <span class="secure-config-inline__suffix">次</span>
                                        </div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>自动封禁时长</label>
                                        <div class="secure-config-inline">
                                            <span class="secure-config-inline__prefix">封禁</span>
                                            <input type="number" name="front_guard_ban_minutes" value="{{ $frontGuardBanMinutes }}" class="form-control secure-config-inline__field">
                                            <span class="secure-config-inline__suffix">分钟</span>
                                        </div>
                                    </div>

                                    <div class="secure-config-field secure-config-field--full">
                                        <div class="secure-config-help">
                                            封禁记录落到 `system_ip_access_rules`（scope=front），可以在
                                            <a href="{{ moduleAdminJump($moduleName, 'secure/ipRules') }}" target="_blank">前台 IP 规则</a>
                                            里查看和移除。白名单 IP、后台请求、静态资源（js/css/图片等）都不参与限流计数。
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="secure-config-section">
                                <h3 class="secure-config-section__title">验证码与校验</h3>
                                <div class="secure-config-section__desc">后台图形验证码和 Google Authenticator 都会接入登录链路，前台提交验证码当前仍以基础设置中的 `open_captcha` 为实际入口。</div>

                                <div class="secure-config-grid">
                                    <div class="secure-config-field">
                                        <label>后台登录验证码</label>
                                        <div class="secure-config-radio-group">
                                            <label class="radio-inline">
                                                <input type="radio" name="admin_login_code" class="styled h-radio" value="1" @if($adminLoginCode==1) checked @endif>
                                                <span class="h-span-val">开启</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="admin_login_code" class="styled h-radio" value="0" @if($adminLoginCode==0) checked @endif>
                                                <span class="h-span-val">关闭</span>
                                            </label>
                                        </div>
                                        <div class="secure-config-help">该项已接入后台登录控制器。</div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>Google Authenticator</label>
                                        <div class="secure-config-radio-group">
                                            <label class="radio-inline">
                                                <input type="radio" name="admin_google_authenticator_mode" class="styled h-radio" value="off" @if($googleAuthenticatorMode === 'off' || $googleAuthenticatorMode === '') checked @endif>
                                                <span class="h-span-val">关闭</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="admin_google_authenticator_mode" class="styled h-radio" value="optional" @if($googleAuthenticatorMode === 'optional') checked @endif>
                                                <span class="h-span-val">可选开启</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="admin_google_authenticator_mode" class="styled h-radio" value="required" @if($googleAuthenticatorMode === 'required') checked @endif>
                                                <span class="h-span-val">强制开启</span>
                                            </label>
                                        </div>
                                        <div class="secure-config-help">可选开启：已绑定账号需要二次验证；强制开启：未绑定账号在密码通过后必须先完成绑定。</div>
                                    </div>

                                    <div class="secure-config-field secure-config-field--full">
                                        <label>Google Authenticator 签发标识</label>
                                        <input type="text" name="admin_google_authenticator_issuer" value="{{$googleAuthenticatorIssuer ?: env('APP_NAME', 'MxzCMS Admin')}}" class="form-control" placeholder="MxzCMS Admin">
                                        <div class="secure-config-help">会显示在 Google Authenticator 中，建议填写站点或后台名称，避免管理员手机里多个站点难以区分。</div>
                                    </div>

                                    <div class="secure-config-field secure-config-field--full">
                                        <label>当前管理员动态口令状态</label>
                                        <div class="secure-config-inline">
                                            <span class="secure-config-chip {{ $currentAdminHasGoogleAuthenticator ? 'is-active' : 'is-pending' }}">{{ $currentAdminHasGoogleAuthenticator ? '已绑定' : '未绑定' }}</span>
                                            <a href="{{$googleAuthenticatorManageUrl}}" class="btn btn-default">管理当前账号 Google Authenticator</a>
                                        </div>
                                        <div class="secure-config-help">绑定页支持二维码和手工密钥两种方式，验证成功后才会正式写入后台账号。</div>
                                    </div>

                                    <div class="secure-config-field">
                                        <label>前台提交验证码</label>
                                        <div class="secure-config-radio-group">
                                            <label class="radio-inline">
                                                <input type="radio" name="home_submit_code" class="styled h-radio" value="1" @if($homeSubmitCode==1) checked @endif>
                                                <span class="h-span-val">开启</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="home_submit_code" class="styled h-radio" value="0" @if($homeSubmitCode==0) checked @endif>
                                                <span class="h-span-val">关闭</span>
                                            </label>
                                        </div>
                                        <div class="secure-config-help is-warning">当前前台登录/注册验证码实际由基本配置中的 `open_captcha` 控制，这里仍属于预留开关。</div>
                                    </div>
                                </div>
                            </div>

                            <div class="secure-config-actions">
                                <button type="button" class="btn btn-info h-sub">保存安全配置</button>
                                <a href="{{moduleAdminJump($moduleName,'base/baseConfig')}}" class="btn btn-default">查看基础设置</a>
                            </div>
                        </form>
                    </div>

                    <div>
                        <div class="secure-config-sidecard">
                            <h4 class="secure-config-sidecard__title">生效范围</h4>
                            <div class="secure-config-sidecard__meta"><strong>后台安全：</strong><span class="secure-config-chip is-active">已接入</span> 登录入口、后台验证码、密码加密 KEY</div>
                            <div class="secure-config-sidecard__meta"><strong>会话层：</strong><span class="secure-config-chip is-active">已接入</span> Cookie、Session 驱动、时长、加密</div>
                            <div class="secure-config-sidecard__meta"><strong>访问控制：</strong><span class="secure-config-chip is-active">已接入</span> 白名单优先、其它 IP 拦截、IP 黑名单、动态自动封禁、后台账号锁定</div>
                            <div class="secure-config-sidecard__meta"><strong>动态口令：</strong><span class="secure-config-chip is-active">已接入</span> Google Authenticator 绑定、校验、强制绑定策略</div>
                            <div class="secure-config-sidecard__meta"><strong>策略预留：</strong><span class="secure-config-chip is-pending">待接入</span> 前台提交验证码</div>
                        </div>

                        <div class="secure-config-sidecard">
                            <h4 class="secure-config-sidecard__title">当前状态</h4>
                            <div class="secure-config-sidecard__meta"><strong>Session Cookie：</strong>{{$sessionCookie ?: '未设置'}}</div>
                            <div class="secure-config-sidecard__meta"><strong>Session 域名：</strong>{{$sessionDomain ?: '留空'}}</div>
                            <div class="secure-config-sidecard__meta"><strong>Session 加密：</strong>{{$sessionEncrypt ? '已开启' : '未开启'}}</div>
                            <div class="secure-config-sidecard__meta"><strong>后台入口预览：</strong>{{ $adminLoginEntrance ? url('admin/login/' . $adminLoginEntrance) : url('admin/login') }}</div>
                            <div class="secure-config-sidecard__meta"><strong>IP 模式：</strong>{{ $adminIpAccessMode === 'whitelist_only' ? '仅白名单可访问' : '允许所有 IP' }}</div>
                            <div class="secure-config-sidecard__meta"><strong>Google Authenticator：</strong>{{ $googleAuthenticatorMode === 'required' ? '强制开启' : ($googleAuthenticatorMode === 'optional' ? '可选开启' : '已关闭') }}</div>
                            <div class="secure-config-sidecard__meta"><strong>动态封禁：</strong>{{$activeAutoBanCount}} 条生效中</div>
                        </div>

                        <div class="secure-config-sidecard">
                            <h4 class="secure-config-sidecard__title">维护建议</h4>
                            <div class="secure-config-sidecard__desc">优先保证会话、登录入口、后台 IP 模式和 Google Authenticator 策略配置准确；黑名单会同时作用于后台登录入口和后台已登录路由，白名单优先于所有封禁规则。</div>
                        </div>
                    </div>
                </div>

                @include(moduleAdminTemplate($moduleName)."public.footer")
            </div>
        </div>
    </div>
</div>

@include(moduleAdminTemplate($moduleName)."public.js")
<script>
    function generateAdminLoginEntranceValue() {
        var chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        var value = 'adm-';
        for (var i = 0; i < 8; i++) {
            value += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return value;
    }

    function syncAdminLoginEntrancePreview() {
        var raw = $('#adminLoginEntrance').val() || '';
        var normalized = raw.replace(/[^a-zA-Z0-9\-_]/g, '');
        if (raw !== normalized) {
            $('#adminLoginEntrance').val(normalized);
        }

        var baseUrl = @json(url('admin/login'));
        $('#adminLoginEntrancePreview').text(normalized ? (baseUrl + '/' + normalized) : baseUrl);
    }

    $('#adminLoginEntrance').on('input', syncAdminLoginEntrancePreview);

    $('#generateAdminLoginEntrance').click(function () {
        $('#adminLoginEntrance').val(generateAdminLoginEntranceValue());
        syncAdminLoginEntrancePreview();
    });

    $('.h-sub').click(function () {
        layer.load(1);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            method: 'post',
            url: "{{moduleAdminJump($moduleName,'secure/toolSubmit')}}",
            data: new FormData($('#myForm')[0]),
            dataType: 'json',
            cache: false,
            processData: false,
            contentType: false,
            success: function (res) {
                layer.closeAll();
                if (res.status == 200) {
                    layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                        window.location.reload();
                    });
                } else {
                    layer.msg(res.msg, {icon: 2});
                }
            },
            error: function () {
                layer.closeAll();
                layer.msg("系统错误，请稍后重试", {icon: 5});
            }
        });
    });

    $(function () {
        syncAdminLoginEntrancePreview();
    });
</script>
<script type="text/javascript" src="{{asset("assets/module")}}/js/plugins/forms/selects/select2.min.js"></script>
<script type="text/javascript" src="{{asset("assets/module")}}/js/pages/form_select2.js"></script>
</body>
</html>
