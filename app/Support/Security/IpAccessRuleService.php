<?php

namespace App\Support\Security;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Modules\System\Models\IpAccessRule;
use Modules\System\Models\SystemIpBlockLog;

class IpAccessRuleService
{
    public const RULE_ALLOW = 'allow';
    public const RULE_DENY = 'deny';
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_AUTO = 'auto';
    public const MATCH_EXACT = 'exact';
    public const MATCH_CIDR = 'cidr';
    public const MATCH_RANGE = 'range';
    public const SCOPE_ALL = 'all';
    public const SCOPE_ADMIN = 'admin';
    public const SCOPE_FRONT = 'front';

    public static function scopes(): array
    {
        return [self::SCOPE_ALL, self::SCOPE_ADMIN, self::SCOPE_FRONT];
    }

    public static function inspect(string $ip, string $scope = self::SCOPE_ADMIN): array
    {
        $ip = trim($ip);
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return static::allowResult();
        }

        if (static::matchesStaticRules($ip, (string) cacheGlobalSettingsByKey('whitelist_ip'))) {
            return static::allowResult('whitelist_setting', 'white_list');
        }

        $allowRule = static::findMatchedDatabaseRule($ip, [self::RULE_ALLOW], $scope);
        if ($allowRule) {
            return static::allowResult('allow_rule', $allowRule->rule_value, $allowRule);
        }

        $denyRule = static::findMatchedDatabaseRule($ip, [self::RULE_DENY], $scope);
        if ($denyRule) {
            static::touchMatchedRule($denyRule, $ip, $scope);
            static::logBlockEvent($ip, $scope, 'database_rule', $denyRule);
            return static::denyResult('database_rule', $denyRule->rule_value, $denyRule);
        }

        if (static::matchesStaticRules($ip, (string) cacheGlobalSettingsByKey('blacklist_ip'))) {
            static::logBlockEvent($ip, $scope, 'blacklist_setting');
            return static::denyResult('blacklist_setting', 'blacklist_ip');
        }

        if ($scope === self::SCOPE_ADMIN && static::isWhitelistOnlyModeEnabled()) {
            static::logBlockEvent($ip, $scope, 'whitelist_only_mode');
            return static::denyResult('whitelist_only_mode', 'admin_ip_access_mode');
        }

        return static::allowResult();
    }

    public static function autoBanCurrentIpForLoginFailure(int $failedAttempts, ?string $username = null): void
    {
        $threshold = max(0, intval(cacheGlobalSettingsByKey('ip_auto_ban_threshold')));
        $minutes = max(0, intval(cacheGlobalSettingsByKey('ip_auto_ban_minutes')));
        if ($threshold <= 0 || $minutes <= 0 || $failedAttempts < $threshold) {
            return;
        }

        $ip = trim((string) get_ip());
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return;
        }

        $reason = '后台登录失败次数达到阈值';
        if ($username !== null && trim($username) !== '') {
            $reason .= '（账号：' . trim($username) . '）';
        }

        static::autoBan($ip, self::SCOPE_ADMIN, $reason, $minutes);
    }

    /**
     * 通用自动封禁：前台、后台、插件都可以调用
     *
     * @return bool 是否真的写入了封禁规则（白名单、表不存在、参数为 0 时返回 false）
     */
    public static function autoBan(string $ip, string $scope, string $reason, int $minutes = 0): bool
    {
        $ip = trim($ip);
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        if (!in_array($scope, static::scopes(), true)) {
            $scope = self::SCOPE_ALL;
        }

        // 白名单永远不会被自动封禁，避免把管理员和监控自己关在门外
        if (static::matchesStaticRules($ip, (string) cacheGlobalSettingsByKey('whitelist_ip'))) {
            return false;
        }

        if (static::findMatchedDatabaseRule($ip, [self::RULE_ALLOW], $scope)) {
            return false;
        }

        if ($minutes <= 0 || !static::hasRuleTable()) {
            return false;
        }

        $existing = IpAccessRule::query()
            ->where('scope', $scope)
            ->where('rule_type', self::RULE_DENY)
            ->where('match_type', self::MATCH_EXACT)
            ->where('rule_value', $ip)
            ->where('source_type', self::SOURCE_AUTO)
            ->first();

        $banCount = $existing ? (intval($existing->ban_count) + 1) : 1;
        $effectiveMinutes = min($minutes * (2 ** max(0, $banCount - 1)), 1440);
        $expiresAt = Carbon::now()->addMinutes($effectiveMinutes);
        $reason .= sprintf(' [第 %d 次自动封禁，时长 %d 分钟]', $banCount, $effectiveMinutes);

        IpAccessRule::query()->updateOrCreate(
            [
                'scope' => $scope,
                'rule_type' => self::RULE_DENY,
                'match_type' => self::MATCH_EXACT,
                'rule_value' => $ip,
                'source_type' => self::SOURCE_AUTO,
            ],
            [
                'status' => 1,
                'reason' => $reason,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'ban_count' => $banCount,
                'updated_at' => now(),
            ]
        );

        static::logBlockEvent($ip, $scope, 'auto_ban');

        return true;
    }

    /**
     * 手动添加规则（后台 IP 规则页用）
     */
    public static function addRule(array $payload): ?IpAccessRule
    {
        if (!static::hasRuleTable()) {
            return null;
        }

        $ip = trim((string) ($payload['rule_value'] ?? ''));
        $scope = (string) ($payload['scope'] ?? self::SCOPE_FRONT);
        if ($ip === '' || !in_array($scope, static::scopes(), true)) {
            return null;
        }

        $now = now()->format('Y-m-d H:i:s');
        $expiresAt = trim((string) ($payload['expires_at'] ?? ''));

        return IpAccessRule::query()->create([
            'scope' => $scope,
            'rule_type' => (string) ($payload['rule_type'] ?? self::RULE_DENY) === self::RULE_ALLOW ? self::RULE_ALLOW : self::RULE_DENY,
            'match_type' => trim((string) ($payload['match_type'] ?? '')) ?: static::detectMatchType($ip),
            'rule_value' => $ip,
            'source_type' => self::SOURCE_MANUAL,
            'status' => array_key_exists('status', $payload) && empty($payload['status']) ? 0 : 1,
            'reason' => trim((string) ($payload['reason'] ?? '')),
            'expires_at' => $expiresAt !== '' ? $expiresAt : null,
            'hit_count' => 0,
            'last_hit_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * 移除规则（解封 / 删除白名单都用它）
     */
    public static function removeRule(int $id): bool
    {
        if (!static::hasRuleTable() || $id <= 0) {
            return false;
        }

        return (bool) IpAccessRule::query()->where('id', $id)->delete();
    }

    /**
     * 清理已过期的规则
     */
    public static function clearExpired(): int
    {
        if (!static::hasRuleTable()) {
            return 0;
        }

        return IpAccessRule::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();
    }

    private static function findMatchedDatabaseRule(string $ip, array $ruleTypes, string $scope): ?IpAccessRule
    {
        if (!static::hasRuleTable()) {
            return null;
        }

        $rules = IpAccessRule::query()
            ->where('status', 1)
            ->whereIn('scope', [self::SCOPE_ALL, $scope])
            ->whereIn('rule_type', $ruleTypes)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByRaw("CASE WHEN scope = ? THEN 0 WHEN scope = ? THEN 1 ELSE 2 END", [$scope, self::SCOPE_ALL])
            ->orderByDesc('id')
            ->get();

        foreach ($rules as $rule) {
            if (static::matchesRule($ip, $rule->match_type, $rule->rule_value)) {
                return $rule;
            }
        }

        return null;
    }

    private static function matchesStaticRules(string $ip, string $rawRules): bool
    {
        foreach (static::normalizeRuleTokens($rawRules) as $ruleValue) {
            if (static::matchesRule($ip, static::detectMatchType($ruleValue), $ruleValue)) {
                return true;
            }
        }

        return false;
    }

    private static function normalizeRuleTokens(string $rawRules): array
    {
        $tokens = preg_split('/[\r\n,;|]+/', $rawRules) ?: [];
        $tokens = array_map('trim', $tokens);
        return array_values(array_filter($tokens, static function ($item) {
            return $item !== '';
        }));
    }

    /**
     * 校验规则值是否为合法的 IPv4 / CIDR / IP 段
     */
    public static function isValidRuleValue(string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }

        $matchType = static::detectMatchType($value);

        if ($matchType === self::MATCH_EXACT) {
            return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
        }

        if ($matchType === self::MATCH_CIDR) {
            [$subnet, $mask] = array_pad(explode('/', $value, 2), 2, null);
            $subnet = trim((string) $subnet);
            $mask = intval($mask);

            return filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
                && $mask >= 0 && $mask <= 32;
        }

        if ($matchType === self::MATCH_RANGE) {
            [$start, $end] = array_pad(explode('-', $value, 2), 2, null);
            $start = trim((string) $start);
            $end = trim((string) $end);

            return filter_var($start, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
                && filter_var($end, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
        }

        return false;
    }

    /**
     * 根据写法推断匹配方式：带 / 的是 CIDR，带 - 的是范围，其余按单 IP
     */
    public static function detectMatchType(string $ruleValue): string
    {
        if (strpos($ruleValue, '/') !== false) {
            return self::MATCH_CIDR;
        }

        if (strpos($ruleValue, '-') !== false) {
            return self::MATCH_RANGE;
        }

        return self::MATCH_EXACT;
    }

    private static function matchesRule(string $ip, ?string $matchType, ?string $ruleValue): bool
    {
        $ruleValue = trim((string) $ruleValue);
        if ($ruleValue === '') {
            return false;
        }

        $matchType = $matchType ?: static::detectMatchType($ruleValue);
        if ($matchType === self::MATCH_EXACT) {
            return $ip === $ruleValue;
        }

        if ($matchType === self::MATCH_CIDR) {
            return static::matchesCidr($ip, $ruleValue);
        }

        if ($matchType === self::MATCH_RANGE) {
            return static::matchesRange($ip, $ruleValue);
        }

        return false;
    }

    private static function matchesCidr(string $ip, string $ruleValue): bool
    {
        [$subnet, $mask] = array_pad(explode('/', $ruleValue, 2), 2, null);
        $subnet = trim((string) $subnet);
        $mask = intval($mask);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false
            || filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false
            || $mask < 0
            || $mask > 32) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = $mask === 0 ? 0 : -1 << (32 - $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    private static function matchesRange(string $ip, string $ruleValue): bool
    {
        [$start, $end] = array_pad(explode('-', $ruleValue, 2), 2, null);
        $start = trim((string) $start);
        $end = trim((string) $end);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false
            || filter_var($start, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false
            || filter_var($end, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        $ipLong = ip2long($ip);
        return $ipLong >= ip2long($start) && $ipLong <= ip2long($end);
    }

    private static function touchMatchedRule(IpAccessRule $rule, string $ip, string $scope): void
    {
        $rule->forceFill([
            'hit_count' => intval($rule->hit_count) + 1,
            'last_hit_at' => now(),
        ])->save();
    }

    /**
     * 记录访问被拒绝/拦截事件
     */
    public static function logBlockEvent(string $ip, string $scope, string $source, ?IpAccessRule $rule = null, string $reason = ''): void
    {
        try {
            $payload = [
                'ip' => $ip,
                'scope' => $scope,
                'source' => $source,
                'rule_id' => $rule->id ?? 0,
                'rule_value' => $rule->rule_value ?? '',
                'request_path' => request()->getPathInfo(),
            ];

            hook('Loger', [
                'module' => 'System',
                'type' => 'operate',
                'two_type' => 'ipBlock',
                'params' => $payload,
                'remark' => $reason ?: 'IP 访问命中封禁规则',
                'unique_id' => $ip,
                'requestid' => request()->requestid ?? '',
            ]);

            if (class_exists(SystemIpBlockLog::class) && Schema::hasTable(SystemIpBlockLog::TABLE_NAME)) {
                SystemIpBlockLog::query()->create([
                    'ip' => $ip,
                    'scope' => $scope,
                    'source' => $source,
                    'rule_id' => $rule->id ?? 0,
                    'rule_value' => $rule->rule_value ?? '',
                    'request_path' => request()->getPathInfo(),
                    'user_agent' => request()->userAgent() ?? '',
                    'reason' => $reason ?: static::denyReason($source, $rule),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            // ignore logging failures
        }
    }

    private static function denyReason(string $source, ?IpAccessRule $rule): string
    {
        if ($source === 'database_rule') {
            return '命中数据库封禁规则' . ($rule ? '（' . $rule->rule_value . '）' : '');
        }
        if ($source === 'blacklist_setting') {
            return '命中系统黑名单设置';
        }
        if ($source === 'whitelist_only_mode') {
            return '后台仅白名单模式拒绝访问';
        }
        if ($source === 'auto_ban') {
            return '触发自动封禁';
        }
        if ($source === 'rate_limit') {
            return '请求频率超限';
        }

        return '访问被拒绝';
    }

    private static function allowResult(string $source = '', string $matchedRule = '', ?IpAccessRule $rule = null): array
    {
        return [
            'blocked' => false,
            'source' => $source,
            'matched_rule' => $matchedRule,
            'rule' => $rule,
        ];
    }

    private static function denyResult(string $source, string $matchedRule, ?IpAccessRule $rule = null): array
    {
        return [
            'blocked' => true,
            'source' => $source,
            'matched_rule' => $matchedRule,
            'rule' => $rule,
        ];
    }

    private static function hasRuleTable(): bool
    {
        return class_exists(IpAccessRule::class) && Schema::hasTable(IpAccessRule::TABLE_NAME);
    }

    private static function isWhitelistOnlyModeEnabled(): bool
    {
        return trim((string) cacheGlobalSettingsByKey('admin_ip_access_mode')) === 'whitelist_only';
    }
}
