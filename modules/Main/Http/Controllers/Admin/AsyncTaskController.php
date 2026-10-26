<?php

namespace Modules\Main\Http\Controllers\Admin;

use App\Support\Async\AsyncTaskStore;
use Modules\ModulesController;

class AsyncTaskController extends ModulesController
{
    public function index()
    {
        $asyncId = trim((string) $this->request->query('async_id', ''));
        return view('admin.cms.asyncTask', [
            'async_id' => $asyncId,
        ]);
    }

    public function status()
    {
        $asyncId = trim((string) $this->request->query('async_id', ''));
        if ($asyncId === '') {
            return [
                'status' => 400,
                'msg' => 'async_id 不能为空',
            ];
        }

        $task = app(AsyncTaskStore::class)->get($asyncId);
        if (!is_array($task)) {
            return [
                'status' => 404,
                'msg' => '任务不存在',
            ];
        }

        return [
            'status' => 200,
            'msg' => 'ok',
            'data' => [
                'async_id' => $asyncId,
                'status_code' => intval($task['status_code'] ?? 0),
                'moduleName' => (string) ($task['moduleName'] ?? ''),
                'actionName' => (string) ($task['actionName'] ?? ''),
                'created_at' => (string) ($task['created_at'] ?? ''),
                'updated_at' => (string) ($task['updated_at'] ?? ''),
                'duration_ms' => intval($task['duration_ms'] ?? 0),
                'exit_code' => isset($task['exit_code']) ? intval($task['exit_code']) : null,
                'output' => (string) ($task['output'] ?? ''),
                'error' => (string) ($task['error'] ?? ''),
                'reason_code' => (string) ($task['reason_code'] ?? ''),
            ],
        ];
    }
}

