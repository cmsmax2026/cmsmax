<?php

namespace Modules\System\Listeners;

use Modules\Main\Models\Member;
use Modules\Main\Services\ServiceModel;
use Modules\System\Http\Controllers\Common\SessionKey;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserLogin {

    public function handle(\Modules\System\Events\UserLogin $event) {
        //事件逻辑 ...

        $moduleName = ucfirst($event->data['moduleName']);
        //参数
        $all = $event->data['all'];
        $apiLogin = !empty($event->data['api_login']);
        //登录注册配置
        $loginRegister = $event->data['loginRegister'];
        if (!$loginRegister['open_login']) return returnArr(0, "未开放登录");

        if (!$apiLogin && $loginRegister['open_code_verify']) {
            $verify = hook('GetSendCode', ['moduleName' => 'System', 'object_type' => 'captcha', 'operate_type' => 'verify', 'captcha' => $all['captcha']])[0];
            if ($verify['status'] != 200) return $verify;
        }
        //用户登录
        $loginName = trim((string) ($all['name'] ?? ''));
        $check = Member::query()
            ->where(function ($q) use ($loginName) {
                $q->where("email", $loginName)
                    ->orWhere("phone", $loginName)
                    ->orWhere("username", $loginName);
            })
            ->orderByDesc('uid')
            ->first();
        if (!$check) return returnArr(0, "账号或密码错误，请检查后重试");
        if (intval($check->status) !== 1) return returnArr(0, "当前账号已被禁用");
        $password = ServiceModel::getPassword($all['password']);
        if ($password != $check->password) return returnArr(0, "账号或密码错误，请检查后重试");
        $data = $check;
        $data['avatar'] = GetUrlByPath($data['avatar']);

        if ($apiLogin) {
            $token = JWTAuth::fromUser($data);
            $data['token'] = $token;
        }

        session([SessionKey::HomeInfo => $data]);
        session()->save();
        return returnArr(200, '登录成功', $data);
    }

}
