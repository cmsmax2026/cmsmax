<?php

namespace Modules\Main\Http\Middleware;

use Closure;
use App\Support\Update\UpdateResponseFactory;
use Modules\Main\Http\Controllers\Admin\LoginController;
use Modules\Main\Models\Member;
use Modules\System\Http\Controllers\Common\SessionKey;

class CheckAdmin {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $userInfo = session(SessionKey::AdminInfo);
        //检查是否登录，权限检查
        $path = $request->path();
        if (explode('/', $path)[0] != 'admin') {
            return $next($request);
        }
        if (empty($userInfo)) {
//            $entrance = (string) cacheGlobalSettingsByKey('admin_login_entrance');
//            if ($entrance !== '') {
//                abort(404, '页面不存在');
//            }
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(UpdateResponseFactory::error('未登录或登录已失效，请重新登录', [
                    'reason_code' => 'unauthenticated',
                    'redirect' => url('admin/login'),
                ], 401), 200);
            }
            return redirect('admin/login');
        }

        $checkPass = Member::query()
            ->where('uid',$userInfo['uid'])
            ->where('password',$userInfo['password'])
            ->count();
        if(!$checkPass){
            return call_user_func([new LoginController(),'logout']);
        }

        if ($userInfo['uid'] != 1 && session(SessionKey::CurrentUserPermissionGroupInfo)['type'] != 'admin') {
            if (session(SessionKey::NoAuthNum) > 5 || !session(SessionKey::CurrentUserPermissionGroupInfo)) {
                session([SessionKey::NoAuthNum => 0]);
                $authModuleName = array_keys(session(SessionKey::CurrentUserPermissionGroupInfo)['role_array'] ?: [])[0];
                if ($authModuleName) return redirect(moduleAdminJump(strtolower($authModuleName), 'logout'));
                return redirect("admin/logout");
            }
            session([SessionKey::NoAuthNum => session(SessionKey::NoAuthNum) + 1]);
            return back()->with('pageDataMsg', getTranslateByKey('no_permission'))->with('pageDataStatus', '0');
        }

        session([
            'now_module' => 'main',
        ]);

        cacheGlobalSettings();
        return $next($request);
    }
}




