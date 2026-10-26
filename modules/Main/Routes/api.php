<?php

use Illuminate\Support\Facades\Route;
use Modules\Main\Http\Controllers\Home\HomeController;
use Modules\Main\Http\Controllers\Home\ModelController;
use Modules\Main\Http\Middleware\CheckHome;
use Modules\Main\Http\Middleware\CheckInstall;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware([CheckInstall::class, CheckHome::class])->group(function () {
    Route::post('/auth/login', [\Modules\Main\Http\Controllers\Api\LoginController::class, 'login']);
    Route::post('/auth/logout', [\Modules\Main\Http\Controllers\Api\LoginController::class, 'logout']);

    Route::get('/page/{model}', [ModelController::class, 'list']);
    Route::get('/list/{model}', [ModelController::class, 'list']);
    Route::get('/detail/{model}/{id}', [ModelController::class, 'detail']);
    Route::post('/detail/{model}/{id}/good', [ModelController::class, 'good']);
    Route::get('/detail/{model}/{id}/download/{field}/{index?}', [ModelController::class, 'download']);
    Route::get('/handle/{model}', [ModelController::class, 'handle']);
    Route::post('/handle/{model}', [ModelController::class, 'handle']);
    Route::post('/asynCall', [\Modules\Main\Http\Controllers\Api\AsynController::class, 'asynCall']);
    Route::post('/anyCall', [\Modules\Main\Http\Controllers\Api\AnyCallController::class, 'anyCall']);

});

// 第三方客户端（APP 等）异常上报接口：统一走 CMS 主程序路径，仅做安装态校验，避免 PC 首页逻辑干扰
Route::middleware([CheckInstall::class])->group(function () {
    Route::post('/third/exception-report', [\Modules\Main\Http\Controllers\Api\ThirdController::class, 'report']);
});

