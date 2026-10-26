<?php

namespace Modules\Main\Http\Controllers\Api;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class ThirdController extends BaseController
{
    /**
     * APP 等第三方上报错误日志时校验的默认 key。
     * 可通过 config('app.third_app_key') 覆盖，默认写死一个即可。
     */
    private const DEFAULT_APP_KEY = 'mxzcms_third_report_default_key';

    /**
     * 第三方客户端异常上报接口。
     * 统一走 CMS 主程序路径：校验 app_key -> 组装内容 -> 有 Log 模块则写入第三方日志（type=third, two_type=app），无则暂不入库。
     */
    public function report(Request $request)
    {
        // 简单的 app_key 校验（固定 key，不做复杂签名）
        $appKey = (string) $request->input('app_key', '');
        $expected = (string) (config('app.third_app_key', self::DEFAULT_APP_KEY));
        if ($appKey === '' || !hash_equals($expected, $appKey)) {
            return response()->json(['status' => 0, 'msg' => 'app_key 无效'], 401, [], JSON_UNESCAPED_UNICODE);
        }

        // 组装异常内容（数组，Logger 会整体 json_encode 进 context 字段）
        $payload = [
            'message'     => (string) $request->input('message', ''),
            'stack'       => (string) $request->input('stack', ''),
            'app_version' => (string) $request->input('app_version', ''),
            'platform'    => (string) $request->input('platform', ''),
            'os_version'  => (string) $request->input('os_version', ''),
            'device'      => (string) $request->input('device', ''),
            'uid'         => (string) $request->input('uid', ''),
            'page'        => (string) $request->input('page', ''),
            'level'       => (string) $request->input('level', 'error'),
            'client_type' => (string) $request->input('client_type', 'app'),
            'extra'       => $request->input('extra', []),
            'ip'          => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'requestid'   => $request->requestid ?? '',
            'received_at' => date('Y-m-d H:i:s'),
        ];

        // 仅有 Log 模块时才入库，否则暂不入库
        if ($this->isLogModuleEnabled()) {
            hook("Loger", [
                'module'    => 'main',
                'type'      => 'third',
                'two_type'  => 'app',
                'params'    => $payload,
                'remark'    => '',
                'unique_id' => '',
                'requestid' => $request->requestid ?? '',
            ]);
        }

        return response()->json(['status' => 1, 'msg' => 'ok'], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 判断 Log 模块是否启用（active 缓存中 identification=Log 且 status=1）。
     */
    protected function isLogModuleEnabled(): bool
    {
        $active = Cache::get('modules-active', []);
        if (!is_array($active) || !isset($active['log'])) {
            return false;
        }
        return (int) ($active['log']['status'] ?? 0) === 1;
    }
}
