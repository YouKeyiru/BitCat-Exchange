<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->resource('agent-users', AgentUserController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('users', UserController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('user-moni', UserMoniController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('fee-rebates', FeeRebatesController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('profit-rebates', ProfitRebatesController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('contract-entrusts', ContractEntrustController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('contract-positions', ContractPositionController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('contract-trans', ContractTransController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('agent-assets', AgentAssetsController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('user-assets', UserAssetsController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('agent-withdraws', WithdrawController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('user-recharges', RechargesController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('user-withdraws', UserWithdrawController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('user-withdraw-address', UserWithdrawAddressController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('user-withdraw-records', UserWithdrawRecordController::class, ['except' => ['create','edit', 'delete']]);

    $router->resource('fb-appeals', FbAppealController::class, ['except' => ['create','delete']]);
    $router->resource('fb-buyings', FbBuyingController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('fb-sells', FbSellController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('fb-trans', FbTransController::class, ['except' => ['create','edit', 'delete']]);
    $router->resource('exchange-order', ExchangeOrderController::class, ['except' => ['create','edit', 'delete']]);

    $router->resource('user-money-log', UserMoneyLogController::class, ['except' => ['create','edit', 'delete']]);

    $router->resource('user-return', FeeReturnController::class);

});
