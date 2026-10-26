<?php


use Illuminate\Support\Facades\Route;
use Modules\Main\Http\Controllers\Admin\LoginController;
use Modules\Main\Http\Middleware\AdminLanguage;
use Modules\Main\Http\Middleware\CheckAdmin;
use Modules\Main\Http\Middleware\CheckIpBlacklist;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware([AdminLanguage::class, CheckIpBlacklist::class])->namespace("Admin")->group(function () {
    Route::get('/login/google-authenticator/{action}/{one?}', "LoginController@googleAuthenticator")
        ->where('action', 'setup|verify|manage');
    Route::post('/login/google-authenticator/bind/{one?}', "LoginController@bindGoogleAuthenticator");
    Route::post('/login/google-authenticator/confirm/{one?}', "LoginController@confirmGoogleAuthenticator");
    Route::post('/login/google-authenticator/disable/{one?}', "LoginController@disableGoogleAuthenticator");
    Route::post('/login/handle/{one?}', "LoginController@handle");
    Route::any('/login/{one?}', "LoginController@login");
    Route::any('/logout', "LoginController@logout");
});

//黑名单
Route::any('/blacklist', function () {
    return view("admin/" . ADMIN_SKIN . "/other/403", [
        'content' => '您的IP：<span style="color:red;font-size: 1.6rem;">' . get_ip() . '</span>，已被纳入黑名单！！！'
    ]);
});
//菜单排序功能

//需要登录的路由组   黑名单中间件
Route::middleware([AdminLanguage::class, CheckAdmin::class, CheckIpBlacklist::class])->group(function () {
    Route::group(['namespace' => 'Admin'], function () {
        Route::any('/', ["uses" => "IndexController@index", "permissions" => "index"]);
        Route::any('/index', ["uses" => "IndexController@index", "permissions" => "index"]);
        Route::any('/clear', ["uses" => "IndexController@clearCache", "permissions" => ""]);
        Route::post('/clear/advanced', ["uses" => "IndexController@clearAdvancedArtifacts", "permissions" => ""]);
        Route::any('/changeLang', ["uses" => "IndexController@changeLang", "permissions" => ""]);
        Route::any('/entryModule', ["uses" => "IndexController@entryModule", "permissions" => ""]);

        Route::any('/myinfo', ["uses" => "UserController@myinfo", "permissions" => ""]);

        //功能模块列表
        Route::any('/module', ["uses" => "FuncController@module", "permissions" => "module"]);
        Route::any('/plugin', ["uses" => "FuncController@plugin", "permissions" => "plugin"]);
        Route::any('/theme', ["uses" => "FuncController@theme", "permissions" => "theme"]);
        Route::any('/theme/install', ["uses" => "FuncController@themeInstall", "permissions" => "theme/install"]);
        Route::any('/theme/uninstall', ["uses" => "FuncController@themeUninstall", "permissions" => "theme/uninstall"]);
        Route::any('/theme/changeStatus', ["uses" => "FuncController@changeThemeStatus", "permissions" => "theme/changeStatus"]);
        Route::any('/theme/changeTop', ["uses" => "FuncController@changeThemeTop", "permissions" => "theme/changeStatus"]);
        Route::any('/theme/setting', ["uses" => "FuncController@setting", "permissions" => "theme/setting"]);
        Route::any('/theme/preview', ["uses" => "FuncController@preview", "permissions" => "theme/preview"]);

        Route::any('/theme/themeMenuList', ["uses" => "MenuController@themeMenuList", "permissions" => "theme/themeMenuList"]);
        Route::any('/theme/themeMenuAdd', ["uses" => "MenuController@themeMenuAdd", "permissions" => "theme/themeMenuAdd"]);
        Route::any('/theme/themeMenuEdit', ["uses" => "MenuController@themeMenuEdit", "permissions" => "theme/themeMenuEdit"]);
        Route::any('/theme/themeMenuDelete', ["uses" => "MenuController@themeMenuDelete", "permissions" => "theme/themeMenuDelete"]);
        Route::any('/theme/themeMenuChangeStatus', ["uses" => "MenuController@themeMenuChangeStatus", "permissions" => "theme/themeMenuChangeStatus"]);
        Route::any('/theme/themeMenuMove', ["uses" => "MenuController@themeMenuMove", "permissions" => "theme/themeMenuEdit"]);
        Route::any('/theme/themeMenuSearchModuleMenu', ["uses" => "MenuController@themeMenuSearchModuleMenu", "permissions" => "theme/themeMenuSearchModuleMenu"]);
        Route::any('/theme/themeMenuGeneratePreset', ["uses" => "MenuController@themeMenuGeneratePreset", "permissions" => "theme/themeMenuAdd"]);
        Route::any('/theme/diy', ["uses" => "ThemeController@diy", "permissions" => "theme/diy"]);
        Route::any('/theme/themeSettings', ["uses" => "ThemeController@themeSettings", "permissions" => "theme/setting"]);
        Route::any('/theme/themeSlots', ["uses" => "ThemeController@themeSlots", "permissions" => "theme/setting"]);
        Route::any('/theme/themeSlotFile', ["uses" => "ThemeController@themeSlotFile", "permissions" => "theme/setting"]);
        Route::any('/theme/themeShorttags', ["uses" => "ThemeController@themeShorttags", "permissions" => "theme/setting"]);
        Route::any('/theme/themeHooks', ["uses" => "ThemeController@themeHooks", "permissions" => "theme/setting"]);
        Route::any('/theme/templateEditor', ["uses" => "ThemeController@templateEditor", "permissions" => "theme/setting"]);
        Route::any('/theme/templateSave', ["uses" => "ThemeController@templateSave", "permissions" => "theme/setting"]);


        //在线模块列表
        Route::any('/cloud', ["uses" => "FuncController@onlineCloudList", "permissions" => ""]);

        //功能安装
        Route::any('/module/install', ["uses" => "FuncController@install", "permissions" => ""]);
        Route::any('/module/recover-install', ["uses" => "FuncController@recoverInstall", "permissions" => ""]);
        Route::any('/module/delete', ["uses" => "FuncController@delete", "permissions" => ""]);
        //模块卸载
        Route::any('/module/uninstall', ["uses" => "FuncController@uninstall", "permissions" => ""]);
        //功能 启用/禁用
        Route::any('/module/changeStatus', ["uses" => "FuncController@changeStatus", "permissions" => ""]);
        //设为首页
        Route::any('/module/changeIndex', ["uses" => "FuncController@changeIndex", "permissions" => ""]);
        Route::any('/module/changeBack', ["uses" => "FuncController@changeBack", "permissions" => ""]);
        Route::any('/module/changeTop', ["uses" => "FuncController@changeTop", "permissions" => ""]);

        //插件配置
        Route::group(["prefix" => "plugin"], function () {
            Route::any('config', ["uses" => "PluginController@config", "permissions" => "plugin/config"]);
        });
    });

    Route::group(['namespace' => 'Admin'], function () {
        Route::group(["prefix" => "cms"], function () {
            Route::any('updateCmsVersion', ["uses" => "UpdateController@updateCmsVersion", "permissions" => "cms/updateCmsVersion"]);
            Route::any('checklimit', ["uses" => "UpdateController@checklimit", "permissions" => "cms/checklimit"]);
            Route::get('asyncTask', ["uses" => "AsyncTaskController@index", "permissions" => ""]);
            Route::get('asyncTask/status', ["uses" => "AsyncTaskController@status", "permissions" => ""]);
        });
    });

});

