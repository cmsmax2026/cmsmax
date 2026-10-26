@include(moduleAdminTemplate($moduleName)."public.header")
<style>
    .ip-rules-hero { margin-bottom: 20px; padding: 22px 24px; border: 1px solid #e5e7eb; border-radius: 16px; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); box-shadow: 0 16px 40px rgba(15, 23, 42, .06); }
    .ip-rules-hero__title { margin: 0; font-size: 22px; font-weight: 700; color: #111827; }
    .ip-rules-hero__desc { margin-top: 8px; color: #6b7280; line-height: 1.8; max-width: 860px; }
    .ip-rules-overview { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-top: 18px; }
    .ip-rules-overview__item { padding: 14px 16px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; }
    .ip-rules-overview__name { color: #6b7280; font-size: 13px; }
    .ip-rules-overview__value { margin-top: 6px; font-size: 22px; font-weight: 700; color: #111827; }
    .ip-rules-layout { display: grid; gap: 18px; }
    .ip-rules-card { padding: 20px 22px; border: 1px solid #e5e7eb; border-radius: 16px; background: #fff; box-shadow: 0 16px 40px rgba(15, 23, 42, .06); }
    .ip-rules-card__title { margin: 0; font-size: 16px; font-weight: 700; color: #111827; }
    .ip-rules-card__desc { margin-top: 6px; color: #6b7280; font-size: 13px; line-height: 1.7; }
    .ip-rules-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-top: 16px; }
    .ip-rules-field { display: grid; gap: 6px; }
    .ip-rules-field--full { grid-column: 1 / -1; }
    .ip-rules-field label { font-weight: 600; color: #374151; font-size: 13px; }
    .ip-rules-field .form-control { width: 100%; box-sizing: border-box; }
    .ip-rules-help { margin-top: 14px; padding: 12px 14px; border-radius: 10px; background: #f8fafc; border: 1px solid #e5e7eb; color: #475569; font-size: 13px; line-height: 1.8; }
    .ip-rules-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 16px; }
    .ip-rules-table th, .ip-rules-table td { padding: 10px 12px; border-bottom: 1px solid #eef2f7; text-align: left; vertical-align: middle; }
    .ip-rules-table th { background: #f8fafc; color: #475569; font-weight: 700; white-space: nowrap; }
    .ip-rules-table tr:hover td { background: #fafcff; }
    .ip-rules-tag { display: inline-flex; align-items: center; height: 22px; padding: 0 9px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #eff6ff; color: #1d4ed8; }
    .ip-rules-tag--gray { background: #f1f5f9; color: #64748b; }
    .ip-rules-tag--red { background: #fef2f2; color: #dc2626; }
    .ip-rules-tag--green { background: #ecfdf3; color: #047857; }
    .ip-rules-tag--amber { background: #fff7ed; color: #c2410c; }
    .ip-rules-empty { padding: 30px 0; text-align: center; color: #94a3b8; }
    .ip-rules-filter { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-top: 14px; }
    .ip-rules-filter .form-control { min-width: 150px; }
    .ip-rules-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
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

                <div class="ip-rules-hero">
    <h1 class="ip-rules-hero__title">前台 IP 规则</h1>
    <div class="ip-rules-hero__desc">
        规则记录存在 `system_ip_access_rules` 表，所有模块的前台请求共享这一份。
        白名单（allow）优先放行且不计数；拒绝（deny）命中即拦截。自动封禁产生的记录到期后自动失效。
    </div>
    <div class="ip-rules-overview">
        <div class="ip-rules-overview__item">
            <div class="ip-rules-overview__name">规则总数</div>
            <div class="ip-rules-overview__value">{{ $stats['total'] }}</div>
        </div>
        <div class="ip-rules-overview__item">
            <div class="ip-rules-overview__name">生效中</div>
            <div class="ip-rules-overview__value">{{ $stats['active'] }}</div>
        </div>
        <div class="ip-rules-overview__item">
            <div class="ip-rules-overview__name">自动封禁</div>
            <div class="ip-rules-overview__value">{{ $stats['auto'] }}</div>
        </div>
        <div class="ip-rules-overview__item">
            <div class="ip-rules-overview__name">已过期</div>
            <div class="ip-rules-overview__value">{{ $stats['expired'] }}</div>
        </div>
        <div class="ip-rules-overview__item">
            <div class="ip-rules-overview__name">前台防护</div>
            <div class="ip-rules-overview__value">{{ $guardEnabled ? '开启' : '关闭' }}</div>
        </div>
    </div>
</div>

<div class="ip-rules-layout">
    <div class="ip-rules-card">
        <h2 class="ip-rules-card__title">添加规则</h2>
        <div class="ip-rules-card__desc">支持单 IP（`203.0.113.5`）、CIDR（`203.0.113.0/24`）、IP 段（`203.0.113.1-203.0.113.20`）。</div>

        <form id="ipRuleForm">
            {{ csrf_field() }}
            <div class="ip-rules-grid">
                <div class="ip-rules-field">
                    <label for="scope">作用域</label>
                    <select class="form-control" id="scope" name="scope">
                        <option value="front">前台（所有模块）</option>
                        <option value="all">全部（前台 + 后台）</option>
                        <option value="admin">仅后台</option>
                    </select>
                </div>
                <div class="ip-rules-field">
                    <label for="rule_type">类型</label>
                    <select class="form-control" id="rule_type" name="rule_type">
                        <option value="deny">拒绝（封禁）</option>
                        <option value="allow">允许（白名单）</option>
                    </select>
                </div>
                <div class="ip-rules-field">
                    <label for="rule_value">IP / 网段</label>
                    <input type="text" class="form-control" id="rule_value" name="rule_value" placeholder="203.0.113.5">
                </div>
                <div class="ip-rules-field">
                    <label for="minutes">有效期（分钟）</label>
                    <input type="number" class="form-control" id="minutes" name="minutes" value="0" min="0">
                </div>
                <div class="ip-rules-field ip-rules-field--full">
                    <label for="reason">备注原因</label>
                    <input type="text" class="form-control" id="reason" name="reason" placeholder="例如：刷帖攻击">
                </div>
            </div>

            <div class="ip-rules-actions" style="margin-top:14px;">
                <button type="button" class="btn btn-primary" id="addRuleBtn">添加规则</button>
                <span style="color:#64748b; font-size:13px;">有效期填 0 表示长期有效。</span>
            </div>
        </form>
    </div>

    <div class="ip-rules-card">
        <h2 class="ip-rules-card__title">批量导入</h2>
        <div class="ip-rules-card__desc">每行一条规则，支持单 IP、CIDR、IP 段；空行与 <code>#</code> / <code>//</code> 开头的注释行会被跳过。</div>

        <form id="batchIpRuleForm">
            {{ csrf_field() }}
            <div class="ip-rules-grid">
                <div class="ip-rules-field">
                    <label for="batch_scope">作用域</label>
                    <select class="form-control" id="batch_scope" name="batch_scope">
                        <option value="front">前台（所有模块）</option>
                        <option value="all">全部（前台 + 后台）</option>
                        <option value="admin">仅后台</option>
                    </select>
                </div>
                <div class="ip-rules-field">
                    <label for="batch_rule_type">类型</label>
                    <select class="form-control" id="batch_rule_type" name="batch_rule_type">
                        <option value="deny">拒绝（封禁）</option>
                        <option value="allow">允许（白名单）</option>
                    </select>
                </div>
                <div class="ip-rules-field">
                    <label for="batch_minutes">有效期（分钟）</label>
                    <input type="number" class="form-control" id="batch_minutes" name="batch_minutes" value="0" min="0">
                </div>
                <div class="ip-rules-field ip-rules-field--full">
                    <label for="batch_values">规则列表</label>
                    <textarea class="form-control" id="batch_values" name="batch_values" rows="6" placeholder="203.0.113.5&#10;203.0.113.0/24&#10;203.0.113.1-203.0.113.20&#10;# 这行是注释"></textarea>
                </div>
            </div>

            <div class="ip-rules-actions" style="margin-top:14px;">
                <button type="button" class="btn btn-primary" id="batchAddRuleBtn">批量添加</button>
                <span style="color:#64748b; font-size:13px;">导入时会自动识别单 IP、CIDR 与 IP 段。</span>
            </div>
        </form>
    </div>

    <div class="ip-rules-card">
        <h2 class="ip-rules-card__title">规则列表</h2>
        <div class="ip-rules-card__desc">命中次数和最后命中时间由规则被触发时自动更新。</div>

        <form class="ip-rules-filter" method="get" action="{{ moduleAdminJump($moduleName, 'secure/ipRules') }}">
            <div>
                <label style="font-size:12px; color:#64748b;">作用域</label><br>
                <select class="form-control" name="scope">
                    <option value="">全部</option>
                    <option value="front" @selected($scope === 'front')>前台</option>
                    <option value="admin" @selected($scope === 'admin')>后台</option>
                    <option value="all" @selected($scope === 'all')>全部</option>
                </select>
            </div>
            <div>
                <label style="font-size:12px; color:#64748b;">IP 关键词</label><br>
                <input type="text" class="form-control" name="keyword" value="{{ $keyword }}" placeholder="按规则值搜索">
            </div>
            <button type="submit" class="btn btn-default">筛选</button>
            <a class="btn btn-default" href="{{ moduleAdminJump($moduleName, 'secure/ipRules') }}">重置</a>
            <button type="button" class="btn btn-default" id="clearExpiredBtn">清理已过期</button>
            <a class="btn btn-default" href="{{ moduleAdminJump($moduleName, 'secure/ipBlockLogs') }}">拦截日志</a>
        </form>

        <table class="ip-rules-table">
            <thead>
            <tr>
                <th style="width:56px;">ID</th>
                <th style="width:88px;">作用域</th>
                <th style="width:88px;">类型</th>
                <th>规则值</th>
                <th style="width:88px;">来源</th>
                <th style="width:64px;">阶梯</th>
                <th>原因</th>
                <th style="width:76px;">命中</th>
                <th style="width:150px;">到期</th>
                <th style="width:88px;">状态</th>
                <th style="width:88px;">操作</th>
            </tr>
            </thead>
            <tbody>
            @forelse($rules as $rule)
                @php($expired = !empty($rule->expires_at) && strtotime((string) $rule->expires_at) <= time())
                <tr>
                    <td>{{ $rule->id }}</td>
                    <td>
                        @if($rule->scope === 'front')
                            <span class="ip-rules-tag">前台</span>
                        @elseif($rule->scope === 'admin')
                            <span class="ip-rules-tag ip-rules-tag--gray">后台</span>
                        @else
                            <span class="ip-rules-tag ip-rules-tag--amber">全部</span>
                        @endif
                    </td>
                    <td>
                        @if($rule->rule_type === 'allow')
                            <span class="ip-rules-tag ip-rules-tag--green">允许</span>
                        @else
                            <span class="ip-rules-tag ip-rules-tag--red">拒绝</span>
                        @endif
                    </td>
                    <td>{{ $rule->rule_value }}</td>
                    <td>{{ $rule->source_type === 'auto' ? '自动' : '手动' }}</td>
                    <td>
                        @if($rule->source_type === 'auto')
                            <span class="ip-rules-tag ip-rules-tag--red" title="已被自动封禁 {{ (int) $rule->ban_count }} 次">第 {{ (int) $rule->ban_count }} 次</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $rule->reason ?: '—' }}</td>
                    <td>{{ (int) $rule->hit_count }}</td>
                    <td>{{ $rule->expires_at ?: '长期' }}</td>
                    <td>
                        @if($expired)
                            <span class="ip-rules-tag ip-rules-tag--gray">已过期</span>
                        @elseif((int) $rule->status === 1)
                            <span class="ip-rules-tag ip-rules-tag--green">生效</span>
                        @else
                            <span class="ip-rules-tag ip-rules-tag--gray">停用</span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-xs remove-rule" data-id="{{ $rule->id }}">移除</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="ip-rules-empty">还没有规则。上面添加一条，或者开启前台防护让系统自动封禁。</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @if($rules->hasPages())
            <div style="margin-top:16px;">{{ $rules->links('themes.default.public.pagination', ['data' => ['side_num' => 2, 'page_position' => 'left']]) }}</div>
        @endif
    </div>

                @include(moduleAdminTemplate($moduleName)."public.footer")
            </div>
        </div>
    </div>
</div>

@include(moduleAdminTemplate($moduleName)."public.js")
<script>
(function () {
    var token = "{{ csrf_token() }}";
    var baseUrl = "{{ moduleAdminJump($moduleName, 'secure/') }}";

    function post(action, data, onOk) {
        var body = new FormData();
        Object.keys(data).forEach(function (key) {
            body.append(key, data[key]);
        });

        fetch(baseUrl + action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: body,
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json();
        }).then(function (res) {
            if (res && (res.status === 200 || res.status === '200')) {
                if (onOk) { onOk(res); }
                location.reload();
            } else {
                alert((res && res.msg) ? res.msg : '操作失败');
            }
        }).catch(function () {
            alert('网络异常，请重试');
        });
    }

    var addBtn = document.getElementById('addRuleBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            var value = document.getElementById('rule_value').value.trim();
            if (value === '') {
                alert('请填写 IP 或网段');
                return;
            }
            post('addIpRule', {
                scope: document.getElementById('scope').value,
                rule_type: document.getElementById('rule_type').value,
                rule_value: value,
                minutes: document.getElementById('minutes').value,
                reason: document.getElementById('reason').value
            });
        });
    }

    var batchAddBtn = document.getElementById('batchAddRuleBtn');
    if (batchAddBtn) {
        batchAddBtn.addEventListener('click', function () {
            var values = document.getElementById('batch_values').value.trim();
            if (values === '') {
                alert('请填写要批量导入的规则列表');
                return;
            }
            post('addIpRule', {
                scope: document.getElementById('batch_scope').value,
                rule_type: document.getElementById('batch_rule_type').value,
                batch_values: values,
                minutes: document.getElementById('batch_minutes').value,
                reason: '后台批量导入'
            });
        });
    }

    document.querySelectorAll('.remove-rule').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!confirm('确定移除这条规则？')) {
                return;
            }
            post('removeIpRule', { id: button.getAttribute('data-id') });
        });
    });

    var clearBtn = document.getElementById('clearExpiredBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (!confirm('清理所有已过期的规则？')) {
                return;
            }
            post('clearExpiredIpRules', {});
        });
    }
})();
</script>
