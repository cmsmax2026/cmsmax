<?php

namespace Modules\System\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Modules\Main\Models\Member;
use Modules\Main\Services\ServiceModel;
use Modules\System\Http\Controllers\Common\CommonController;
use Modules\System\Http\Controllers\Common\SessionKey;

class IndexController extends CommonController {
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index() {
        $pageData = [
            'title' => '后台—首页',
            'controller' => 'Index'
        ];
        $uid = session(SessionKey::HomeInfo)['uid'];
        if (session('login_type') == 'admin') {
            $data['user'] = [
                'all_user' => ServiceModel::getHomeUser(Member::TABLE_NAME, ['user_type' => 1]),
                'month_user' => ServiceModel::getHomeUser(Member::TABLE_NAME, ['user_type' => 1, 'month' => 1]),
                'day_user' => ServiceModel::getHomeUser(Member::TABLE_NAME, ['user_type' => 1, 'day' => 1]),
            ];
        }
        return $this->adminView('index.index', [
            'pageData' => $pageData
        ]);
    }

}
