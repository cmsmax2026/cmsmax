<?php

namespace Modules\Formtools\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Formtools\Models\FormModel;
use Modules\Formtools\Services\FormtoolsPaymentService;
use Modules\Main\Models\Common;

class PaidRecordController extends Controller
{
    protected $request;
    protected $paymentService;

    public function __construct(Request $request, FormtoolsPaymentService $paymentService)
    {
        $this->request = $request;
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $pageData = getURIByRoute($this->request);
        $pageData['title'] = '付费记录';
        $pageData['subtitle'] = '内容付费订单';
        $pageData['moduleName'] = $pageData['moduleName'] ?? 'Formtools';

        $filters = [
            'order_no' => trim((string) $this->request->query('order_no', '')),
            'user_id' => trim((string) $this->request->query('user_id', '')),
            'model' => trim((string) $this->request->query('model', '')),
            'content_id' => trim((string) $this->request->query('content_id', '')),
            'status' => (string) $this->request->query('status', ''),
            'pay_method' => (string) $this->request->query('pay_method', ''),
            'start' => trim((string) $this->request->query('start', '')),
            'end' => trim((string) $this->request->query('end', '')),
        ];

        $query = Common::query()->from('module_formtools_paid_records');
        if ($filters['order_no'] !== '') {
            $query->where('order_no', 'like', '%' . $filters['order_no'] . '%');
        }
        if ($filters['user_id'] !== '' && ctype_digit($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }
        if ($filters['model'] !== '') {
            $query->where('model_identification', $filters['model']);
        }
        if ($filters['content_id'] !== '' && ctype_digit($filters['content_id'])) {
            $query->where('content_id', (int) $filters['content_id']);
        }
        if ($filters['status'] !== '' && in_array($filters['status'], ['0', '1', '2', '3', '4'], true)) {
            $query->where('status', (int) $filters['status']);
        }
        if ($filters['pay_method'] !== '') {
            $query->where('pay_method', $filters['pay_method']);
        }
        if ($filters['start'] !== '') {
            $query->where('created_at', '>=', $filters['start'] . ' 00:00:00');
        }
        if ($filters['end'] !== '') {
            $query->where('created_at', '<=', $filters['end'] . ' 23:59:59');
        }

        $pageData['records'] = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $pageData['filters'] = $filters;

        $pageData['summary'] = $this->buildSummary($filters);
        $pageData['models'] = FormModel::query()
            ->withoutReserved()
            ->orderBy('name')
            ->get(['id', 'name', 'identification'])
            ->map(fn ($m) => ['name' => (string) $m->name, 'identification' => (string) $m->identification])
            ->toArray();

        return view('formtools::admin.paidRecord.index', [
            'pageData' => $pageData,
        ]);
    }

    public function detail()
    {
        $pageData = getURIByRoute($this->request);
        $pageData['title'] = '付费详情';
        $pageData['subtitle'] = '订单详情';
        $pageData['moduleName'] = $pageData['moduleName'] ?? 'Formtools';

        $orderNo = trim((string) $this->request->query('order_no', ''));
        $record = $orderNo !== '' ? $this->paymentService->getOrder($orderNo) : null;
        if (!$record) {
            return redirect('/admin/formtools/paidRecord')->with('pageDataMsg', '订单不存在');
        }
        $extra = $record->extra ? json_decode($record->extra, true) : [];
        $pageData['record'] = $record;
        $pageData['extra'] = is_array($extra) ? $extra : [];
        $pageData['content'] = null;
        if ($record->model_identification && $record->content_id) {
            $tableName = 'module_formtools_' . $record->model_identification;
            $pageData['content'] = Common::query()->from($tableName)->where('id', (int) $record->content_id)->first();
        }
        $pageData['user'] = null;
        if ((int) $record->user_id > 0) {
            $pageData['user'] = $this->resolveUserSnapshot($record->user_id, $record->user_type);
        }

        return view('formtools::admin.paidRecord.detail', [
            'pageData' => $pageData,
        ]);
    }

    public function markRefunded()
    {
        $orderNo = trim((string) $this->request->input('order_no', ''));
        $reason = trim((string) $this->request->input('reason', ''));
        if ($orderNo === '') {
            return response()->json(['code' => 400, 'msg' => '缺少订单号']);
        }
        $order = $this->paymentService->getOrder($orderNo);
        if (!$order) {
            return response()->json(['code' => 404, 'msg' => '订单不存在']);
        }
        if ((int) $order->status === FormtoolsPaymentService::STATUS_REFUNDED) {
            return response()->json(['code' => 400, 'msg' => '该订单已经是已退款状态']);
        }
        if ((int) $order->status !== FormtoolsPaymentService::STATUS_PAID) {
            return response()->json(['code' => 400, 'msg' => '只有已支付订单可标记退款']);
        }
        $now = date('Y-m-d H:i:s');
        $extra = (array) ($order->extra ? json_decode($order->extra, true) : []);
        $extra['refund'] = [
            'reason' => $reason,
            'admin' => $this->resolveAdminName(),
            'time' => $now,
        ];
        $ok = (bool) Common::query()
            ->from('module_formtools_paid_records')
            ->where('order_no', $orderNo)
            ->update([
                'status' => FormtoolsPaymentService::STATUS_REFUNDED,
                'extra' => json_encode($extra, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);
        return response()->json([
            'code' => $ok ? 200 : 500,
            'msg' => $ok ? '已标记为退款' : '操作失败',
        ]);
    }

    public function markPaid()
    {
        $orderNo = trim((string) $this->request->input('order_no', ''));
        $reason = trim((string) $this->request->input('reason', ''));
        if ($orderNo === '') {
            return response()->json(['code' => 400, 'msg' => '缺少订单号']);
        }
        $order = $this->paymentService->getOrder($orderNo);
        if (!$order) {
            return response()->json(['code' => 404, 'msg' => '订单不存在']);
        }
        if ((int) $order->status === FormtoolsPaymentService::STATUS_PAID) {
            return response()->json(['code' => 400, 'msg' => '该订单已支付']);
        }
        if ((int) $order->status === FormtoolsPaymentService::STATUS_REFUNDED) {
            return response()->json(['code' => 400, 'msg' => '已退款订单不能重新标记为已支付']);
        }
        $ok = $this->paymentService->markAsPaid($orderNo, [
            'transaction_id' => 'ADMIN_' . date('YmdHis') . mt_rand(100, 999),
            'pay_method' => (string) ($order->pay_method ?: 'Manual'),
            'paid_at_raw' => date('Y-m-d H:i:s'),
            'admin_manual' => $this->resolveAdminName(),
            'admin_reason' => $reason,
        ]);
        return response()->json([
            'code' => $ok ? 200 : 500,
            'msg' => $ok ? '已手动标记为支付' : '操作失败',
        ]);
    }

    public function cancel()
    {
        $orderNo = trim((string) $this->request->input('order_no', ''));
        if ($orderNo === '') {
            return response()->json(['code' => 400, 'msg' => '缺少订单号']);
        }
        $order = $this->paymentService->getOrder($orderNo);
        if (!$order) {
            return response()->json(['code' => 404, 'msg' => '订单不存在']);
        }
        if ((int) $order->status === FormtoolsPaymentService::STATUS_PAID) {
            return response()->json(['code' => 400, 'msg' => '已支付订单不能取消']);
        }
        $ok = $this->paymentService->markCanceled($orderNo);
        return response()->json([
            'code' => $ok ? 200 : 500,
            'msg' => $ok ? '已取消订单' : '操作失败',
        ]);
    }

    private function buildSummary(array $filters): array
    {
        $base = Common::query()->from('module_formtools_paid_records');
        if ($filters['model'] !== '') {
            $base->where('model_identification', $filters['model']);
        }
        if ($filters['start'] !== '') {
            $base->where('created_at', '>=', $filters['start'] . ' 00:00:00');
        }
        if ($filters['end'] !== '') {
            $base->where('created_at', '<=', $filters['end'] . ' 23:59:59');
        }

        $rows = (clone $base)->get(['status', 'amount']);
        $summary = [
            'count_total' => 0,
            'count_paid' => 0,
            'count_pending' => 0,
            'count_refunded' => 0,
            'amount_paid' => 0.0,
            'amount_pending' => 0.0,
        ];
        foreach ($rows as $row) {
            $summary['count_total']++;
            $amount = (float) $row->amount;
            $status = (int) $row->status;
            if ($status === FormtoolsPaymentService::STATUS_PAID) {
                $summary['count_paid']++;
                $summary['amount_paid'] += $amount;
            } elseif ($status === FormtoolsPaymentService::STATUS_PENDING) {
                $summary['count_pending']++;
                $summary['amount_pending'] += $amount;
            } elseif ($status === FormtoolsPaymentService::STATUS_REFUNDED) {
                $summary['count_refunded']++;
            }
        }
        return $summary;
    }

    private function resolveUserSnapshot(int $userId, string $userType): ?array
    {
        if ($userId <= 0) {
            return null;
        }
        $try = function (string $table) use ($userId) {
            try {
                $row = Common::query()->from($table)->where('id', $userId)->first();
                if ($row) {
                    return (array) $row;
                }
            } catch (\Throwable $e) {
                return null;
            }
            return null;
        };
        $candidates = $userType === 'admin'
            ? ['admin', 'system_admin', 'module_system_admin', 'users']
            : ['module_member_auth', 'member', 'members', 'module_member_members', 'users'];
        foreach ($candidates as $table) {
            $row = $try($table);
            if ($row) {
                return [
                    'id' => $userId,
                    'name' => (string) ($row['name'] ?? $row['nickname'] ?? $row['username'] ?? ('User#' . $userId)),
                    'mobile' => (string) ($row['mobile'] ?? $row['phone'] ?? ''),
                    'email' => (string) ($row['email'] ?? ''),
                ];
            }
        }
        return ['id' => $userId, 'name' => 'User#' . $userId];
    }

    private function resolveAdminName(): string
    {
        try {
            $name = session('admin_name') ?: session('user_name') ?: session('username');
            if ($name) {
                return (string) $name;
            }
        } catch (\Throwable $e) {
        }
        return 'admin';
    }

    public static function statusLabel(int $status): array
    {
        return match ($status) {
            FormtoolsPaymentService::STATUS_PENDING => ['label' => '待支付', 'class' => 'ft-paid-tag ft-paid-tag--pending'],
            FormtoolsPaymentService::STATUS_PAID => ['label' => '已支付', 'class' => 'ft-paid-tag ft-paid-tag--paid'],
            FormtoolsPaymentService::STATUS_REFUNDED => ['label' => '已退款', 'class' => 'ft-paid-tag ft-paid-tag--refunded'],
            FormtoolsPaymentService::STATUS_EXPIRED => ['label' => '已过期', 'class' => 'ft-paid-tag ft-paid-tag--expired'],
            FormtoolsPaymentService::STATUS_CANCELED => ['label' => '已取消', 'class' => 'ft-paid-tag ft-paid-tag--canceled'],
            default => ['label' => '未知', 'class' => 'ft-paid-tag ft-paid-tag--unknown'],
        };
    }
}
