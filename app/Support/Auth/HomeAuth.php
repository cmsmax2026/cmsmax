<?php

namespace App\Support\Auth;

use Illuminate\Contracts\Support\Arrayable;
use Modules\System\Http\Controllers\Common\SessionKey;

/**
 * 读取前台登录态
 *
 * 注意：CMS 登录时写入 session 的是 Members 的 Eloquent 模型
 * （Modules\System\Listeners\UserLogin 里 session([HomeInfo => $data])）。
 * 直接 (array) 强转模型会拿到 \0*\0attributes 之类的内部属性键，
 * $info['uid'] 永远取不到 —— 所以这里统一转成数组后再读。
 */
class HomeAuth
{
    /**
     * 当前登录会员信息（未登录返回空数组）
     */
    public static function info(): array
    {
        $info = session(SessionKey::HomeInfo);

        if ($info instanceof Arrayable) {
            $info = $info->toArray();
        } elseif (is_object($info) && method_exists($info, 'toArray')) {
            $info = $info->toArray();
        } elseif (is_object($info)) {
            $info = (array) $info;
        }

        return is_array($info) ? $info : [];
    }

    public static function uid(): int
    {
        return (int) (self::info()['uid'] ?? 0);
    }

    public static function check(): bool
    {
        return self::uid() > 0;
    }
}
