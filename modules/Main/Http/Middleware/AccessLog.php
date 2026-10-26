<?php

namespace Modules\Main\Http\Middleware;

use Closure;
use Mxzcms\Modules\session\SessionKey;

class AccessLog {

    public function handle($request, Closure $next) {
        // 仅记录前台访问：后台路由统一以 /admin 前缀，且挂在 web 组下，
        // 这里跳过避免污染前台统计；API 走 api 组不会进入本中间件。
        if ($request->is('admin/*')) {
            return $next($request);
        }

        // 未匹配到路由（如 404 兜底）时不记录，getURIByRoute 依赖路由对象
        if (!$request->route()) {
            return $next($request);
        }

        $userInfo = session(SessionKey::HomeInfo);
        $routeArr = getURIByRoute($request);
        hook("Loger", [
            'module' => $routeArr['moduleName'],
            'type' => "access",
            'two_type' => "web",
            'params' => [],
            'remark' => "访问页面",
            'unique_id' => $userInfo['uid'] ?: '',
            'requestid' => $request->requestid
        ]);
        return $next($request);
    }
}
