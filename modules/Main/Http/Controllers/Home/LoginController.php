<?php

namespace Modules\Main\Http\Controllers\Home;

use App\Support\Auth\HomeAuth;
use App\Support\Auth\HomeLoginRedirect;
use Modules\Main\Models\Member;
use Modules\Main\Services\ServiceModel;
use Modules\ModulesController;
use Modules\System\Http\Controllers\Common\SessionKey;

class LoginController extends ModulesController {

    protected function getLoginRegisterConfig(): array
    {
        return hook('GetHomeBasicConfig', ['moduleName' => 'System'])[0]['login_register'];
    }

    /**
     * 当前是否已经登录（登录页 / 注册页对已登录用户直接回跳，不再重复展示表单）
     */
    protected function isLogged(): bool
    {
        return HomeAuth::check();
    }

    protected function canSendCode(array $all, array $loginRegister): array
    {
        $objectType = trim((string) ($all['object_type'] ?? ''));
        $codeType = (int) ($all['code_type'] ?? 0);

        if ($objectType === 'captcha') {
            return $loginRegister['open_login']
                ? returnArr(200, '允许发送')
                : returnArr(0, '未开放登录');
        }

        if (in_array($objectType, ['email', 'phone'], true)) {
            if ($codeType === 2 && !$loginRegister['open_register']) {
                return returnArr(0, '未开放注册');
            }

            if ($codeType === 3) {
                if (!$loginRegister['open_login']) {
                    return returnArr(0, '未开放登录');
                }

                if ($objectType === 'email' && empty($loginRegister['open_email_verify'])) {
                    return returnArr(0, '未开启邮箱找回');
                }

                if ($objectType === 'phone' && empty($loginRegister['open_phone_verify'])) {
                    return returnArr(0, '未开启手机找回');
                }
            }
        }

        return returnArr(200, '允许发送');
    }

    //登录
    public function login() {
        if ($this->request->isMethod("post")) {
            //用户登录
            $api = new \Modules\Main\Http\Controllers\Api\LoginController();
            $res = $api->login();
            if ($res['status'] != 200) {
                return back()->withInput()->with(["errormsg" => $res['msg'], "status" => 500]);
            }

            // 登录成功：回到来源模块，没有来源时才回会员中心
            return redirect(HomeLoginRedirect::take(url("member")));
        }

        // GET：把 ?redirect= 记下来，等 POST 时消费
        HomeLoginRedirect::captureFromRequest($this->request);

        $loginRegister = $this->getLoginRegisterConfig();
        if (!$loginRegister['open_login']) return back();

        // 已登录还来登录页：直接回来源（或会员中心）
        if ($this->isLogged()) {
            return redirect(HomeLoginRedirect::peek() ?: url("member"));
        }

        return HomeView('index.login', $loginRegister);
    }

    //注册
    public function register() {
        if ($this->request->isMethod("post")) {
            //用户登录
            $api = new \Modules\Main\Http\Controllers\Api\LoginController();
            $res = $api->register();
            if ($res['status'] != 200) return returnArr(0, $res['msg']);

            // 注册成功：继续把来源带去登录页，登录完就能回到来源模块
            $back = HomeLoginRedirect::peek();

            return returnArr(200, '注册成功', [
                'url' => $back !== '' ? HomeLoginRedirect::loginUrl($back) : url("login"),
            ]);
        }

        // GET：把 ?redirect= 记下来
        HomeLoginRedirect::captureFromRequest($this->request);

        $loginRegister = $this->getLoginRegisterConfig();
        if (!$loginRegister['open_register']) return back();

        // 已登录还来注册页：直接回来源（或会员中心）
        if ($this->isLogged()) {
            return redirect(HomeLoginRedirect::peek() ?: url("member"));
        }

        return HomeView('index.register', $loginRegister);
    }

    //忘记密码
    public function forgot() {
        if ($this->request->isMethod("post")) {
            $api = new \Modules\Main\Http\Controllers\Api\LoginController();
            return $api->forgot();
        }
        $loginRegister = $this->getLoginRegisterConfig();
        if (!$loginRegister['open_login']) return back();
        if (empty($loginRegister['open_email_verify']) && empty($loginRegister['open_phone_verify'])) return back();
        return HomeView('index.forgot', $loginRegister);
    }

    //退出
    public function logout() {
        // 支持 /logout?redirect=xxx 显式指定退出后回到哪
        HomeLoginRedirect::captureFromRequest($this->request);

        // 优先回来源模块，其次回退出前所在页面，最后才回登录页
        $back = HomeLoginRedirect::peek();
        session()->forget(SessionKey::HomeInfo);

        if ($back === '') {
            $back = HomeLoginRedirect::normalize((string) url()->previous());
        }

        HomeLoginRedirect::forget();

        return redirect($back !== '' ? $back : url("login"));
    }

    //发送验证码
    public function sendCode() {
        $request = \Request();
        $loginRegister = $this->getLoginRegisterConfig();
        $allow = $this->canSendCode($request->all(), $loginRegister);
        if ($allow['status'] != 200) {
            return $allow;
        }
        $request->offsetSet('uid', session(SessionKey::HomeInfo)['uid'] * 1);
        $api = new \Modules\Main\Http\Controllers\Api\LoginController();
        return $api->sendCode($request);
    }
}
