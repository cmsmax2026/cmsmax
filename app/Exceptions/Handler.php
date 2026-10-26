<?php

namespace App\Exceptions;

use App\Support\Database\AutoSchemaRepairService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler {
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, $exception) {
        // 验证失败：交还给框架默认处理（web 下重定向回上一页并闪存错误，ajax 下返回 JSON）。
        // 否则会被下面的通用 500 逻辑接管，导致表单验证失败直接 500 而不是返回上一页。
        if ($exception instanceof ValidationException) {
            // 直接构造验证失败响应，避免被下方通用 500 逻辑或 CMS 的 ExceptionHandler renderable 钩子接走。
            // 等价于框架默认行为：web 下重定向回上一页并闪存错误，ajax 下返回 JSON。
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                ], $exception->status);
            }

            return redirect()->back()
                ->withInput($request->except($this->dontFlash))
                ->withErrors($exception->errors(), $exception->errorBag);
        }

        if ($exception instanceof PostTooLargeException) {
            $limit = trim((string) ini_get('post_max_size'));
            $msg = (string) getTranslateByKey('upload_too_large');
            if ($limit !== '') {
                $msg .= '（post_max_size=' . $limit . '）';
            }
            $payload = [
                'status' => 413,
                'msg' => $msg,
                'reason_code' => 'post_too_large',
            ];
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($payload, 200);
            }
            return back()->with('pageDataMsg', $msg)->with('pageDataStatus', '0');
        }

        try {
            hook("ExceptionHandler", ['exception' => $exception]);
        } catch (\Throwable $hookException) {
            // Fall through to local fallback rendering below.
        }

        try {
            $error = dealErrorExceptionInfo($exception);
            $module = $this->resolveExceptionModule($request);
            $autoRepair = app(AutoSchemaRepairService::class)->attempt($exception, [
                'module' => $module,
            ]);
            $isAdminRequest = $request->is('admin') || $request->is('admin/*');
            $autoRepairNote = $isAdminRequest
                ? trim((string) ($autoRepair['message_admin'] ?? ''))
                : trim((string) ($autoRepair['message_public'] ?? ''));
            $autoRepairAsyncIds = array_values(array_filter((array) ($autoRepair['async_ids'] ?? [])));
            $autoRepairFirstAsyncId = trim((string) ($autoRepair['first_async_id'] ?? ($autoRepairAsyncIds[0] ?? '')));
            $autoRepairTaskUrl = $isAdminRequest && $autoRepairFirstAsyncId !== ''
                ? url('admin/cms/asyncTask?async_id=' . urlencode($autoRepairFirstAsyncId))
                : '';

            $payload = [
                'status' => 500,
                'msg' => $exception->getMessage(),
                'reason_code' => 'exception',
                'auto_repair_note' => $autoRepairNote,
                'auto_repair_async_ids' => $autoRepairAsyncIds,
                'auto_repair_task_url' => $autoRepairTaskUrl,
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($payload, 200);
            }

            return response()->view($error['view'], [
                'msg' => "statusCode:" . ($error['statusCode'] ?? 500) . ";" . $exception->getMessage(),
                "file" => $exception->getFile(),
                "line" => $exception->getLine(),
                'auto_repair_note' => $autoRepairNote,
                'auto_repair_async_ids' => $autoRepairAsyncIds,
                'auto_repair_task_url' => $autoRepairTaskUrl,
            ], (int) ($error['statusCode'] ?? 500));
        } catch (\Throwable $renderException) {
            $payload = [
                'status' => 500,
                'msg' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($payload, 200);
            }
            return response()->view('errors.500', $payload, 500);
        }
    }

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register() {

        // 自定义
        $this->renderable(function (\Throwable $exception) {
            hook("ExceptionHandler", ['exception'=>$exception]);
        });

        $this->reportable(function (Throwable $e) {

        });
    }

    private function resolveExceptionModule(Request $request): string
    {
        if ($request->route()) {
            $namespace = (string) ($request->route()->getAction()['namespace'] ?? '');
            $parts = explode('\\', $namespace);
            if (!empty($parts[1])) {
                return (string) $parts[1];
            }
        }

        return 'Main';
    }

}
