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

Route::get('/', function () {
    return view('welcome');
});


Route::get('/daily-losing-check', [
    'as' => 'daily.losing.check',
    'uses' => 'DailyCalculateController@index',
]);

Route::get('/daily-losing-check/total/{id}', [
    'as' => 'daily.losing.total',
    'uses' => 'DailyCalculateTotalController@index',
]);

Route::get('/daily-rolling-check', [
    'as' => 'daily.rolling.check',
    'uses' => 'DailyCalculateController@rolling',
]);

Route::get('/daily-rolling-user', [
    'as' => 'daily.rolling.user',
    'uses' => 'DailyInfoMinRollingController@user',
]);

Route::get('/daily-rolling-sub', [
    'as' => 'daily.rolling.sub',
    'uses' => 'DailyInfoMinRollingController@recommends',
]);

Route::get('/daily-check-company', [
    'as' => 'root',
    'uses' => 'DailyCalculateController@company',
]);
Route::get('daily-check-sub/{id}', [
    'as' => 'daily.sub',
    'uses' => 'DailyCalculateSubController@sub',
]);

Route::get('/daily-check-user', [
    'as' => 'daily.user',
    'uses' => 'DailyCalculateSubController@userlist',
]);


Route::get('/game/slot/log', [
    'as' => 'slot.log',
    'uses' => 'GetGameLogDataController@index',
]);


Route::get('/rolling/delete', [
    'as' => 'roll.del',
    'uses' => 'DailyCalculateDelController@start',
]);

Route::post('/sales/change/rolling', [
    'as' => 'sales.change.rolling',
    'uses' => 'ChangeMoneyController@change_rolling',
])->middleware('ajax');

Route::post('/sales/change/losing', [
    'as' => 'sales.change.losing',
    'uses' => 'ChangeMoneyController@change_losing',
])->middleware('ajax');


Route::get('/api/slot/test', [
    'as' => 'api.slot',
    'uses' => 'SlotApiController@index',
]);


Route::get('/game/slot/money/out', [
    'as' => 'slot.money.get',
    'uses' => 'SlotMoneyOutController@index',
]);
