<?php

namespace Modules\System\Listeners;

use App\Support\Database\AutoSchemaRepairService;
use Illuminate\Http\Request;
use Plugins\Logger\Lib\Logger;

class ExceptionHandler
{

    public function handle( \App\Events\ExceptionHandler $event) {

        //事件逻辑 ...
        $exception = $event->data['exception'];//获取事件数据
        $module = 'Main';
        if (\request()->route()) {
            $namespace = (string) (\request()->route()->getAction()['namespace'] ?? '');
            $namespaceParts = explode('\\', $namespace);
            if (!empty($namespaceParts[1])) {
                $module = $namespaceParts[1];
            }
        }
        $array = dealErrorExceptionInfo($exception);
        try {
            $autoRepair = app(AutoSchemaRepairService::class)->attempt($exception, [
                'module' => $module,
            ]);
        } catch (\Throwable $repairException) {
            $autoRepair = [
                'queued' => false,
                'reason' => 'repair_service_exception',
                'message_admin' => '',
                'message_public' => '',
                'error' => $repairException->getMessage(),
            ];
        }
        $isAdminRequest = request()->is('admin') || request()->is('admin/*');
        $autoRepairNote = $isAdminRequest
            ? trim((string) ($autoRepair['message_admin'] ?? ''))
            : trim((string) ($autoRepair['message_public'] ?? ''));
        $autoRepairAsyncIds = array_values(array_filter((array) ($autoRepair['async_ids'] ?? [])));
        $autoRepairFirstAsyncId = trim((string) ($autoRepair['first_async_id'] ?? ($autoRepairAsyncIds[0] ?? '')));
        $autoRepairTaskUrl = $isAdminRequest && $autoRepairFirstAsyncId !== ''
            ? url('admin/cms/asyncTask?async_id=' . urlencode($autoRepairFirstAsyncId))
            : '';
        $logContext = Logger::normalizeSystemContext('error', $exception, [
            'module' => $module ?: 'Main',
            'requestid' => \request()->requestid ?? '',
            'path' => url()->full(),
            'auto_schema_repair' => $autoRepair,
        ]);
        hook("Loger",[
            'module' => $module?:"Main",
            'type' => "system",
            'two_type' => "error" ,
            'params' => $logContext,
            'remark' => "",
            'unique_id' => '',
            'requestid' => \request()->requestid
        ]);

        if ($array['code'] == 40000) {
            //阻止父方法执行
            exit(json_encode(["status" => 40000, "msg" => $array['message']], JSON_UNESCAPED_UNICODE));
        } elseif (in_array($array['statusCode'], [400, 401])) {
            exit(json_encode(["status" => 40001, "msg" => $array['message']], JSON_UNESCAPED_UNICODE));
        } elseif (in_array($array['statusCode'], [404])) {
            die(view('error.404'));
        } elseif (in_array($array['statusCode'], [500])) {
            try {
                die(view('error.500', [
                    'msg' => $array['message'],
                    "file" => $array['file'],
                    "line" => $array['line'],
                    'auto_repair_note' => $autoRepairNote,
                    'auto_repair_async_ids' => $autoRepairAsyncIds,
                    'auto_repair_task_url' => $autoRepairTaskUrl,
                ]));
            }catch (\Exception $e){
                $payload = ["status" => 500, "msg" => $array['message']];
                if ($autoRepairNote !== '') {
                    $payload['auto_repair_note'] = $autoRepairNote;
                }
                if ($autoRepairAsyncIds) {
                    $payload['auto_repair_async_ids'] = $autoRepairAsyncIds;
                }
                if ($autoRepairTaskUrl !== '') {
                    $payload['auto_repair_task_url'] = $autoRepairTaskUrl;
                }
                exit(json_encode($payload, JSON_UNESCAPED_UNICODE));
            }
        }else{
            try {
                die(view('error.500', [
                    'msg' => $array['message'],
                    "file" => $array['file'],
                    "line" => $array['line'],
                    'auto_repair_note' => $autoRepairNote,
                    'auto_repair_async_ids' => $autoRepairAsyncIds,
                    'auto_repair_task_url' => $autoRepairTaskUrl,
                ]));
            }catch (\Exception $e){
                $payload = ["status" => 500, "msg" => $array['message']];
                if ($autoRepairNote !== '') {
                    $payload['auto_repair_note'] = $autoRepairNote;
                }
                if ($autoRepairAsyncIds) {
                    $payload['auto_repair_async_ids'] = $autoRepairAsyncIds;
                }
                if ($autoRepairTaskUrl !== '') {
                    $payload['auto_repair_task_url'] = $autoRepairTaskUrl;
                }
                exit(json_encode($payload, JSON_UNESCAPED_UNICODE));
            }
        }
    }

}
