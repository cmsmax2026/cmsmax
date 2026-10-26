<?php

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

use Illuminate\Support\Facades\Route;

Route::prefix('formtools/open')->namespace('Api')->group(function () {
    Route::get('/{access_identification}/meta', 'OpenApiController@meta');
    Route::post('/{access_identification}/submit', 'OpenApiController@submit');
});

Route::prefix('formtools')->namespace('Api')->group(function () {
    Route::post('/buy/{model}/{id}', 'FormtoolsPaymentApiController@buy');
    Route::get('/check-pay/{order_no}', 'FormtoolsPaymentApiController@check');
    Route::post('/cancel/{order_no}', 'FormtoolsPaymentApiController@cancel');
});
