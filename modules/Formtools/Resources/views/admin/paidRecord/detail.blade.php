@include(moduleAdminTemplate($moduleName)."public.header")
<style>
    .ft-paid-detail {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 18px;
    }
    .ft-paid-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 20px 22px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }
    .ft-paid-card__title {
        margin: 0 0 14px;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }
    .ft-paid-card__title small { font-weight: 400; color: #94a3b8; margin-left: 6px; }
    .ft-paid-info {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 10px 16px;
    }
    .ft-paid-info__label { color: #64748b; font-size: 12px; font-weight: 600; }
    .ft-paid-info__value { color: #0f172a; font-size: 13px; word-break: break-word; }
    .ft-paid-info__value--mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        background: #f8fafc;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
    }
    .ft-paid-extra {
        background: #0f172a;
        color: #e2e8f0;
        padding: 14px;
        border-radius: 10px;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 12px;
        line-height: 1.7;
        white-space: pre-wrap;
        word-break: break-all;
        max-height: 320px;
        overflow: auto;
    }
    .ft-paid-back {
        display: inline-flex;
        align-items: center;
        padding: 7px 14px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 14px;
    }
    .ft-paid-back:hover { background: #e2e8f0; color: #1e293b; }
    .ft-paid-actions { display: flex; gap: 8px; margin-top: 14px; }
    .ft-paid-tag {
        display: inline-flex;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }
    .ft-paid-tag--paid { background: #dcfce7; color: #166534; }
    .ft-paid-tag--pending { background: #fef3c7; color: #92400e; }
    .ft-paid-tag--refunded { background: #fee2e2; color: #991b1b; }
    .ft-paid-tag--expired { background: #e2e8f0; color: #475569; }
    .ft-paid-tag--canceled { background: #f1f5f9; color: #64748b; }
    .ft-paid-tag--unknown { background: #e2e8f0; color: #475569; }
    @media (max-width: 1199px) {
        .ft-paid-detail { grid-template-columns: 1fr; }
    }
</style>
<body>
@include(moduleAdminTemplate($moduleName)."public.nav")
<div class="page-container">
    <div class="page-content">
        @include(moduleAdminTemplate($moduleName)."public.left")
        <div class="content-wrapper">
            <div class="content" style="margin-top: 1rem;">
                @include(moduleAdminTemplate($moduleName)."public.crumb",['breadcrumb'=>[$pageData['title'], $pageData['subtitle'], $record->order_no]])

                <a href="{{ url('admin/formtools/paidRecord') }}" class="ft-paid-back">← {{ getTranslateByKey('ft_paid_back_list') }}</a>

                @php
                    $statusMeta = \Modules\Formtools\Http\Controllers\Admin\PaidRecordController::statusLabel((int) $record->status);
                @endphp

                <div class="ft-paid-detail">
                    <div>
                        <div class="ft-paid-card">
                            <h2 class="ft-paid-card__title">{{ getTranslateByKey('ft_paid_order_info') }}<small>{{ $record->order_no }}</small></h2>
                            <div class="ft-paid-info">
                                <div class="ft-paid-info__label">{{ getTranslateByKey('ft_status_label') }}</div>
                                <div class="ft-paid-info__value"><span class="{{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span></div>
                                <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_amount') }}</div>
                                <div class="ft-paid-info__value" style="font-size:20px;font-weight:800;color:#0f172a;">￥{{ number_format((float) $record->amount, 2) }}</div>
                                <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_pay_method') }}</div>
                                <div class="ft-paid-info__value">{{ $record->pay_method ?: '—' }}<span style="color:#94a3b8;margin-left:6px;">{{ $record->pay_type }}</span></div>
                                <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_trade_no') }}</div>
                                <div class="ft-paid-info__value"><span class="ft-paid-info__value--mono">{{ $record->order_no }}</span></div>
                                <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_txn_no') }}</div>
                                <div class="ft-paid-info__value"><span class="ft-paid-info__value--mono">{{ $record->out_trade_no ?: '—' }}</span></div>
                                <div class="ft-paid-info__label">{{ getTranslateByKey('ft_ml_th_created_at') }}</div>
                                <div class="ft-paid-info__value">{{ $record->created_at }}</div>
                                <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_paid_at') }}</div>
                                <div class="ft-paid-info__value">{{ $record->paid_at ?: '—' }}</div>
                                @if(!empty($record->expire_at))
                                    <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_expire_at') }}</div>
                                    <div class="ft-paid-info__value">{{ $record->expire_at }}</div>
                                @endif
                            </div>
                            <div class="ft-paid-actions">
                                @if((int) $record->status === 1)
                                    <button type="button" class="btn btn-warning ft-paid-refund" data-order="{{ $record->order_no }}">{{ getTranslateByKey('ft_paid_mark_refund') }}</button>
                                @elseif((int) $record->status === 0)
                                    <button type="button" class="btn btn-success ft-paid-markpaid" data-order="{{ $record->order_no }}">{{ getTranslateByKey('ft_paid_manual_mark_paid') }}</button>
                                    <button type="button" class="btn btn-default ft-paid-cancel" data-order="{{ $record->order_no }}">{{ getTranslateByKey('ft_paid_cancel_title') }}</button>
                                @endif
                            </div>
                        </div>

                        <div class="ft-paid-card" style="margin-top:16px;">
                            <h2 class="ft-paid-card__title">{{ getTranslateByKey('ft_paid_content_title') }}<small>{{ $record->model_identification }} #{{ $record->content_id }}</small></h2>
                            @if($content)
                                <div class="ft-paid-info">
                                    <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_content_label') }}</div>
                                    <div class="ft-paid-info__value">
                                        {{ $content->title ?? ($content->name ?? '—') }}
                                        <a href="{{ url('admin/formtools/model') }}?action=Edit&moduleName=formtools&model={{ $record->model_identification }}&id={{ $record->content_id }}" style="color:#6366f1;text-decoration:none;margin-left:8px;font-size:12px;">{{ getTranslateByKey('ft_paid_view_edit') }}</a>
                                    </div>
                                    <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_price') }}</div>
                                    <div class="ft-paid-info__value">￥{{ number_format((float) ($content->price ?? 0), 2) }}</div>
                                    <div class="ft-paid-info__label">{{ getTranslateByKey('ft_status_label') }}</div>
                                    <div class="ft-paid-info__value">{{ $content->status ?? '—' }}</div>
                                </div>
                            @else
                                <div style="color:#94a3b8;font-size:13px;">{{ getTranslateByKey('ft_paid_content_missing') }}</div>
                            @endif
                        </div>

                        <div class="ft-paid-card" style="margin-top:16px;">
                            <h2 class="ft-paid-card__title">{{ getTranslateByKey('ft_paid_buyer') }}</h2>
                            <div class="ft-paid-info">
                                <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_user_id') }}</div>
                                <div class="ft-paid-info__value">{{ $record->user_id }}</div>
                                <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_user_type') }}</div>
                                <div class="ft-paid-info__value">{{ $record->user_type ?: 'user' }}</div>
                                <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_buyer_tag') }}</div>
                                <div class="ft-paid-info__value">{{ $record->user_label ?: '—' }}</div>
                                @if($user)
                                    <div class="ft-paid-info__label">{{ getTranslateByKey('common_nickname') }}</div>
                                    <div class="ft-paid-info__value">{{ $user['name'] }}</div>
                                    @if($user['mobile'])
                                        <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_phone') }}</div>
                                        <div class="ft-paid-info__value">{{ $user['mobile'] }}</div>
                                    @endif
                                    @if($user['email'])
                                        <div class="ft-paid-info__label">{{ getTranslateByKey('ft_paid_email') }}</div>
                                        <div class="ft-paid-info__value">{{ $user['email'] }}</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="ft-paid-card">
                            <h2 class="ft-paid-card__title">{{ getTranslateByKey('ft_paid_extra') }}</h2>
                            @if(empty($extra))
                                <div style="color:#94a3b8;font-size:13px;">{{ getTranslateByKey('ft_paid_no_extra') }}</div>
                            @else
                                <pre class="ft-paid-extra">{{ json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include(moduleAdminTemplate($moduleName)."public.footer")
@include(moduleAdminTemplate($moduleName)."public.js")
<script>
    (function () {
        function bindAction(selector, url, promptText, extraField) {
            document.querySelectorAll(selector).forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var orderNo = btn.getAttribute('data-order');
                    layer.prompt({
                        title: promptText,
                        formType: 0,
                        value: ''
                    }, function (value, index) {
                        layer.close(index);
                        var body = new FormData();
                        body.append('order_no', orderNo);
                        if (extraField) body.append(extraField, value);
                        var loading = layer.msg('{{ getTranslateByKey('ft_paid_processing') }}', { icon: 16, time: 0 });
                        fetch(url, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: body
                        }).then(function (r) { return r.json(); })
                          .then(function (resp) {
                              layer.close(loading);
                              if (resp && resp.code === 200) {
                                  layer.msg(resp.msg || '{{ getTranslateByKey('ft_paid_op_success') }}', { icon: 1 });
                                  setTimeout(function () { location.reload(); }, 600);
                              } else {
                                  layer.msg((resp && resp.msg) || '{{ getTranslateByKey('ft_paid_op_failed') }}', { icon: 2 });
                              }
                          })
                          .catch(function () {
                              layer.close(loading);
                              layer.msg('{{ getTranslateByKey('ft_paid_network_error') }}', { icon: 2 });
                          });
                    });
                });
            });
        }
        bindAction('.ft-paid-refund', '{{ url("admin/formtools/paidRecord/markRefunded") }}', 'getTranslateByKey('ft_paid_refund_reason_ph')', 'reason');
        bindAction('.ft-paid-markpaid', '{{ url("admin/formtools/paidRecord/markPaid") }}', 'getTranslateByKey('ft_paid_reason_ph')', 'reason');
        document.querySelectorAll('.ft-paid-cancel').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var orderNo = btn.getAttribute('data-order');
                layer.confirm('{{ getTranslateByKey('ft_paid_cancel_confirm', 'admin', ['order' => ':O:']) }}'.replace(':O:', orderNo), { title: '{{ getTranslateByKey('ft_paid_cancel_title') }}', btn: ['{{ getTranslateByKey('common_ensure') }}', '{{ getTranslateByKey('common_cancel') }}'] }, function () {
                    var body = new FormData();
                    body.append('order_no', orderNo);
                    var loading = layer.msg('{{ getTranslateByKey('ft_paid_processing') }}', { icon: 16, time: 0 });
                    fetch('{{ url("admin/formtools/paidRecord/cancel") }}', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: body
                    }).then(function (r) { return r.json(); })
                      .then(function (resp) {
                          layer.close(loading);
                          if (resp && resp.code === 200) {
                              layer.msg(resp.msg || '{{ getTranslateByKey('ft_paid_canceled_msg') }}', { icon: 1 });
                              setTimeout(function () { location.reload(); }, 600);
                          } else {
                              layer.msg((resp && resp.msg) || '{{ getTranslateByKey('ft_paid_op_failed') }}', { icon: 2 });
                          }
                      })
                      .catch(function () {
                          layer.close(loading);
                          layer.msg('{{ getTranslateByKey('ft_paid_network_error') }}', { icon: 2 });
                      });
                });
            });
        });
    })();
</script>
</body>
</html>
