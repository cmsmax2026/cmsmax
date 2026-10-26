<?php

namespace Modules\Auth\Http\Middleware;

use App\Support\PackageManifest\PackageManifest;
use Closure;
use Modules\Auth\Models\GroupUser;
use Modules\System\Http\Controllers\Common\SessionKey;

class CheckPermission {

    public function handle($request, Closure $next) {

        //模块自己的判断登录
        $userInfo = session(\Modules\System\Http\Controllers\Common\SessionKey::AdminInfo);
        //检查是否登录，权限检查
        if (!$userInfo['uid']) {
            session()->put("previous", url()->current());
            return redirect('/');
        }

        $route = getURIByRoute($request);
        $moduleName = $route['moduleName'];
        $getPermissionGroupInfoList = hook('GetPermissionGroupInfoList', ['uid' => $userInfo['uid']])[0];
        $groupListInfo = $getPermissionGroupInfoList['list'];
        $group = $getPermissionGroupInfoList['group'];
        $roleArray = $groupListInfo[$group['group_id']];
        $roleModule = $groupListInfo[$group['group_id']]['role_array'][$moduleName] ?: [];

        $config = PackageManifest::load($moduleName, PackageManifest::PACKAGE_MODULE) ?: [];
        $requireAuth = $config['ui']['auth'] ?? ($config['auth'] ?? 'n');
        $allowed = in_array($route['uri'], $roleModule);
        if (!$allowed && $request->isMethod('post')) {
            $uri = $route['uri'];
            if (preg_match('#/(store|upload|upload-editor)$#', $uri)) {
                $fallbackCreate = preg_replace('#/(store|upload|upload-editor)$#', '/create', $uri);
                $fallbackIndex = preg_replace('#/(store|upload|upload-editor)$#', '/index', $uri);
                $allowed = in_array($fallbackCreate, $roleModule) || in_array($fallbackIndex, $roleModule);
            }
        }
        if ($requireAuth == 'y' && $roleArray['type'] != 'admin' && !$allowed) {
            return back()->with('pageDataMsg', getTranslateByKey('no_permission'))->with('pageDataStatus', '0');
        }
        session([SessionKey::CurrentUserPermissionGroupInfo => $roleArray]);
        $userInfo['type'] = $roleArray['type'];
        $userInfo['group_id'] = $group['group_id'];
        session([SessionKey::AdminInfo => $userInfo]);

        return $next($request);
    }
}
