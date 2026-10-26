@include(moduleAdminTemplate($moduleName)."public.header")
<style>
    .ip-logs-hero { margin-bottom: 20px; padding: 22px 24px; border: 1px solid #e5e7eb; border-radius: 16px; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); box-shadow: 0 16px 40px rgba(15, 23, 42, .06); }
    .ip-logs-hero__title { margin: 0; font-size: 22px; font-weight: 700; color: #111827; }
    .ip-logs-hero__desc { margin-top: 8px; color: #6b7280; line-height: 1.8; max-width: 860px; }
    .ip-logs-overview { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-top: 18px; }
    .ip-logs-overview__item { padding: 14px 16px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; }
    .ip-logs-overview__name { color: #6b7280; font-size: 13px; }
    .ip-logs-overview__value { margin-top: 6px; font-size: 22px; font-weight: 700; color: #111827; }
    .ip-logs-card { padding: 20px 22px; border: 1px solid #e5e7eb; border-radius: 16px; background: #fff; box-shadow: 0 16px 40px rgba(15, 23, 42, .06); }
    .ip-logs-card__title { margin: 0; font-size: 16px; font-weight: 700; color: #111827; }
    .ip-logs-card__desc { margin-top: 6px; color: #6b7280; font-size: 13px; line-height: 1.7; }
    .ip-logs-filter { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-top: 14px; }
    .ip-logs-filter .form-control { min-width: 160px; }
    .ip-logs-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 16px; }
    .ip-logs-table th, .ip-logs-table td { padding: 10px 12px; border-bottom: 1px solid #eef2f7; text-align: left; vertical-align: middle; }
    .ip-logs-table th { background: #f8fafc; color: #475569; font-weight: 700; white-space: nowrap; }
    .ip-logs-table tr:hover td { background: #fafcff; }
    .ip-logs-tag { display: inline-flex; align-items: center; height: 22px; padding: 0 9px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #eff6ff; color: #1d4ed8; }
    .ip-logs-tag--red { background: #fef2f2; color: #dc2626; }
    .ip-logs-tag--amber { background: #fff7ed; color: #c2410c; }
    .ip-logs-tag--gray { background: #f1f5f9; color: #64748b; }
    .ip-logs-empty { padding: 30px 0; text-align: center; color: #94a3b8; }
    .ip-logs-json { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #64748b; font-size: 12px; }
    .ip-logs-actions { display: flex; gap: 8px; }
    .ip-logs-dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; margin-bottom: 20px; }
    .ip-logs-chart { display: flex; align-items: flex-end; gap: 4px; height: 140px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; }
    .ip-logs-chart__bar { flex: 1; min-width: 8px; background: #3b82f6; border-radius: 4px 4px 0 0; position: relative; transition: opacity .2s; }
    .ip-logs-chart__bar:hover { opacity: .8; }
    .ip-logs-chart__tooltip { position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: 6px; padding: 4px 8px; border-radius: 6px; background: #1e293b; color: #fff; font-size: 11px; white-space: nowrap; pointer-events: none; opacity: 0; transition: opacity .2s; }
    .ip-logs-chart__bar:hover .ip-logs-chart__tooltip { opacity: 1; }
    .ip-logs-top { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 12px; }
    .ip-logs-top th, .ip-logs-top td { padding: 8px 10px; border-bottom: 1px solid #eef2f7; text-align: left; }
    .ip-logs-top th { color: #64748b; font-weight: 600; }
    .ip-logs-top__bar { height: 6px; border-radius: 3px; background: #3b82f6; min-width: 2px; }
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

                <div class="ip-logs-hero">
                    <h1 class="ip-logs-hero__title">IP 拦截日志</h1>
                    <div class="ip-logs-hero__desc">
                        记录所有被前台/后台 IP 规则拒绝、自动封禁或频率超限的事件。数据写入 <code>system_ip_block_logs</code> 表，不依赖 Logger 插件也能查询。
                    </div>
                    @if($hasTable)
                        <div class="ip-logs-overview">
                            <div class="ip-logs-overview__item">
                                <div class="ip-logs-overview__name">总拦截次数</div>
                                <div class="ip-logs-overview__value">{{ \Modules\System\Models\SystemIpBlockLog::query()->count() }}</div>
                            </div>
                            <div class="ip-logs-overview__item">
                                <div class="ip-logs-overview__name">今日拦截</div>
                                <div class="ip-logs-overview__value">{{ \Modules\System\Models\SystemIpBlockLog::query()->where('created_at', '>=', now()->startOfDay()->format('Y-m-d H:i:s'))->count() }}</div>
                            </div>
                            <div class="ip-logs-overview__item">
                                <div class="ip-logs-overview__name">独立 IP 数</div>
                                <div class="ip-logs-overview__value">{{ \Modules\System\Models\SystemIpBlockLog::query()->distinct('ip')->count('ip') }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                @if($hasTable)
                    <div class="ip-logs-dashboard">
                        <div class="ip-logs-card">
                            <h2 class="ip-logs-card__title">Top 10 攻击 IP</h2>
                            <div class="ip-logs-card__desc">按拦截次数排序，可点击 IP 快速筛选。</div>
                            @php($topMax = $topIps->isNotEmpty() ? max($topIps->pluck('total')->toArray()) : 1)
                            <table class="ip-logs-top">
                                <thead>
                                <tr>
                                    <th>IP</th>
                                    <th style="width:60px; text-align:right;">次数</th>
                                    <th style="width:90px;">占比</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($topIps as $item)
                                    <tr>
                                        <td>
                                            <a href="{{ moduleAdminJump($moduleName, 'secure/ipBlockLogs') }}?ip={{ urlencode($item->ip) }}">{{ $item->ip }}</a>
                                        </td>
                                        <td style="text-align:right;">{{ $item->total }}</td>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:8px;">
                                                <div class="ip-logs-top__bar" style="width:{{ min(100, round($item->total / $topMax * 100)) }}%;"></div>
                                                <span style="color:#64748b;font-size:12px;">{{ min(100, round($item->total / $topMax * 100)) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="ip-logs-empty">暂无数据</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="ip-logs-card">
                            <h2 class="ip-logs-card__title">最近 24 小时趋势</h2>
                            <div class="ip-logs-card__desc">每小时拦截次数分布。</div>
                            @php($hourMax = max(1, max($hourly)))
                            <div class="ip-logs-chart">
                                @foreach($hourly as $hour => $count)
                                    <div class="ip-logs-chart__bar" style="height:{{ min(100, round($count / $hourMax * 100)) }}%;">
                                        <div class="ip-logs-chart__tooltip">{{ $hour }}: {{ $count }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="ip-logs-card">
                            <h2 class="ip-logs-card__title">最近 7 天趋势</h2>
                            <div class="ip-logs-card__desc">每日拦截次数分布。</div>
                            @php($dayMax = max(1, max($daily)))
                            <div class="ip-logs-chart">
                                @foreach($daily as $day => $count)
                                    <div class="ip-logs-chart__bar" style="height:{{ min(100, round($count / $dayMax * 100)) }}%;">
                                        <div class="ip-logs-chart__tooltip">{{ $day }}: {{ $count }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="ip-logs-card">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <div>
                            <h2 class="ip-logs-card__title">日志列表</h2>
                            <div class="ip-logs-card__desc">按 IP 或来源筛选，查看每次拦截的详情。</div>
                        </div>
                        <div class="ip-logs-actions">
                            <a class="btn btn-default" href="{{ moduleAdminJump($moduleName, 'secure/ipRules') }}">返回 IP 规则</a>
                        </div>
                    </div>

                    @if(!$hasTable)
                        <div class="alert alert-warning" style="margin-top:14px;">
                            日志表 <code>system_ip_block_logs</code> 尚未创建，请先执行迁移或安装更新脚本。
                        </div>
                    @else
                        <form class="ip-logs-filter" method="get" action="{{ moduleAdminJump($moduleName, 'secure/ipBlockLogs') }}">
                            <div>
                                <label style="font-size:12px; color:#64748b;">IP 关键词</label><br>
                                <input type="text" class="form-control" name="ip" value="{{ $ip }}" placeholder="按 IP 搜索">
                            </div>
                            <div>
                                <label style="font-size:12px; color:#64748b;">拦截来源</label><br>
                                <select class="form-control" name="source">
                                    <option value="">全部</option>
                                    @foreach($sources as $key => $label)
                                        <option value="{{ $key }}" @selected($source === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-default">筛选</button>
                            <a class="btn btn-default" href="{{ moduleAdminJump($moduleName, 'secure/ipBlockLogs') }}">重置</a>
                        </form>

                        <table class="ip-logs-table">
                            <thead>
                            <tr>
                                <th style="width:56px;">ID</th>
                                <th style="width:120px;">时间</th>
                                <th style="width:130px;">IP</th>
                                <th style="width:88px;">作用域</th>
                                <th style="width:110px;">来源</th>
                                <th style="width:76px;">规则 ID</th>
                                <th>原因 / 路径</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->created_at }}</td>
                                    <td>{{ $log->ip }}</td>
                                    <td>
                                        @if($log->scope === 'front')
                                            <span class="ip-logs-tag">前台</span>
                                        @elseif($log->scope === 'admin')
                                            <span class="ip-logs-tag ip-logs-tag--gray">后台</span>
                                        @else
                                            <span class="ip-logs-tag ip-logs-tag--amber">全部</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->source === 'rate_limit')
                                            <span class="ip-logs-tag ip-logs-tag--amber">{{ $sources[$log->source] ?? $log->source }}</span>
                                        @elseif($log->source === 'auto_ban')
                                            <span class="ip-logs-tag ip-logs-tag--red">{{ $sources[$log->source] ?? $log->source }}</span>
                                        @else
                                            <span class="ip-logs-tag ip-logs-tag--gray">{{ $sources[$log->source] ?? $log->source }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->rule_id > 0 ? $log->rule_id : '—' }}</td>
                                    <td>
                                        <div>{{ $log->reason ?: '—' }}</div>
                                        <div class="ip-logs-json" title="{{ $log->request_path }}">{{ $log->request_path }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="ip-logs-empty">暂无拦截日志。触发封禁或频率超限后会自动记录。</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        @if($logs->hasPages())
                            <div style="margin-top:16px;">{{ $logs->links('themes.default.public.pagination', ['data' => ['side_num' => 2, 'page_position' => 'left']]) }}</div>
                        @endif
                    @endif
                </div>

                @include(moduleAdminTemplate($moduleName)."public.footer")
            </div>
        </div>
    </div>
</div>

@include(moduleAdminTemplate($moduleName)."public.js")
</body>
