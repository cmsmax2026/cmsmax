<?php

namespace Modules\Main\Http\Controllers\Admin;

use App\Support\Security\AdminMfaService;
use App\Support\Security\IpAccessRuleService;
use App\Support\Security\TotpService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Modules\Main\Models\Member;
use Modules\Main\Models\Modules;
use Modules\Main\Services\ServiceModel;
use Modules\ModulesController;
use Modules\System\Http\Controllers\Common\SessionKey;
use function back;
use function cacheGlobalSettingsByKey;
use function getTranslateByKey;
use function redirect;
use function session;
use function view;

class LoginController extends ModulesController {
    private const LOGIN_LOCK_CACHE_PREFIX = 'admin_login_lock:';
    private const PENDING_LOGIN_SESSION_KEY = 'admin_pending_login';
    private const PENDING_GOOGLE_AUTH_SECRET_KEY = 'admin_pending_google_auth_secret';

    //登录
    public function login(Request $request, ?string $one = null) {
        $currentAdmin = $this->getLoggedAdmin();
        if (!empty($currentAdmin)) {
            if (!$this->hasValidAdminSession($currentAdmin)) {
                $this->forceClearAdminSession();
            } else {
            //返回上一层，带上已登录提示
                $identification = Modules::query()
                    ->where('is_backend',1)
                    ->where('status',1)
                    ->value("identification");
                if($identification){
                    return redirect(url("admin/entryModule?m=".$identification));
                }
                return redirect("/admin")->with("errormsg", getTranslateByKey('already_logged_in'));
            }
        }

        $entrance = $this->validateLoginEntrance($one);

        return view("admin/login/login", [
            'loginAction' => $this->buildLoginHandleUrl($entrance),
            'loginEntrance' => $entrance,
            'googleAuthenticatorMode' => AdminMfaService::getMode(),
        ]);
    }

    public function handle(Request $request, ?string $one = null) {
        $entrance = $this->validateLoginEntrance($one);
        if ($this->request->isMethod("POST")) {
            $post = $this->request->all();
            $username = trim((string) ($post["username"] ?? ''));
            $password = (string) ($post["password"] ?? '');
            $remember = (string) ($post["is_remember"] ?? '');

            if ($username === '' || $password === '') {
                return back()->withErrors([getTranslateByKey('user_name_or_password_required')])->withInput();
            }

            $lockState = $this->getLoginLockState($username);
            if ($lockState['locked']) {
                return back()->withErrors([$lockState['message']])->withInput();
            }

            $rememberPassword = (string) (session('admin_remember')['password'] ?? '');
            $submittedPasswordHash = ServiceModel::getPassword($password);
            session()->put("admin_remember", [
                "username" => $username,
                "is_remember" => $remember,
                "password" => $rememberPassword !== '' ? $rememberPassword : $submittedPasswordHash,
            ]);

            if (cacheGlobalSettingsByKey("admin_login_code") == 1) {
                try {
                    $this->validate($this->request, [
                        'captcha' => 'required|captcha'
                    ], [
                        'captcha.required' => getTranslateByKey('please_fill_in_the_verification_code'),
                        'captcha.captcha' => getTranslateByKey('verification_code_error'),
                    ]);
                } catch (\Exception $e) {
                    return back()->withErrors([getTranslateByKey('verification_code_error')])->withInput();
                }
            }

            $first = ServiceModel::loginFindUserV2(Member::TABLE_NAME, $post);
            if (!$first) {
                return back()->withErrors([$this->recordLoginFailure($username, getTranslateByKey('incorrect_username'))])->withInput();
            }

            if ((string) $first["password"] !== $submittedPasswordHash) {
                if ((string) $first["password"] !== $rememberPassword) {
                    hook("Loger", [
                        'module' => 'Main',
                        'type' => "operate",
                        'two_type' => "passE",
                        'params' => [
                            'uid' => $first['uid'],
                            'username' => $first['username'],
                        ],
                        'remark' => '登录后台-密码错误',
                        'unique_id' => $first['uid'],
                        'requestid' => $this->request->requestid
                    ]);
                    session()->put("admin_remember", array_merge(session('admin_remember') ?: [], ["password" => null]));
                    return back()->withErrors([$this->recordLoginFailure($username, getTranslateByKey('incorrect_password'))])->withInput();
                }
            } else {
                session()->put("admin_remember", array_merge(session('admin_remember') ?: [], ["password" => $submittedPasswordHash]));
            }

            $admin = $this->normalizeAdminPayload($first);
            $this->storePendingAdminLogin($admin, $remember, $entrance, $username);

            if (AdminMfaService::shouldRequireSetup(intval($admin['uid'] ?? 0))) {
                $this->clearPendingGoogleAuthenticatorSecret();
                return redirect($this->buildGoogleAuthenticatorUrl('setup', $entrance));
            }

            if (AdminMfaService::shouldRequireVerification(intval($admin['uid'] ?? 0))) {
                return redirect($this->buildGoogleAuthenticatorUrl('verify', $entrance));
            }

            return $this->completeAdminLogin($admin, $remember, $username);
        }

        return redirect($this->buildLoginUrl($entrance));
    }

    public function googleAuthenticator(Request $request, string $action, ?string $one = null)
    {
        $entrance = $this->validateLoginEntrance($one);
        $loggedAdmin = $this->getLoggedAdmin();
        $pendingLogin = $this->getPendingAdminLogin();
        $pendingAdmin = $this->normalizeAdminPayload($pendingLogin['admin'] ?? []);

        if ($action === 'verify') {
            if (empty($pendingAdmin) || !AdminMfaService::hasEnabledBinding(intval($pendingAdmin['uid'] ?? 0))) {
                return redirect($this->buildLoginUrl($entrance));
            }

            return view('admin/login/google-authenticator', [
                'pageTitle' => 'Google Authenticator 校验',
                'mode' => 'verify',
                'entrance' => $entrance,
                'adminUser' => $pendingAdmin,
                'bindAction' => $this->buildGoogleAuthenticatorPostUrl('bind', $entrance),
                'confirmAction' => $this->buildGoogleAuthenticatorPostUrl('confirm', $entrance),
                'disableAction' => $this->buildGoogleAuthenticatorPostUrl('disable', $entrance),
                'secret' => '',
                'provisioningUri' => '',
                'qrPreviewUrl' => '',
                'googleAuthenticatorMode' => AdminMfaService::getMode(),
                'hasBinding' => true,
                'isPendingLogin' => true,
            ]);
        }

        if ($action === 'setup') {
            $targetAdmin = !empty($pendingAdmin) ? $pendingAdmin : $loggedAdmin;
            if (empty($targetAdmin)) {
                return redirect($this->buildLoginUrl($entrance));
            }

            if (AdminMfaService::hasEnabledBinding(intval($targetAdmin['uid'] ?? 0))) {
                return redirect($this->buildGoogleAuthenticatorUrl(!empty($pendingAdmin) ? 'verify' : 'manage', $entrance));
            }

            $secret = $this->getPendingGoogleAuthenticatorSecret(intval($targetAdmin['uid'] ?? 0));
            $provisioningUri = TotpService::buildProvisioningUri(
                $this->getGoogleAuthenticatorIssuer(),
                $this->buildGoogleAuthenticatorAccountName($targetAdmin),
                $secret
            );

            return view('admin/login/google-authenticator', [
                'pageTitle' => '绑定 Google Authenticator',
                'mode' => 'setup',
                'entrance' => $entrance,
                'adminUser' => $targetAdmin,
                'bindAction' => $this->buildGoogleAuthenticatorPostUrl('bind', $entrance),
                'confirmAction' => $this->buildGoogleAuthenticatorPostUrl('confirm', $entrance),
                'disableAction' => $this->buildGoogleAuthenticatorPostUrl('disable', $entrance),
                'secret' => $secret,
                'provisioningUri' => $provisioningUri,
                'qrPreviewUrl' => $this->buildGoogleAuthenticatorQrPreviewUrl($provisioningUri),
                'googleAuthenticatorMode' => AdminMfaService::getMode(),
                'hasBinding' => false,
                'isPendingLogin' => !empty($pendingAdmin),
            ]);
        }

        if (empty($loggedAdmin)) {
            return redirect($this->buildLoginUrl($entrance));
        }

        $secret = '';
        $provisioningUri = '';
        $hasBinding = AdminMfaService::hasEnabledBinding(intval($loggedAdmin['uid'] ?? 0));
        if (!$hasBinding) {
            $secret = $this->getPendingGoogleAuthenticatorSecret(intval($loggedAdmin['uid'] ?? 0));
            $provisioningUri = TotpService::buildProvisioningUri(
                $this->getGoogleAuthenticatorIssuer(),
                $this->buildGoogleAuthenticatorAccountName($loggedAdmin),
                $secret
            );
        } else {
            $this->clearPendingGoogleAuthenticatorSecret();
        }

        return view('admin/login/google-authenticator', [
            'pageTitle' => 'Google Authenticator 管理',
            'mode' => 'manage',
            'entrance' => $entrance,
            'adminUser' => $loggedAdmin,
            'bindAction' => $this->buildGoogleAuthenticatorPostUrl('bind', $entrance),
            'confirmAction' => $this->buildGoogleAuthenticatorPostUrl('confirm', $entrance),
            'disableAction' => $this->buildGoogleAuthenticatorPostUrl('disable', $entrance),
            'secret' => $secret,
            'provisioningUri' => $provisioningUri,
            'qrPreviewUrl' => $provisioningUri !== '' ? $this->buildGoogleAuthenticatorQrPreviewUrl($provisioningUri) : '',
            'googleAuthenticatorMode' => AdminMfaService::getMode(),
            'hasBinding' => $hasBinding,
            'isPendingLogin' => false,
        ]);
    }

    public function bindGoogleAuthenticator(Request $request, ?string $one = null)
    {
        $entrance = $this->validateLoginEntrance($one);
        $pendingLogin = $this->getPendingAdminLogin();
        $pendingAdmin = $this->normalizeAdminPayload($pendingLogin['admin'] ?? []);
        $loggedAdmin = $this->getLoggedAdmin();
        $targetAdmin = !empty($pendingAdmin) ? $pendingAdmin : $loggedAdmin;
        if (empty($targetAdmin)) {
            return redirect($this->buildLoginUrl($entrance));
        }

        $uid = intval($targetAdmin['uid'] ?? 0);
        $secret = $this->getPendingGoogleAuthenticatorSecret($uid);
        $code = (string) $request->input('google_authenticator_code', '');
        if (!TotpService::verifyCode($secret, $code, 1)) {
            $message = !empty($pendingLogin)
                ? $this->recordLoginFailure((string) ($pendingLogin['username'] ?? ''), 'Google Authenticator 动态验证码错误')
                : 'Google Authenticator 动态验证码错误';
            return back()->withErrors([$message])->withInput();
        }

        AdminMfaService::upsertSecret($uid, $secret);
        $this->clearPendingGoogleAuthenticatorSecret();

        if (!empty($pendingLogin) && intval($pendingAdmin['uid'] ?? 0) === $uid) {
            return $this->completeAdminLogin($pendingAdmin, (string) ($pendingLogin['remember'] ?? ''), (string) ($pendingLogin['username'] ?? ''));
        }

        return redirect($this->buildGoogleAuthenticatorUrl('manage', $entrance))->with('statusmsg', 'Google Authenticator 已绑定');
    }

    public function confirmGoogleAuthenticator(Request $request, ?string $one = null)
    {
        $entrance = $this->validateLoginEntrance($one);
        $pendingLogin = $this->getPendingAdminLogin();
        $pendingAdmin = $this->normalizeAdminPayload($pendingLogin['admin'] ?? []);
        if (empty($pendingAdmin)) {
            return redirect($this->buildLoginUrl($entrance));
        }

        $code = (string) $request->input('google_authenticator_code', '');
        if (!AdminMfaService::verifyForUid(intval($pendingAdmin['uid'] ?? 0), $code)) {
            $message = $this->recordLoginFailure((string) ($pendingLogin['username'] ?? ''), 'Google Authenticator 动态验证码错误');
            return back()->withErrors([$message])->withInput();
        }

        AdminMfaService::touchVerified(intval($pendingAdmin['uid'] ?? 0));

        return $this->completeAdminLogin($pendingAdmin, (string) ($pendingLogin['remember'] ?? ''), (string) ($pendingLogin['username'] ?? ''));
    }

    public function disableGoogleAuthenticator(Request $request, ?string $one = null)
    {
        $entrance = $this->validateLoginEntrance($one);
        $loggedAdmin = $this->getLoggedAdmin();
        if (empty($loggedAdmin)) {
            return redirect($this->buildLoginUrl($entrance));
        }

        $uid = intval($loggedAdmin['uid'] ?? 0);
        $code = (string) $request->input('google_authenticator_code', '');
        if (!AdminMfaService::verifyForUid($uid, $code)) {
            return back()->withErrors(['当前 Google Authenticator 动态验证码错误']);
        }

        AdminMfaService::disable($uid);
        $this->clearPendingGoogleAuthenticatorSecret();

        return redirect($this->buildGoogleAuthenticatorUrl('manage', $entrance))->with('statusmsg', 'Google Authenticator 已关闭');
    }

    //退出
    public function logout(Request $request) {
        $this->forceClearAdminSession();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->save();
        $entrance = (string) cacheGlobalSettingsByKey('admin_login_entrance');
        return redirect($entrance !== '' ? 'admin/login/' . $entrance : 'admin/login');
    }

    private function getLoginLockKey(string $username): string
    {
        return self::LOGIN_LOCK_CACHE_PREFIX . md5(strtolower(trim($username)) . '|' . get_ip());
    }

    private function getLoginLockConfig(): array
    {
        return [
            'limit_count' => max(0, intval(cacheGlobalSettingsByKey('limit_count'))),
            'limit_time' => max(0, intval(cacheGlobalSettingsByKey('limit_time'))),
        ];
    }

    private function getLoginLockState(string $username): array
    {
        $config = $this->getLoginLockConfig();
        if ($config['limit_count'] <= 0 || $config['limit_time'] <= 0 || trim($username) === '') {
            return ['locked' => false, 'message' => ''];
        }

        $state = Cache::get($this->getLoginLockKey($username));
        if (!is_array($state)) {
            return ['locked' => false, 'message' => ''];
        }

        $lockedUntil = intval($state['locked_until'] ?? 0);
        if ($lockedUntil <= time()) {
            Cache::forget($this->getLoginLockKey($username));
            return ['locked' => false, 'message' => ''];
        }

        $seconds = max(1, $lockedUntil - time());
        $minutes = (int) ceil($seconds / 60);
        return [
            'locked' => true,
            'message' => getTranslateByKey('admin_account_locked') . '（' . getTranslateByKey('remaining_time') . '：' . $minutes . getTranslateByKey('minutes') . '）',
        ];
    }

    private function recordLoginFailure(string $username, string $defaultMessage): string
    {
        $username = trim($username);
        $config = $this->getLoginLockConfig();
        if ($config['limit_count'] <= 0 || $config['limit_time'] <= 0 || $username === '') {
            return $defaultMessage;
        }

        $cacheKey = $this->getLoginLockKey($username);
        $state = Cache::get($cacheKey);
        if (!is_array($state)) {
            $state = [
                'fail_count' => 0,
                'locked_until' => 0,
            ];
        }

        $state['fail_count'] = intval($state['fail_count'] ?? 0) + 1;
        IpAccessRuleService::autoBanCurrentIpForLoginFailure($state['fail_count'], $username);
        if ($state['fail_count'] >= $config['limit_count']) {
            $state['locked_until'] = time() + ($config['limit_time'] * 60);
            Cache::put($cacheKey, $state, now()->addMinutes($config['limit_time']));
            return getTranslateByKey('admin_account_locked') . '（' . getTranslateByKey('lock_duration') . '：' . $config['limit_time'] . getTranslateByKey('minutes') . '）';
        }

        Cache::put($cacheKey, $state, now()->addMinutes($config['limit_time']));
        $remainingAttempts = max(0, $config['limit_count'] - $state['fail_count']);
        if ($remainingAttempts <= 0) {
            return $defaultMessage;
        }

        return $defaultMessage . '（' . getTranslateByKey('remaining_attempts') . '：' . $remainingAttempts . '）';
    }

    private function clearLoginFailureState(string $username): void
    {
        $username = trim($username);
        if ($username === '') {
            return;
        }

        Cache::forget($this->getLoginLockKey($username));
    }

    private function validateLoginEntrance(?string $one): string
    {
        $entrance = $this->normalizeEntrance(cacheGlobalSettingsByKey('admin_login_entrance'));
        $currentEntrance = $this->normalizeEntrance($one);
        if ($entrance !== '') {
            if ($currentEntrance !== $entrance) {
                abort(404, '页面不存在');
            }

            return $entrance;
        }

        if ($currentEntrance !== '') {
            abort(404, '页面不存在');
        }

        return '';
    }

    private function normalizeEntrance($value): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', trim((string) $value)) ?: '';
    }

    private function buildLoginUrl(string $entrance = ''): string
    {
        return url($entrance !== '' ? 'admin/login/' . $entrance : 'admin/login');
    }

    private function buildLoginHandleUrl(string $entrance = ''): string
    {
        return url($entrance !== '' ? 'admin/login/handle/' . $entrance : 'admin/login/handle');
    }

    private function buildGoogleAuthenticatorUrl(string $action, string $entrance = ''): string
    {
        $path = 'admin/login/google-authenticator/' . $action;
        if ($entrance !== '') {
            $path .= '/' . $entrance;
        }

        return url($path);
    }

    private function buildGoogleAuthenticatorPostUrl(string $action, string $entrance = ''): string
    {
        $path = 'admin/login/google-authenticator/' . $action;
        if ($entrance !== '') {
            $path .= '/' . $entrance;
        }

        return url($path);
    }

    private function normalizeAdminPayload($admin): array
    {
        if (is_array($admin)) {
            return $admin;
        }

        if ($admin instanceof \Illuminate\Contracts\Support\Arrayable) {
            return $admin->toArray();
        }

        return json_decode(json_encode($admin, JSON_UNESCAPED_UNICODE), true) ?: [];
    }

    private function storePendingAdminLogin(array $admin, string $remember, string $entrance, string $username): void
    {
        session()->put(self::PENDING_LOGIN_SESSION_KEY, [
            'admin' => $admin,
            'remember' => $remember,
            'entrance' => $entrance,
            'username' => $username,
        ]);
    }

    private function getPendingAdminLogin(): array
    {
        $pending = session(self::PENDING_LOGIN_SESSION_KEY);
        return is_array($pending) ? $pending : [];
    }

    private function clearPendingAdminLogin(): void
    {
        session()->forget(self::PENDING_LOGIN_SESSION_KEY);
        $this->clearPendingGoogleAuthenticatorSecret();
    }

    private function forceClearAdminSession(): void
    {
        session()->forget(SessionKey::AdminInfo);
        session()->forget('admin_previous');
        session()->forget('login_type');
        $this->clearPendingAdminLogin();
    }

    private function getLoggedAdmin(): array
    {
        return $this->normalizeAdminPayload(session(SessionKey::AdminInfo));
    }

    private function getPendingGoogleAuthenticatorSecret(int $uid): string
    {
        $pending = session(self::PENDING_GOOGLE_AUTH_SECRET_KEY);
        if (is_array($pending) && intval($pending['uid'] ?? 0) === $uid) {
            return TotpService::normalizeSecret((string) ($pending['secret'] ?? ''));
        }

        $secret = TotpService::generateSecret();
        session()->put(self::PENDING_GOOGLE_AUTH_SECRET_KEY, [
            'uid' => $uid,
            'secret' => $secret,
        ]);

        return $secret;
    }

    private function clearPendingGoogleAuthenticatorSecret(): void
    {
        session()->forget(self::PENDING_GOOGLE_AUTH_SECRET_KEY);
    }

    private function completeAdminLogin(array $admin, string $remember, string $username)
    {
        $this->clearLoginFailureState($username);
        $this->clearPendingAdminLogin();

        session()->put(SessionKey::AdminInfo, $admin);
        hook("Loger", [
            'module' => 'Main',
            'type' => "operate",
            'two_type' => "loginS",
            'params' => [
                'uid' => $admin['uid'] ?? 0,
                'username' => $admin['username'] ?? '',
            ],
            'remark' => '登录后台成功',
            'unique_id' => $admin['uid'] ?? 0,
            'requestid' => $this->request->requestid
        ]);

        if ($remember !== "on") {
            session()->forget("admin_remember");
        }

        $previous = trim((string) session("admin_previous"));
        if ($previous !== '') {
            session()->forget('admin_previous');
            $previous = $this->normalizeInternalRedirect($previous);
            if ($previous !== '') {
                return redirect($previous);
            }
        }

        $identification = Modules::query()
            ->where('is_backend',1)
            ->where('status',1)
            ->value("identification");
        if($identification){
            return redirect(url("admin/entryModule?m=".$identification));
        }

        return redirect("/admin/index");
    }

    private function normalizeInternalRedirect(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $url)) {
            $parts = parse_url($url) ?: [];
            $path = (string) ($parts['path'] ?? '');
            if ($path === '') {
                $path = '/';
            }
            $query = (string) ($parts['query'] ?? '');
            return $query !== '' ? ($path . '?' . $query) : $path;
        }

        if ($url[0] !== '/') {
            return '/' . $url;
        }

        return $url;
    }

    private function getGoogleAuthenticatorIssuer(): string
    {
        $issuer = trim((string) cacheGlobalSettingsByKey('admin_google_authenticator_issuer'));
        if ($issuer !== '') {
            return $issuer;
        }

        return trim((string) env('APP_NAME', 'MxzCMS Admin')) ?: 'MxzCMS Admin';
    }

    private function buildGoogleAuthenticatorAccountName(array $admin): string
    {
        $username = trim((string) ($admin['username'] ?? 'admin'));
        $host = parse_url(url('/'), PHP_URL_HOST) ?: 'localhost';

        return $username . '@' . $host;
    }

    private function buildGoogleAuthenticatorQrPreviewUrl(string $uri): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($uri);
    }

    private function hasValidAdminSession(array $admin): bool
    {
        $uid = intval($admin['uid'] ?? 0);
        $password = (string) ($admin['password'] ?? '');
        if ($uid <= 0 || $password === '') {
            return false;
        }

        return Member::query()
            ->where('uid', $uid)
            ->where('password', $password)
            ->exists();
    }
}
