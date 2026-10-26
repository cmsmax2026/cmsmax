<?php

namespace Modules\Formtools\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Formtools\Services\FormtoolsPaymentService;
use Modules\Main\Models\Common;

class FormtoolsPaymentApiController extends Controller
{
    protected $paymentService;
    protected $request;

    public function __construct(Request $request, FormtoolsPaymentService $paymentService)
    {
        $this->request = $request;
        $this->paymentService = $paymentService;
    }

    /**
     * 创建订单并调起 Pay hook（req_type=pay）
     * POST /formtools/buy/{model}/{id}
     */
    public function buy(string $model, int $id)
    {
        $all = $this->request->all();
        $payMethod = (string) ($all['pay_method'] ?? $all['pay'] ?? 'WeChat');
        $payType = (string) ($all['pay_type'] ?? $all['type'] ?? 'public');

        $userInfo = $this->paymentService->normalizeUser(null);
        if ($userInfo['id'] <= 0) {
            return response()->json(['code' => 401, 'msg' => '请先登录']);
        }

        $orderRes = $this->paymentService->createOrder($model, $id, $userInfo, $payMethod, $payType);
        if (($orderRes['code'] ?? 0) !== 200) {
            return response()->json($orderRes);
        }
        $order = $orderRes['data'];

        $payHookPayload = [
            'moduleName' => 'pay',
            'cloudType' => 'plugin',
            'data' => [
                'request' => $this->request,
                'module' => 'formtools',
                'action' => 'paidCallback',
                'req_type' => 'pay',
                'pay_method' => $payMethod,
                'pay_type' => $payType,
                'outTradeNo' => $order['order_no'],
                'totalFee' => (float) $order['amount'],
                'openid' => (string) ($all['openid'] ?? ''),
                'callback_data' => [],
            ],
        ];

        try {
            $res = hook('Pay', $payHookPayload);
        } catch (\Throwable $e) {
            Log::error('Formtools pay hook failed: ' . $e->getMessage(), [
                'order_no' => $order['order_no'],
                'model' => $model,
                'id' => $id,
            ]);
            return response()->json([
                'code' => 500,
                'msg' => '支付调起失败：' . $e->getMessage(),
                'data' => ['order_no' => $order['order_no']],
            ]);
        }

        $hookResult = is_array($res) ? ($res[0] ?? null) : $res;
        if (!is_array($hookResult)) {
            $hookResult = ['status' => 0, 'msg' => '支付插件未注册或未响应'];
        }

        return response()->json([
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'order_no' => $order['order_no'],
                'amount' => (float) $order['amount'],
                'pay_method' => $payMethod,
                'pay_type' => $payType,
                'pay_url' => $hookResult['pay_url'] ?? '',
                'qrcode' => $hookResult['qrcode'] ?? '',
                'pay_params' => $hookResult['pay_params'] ?? ($hookResult['params'] ?? []),
                'check_url' => url('/formtools/check-pay/' . $order['order_no']),
            ],
            'hook' => $hookResult,
        ]);
    }

    /**
     * 查询订单状态（前端轮询）
     * GET /formtools/check-pay/{order_no}
     */
    public function check(string $orderNo)
    {
        $order = $this->paymentService->getOrder($orderNo);
        if (!$order) {
            return response()->json(['code' => 404, 'msg' => '订单不存在']);
        }
        return response()->json([
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'order_no' => $order->order_no,
                'status' => (int) $order->status,
                'is_paid' => (int) $order->status === FormtoolsPaymentService::STATUS_PAID,
                'amount' => (float) $order->amount,
                'paid_at' => (string) ($order->paid_at ?? ''),
            ],
        ]);
    }

    /**
     * 取消未支付订单
     * POST /formtools/cancel/{order_no}
     */
    public function cancel(string $orderNo)
    {
        $order = $this->paymentService->getOrder($orderNo);
        if (!$order) {
            return response()->json(['code' => 404, 'msg' => '订单不存在']);
        }
        $userInfo = $this->paymentService->normalizeUser(null);
        if ((int) $order->user_id !== $userInfo['id']) {
            return response()->json(['code' => 403, 'msg' => '无权限操作该订单']);
        }
        $ok = $this->paymentService->markCanceled($orderNo);
        return response()->json(['code' => $ok ? 200 : 400, 'msg' => $ok ? '已取消' : '订单已支付，无法取消']);
    }
}
