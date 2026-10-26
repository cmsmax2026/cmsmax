@include(moduleAdminTemplate($moduleName)."public.header")
<style>
    .ft-paid-summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }
    .ft-paid-summary__card {
        padding: 18px 20px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }
    .ft-paid-summary__label { font-size: 12px; color: #64748b; }
    .ft-paid-summary__value { font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 4px; }
    .ft-paid-summary__value--paid { color: #15803d; }
    .ft-paid-summary__value--pending { color: #b45309; }
    .ft-paid-summary__value--refunded { color: #991b1b; }
    .ft-paid-summary__suffix { font-size: 12px; color: #94a3b8; margin-left: 4px; }

    .ft-paid-filter {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr)) auto;
        gap: 10px;
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        margin-bottom: 18px;
        align-items: end;
    }
    .ft-paid-filter label {
        display: block;
        font-size: 12px;
        color: #475569;
        margin-bottom: 4px;
        font-weight: 600;
    }
    .ft-paid-filter input,
    .ft-paid-filter select {
        width: 100%;
        padding: 7px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        background: #f8fafc;
        box-sizing: border-box;
    }
    .ft-paid-filter input:focus,
    .ft-paid-filter select:focus {
        outline: none;
        background: #fff;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }
    .ft-paid-filter__actions { display: flex; gap: 8px; }
    .ft-paid-filter__btn {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border-radius: 8px;
        background: #6366f1;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        border: 0;
        cursor: pointer;
        text-decoration: none;
    }
    .ft-paid-filter__btn--ghost { background: #f1f5f9; color: #475569; }

    .ft-paid-table {
        width: 100%;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        border-collapse: separate;
        border-spacing: 0;
    }
    .ft-paid-table th {
        background: #f8fafc;
        text-align: left;
        padding: 12px 14px;
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
        border-bottom: 1px solid #edf2f7;
    }
    .ft-paid-table td {
        padding: 12px 14px;
        font-size: 13px;
        color: #1e293b;
        border-bottom: 1px solid #edf2f7;
    }
    .ft-paid-table tr:last-child td { border-bottom: 0; }
    .ft-paid-table tr:hover { background: #fafbff; }

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

    .ft-paid-amount { font-weight: 700; color: #0f172a; }
    .ft-paid-order { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; color: #475569; }
    .ft-paid-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .ft-paid-actions .btn { padding: 4px 10px; font-size: 12px; border-radius: 999px; }

    .ft-paid-pagination { margin-top: 16px; }
    .ft-paid-empty { padding: 40px 20px; text-align: center; color: #94a3b8; }

    @media (max-width: 1199px) {
        .ft-paid-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .ft-paid-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767px) {
        .ft-paid-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ft-paid-filter { grid-template-columns: 1fr; }
    }
</style>
<body>
@include(moduleAdminTemplate($moduleName)."public.nav")
<div class="page-container">
    <div class="page-content">
        @include(moduleAdminTemplate($moduleName)."public.left")
        <div class="content-wrapper">
            <div class="content" style="margin-top: 1rem;">
                @include(moduleAdminTemplate($moduleName)."public.crumb",['breadcrumb'=>[$pageData['title'],$pageData['subtitle']]])

                <div class="ft-paid-summary">
                    <div class="ft-paid-summary__card">
                        <div class="ft-paid-summary__label">{{ getTranslateByKey('ft_paid_total') }}</div>
                        <div class="ft-paid-summary__value">{{ number_format($summary['count_total'] ?? 0) }}<span class="ft-paid-summary__suffix">{{ getTranslateByKey('ft_paid_unit_suffix') }}</span></div>
                    </div>
                    <div class="ft-paid-summary__card">
                        <div class="ft-paid-summary__label">{{ getTranslateByKey('ft_paid_paid') }}</div>
                        <div class="ft-paid-summary__value ft-paid-summary__value--paid">{{ number_format($summary['count_paid'] ?? 0) }}<span class="ft-paid-summary__suffix">{{ getTranslateByKey('ft_paid_unit_suffix') }}</span></div>
                    </div>
                    <div class="ft-paid-summary__card">
                        <div class="ft-paid-summary__label">{{ getTranslateByKey('ft_paid_pending') }}</div>
                        <div class="ft-paid-summary__value ft-paid-summary__value--pending">{{ number_format($summary['count_pending'] ?? 0) }}<span class="ft-paid-summary__suffix">{{ getTranslateByKey('ft_paid_unit_suffix') }}</span></div>
                    </div>
                    <div class="ft-paid-summary__card">
                        <div class="ft-paid-summary__label">{{ getTranslateByKey('ft_paid_refunded') }}</div>
                        <div class="ft-paid-summary__value ft-paid-summary__value--refunded">{{ number_format($summary['count_refunded'] ?? 0) }}<span class="ft-paid-summary__suffix">{{ getTranslateByKey('ft_paid_unit_suffix') }}</span></div>
                    </div>
                    <div class="ft-paid-summary__card">
                        <div class="ft-paid-summary__label">{{ getTranslateByKey('ft_paid_paid_amount') }}</div>
                        <div class="ft-paid-summary__value ft-paid-summary__value--paid">￥{{ number_format($summary['amount_paid'] ?? 0, 2) }}</div>
                    </div>
                </div>

                <form class="ft-paid-filter" method="get" action="{{ url('admin/formtools/paidRecord') }}">
                    <div>
                        <label>{{ getTranslateByKey('ft_paid_order_no') }}</label>
                        <input type="text" name="order_no" value="{{ $filters['order_no'] }}" placeholder="{{ getTranslateByKey('ft_paid_order_no_placeholder') }}">
                    </div>
                    <div>
                        <label>{{ getTranslateByKey('ft_paid_user_id') }}</label>
                        <input type="text" name="user_id" value="{{ $filters['user_id'] }}" placeholder="{{ getTranslateByKey('ft_paid_user_id') }}">
                    </div>
                    <div>
                        <label>{{ getTranslateByKey('ft_paid_model') }}</label>
                        <select name="model">
                            <option value="">{{ getTranslateByKey('common_all') }}</option>
                            @foreach($models as $m)
                                <option value="{{ $m['identification'] }}" @if($filters['model']===$m['identification']) selected @endif>{{ $m['name'] }} ({{ $m['identification'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>{{ getTranslateByKey('ft_paid_content_id') }}</label>
                        <input type="text" name="content_id" value="{{ $filters['content_id'] }}" placeholder="{{ getTranslateByKey('ft_paid_content_id') }}">
                    </div>
                    <div>
                        <label>{{ getTranslateByKey('ft_status_label') }}</label>
                        <select name="status">
                            <option value="">{{ getTranslateByKey('common_all') }}</option>
                            <option value="0" @if($filters['status']==='0') selected @endif>{{ getTranslateByKey('ft_paid_pending') }}</option>
                            <option value="1" @if($filters['status']==='1') selected @endif>{{ getTranslateByKey('ft_paid_paid') }}</option>
                            <option value="2" @if($filters['status']==='2') selected @endif>{{ getTranslateByKey('ft_paid_refunded') }}</option>
                            <option value="3" @if($filters['status']==='3') selected @endif>{{ getTranslateByKey('ft_paid_expired') }}</option>
                            <option value="4" @if($filters['status']==='4') selected @endif>{{ getTranslateByKey('ft_paid_canceled') }}</option>
                        </select>
                    </div>
                    <div>
                        <label>{{ getTranslateByKey('ft_paid_pay_method') }}</label>
                        <select name="pay_method">
                            <option value="">{{ getTranslateByKey('common_all') }}</option>
                            <option value="WeChat" @if($filters['pay_method']==='WeChat') selected @endif>{{ getTranslateByKey('ft_paid_wechat') }}</option>
                            <option value="Alipay" @if($filters['pay_method']==='Alipay') selected @endif>{{ getTranslateByKey('ft_paid_alipay') }}</option>
                        </select>
                    </div>
                    <div>
                        <label>{{ getTranslateByKey('ft_paid_date') }}</label>
                        <div style="display:flex;gap:6px;">
                            <input type="date" name="start" value="{{ $filters['start'] }}">
                            <input type="date" name="end" value="{{ $filters['end'] }}">
                        </div>
                    </div>
                    <div class="ft-paid-filter__actions">
                        <button type="submit" class="ft-paid-filter__btn">{{ getTranslateByKey('ft_paid_search') }}</button>
                        <a href="{{ url('admin/formtools/paidRecord') }}" class="ft-paid-filter__btn ft-paid-filter__btn--ghost">{{ getTranslateByKey('ft_paid_reset') }}</a>
                    </div>
                </form>

                @if($records && count($records) > 0)
                    <table class="ft-paid-table">
                        <thead>
                            <tr>
                                <th>{{ getTranslateByKey('ft_paid_order_no') }}</th>
                                <th>{{ getTranslateByKey('ft_paid_user') }}</th>
                                <th>{{ getTranslateByKey('ft_paid_model_content') }}</th>
                                <th>{{ getTranslateByKey('ft_paid_amount') }}</th>
                                <th>{{ getTranslateByKey('ft_paid_pay') }}</th>
                                <th>{{ getTranslateByKey('ft_status_label') }}</th>
                                <th>{{ getTranslateByKey('ft_ml_th_created_at') }}</th>
                                <th>{{ getTranslateByKey('ft_paid_paid_at') }}</th>
                                <th>{{ getTranslateByKey('operation') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $r)
                                @php
                                    $statusMeta = \Modules\Formtools\Http\Controllers\Admin\PaidRecordController::statusLabel((int) $r->status);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="ft-paid-order">{{ $r->order_no }}</div>
                                        @if(!empty($r->out_trade_no))
                                            <div class="ft-paid-order" style="color:#94a3b8;margin-top:2px;">{{ $r->out_trade_no }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $r->user_label ?: 'User#' . $r->user_id }}</div>
                                        <div style="font-size:11px;color:#94a3b8;">ID: {{ $r->user_id }} · {{ $r->user_type }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $r->model_identification }}</div>
                                        <div style="font-size:12px;color:#64748b;margin-top:2px;">
                                            <a href="{{ url('admin/formtools/model') }}?action=Edit&moduleName=formtools&model={{ $r->model_identification }}&id={{ $r->content_id }}" style="color:#6366f1;text-decoration:none;">
                                                #{{ $r->content_id }} {{ $r->content_title }}
                                            </a>
                                        </div>
                                    </td>
                                    <td><span class="ft-paid-amount">￥{{ number_format((float) $r->amount, 2) }}</span></td>
                                    <td>{{ $r->pay_method ?: '—' }}<div style="font-size:11px;color:#94a3b8;">{{ $r->pay_type }}</div></td>
                                    <td><span class="{{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span></td>
                                    <td style="font-size:12px;color:#64748b;">{{ $r->created_at }}</td>
                                    <td style="font-size:12px;color:#64748b;">{{ $r->paid_at ?: '—' }}</td>
                                    <td>
                                        <div class="ft-paid-actions">
                                            <a href="{{ url('admin/formtools/paidRecord/detail?order_no=' . $r->order_no) }}" class="btn btn-info">{{ getTranslateByKey('ft_paid_detail') }}</a>
                                            @if((int) $r->status === 1)
                                                <button type="button" class="btn btn-warning ft-paid-refund" data-order="{{ $r->order_no }}">{{ getTranslateByKey('ft_paid_refund_btn') }}</button>
                                            @elseif((int) $r->status === 0)
                                                <button type="button" class="btn btn-success ft-paid-markpaid" data-order="{{ $r->order_no }}">{{ getTranslateByKey('ft_paid_mark_paid') }}</button>
                                                <button type="button" class="btn btn-default ft-paid-cancel" data-order="{{ $r->order_no }}">{{ getTranslateByKey('common_cancel') }}</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="ft-paid-pagination">
                        {!! $records->appends($filters)->render() !!}
                    </div>
                @else
                    <div class="ft-paid-table">
                        <div class="ft-paid-empty">
                            <i class="icon-inbox" style="font-size:48px;color:#cbd5e1;"></i>
                            <div style="margin-top:12px;">{{ getTranslateByKey('ft_paid_empty') }}</div>
                        </div>
                    </div>
                @endif
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
