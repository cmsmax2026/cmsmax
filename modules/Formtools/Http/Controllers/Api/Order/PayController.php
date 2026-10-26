<?php

namespace Modules\Formtools\Http\Controllers\Api\Order;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Formtools\Services\FormtoolsPaymentService;
use Modules\Main\Models\Common;

class PayController extends Controller
{
    protected $paymentService;
    protected $request;

    public function __construct(Request $request, FormtoolsPaymentService $paymentService)
    {
        $this->request = $request;
        $this->paymentService = $paymentService;
    }

    /**
     * 支付成功回调
     * 由 plugins/Pay/Http/Controllers/InitController::callback 触发
     *  - class:  Modules\Formtools\Http\Controllers\Api\Order\PayController
     *  - action: paidCallback  (与 hook 传入的 action 一致)
     *
     * $data 是 WeChatApi::callback / AlipayApi::callback 解析后的数组，至少包含：
     *   - out_trade_no   : 系统订单号（= module_formtools_paid_records.order_no）
     *   - transaction_id : 微信交易号（= out_trade_no 的支付宝对应字段）
     *   - status         : 1=成功 2=失败
     *   - total_fee      : 金额（分，微信）/ 元（支付宝）
     *   - openid
     *   - pay_method     : WeChat / Alipay
     */
    public function paidCallback($data)
    {
        try {
            $orderNo = (string) ($data['out_trade_no'] ?? '');
            if ($orderNo === '') {
                Log::warning('Formtools paidCallback: missing out_trade_no', (array) $data);
                return ['status' => 0, 'msg' => '缺少订单号'];
            }
            $isSuccess = (int) ($data['status'] ?? 0) === 1;
            if (!$isSuccess) {
                Log::info('Formtools paidCallback: payment not success', ['order_no' => $orderNo, 'data' => $data]);
                return ['status' => 0, 'msg' => '支付未成功'];
            }

            $order = $this->paymentService->getOrder($orderNo);
            if (!$order) {
                Log::warning('Formtools paidCallback: order not found', ['order_no' => $orderNo]);
                return ['status' => 0, 'msg' => '订单不存在'];
            }
            if ((int) $order->status === FormtoolsPaymentService::STATUS_PAID) {
                return ['status' => 1, 'msg' => '已支付，幂等返回'];
            }

            $callbackData = [
                'transaction_id' => (string) ($data['transaction_id'] ?? $data['trade_no'] ?? ''),
                'total_fee' => $data['total_fee'] ?? $data['total_amount'] ?? 0,
                'openid' => (string) ($data['openid'] ?? ''),
                'pay_method' => (string) ($data['pay_method'] ?? $order->pay_method ?? ''),
                'paid_at_raw' => $data['time_end'] ?? $data['gmt_payment'] ?? date('Y-m-d H:i:s'),
            ];

            $ok = $this->paymentService->markAsPaid($orderNo, $callbackData);
            if ($ok) {
                Log::info('Formtools paidCallback: success', [
                    'order_no' => $orderNo,
                    'user_id' => $order->user_id,
                    'model' => $order->model_identification,
                    'content_id' => $order->content_id,
                ]);
            }
            return ['status' => $ok ? 1 : 0, 'msg' => $ok ? 'ok' : '标记支付失败'];
        } catch (\Throwable $e) {
            Log::error('Formtools paidCallback exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }
}
