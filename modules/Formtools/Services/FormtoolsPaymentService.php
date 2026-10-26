<?php

namespace Modules\Formtools\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Main\Models\Common;
use Modules\Main\Services\ServiceModel as MainServiceModel;

class FormtoolsPaymentService
{
    const STATUS_PENDING = 0;
    const STATUS_PAID = 1;
    const STATUS_REFUNDED = 2;
    const STATUS_EXPIRED = 3;
    const STATUS_CANCELED = 4;

    const MASK_PREVIEW_LENGTH = 200;

    /**
     * 把形如 user、array、int $userId 统一成 [id, type, label]
     * 任意入参都能容错
     */
    public function normalizeUser($user = null): array
    {
        $userId = 0;
        $userType = 'guest';
        $userLabel = '';

        if (is_int($user) || (is_string($user) && ctype_digit((string) $user))) {
            $userId = (int) $user;
        } elseif (is_array($user)) {
            $userId = (int) ($user['id'] ?? $user['uid'] ?? $user['user_id'] ?? 0);
            $userType = (string) ($user['type'] ?? $user['user_type'] ?? 'user');
            $userLabel = (string) ($user['label'] ?? $user['name'] ?? $user['nickname'] ?? $user['mobile'] ?? '');
        } elseif (is_object($user)) {
            $userId = (int) ($user->id ?? $user->uid ?? $user->user_id ?? 0);
            $userType = (string) ($user->type ?? $user->user_type ?? 'user');
            $userLabel = (string) ($user->label ?? $user->name ?? $user->nickname ?? $user->mobile ?? '');
        }

        if ($userId <= 0 && function_exists('session')) {
            try {
                $userId = (int) (session('user_id') ?? session('uid') ?? 0);
                if ($userId > 0) {
                    $userType = (string) (session('user_type') ?: 'user');
                    $userLabel = (string) (session('user_label') ?: session('user_name') ?: '');
                }
            } catch (\Throwable $e) {
                // session driver 不可用（如 artisan 命令行）时直接忽略
            }
        }

        if ($userId > 0 && $userLabel === '' && function_exists('session')) {
            try {
                $userLabel = (string) (session('user_name') ?: session('nickname') ?: '');
            } catch (\Throwable $e) {
            }
        }

        return [
            'id' => $userId,
            'type' => $userType ?: 'user',
            'label' => $userLabel,
        ];
    }

    public function dynamicTable(string $model): string
    {
        return 'module_formtools_' . preg_replace('/[^A-Za-z0-9_]/', '', (string) $model);
    }

    public function hasPriceColumn(string $model): bool
    {
        $table = $this->dynamicTable($model);
        if (!Common::query()->getConnection()->getSchemaBuilder()->hasTable($table)) {
            return false;
        }
        return $this->hasDynamicTableColumn($table, 'price');
    }

    public function hasDynamicTableColumn(string $tableName, string $column): bool
    {
        try {
            return Common::query()->getConnection()->getSchemaBuilder()->hasColumn($tableName, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getPrice($record): float
    {
        if (!$record) {
            return 0.0;
        }
        $row = is_object($record) ? get_object_vars($record) : (array) $record;
        return round((float) ($row['price'] ?? 0), 2);
    }

    /**
     * 判断该用户是否已付费该内容
     */
    public function isPaid(string $model, int $contentId, $user = null): bool
    {
        $userId = $this->normalizeUser($user)['id'];
        if ($userId <= 0 || $contentId <= 0) {
            return false;
        }
        $count = Common::query()
            ->from('module_formtools_paid_records')
            ->where([
                'model_identification' => $model,
                'content_id' => $contentId,
                'user_id' => $userId,
                'status' => self::STATUS_PAID,
            ])
            ->where(function ($q) {
                $q->whereNull('expire_at')->orWhere('expire_at', '>', date('Y-m-d H:i:s'));
            })
            ->count();
        return $count > 0;
    }

    /**
     * 取一条最近的已支付记录（用于查看购买时间等）
     */
    public function getPaidRecord(string $model, int $contentId, $user = null): ?object
    {
        $userId = $this->normalizeUser($user)['id'];
        if ($userId <= 0 || $contentId <= 0) {
            return null;
        }
        return Common::query()
            ->from('module_formtools_paid_records')
            ->where([
                'model_identification' => $model,
                'content_id' => $contentId,
                'user_id' => $userId,
                'status' => self::STATUS_PAID,
            ])
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * 详情页统一价格状态（模板调用入口）
     */
    public function getPriceStatus(string $model, $record, $user = null): array
    {
        $userInfo = $this->normalizeUser($user);
        $contentId = (int) (is_object($record) ? ($record->id ?? 0) : ($record['id'] ?? 0));
        $price = $this->getPrice($record);
        $isPaid = $price > 0 ? $this->isPaid($model, $contentId, $userInfo) : true;
        $needPay = $price > 0 && !$isPaid;

        return [
            'price' => $price,
            'price_label' => $price > 0 ? ('￥' . number_format($price, 2)) : '免费',
            'is_paid' => $isPaid,
            'need_pay' => $needPay,
            'is_login' => $userInfo['id'] > 0,
            'user_id' => $userInfo['id'],
            'pay_url' => $this->payUrl($model, $contentId),
            'check_url' => '',
            'order_no' => '',
        ];
    }

    public function payUrl(string $model, int $contentId): string
    {
        return url('/formtools/buy/' . $model . '/' . $contentId);
    }

    public function checkUrl(string $orderNo): string
    {
        return url('/formtools/check-pay/' . $orderNo);
    }

    /**
     * 创建付费订单（status=0 待支付）
     */
    public function createOrder(string $model, int $contentId, $user, string $payMethod = 'WeChat', string $payType = 'public'): array
    {
        $userInfo = $this->normalizeUser($user);
        if ($userInfo['id'] <= 0) {
            return ['code' => 401, 'msg' => '请先登录后再购买'];
        }
        $row = Common::query()->from($this->dynamicTable($model))->where('id', $contentId)->first();
        if (!$row) {
            return ['code' => 404, 'msg' => '内容不存在'];
        }
        $price = $this->getPrice($row);
        if ($price <= 0) {
            return ['code' => 400, 'msg' => '该内容为免费内容，无需购买'];
        }
        if ($this->isPaid($model, $contentId, $userInfo)) {
            return ['code' => 400, 'msg' => '您已购买过该内容'];
        }
        $orderNo = $this->generateOrderNo($model, $userInfo['id']);
        $insertId = Common::query()->from('module_formtools_paid_records')->insertGetId([
            'user_id' => $userInfo['id'],
            'user_type' => $userInfo['type'],
            'user_label' => $userInfo['label'],
            'model_identification' => $model,
            'content_id' => $contentId,
            'content_title' => $this->resolveContentTitle($row),
            'order_no' => $orderNo,
            'amount' => $price,
            'pay_method' => $payMethod,
            'pay_type' => $payType,
            'status' => self::STATUS_PENDING,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if (!$insertId) {
            return ['code' => 500, 'msg' => '创建订单失败'];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'order_no' => $orderNo,
                'amount' => $price,
                'pay_method' => $payMethod,
                'pay_type' => $payType,
            ],
        ];
    }

    public function generateOrderNo(string $model, int $userId): string
    {
        return 'FT' . date('YmdHis') . mt_rand(1000, 9999) . str_pad((string) $userId, 6, '0', STR_PAD_LEFT);
    }

    public function getOrder(string $orderNo): ?object
    {
        return Common::query()
            ->from('module_formtools_paid_records')
            ->where('order_no', $orderNo)
            ->first();
    }

    /**
     * 标记为已支付（Pay 回调时调用）
     */
    public function markAsPaid(string $orderNo, array $callbackData = []): bool
    {
        $order = $this->getOrder($orderNo);
        if (!$order) {
            return false;
        }
        if ((int) $order->status === self::STATUS_PAID) {
            return true;
        }
        $now = date('Y-m-d H:i:s');
        $extra = (array) ($order->extra ? json_decode($order->extra, true) : []);
        $extra['callback'] = $callbackData;
        return (bool) Common::query()
            ->from('module_formtools_paid_records')
            ->where('order_no', $orderNo)
            ->update([
                'status' => self::STATUS_PAID,
                'paid_at' => $now,
                'out_trade_no' => (string) ($callbackData['transaction_id'] ?? $callbackData['trade_no'] ?? $order->out_trade_no ?? ''),
                'extra' => json_encode($extra, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);
    }

    public function markCanceled(string $orderNo): bool
    {
        $order = $this->getOrder($orderNo);
        if (!$order) {
            return false;
        }
        if ((int) $order->status === self::STATUS_PAID) {
            return false;
        }
        return (bool) Common::query()
            ->from('module_formtools_paid_records')
            ->where('order_no', $orderNo)
            ->update([
                'status' => self::STATUS_CANCELED,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * 对内容字段做付费遮罩
     *  - 已付费：原样返回
     *  - 未付费且 price>0：editor/text 类型截断为前 N 字，其他类型置空
     *  - 用户未登录：同样视为未付费
     */
    public function maskContent(string $model, array $fields, $record, $user = null): array
    {
        $userInfo = $this->normalizeUser($user);
        $contentId = (int) (is_object($record) ? ($record->id ?? 0) : ($record['id'] ?? 0));
        $price = $this->getPrice($record);
        $isPaid = $price > 0 ? $this->isPaid($model, $contentId, $userInfo) : true;
        $row = $record ? (is_object($record) ? get_object_vars($record) : (array) $record) : [];

        $skipKeys = ['id', 'pid', 'uid', 'status', 'remark', 'price', 'sort', 'sorts', 'created_at', 'updated_at',
            'seo_title', 'seo_keywords', 'seo_description', 'access_count', 'good_count', 'comment_count', 'download_count'];

        foreach ($fields as &$field) {
            $ident = (string) ($field['identification'] ?? '');
            if ($ident === '' || in_array($ident, $skipKeys, true)) {
                continue;
            }
            $raw = $row[$ident] ?? '';
            $field['raw'] = $raw;
            if ($isPaid) {
                $field['masked'] = false;
                $field['display'] = (string) $raw;
                continue;
            }
            $formtype = (string) ($field['formtype'] ?? '');
            $field['masked'] = true;
            if (in_array($formtype, ['editor', 'textarea', 'text', 'number', 'price'], true)) {
                $snippet = mb_substr(strip_tags((string) $raw), 0, self::MASK_PREVIEW_LENGTH);
                $field['display'] = $snippet . (mb_strlen(strip_tags((string) $raw)) > self::MASK_PREVIEW_LENGTH ? '…' : '');
            } elseif (in_array($formtype, ['image', 'imageAjax', 'upload', 'uploadAjax'], true)) {
                $field['display'] = '';
            } else {
                $field['display'] = (string) $raw;
            }
        }
        unset($field);
        return $fields;
    }

    /**
     * 列表页批量检测哪些内容已付费（避免 N+1）
     * @return array [content_id => bool]
     */
    public function markPaidList(string $model, array $contentIds, $user = null): array
    {
        $userId = $this->normalizeUser($user)['id'];
        $result = array_fill_keys(array_map('intval', $contentIds), false);
        if ($userId <= 0 || empty($contentIds)) {
            return $result;
        }
        $rows = Common::query()
            ->from('module_formtools_paid_records')
            ->where('model_identification', $model)
            ->where('user_id', $userId)
            ->where('status', self::STATUS_PAID)
            ->whereIn('content_id', array_map('intval', $contentIds))
            ->where(function ($q) {
                $q->whereNull('expire_at')->orWhere('expire_at', '>', date('Y-m-d H:i:s'));
            })
            ->pluck('content_id')
            ->toArray();
        foreach ($rows as $cid) {
            $result[(int) $cid] = true;
        }
        return $result;
    }

    public function resolveContentTitle($record): string
    {
        if (!$record) {
            return '';
        }
        $row = is_object($record) ? get_object_vars($record) : (array) $record;
        foreach (['title', 'name', 'subject'] as $candidate) {
            if (!empty($row[$candidate])) {
                return (string) $row[$candidate];
            }
        }
        return 'ID #' . (int) ($row['id'] ?? 0);
    }
}
