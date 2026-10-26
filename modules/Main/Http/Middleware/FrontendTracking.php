<?php

namespace Modules\Main\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

/**
 * 前台资源注入（GJsCss：Get JS / CSS）
 *
 * GJsCss 是一个通用的「前台资源挂载点」：监听 App\Events\GJsCss 的监听器可以返回
 * 任意前端片段——<script>（JS）或 <style>/<link rel="stylesheet">（CSS），hook 返回的是
 * 一串原始 HTML 字符串，由本中间件拼进响应体。它和 AccessLog 不同：AccessLog 只在中间件
 * 里派发一个日志事件，而 GJsCss 必须真正出现在页面 HTML 里才会执行，只能「注入响应体」。
 *
 * 原注入点依赖主题/模块布局手动调用 hook("GJsCss")（default 主题有、mxzcms 主题没有、
 * 各模块自定义布局参差不齐），覆盖残缺。这里统一在 web 中间件组里对所有前台 HTML 响应
 * 注入，保证全部前台页面覆盖：
 *   - CSS（<style> / <link rel="stylesheet">）注入到 <head> 前，避免 FOUC；
 *   - JS（<script>）注入到 </body> 前；
 *   - 对每条片段做幂等判断（响应体已包含该片段则跳过），避免与主题/模块布局里已有的
 *     hook("GJsCss") 调用重复注入、重复上报。
 */
class FrontendTracking
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // 仅前台 HTML 响应注入；后台（/admin 前缀）、API、JSON 请求跳过
        if ($request->is('admin/*') || $request->expectsJson()) {
            return $response;
        }

        if (!$response instanceof Response) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type');
        if (stripos($contentType, 'text/html') === false) {
            return $response;
        }

        $content = (string) $response->getContent();

        // 没有 </body> 无法正常挂载，跳过（如分片/流式响应）
        if (!str_contains($content, '</body>')) {
            return $response;
        }

        $headInject = '';
        $bodyInject = '';

        foreach (hook('GJsCss') as $snippet) {
            if (!is_string($snippet) || $snippet === '') {
                continue;
            }

            // 已被主题/模块布局注入过则跳过，避免重复上报
            if (str_contains($content, $snippet)) {
                continue;
            }

            if ($this->isCss($snippet)) {
                $headInject .= $snippet . "\n";
            } else {
                $bodyInject .= $snippet . "\n";
            }
        }

        if ($headInject !== '') {
            if (str_contains($content, '</head>')) {
                $content = substr_replace($content, $headInject . "</head>", strrpos($content, '</head>'), 6);
            } else {
                // 极少数没有 <head> 的页面，退而求其次放进 body
                $bodyInject = $headInject . $bodyInject;
            }
        }

        if ($bodyInject !== '') {
            $content = substr_replace($content, $bodyInject . "</body>", strrpos($content, '</body>'), 6);
        }

        return $response->setContent($content);
    }

    /**
     * 判断片段是否为 CSS（<style> 或 <link rel="stylesheet">），决定注入到 <head> 还是 <body>
     */
    private function isCss(string $snippet): bool
    {
        if (stripos($snippet, '<style') !== false) {
            return true;
        }

        return stripos($snippet, '<link') !== false
            && stripos($snippet, 'stylesheet') !== false;
    }
}
