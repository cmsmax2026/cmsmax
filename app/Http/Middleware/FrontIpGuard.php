<?php

namespace App\Http\Middleware;

use App\Support\Security\FrontTrafficGuard;
use Closure;

/**
 * 前台 IP 与流量守卫（全局）
 *
 * 挂在 Kernel 的全局中间件栈，排在 TrustProxies 之后，
 * 这样所有模块的前台路由（Bbs / GitCenter / Store …）都会经过这里，各模块不用改代码。
 *
 * 两种请求不处理，交给原有机制：
 * - 后台 /admin/* ：后台有自己的 CheckIpBlacklist 与 IP 访问模式
 * - 静态资源：一个页面会带十几个静态文件，计数的话正常访客也会被误封
 */
class FrontIpGuard
{
    public function handle($request, Closure $next)
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $ip = (string) (function_exists('get_ip') ? get_ip() : $request->ip());

        $result = FrontTrafficGuard::inspect($ip);

        if (!$result['blocked']) {
            return $next($request);
        }

        $status = $result['source'] === 'rate_limit' ? 429 : 403;

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 0,
                'msg' => $result['reason'],
                'data' => [
                    'retry_after' => $result['retry_after'],
                ],
            ], $status)->withHeaders($this->headers($result));
        }

        return response($this->html($result), $status)->withHeaders($this->headers($result));
    }

    private function shouldSkip($request): bool
    {
        // 后台走原来的检查，不重复限流
        if ($request->is('admin') || $request->is('admin/*')) {
            return true;
        }

        $path = (string) $request->path();

        if ($path === '' || preg_match('/\.(js|css|map|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|eot|mp4|mp3|pdf|zip|rar|txt)$/i', $path)) {
            return true;
        }

        return false;
    }

    private function headers(array $result): array
    {
        return $result['retry_after'] > 0 ? ['Retry-After' => (string) $result['retry_after']] : [];
    }

    private function html(array $result): string
    {
        $title = $result['source'] === 'rate_limit' ? '请求过于频繁' : '访问被拒绝';
        $retry = $result['retry_after'] > 0
            ? '<p style="color:#64748b;font-size:14px;">请 ' . (int) ceil($result['retry_after'] / 60) . ' 分钟后再试。</p>'
            : '';

        return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>' . $title . '</title></head>'
            . '<body style="margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;'
            . 'background:#f5f7fb;color:#0f172a;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',\'PingFang SC\',\'Microsoft YaHei\',sans-serif;">'
            . '<div style="text-align:center;padding:32px;background:#fff;border-radius:14px;'
            . 'box-shadow:0 14px 34px rgba(15,23,42,.08);max-width:420px;">'
            . '<div style="font-size:40px;font-weight:800;color:#2563eb;">' . ($result['source'] === 'rate_limit' ? '429' : '403') . '</div>'
            . '<h1 style="margin:12px 0 8px;font-size:20px;">' . $title . '</h1>'
            . '<p style="margin:0;color:#475569;font-size:14px;line-height:1.8;">' . e($result['reason']) . '</p>'
            . $retry
            . '</div></body></html>';
    }
}
