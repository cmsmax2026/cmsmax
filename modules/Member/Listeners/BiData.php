<?php

namespace Modules\Member\Listeners;

use App\Events\BiData as BiDataEvent;
use Illuminate\Support\Facades\DB;

/**
 * Member 模块主动注册 BI 指标（通过 hook('BiData')）。
 * 注：members 主表与认证/VIP 表未启用 Eloquent 时间戳，故仅提供计数类指标。
 */
class BiData
{
    public function handle(BiDataEvent $event)
    {
        return [
            [
                'key' => 'member_total',
                'title' => '会员总数',
                'module' => 'Member',
                'type' => 'kpi',
                'unit' => '人',
                'resolver' => function () {
                    return ['value' => (int) DB::table('members')->count()];
                },
            ],
            [
                'key' => 'member_vip_paid',
                'title' => '已开通VIP人数',
                'module' => 'Member',
                'type' => 'kpi',
                'unit' => '人',
                'resolver' => function () {
                    return ['value' => (int) DB::table('module_member_vip_order')
                        ->where('pay_status', 1)->distinct()->count('uid')];
                },
            ],
            [
                'key' => 'member_auth_passed',
                'title' => '实名认证通过数',
                'module' => 'Member',
                'type' => 'kpi',
                'unit' => '人',
                'resolver' => function () {
                    return ['value' => (int) DB::table('module_member_auth')
                        ->where('status', 1)->count()];
                },
            ],
        ];
    }
}
