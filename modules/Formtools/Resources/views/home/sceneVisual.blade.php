<div class="mx-formtools-scene-visual">
    {!! $visualSceneHtml ?? '' !!}
</div>
@if(!empty($paidMap) && is_array($paidMap))
    <style>
        .ft-doc-list-price-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            margin-left: 6px;
            vertical-align: middle;
        }
        .ft-doc-list-price-badge--paid {
            background: #dcfce7;
            color: #166534;
        }
        .ft-doc-list-price-badge--paid::before {
            content: "\e60b";
            font-family: 'icomoon';
            margin-right: 4px;
            font-size: 11px;
        }
        .ft-doc-list-price-badge--locked {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #b45309;
        }
        .ft-doc-list-price-badge--locked::before {
            content: "\e64c";
            font-family: 'icomoon';
            margin-right: 4px;
            font-size: 11px;
        }
    </style>
    <script>
        (function () {
            var paidMap = @json($paidMap);
            if (!paidMap || typeof paidMap !== 'object') return;
            var items = document.querySelectorAll('.mx-formtools-scene-visual [data-content-id]');
            if (!items.length) {
                items = document.querySelectorAll('.mx-formtools-scene-visual a[href*="/detail/"]');
            }
            items.forEach(function (el) {
                if (el.querySelector('.ft-doc-list-price-badge')) return;
                var id = el.getAttribute('data-content-id') || '';
                if (!id) {
                    var href = el.getAttribute('href') || '';
                    var match = href.match(/\/detail\/[^/]+\/(\d+)/);
                    if (match) id = match[1];
                }
                if (!id) return;
                var price = parseFloat(el.getAttribute('data-content-price') || '0');
                var isPaid = paidMap[String(id)] === true || paidMap[Number(id)] === true;
                if (isPaid) {
                    var badge = document.createElement('span');
                    badge.className = 'ft-doc-list-price-badge ft-doc-list-price-badge--paid';
                    badge.textContent = '已购买';
                    el.appendChild(badge);
                } else if (price > 0) {
                    var badge2 = document.createElement('span');
                    badge2.className = 'ft-doc-list-price-badge ft-doc-list-price-badge--locked';
                    badge2.textContent = '￥' + price.toFixed(2);
                    el.appendChild(badge2);
                }
            });
        })();
    </script>
@endif
@if(isset($priceStatus) && is_array($priceStatus))
    @php
        $ps = $priceStatus;
        $isPaid = !empty($ps['is_paid']);
        $needPay = !empty($ps['need_pay']);
        $price = (float) ($ps['price'] ?? 0);
        $isLogin = !empty($ps['is_login']);
        $payUrl = (string) ($ps['pay_url'] ?? '');
        $_dr = isset($detailRecord) && is_array($detailRecord) ? $detailRecord : (isset($detailRecord) && is_object($detailRecord) ? (array) $detailRecord : []);
        $_d = $data ?? null;
        $contentTitle = $_dr['title'] ?? ($_dr['name'] ?? ($_d->title ?? ($_d->name ?? '')));
    @endphp
    @if($needPay)
        <div class="ft-paid-bar" data-ft-paid-bar>
            <div class="ft-paid-bar__inner">
                <div class="ft-paid-bar__icon"><i class="icon-lock"></i></div>
                <div class="ft-paid-bar__body">
                    <div class="ft-paid-bar__title">本文档为付费内容</div>
                    <div class="ft-paid-bar__meta">
                        <span class="ft-paid-bar__price">￥{{ number_format($price, 2) }}</span>
                        <span class="ft-paid-bar__sep">·</span>
                        <span>一次性买断，永久访问</span>
                    </div>
                </div>
                <div class="ft-paid-bar__actions">
                    @if($isLogin)
                        <button type="button" class="ft-paid-bar__btn ft-paid-bar__btn--primary" data-ft-paid-buy data-pay-url="{{ $payUrl }}" data-check-url="{{ $ps['check_url'] ?? '' }}" data-order-no="{{ $ps['order_no'] ?? '' }}">
                            立即购买
                        </button>
                    @else
                        <a href="{{ \App\Support\Auth\HomeLoginRedirect::loginUrl() }}" class="ft-paid-bar__btn ft-paid-bar__btn--primary">登录后购买</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="ft-paid-mask" data-ft-paid-mask aria-hidden="true">
            <div class="ft-paid-mask__icon"><i class="icon-lock"></i></div>
            <div class="ft-paid-mask__title">已隐藏部分内容</div>
            <div class="ft-paid-mask__desc">购买后即可查看完整内容</div>
        </div>
    @elseif($price > 0 && $isPaid)
        <div class="ft-paid-bar ft-paid-bar--paid">
            <div class="ft-paid-bar__inner">
                <div class="ft-paid-bar__icon"><i class="icon-checkmark-circle"></i></div>
                <div class="ft-paid-bar__body">
                    <div class="ft-paid-bar__title">您已购买此内容</div>
                    <div class="ft-paid-bar__meta">已支付 ￥{{ number_format($price, 2) }}，可永久查看完整内容</div>
                </div>
            </div>
        </div>
    @endif
@endif
<style>
    .ft-paid-bar {
        margin: 16px 0 24px;
        border: 1px solid #fde68a;
        border-radius: 14px;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        box-shadow: 0 8px 24px rgba(217, 119, 6, 0.08);
        overflow: hidden;
    }
    .ft-paid-bar--paid {
        border-color: #bbf7d0;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        box-shadow: 0 8px 24px rgba(34, 197, 94, 0.08);
    }
    .ft-paid-bar__inner {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
    }
    .ft-paid-bar__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(245, 158, 11, 0.18);
        color: #b45309;
        font-size: 22px;
        flex-shrink: 0;
    }
    .ft-paid-bar--paid .ft-paid-bar__icon {
        background: rgba(34, 197, 94, 0.18);
        color: #166534;
    }
    .ft-paid-bar__body {
        flex: 1;
        min-width: 0;
    }
    .ft-paid-bar__title {
        font-size: 15px;
        font-weight: 700;
        color: #92400e;
        line-height: 1.4;
    }
    .ft-paid-bar--paid .ft-paid-bar__title {
        color: #166534;
    }
    .ft-paid-bar__meta {
        margin-top: 4px;
        font-size: 13px;
        color: #b45309;
    }
    .ft-paid-bar--paid .ft-paid-bar__meta {
        color: #166534;
    }
    .ft-paid-bar__sep {
        margin: 0 4px;
        opacity: 0.6;
    }
    .ft-paid-bar__price {
        font-size: 18px;
        font-weight: 800;
        color: #b45309;
    }
    .ft-paid-bar__actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    .ft-paid-bar__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 20px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
        border: 0;
    }
    .ft-paid-bar__btn--primary {
        background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
        color: #fff;
        box-shadow: 0 6px 16px rgba(234, 88, 12, 0.3);
    }
    .ft-paid-bar__btn--primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(234, 88, 12, 0.4);
    }
    .ft-paid-mask {
        position: relative;
        margin-top: 8px;
        padding: 32px 20px;
        text-align: center;
        background: linear-gradient(180deg, rgba(255, 251, 235, 0.6) 0%, rgba(254, 243, 199, 0.85) 100%);
        border-radius: 12px;
        border: 1px dashed #fcd34d;
    }
    .ft-paid-mask__icon {
        font-size: 36px;
        color: #d97706;
        margin-bottom: 8px;
    }
    .ft-paid-mask__title {
        font-size: 15px;
        font-weight: 700;
        color: #92400e;
    }
    .ft-paid-mask__desc {
        font-size: 13px;
        color: #b45309;
        margin-top: 4px;
    }
    @media (max-width: 640px) {
        .ft-paid-bar__inner { flex-direction: column; align-items: flex-start; }
        .ft-paid-bar__actions { width: 100%; }
        .ft-paid-bar__btn { width: 100%; }
    }
</style>
<script>
    (function () {
        var buyBtn = document.querySelector('[data-ft-paid-buy]');
        if (!buyBtn) return;
        var payUrl = buyBtn.getAttribute('data-pay-url');
        buyBtn.addEventListener('click', function () {
            if (typeof layer === 'undefined') {
                window.location.href = payUrl;
                return;
            }
            var loading = layer.msg('正在创建订单…', { icon: 16, time: 0 });
            fetch(payUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                body: JSON.stringify({ pay_method: 'WeChat', pay_type: 'public' })
            }).then(function (r) { return r.json(); })
              .then(function (resp) {
                  layer.close(loading);
                  if (!resp || resp.code !== 200) {
                      layer.msg((resp && resp.msg) || '创建订单失败');
                      return;
                  }
                  var pay = resp.data || {};
                  if (pay.pay_url) {
                      window.location.href = pay.pay_url;
                  } else if (pay.qrcode) {
                      layer.open({
                          type: 1,
                          title: '扫码支付',
                          area: ['280px', '320px'],
                          content: '<div style="text-align:center;padding:20px;"><img src="' + pay.qrcode + '" style="width:200px;height:200px;"><div style="margin-top:12px;color:#666;">订单号：' + (pay.order_no || '') + '</div></div>'
                      });
                  } else {
                      layer.msg('已创建订单，请到订单中心完成支付');
                  }
              })
              .catch(function () {
                  layer.close(loading);
                  layer.msg('网络异常，请稍后重试');
              });
        });
    })();
</script>
