<?php

namespace App\Events;

/**
 * BI 指标注册事件。
 *
 * BI 模块通过 hook('BiData') 触发，各业务模块在自己的
 * Listeners/BiData.php 中监听本事件并返回「指标定义」数组，
 * 实现「各模块主动注册指标」。
 *
 * 指标定义格式（由 BI 模块约定）：
 * [
 *   'key'      => 'ad_spend_trend',                 // 唯一标识
 *   'title'    => '广告消耗趋势',                    // 展示名
 *   'module'   => 'AD',                             // 所属模块
 *   'type'     => 'line',                           // kpi | line | bar | pie | table
 *   'unit'     => '¥',                              // 单位（可选）
 *   'resolver' => function (array $params) {        // 返回数据，必须可序列化（会被缓存）
 *       return [...];                               // 见 MetricService 约定
 *   },
 * ]
 */
class BiData
{
    public $params;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }
}
