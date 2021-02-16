<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'domain'        => env('ADMIN_DOMAIN'),
    'port'          => '8081',
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'MainController@main')->name('home');
    $router->resource('users', UserController::class);
    $router->resource('members', MemberController::class);
    $router->resource('subs', SubStruController::class);
    $router->resource('deposits', DepositController::class);
    $router->resource('banks', BankController::class);
    $router->resource('inquotes', InquotesController::class);
    $router->resource('depositMin', DepositMinController::class);
    $router->resource('refundMin', RefundMinController::class);
    $router->resource('refundquotes', RefundQuoteController::class);
    $router->resource('gameinquotes', GameInfoController::class);
    $router->resource('refunds', RefundController::class);
    $router->resource('user-cs-boards', UserCsBoardController::class);
    $router->resource('headquarters', HeadquarterController::class);
    $router->resource('game-members', GameMemberController::class);
    $router->resource('log-moneys', LogMoneyController::class);
    $router->resource('game-logs', GameLogController::class);
    $router->resource('game-sit-logs', GameLogSitController::class);
    $router->resource('game-tour-logs', GameTourLogController::class);
    $router->resource('headquarter-deposits', HeadquarterDepositController::class);
    $router->resource('dailyinfo-masters', DailyinfoMasterController::class);
    //$router->resource('dailyinfo-company', DailyinfoCompanyController::class);
    /*$router->resource('dailyinfo-sub_company', DailyinfoBottomTotalController::class);
    $router->resource('dailyinfo-distributor', DailyinfoBottomTotalController::class);
    $router->resource('dailyinfo-store', DailyinfoBottomTotalController::class);
    $router->resource('dialyinfo-user', DailyinfoUserController::class);
    $router->resource('dailyinfo_bottom', DailyinfoBottomController::class);*/
    //$router->resource('dailyinfo-bottom-totals', DailyinfoBottomTotalController::class);
    $router->resource('refund-admins', RefundAdminController::class);
    $router->resource('money-logs', MoneyLogController::class);
    $router->resource('resources', ResourceController::class);
    $router->resource('current-set', CurrentSetController::class);
    $router->resource('realtime-company', RealTimeCompanyController::class);
    $router->resource('realtime-sub_company', RealTimeSubController::class);
    $router->resource('realtime-distributor', RealTimeSubController::class);
    $router->resource('realtime-store', RealTimeSubController::class);
    $router->resource('points', PointController::class);
    $router->resource('realtime-company', RealTimeCompanyController::class);
    $router->resource('admin-members', adminMemberController::class);


    $router->get('api/selectusers', 'ApiController@selectusers');
    $router->get('api/selectonlyusers', 'ApiController@selectonlyusers');
    $router->get('api/select-sub-company', 'ApiController@selectSubcompany');
    $router->get('api/select-distributor', 'ApiController@selectDistributor');
    $router->get('api/select-store', 'ApiController@selectStore');

    $router->resource('daily-info-losings', DailyInfoLosingController::class);
    $router->resource('daily-losings_view', DailyInfoLosingViewController::class);

    $router->resource('dailyinfo-company', DailyInfoLosingCompanyController::class);
    $router->resource('dailyinfo-sub_company', DailyInfoLosingSubController::class);
    $router->resource('dailyinfo-distributor', DailyInfoLosingDistController::class);
    $router->resource('dailyinfo-store', DailyInfoLosingStoreController::class);
    $router->resource('dialyinfo-user', DailyInfoLosingUserController::class);
    //$router->resource('dailyinfo/{slug}', DailyInfoSalesMixController::class);

    $router->resource('headquarters-public', HeadquarterPublicController::class);
    $router->resource('points-public', PointPublicController::class);

    $router->resource('deposit-admins', DepositAdminController::class);
});
