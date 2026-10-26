<?php

namespace Modules\Main\Http\Middleware;

use App\Support\Auth\HomeAuth;
use App\Support\Auth\HomeLoginRedirect;
use Closure;
use Modules\Main\Services\ServiceModel;
use Mxzcms\Modules\session\SessionKey;


class CheckHomeLogin {
    public function handle($request, Closure $next) {
        $userInfo = HomeAuth::info();
        if ((int) ($userInfo['uid'] ?? 0) <= 0) {
            // 记住来源，登录 / 注册后回到原来的模块
            return HomeLoginRedirect::toLogin();
        }

        $userInfo['messageNum'] = ServiceModel::getNoReadMessageNum($userInfo['uid']);
        view()->share([
            'userInfo' => $userInfo
        ]);
        return $next($request);
    }
}




