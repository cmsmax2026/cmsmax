<?php

namespace App\Support\Auth;

/**
 * 前台「登录 / 注册后回到来源模块」的地址管理
 *
 * 背景：模块（如 Bbs）把游客弹去 /login，CMS 登录成功后原来固定跳 /member，
 * 用户就脱离了原来的模块。这里提供统一的来源记录与回跳能力：
 *
 *   1. 中间件 / 控制器在跳登录页前调用 HomeLoginRedirect::toLogin() 记住来源；
 *   2. 模块在渲染「登录 / 注册」入口时用 HomeLoginRedirect::loginUrl() 带上 ?redirect=；
 *   3. 登录 / 注册成功后 HomeLoginRedirect::take($fallback) 取回并清空。
 *
 * 安全：只放行同源站内地址，杜绝开放重定向（open redirect）。
 */
class HomeLoginRedirect
{
    public const SESSION_KEY = 'home_login_redirect';

    /** 这些路径不会被记为来源，避免「登录后又跳回登录页」的死循环 */
    private const SKIP_PATHS = ['login', 'register', 'forgot', 'logout', 'member'];

    /**
     * 记住来源地址；不传则记录当前完整 URL。返回实际写入的地址（非法则为空串）。
     */
    public static function remember(?string $url = null): string
    {
        $url = self::normalize($url ?? self::currentUrl());
        if ($url === '') {
            return '';
        }

        session()->put(self::SESSION_KEY, $url);

        return $url;
    }

    /**
     * 从请求的 ?redirect= 参数里捕获来源（没有该参数时不动已有记录）。
     */
    public static function captureFromRequest($request = null): string
    {
        try {
            $request = $request ?: request();
            $redirect = trim((string) $request->input('redirect', ''));
        } catch (\Throwable $exception) {
            return '';
        }

        return $redirect === '' ? '' : self::remember($redirect);
    }

    /** 只读取来源，不清空 */
    public static function peek(): string
    {
        return self::normalize((string) session(self::SESSION_KEY, ''));
    }

    /** 读取并清空来源，取不到时回落到 $fallback（fallback 不做认证页过滤） */
    public static function take(?string $fallback = null): string
    {
        $url = self::peek();
        session()->forget(self::SESSION_KEY);

        if ($url !== '') {
            return $url;
        }

        return self::normalize((string) ($fallback ?? ''), false);
    }

    public static function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * 记住当前地址并返回一个跳登录页的重定向响应
     */
    public static function toLogin(?string $url = null)
    {
        self::remember($url);

        return redirect(self::loginUrl());
    }

    /** 带 ?redirect= 的登录页地址 */
    public static function loginUrl(?string $returnUrl = null): string
    {
        return self::authUrl('login', $returnUrl);
    }

    /** 带 ?redirect= 的注册页地址 */
    public static function registerUrl(?string $returnUrl = null): string
    {
        return self::authUrl('register', $returnUrl);
    }

    /**
     * 生成认证页地址，并把回跳地址拼到 redirect 参数上
     */
    public static function authUrl(string $path, ?string $returnUrl = null): string
    {
        $returnUrl = self::normalize($returnUrl ?? self::currentUrl());

        return $returnUrl === ''
            ? url($path)
            : url($path . '?redirect=' . urlencode($returnUrl));
    }

    public static function currentUrl(): string
    {
        try {
            return request()->fullUrl();
        } catch (\Throwable $exception) {
            return '';
        }
    }

    /**
     * 归一化：只放行同源站内地址，非法地址返回空串
     *
     * @param bool $skipAuthPaths 是否过滤掉 login/register/forgot/logout/member 这类
     *                            认证相关路径。来源地址必须过滤（防死循环），
     *                            默认回落地址（如 /member）不能过滤。
     */
    public static function normalize(?string $url, bool $skipAuthPaths = true): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        // 挡掉控制字符（javascript: 之类的绕过手法）
        if (preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return '';
        }

        // 协议相对地址补全协议后再按绝对地址处理
        if (str_starts_with($url, '//')) {
            $url = (self::requestScheme() ?: 'http') . ':' . $url;
        }

        if (preg_match('#^https?://#i', $url)) {
            $parts = parse_url($url);
            $host = strtolower((string) ($parts['host'] ?? ''));
            $selfHost = strtolower(self::requestHost());
            if ($host === '' || $selfHost === '' || $host !== $selfHost) {
                return '';
            }

            $url = (string) ($parts['path'] ?? '');
            if (!empty($parts['query'])) {
                $url .= '?' . $parts['query'];
            }
        }

        // 兼容二级目录部署：从请求推断子目录前缀（如 /cms），把路径里的 /cms 剥掉，
        // 只保留根相对路径（如 /bbs/...）。否则 url() 之后会再拼一次前缀 → 双前缀 404。
        $base = self::basePath();
        if ($base !== '') {
            if ($url === $base) {
                $url = '/';
            } elseif (str_starts_with($url, $base . '/')) {
                $url = '/' . substr($url, strlen($base) + 1);
            }
        }

        // 只接受站内绝对路径
        if (!str_starts_with($url, '/')) {
            return '';
        }

        if ($skipAuthPaths) {
            $path = trim((string) (parse_url($url, PHP_URL_PATH) ?? ''), '/');
            $firstSegment = strtolower((string) (explode('/', $path)[0] ?? ''));
            if ($firstSegment !== '' && in_array($firstSegment, self::SKIP_PATHS, true)) {
                return '';
            }
        }

        return $url;
    }

    private static function requestHost(): string
    {
        try {
            return (string) request()->getHost();
        } catch (\Throwable $exception) {
            return '';
        }
    }

    private static function requestScheme(): string
    {
        try {
            return (string) request()->getScheme();
        } catch (\Throwable $exception) {
            return '';
        }
    }

    /**
     * 从当前请求推断子目录前缀（如 http://host/cms/... 中的 "/cms"）。
     * 与 AppServiceProvider 里 forceRootUrl 的推断方式保持一致，不依赖 APP_URL 配置。
     */
    private static function basePath(): string
    {
        try {
            $request = request();
            if (!$request) {
                // 无请求上下文（队列/命令）时回退到 APP_URL 路径
                $appUrl = (string) config('app.url', '');
                $path = $appUrl === '' ? '' : (string) (parse_url($appUrl, PHP_URL_PATH) ?? '');

                return rtrim($path, '/');
            }

            // getBaseUrl() 已包含子目录（如 /cms），并自动剥离 /index.php
            $base = str_replace('\\', '/', (string) $request->getBaseUrl());
            $base = preg_replace('#/index\.php$#i', '', $base);
            if ($base === '' || $base === '/') {
                return '';
            }

            return rtrim($base, '/');
        } catch (\Throwable $exception) {
            return '';
        }
    }
}
