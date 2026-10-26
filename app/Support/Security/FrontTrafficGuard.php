<?php

namespace App\Support\Security;

use Illuminate\Support\Facades\Cache;

/**
 * 前台流量守卫（L3 框架层）
 *
 * 只负责"流量维度"的判断：每分钟请求数、违规累计、触发自动封禁。
 * IP 名单本身交给 IpAccessRuleService，两张皮分开，方便单独调参。
 *
 * 缓存适配：开发用 file、生产用 redis 都能跑；
 * file 驱动下写操作有锁竞争，所以计数用 Cache::increment 的惰性初始化，
 * 并尽量把"写库"动作收敛到真正触发封禁的那一刻。
 */
class FrontTrafficGuard
{
    public const SETTING_ENABLED = 'front_guard_enabled';
    public const SETTING_RATE_LIMIT = 'front_guard_rate_limit';
    public const SETTING_VIOLATION_LIMIT = 'front_guard_violation_limit';
    public const SETTING_BAN_MINUTES = 'front_guard_ban_minutes';
    public const SETTING_VIOLATION_WINDOW = 'front_guard_violation_window';

    /**
     * 默认配置，后台没配过时用它
     */
    public const DEFAULTS = [
        self::SETTING_ENABLED => 0,
        self::SETTING_RATE_LIMIT => 120,
        self::SETTING_VIOLATION_LIMIT => 5,
        self::SETTING_BAN_MINUTES => 30,
        self::SETTING_VIOLATION_WINDOW => 10,
    ];

    /**
     * 检查一次前台请求
     *
     * @return array{blocked:bool, reason:string, source:string, retry_after:int, remaining:int}
     */
    public static function inspect(string $ip): array
    {
        $pass = [
            'blocked' => false,
            'reason' => '',
            'source' => '',
            'retry_after' => 0,
            'remaining' => 0,
        ];

        if (!self::enabled()) {
            return $pass;
        }

        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return $pass;
        }

        // 1. 先看名单（白名单直接放行，黑名单直接拦）
        $ruleResult = IpAccessRuleService::inspect($ip, IpAccessRuleService::SCOPE_FRONT);
        if ($ruleResult['blocked']) {
            return [
                'blocked' => true,
                'reason' => 'IP 已被列入封禁名单',
                'source' => $ruleResult['source'],
                'retry_after' => 0,
                'remaining' => 0,
            ];
        }

        if (($ruleResult['source'] ?? '') === 'whitelist_setting' || ($ruleResult['source'] ?? '') === 'allow_rule') {
            // 白名单不计数，避免把可信来源的统计灌满
            return $pass;
        }

        // 2. 每分钟请求数
        $rateLimit = self::setting(self::SETTING_RATE_LIMIT);
        if ($rateLimit > 0) {
            $count = self::increment(self::rateKey($ip), 120);
            $pass['remaining'] = max(0, $rateLimit - $count);

            if ($count > $rateLimit) {
                $violations = self::increment(self::violationKey($ip), self::setting(self::SETTING_VIOLATION_WINDOW) * 60);
                $reason = sprintf('%d 分钟内请求过于频繁（第 %d 次违规）', self::setting(self::SETTING_VIOLATION_WINDOW), $violations);

                if ($violations >= self::setting(self::SETTING_VIOLATION_LIMIT)) {
                    $banned = IpAccessRuleService::autoBan(
                        $ip,
                        IpAccessRuleService::SCOPE_FRONT,
                        '前台请求频率超限，自动封禁',
                        self::setting(self::SETTING_BAN_MINUTES)
                    );

                    if ($banned) {
                        self::forget(self::violationKey($ip));

                        return [
                            'blocked' => true,
                            'reason' => '请求过于频繁，已被自动封禁',
                            'source' => 'auto_ban',
                            'retry_after' => self::setting(self::SETTING_BAN_MINUTES) * 60,
                            'remaining' => 0,
                        ];
                    }
                }

                IpAccessRuleService::logBlockEvent($ip, IpAccessRuleService::SCOPE_FRONT, 'rate_limit', null, $reason);

                return [
                    'blocked' => true,
                    'reason' => $reason,
                    'source' => 'rate_limit',
                    'retry_after' => self::secondsUntilNextMinute(),
                    'remaining' => 0,
                ];
            }
        }

        return $pass;
    }

    public static function enabled(): bool
    {
        return (int) self::setting(self::SETTING_ENABLED) === 1;
    }

    /**
     * 读取配置：优先系统设置，没配过用默认值
     */
    public static function setting(string $key, $default = null)
    {
        if (!function_exists('cacheGlobalSettingsByKey')) {
            return $default ?? (self::DEFAULTS[$key] ?? 0);
        }

        $value = cacheGlobalSettingsByKey($key);
        if ($value === '' || $value === null) {
            return $default ?? (self::DEFAULTS[$key] ?? 0);
        }

        return is_numeric($value) ? $value + 0 : $value;
    }

    private static function rateKey(string $ip): string
    {
        return 'front_guard:rate:' . md5($ip) . ':' . date('YmdHi');
    }

    private static function violationKey(string $ip): string
    {
        return 'front_guard:violation:' . md5($ip);
    }

    /**
     * 兼容 file / redis：increment 失败时退回 get+put
     */
    private static function increment(string $key, int $ttl): int
    {
        try {
            if (!Cache::has($key)) {
                Cache::put($key, 0, $ttl);
            }

            $value = Cache::increment($key);
            Cache::put($key, $value, $ttl);

            return (int) $value;
        } catch (\Throwable $throwable) {
            $value = (int) Cache::get($key, 0) + 1;
            Cache::put($key, $value, $ttl);

            return $value;
        }
    }

    private static function forget(string $key): void
    {
        try {
            Cache::forget($key);
        } catch (\Throwable $throwable) {
            // 缓存不可用时忽略
        }
    }

    private static function secondsUntilNextMinute(): int
    {
        return max(1, 60 - (int) date('s'));
    }
}
