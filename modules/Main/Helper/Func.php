<?php

namespace Modules\Main\Helper;

use App\Support\I18n\ThemeTranslator;
use Illuminate\Support\Facades\Cache;
use Modules\System\Http\Controllers\Common\SessionKey;
use Tymon\JWTAuth\Facades\JWTAuth;

trait Func {
    public static function admin_api_current_user() {
        // CLI（artisan 命令、队列、调度）没有 HTTP 请求上下文，取不到 JWT token，
        // 继续往下会 die() 把整条命令打断（例如 route:list 反射控制器时）
        if (app()->runningInConsole()) {
            return null;
        }
        $request = \Request();
        if ($request->isMethod('OPTIONS')) exit();
        if ($request->home_user_info) {
            return $request->home_user_info;
        } else {
            try {
                $user = JWTAuth::toUser(JWTAuth::getToken());     //获取用户名
                if (!$user) die(json_encode(['status' => 0, 'msg' => '获取用户信息错误']));
            } catch (\Exception $exception) {
                die(json_encode(['status' => 0, 'msg' => '获取用户信息错误']));
            }
            return $user;
        }
    }

    public static function theme_lang_pack($theme, $langkey, $lang = "zh") {
        return ThemeTranslator::translate($langkey, [], $theme, $lang);
    }


    //获取历史入口
    public static function getHistoricalEntry() {
        $key = self::getHistoricalEntryCacheKey();
        $data = self::normalizeHistoricalEntryList(Cache::get($key) ?: []);
        Cache::put($key, $data, 60 * 24 * 365);
        return $data;
    }

    //保存历史入口 1=单储存 2=整个储存
    public static function saveHistoricalEntry($type = 1, $array = []) {
        $key = self::getHistoricalEntryCacheKey();
        if ($type == 2) {
            $list = self::normalizeHistoricalEntryList((array) $array);
        } else {
            $list = Cache::get($key) ?: [];
            $array['timestamp'] = time();
            $list[$array['module']] = $array;
            $list = self::normalizeHistoricalEntryList(array_values($list));
        }
        Cache::put($key, $list, 60 * 24 * 365);
    }

    private static function normalizeHistoricalEntryList(array $list): array
    {
        $result = [];
        foreach ($list as $item) {
            if (!is_array($item)) {
                continue;
            }
            $module = trim((string) ($item['module'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));
            if ($module === '' || $url === '') {
                continue;
            }

            $isExternal = preg_match('#^https?://#i', $url);
            if ($isExternal) {
                // 外链：保持完整 URL，避免重复标准化时丢失 host 或被误判为内链、继续拼接子目录
                $url = $url;
            } else {
                // 内部链接：兼容子目录（子路径）部署，补全应用基址。
                // 幂等：本方法在 save 与 get 读取缓存时都会被调用，已含子目录基址时不再叠加，
                // 否则 /subdir/admin 在多次标准化后会变成 /subdir/subdir/admin …… 路径被重复拼接。
                $base = ltrim((string) parse_url(url('/'), PHP_URL_PATH), '/');
                $url = ltrim($url, '/');
                $query = '';
                $qPos = strpos($url, '?');
                if ($qPos !== false) {
                    $query = substr($url, $qPos + 1);
                    $url = substr($url, 0, $qPos);
                }
                $url = trim($url, '/');
                // 先剥离可能已存在的子目录基址（兼容历史数据被多次拼接的情况），再补一次，
                // 保证无论基址是几段、已被叠加多少层，最终只出现一次。
                if ($base !== '') {
                    $prefix = $base . '/';
                    while (strpos($url, $prefix) === 0) {
                        $url = substr($url, strlen($prefix));
                    }
                    $url = ($url === '' || $url === $base) ? $base : ($base . '/' . $url);
                }
                $url = '/' . $url;
                if ($query !== '') {
                    $url .= '?' . $query;
                }
            }

            $item['module'] = $module;
            $item['url'] = $url;
            $item['name'] = trim((string) ($item['name'] ?? $module));
            $item['timestamp'] = intval($item['timestamp'] ?? 0);

            $dedupeKey = strtolower($module) . '|' . strtolower($url);
            if (!isset($result[$dedupeKey]) || intval($result[$dedupeKey]['timestamp'] ?? 0) <= $item['timestamp']) {
                $result[$dedupeKey] = $item;
            }
        }

        $result = array_values($result);
        if ($result) {
            array_multisort(array_column($result, 'timestamp'), SORT_DESC, $result);
        }

        return array_slice($result, 0, 8);
    }

    private static function getHistoricalEntryCacheKey(): string
    {
        $adminInfo = session(SessionKey::AdminInfo);
        if (is_array($adminInfo)) {
            $uid = intval($adminInfo['uid'] ?? 0);
            if ($uid > 0) {
                return 'MainHistoricalEntryList:' . $uid;
            }
        }

        if (is_object($adminInfo) && method_exists($adminInfo, 'toArray')) {
            $uid = intval(($adminInfo->toArray()['uid'] ?? 0));
            if ($uid > 0) {
                return 'MainHistoricalEntryList:' . $uid;
            }
        }

        return 'MainHistoricalEntryList';
    }
}
