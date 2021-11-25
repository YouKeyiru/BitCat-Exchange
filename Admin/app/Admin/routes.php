<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix' => config('admin.route.prefix'),
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
    'as' => config('admin.route.prefix') . '.',
], function (Router $router) {
    $router->get('/', 'HomeController@index')->name('home');

    //代理
    $router->resource('agent-users', AgentUserController::class);

    //轮播图 公告 协议 版本
    $router->resource('system-slides', SystemSlidesController::class);
    $router->resource('system-posts', SystemPostsController::class);
    $router->resource('system-agrees', SystemAgreeController::class);
    $router->resource('system-versions', SystemVersionController::class);

    //用户 实名
    $router->resource('users', UserController::class);
    $router->resource('authentications', AuthenticationController::class);

    //资产
    $router->resource('user-assets', UserAssetController::class);
    //资金流水
    $router->resource('user-money-logs', UserMoneyLogController::class);
    //划转记录
    $router->resource('transfers', TransferController::class);


    //合约/币币/资产 币种
    $router->resource('products-contracts', ProductsContractController::class);
    $router->resource('products-exchanges', ProductsExchangeController::class);
    $router->resource('products-exchangesext', ProductsExchangeextController::class);
    $router->resource('wallet-codes', WalletCodeController::class);


    //币币交易
    $router->resource('exchange-orders', ExchangeOrderController::class);

    //合约交易
    $router->resource('contract-entrusts', ContractEntrustController::class);
    $router->resource('contract-positions', ContractPositionController::class);
    $router->resource('contract-trans', ContractTransController::class);

    //法币交易
    $router->resource('fb-appeals', FbAppealController::class);
    $router->resource('fb-buyings', FbBuyingController::class);
    $router->resource('fb-sells', FbSellController::class);
    $router->resource('fb-trans', FbTransController::class);
    $router->resource('fb-shop-applies', FbShopApplyController::class);

    //反馈列表
//    $router->resource('feed-backs', FeedBackController::class);

    //地址充值
    $router->resource('addr-recharges', AddressRechargeController::class);
    //地址列表
    $router->resource('user-addresses', UserAddressController::class);
    //充值列表
    $router->resource('recharges', RechargesController::class);
    //提币地址
    $router->resource('user-withdraw-addresses', UserWithdrawAddressController::class);
    //提币记录
    $router->resource('user-withdraw-records', UserWithdrawRecordController::class);

    //赠金列表
    $router->resource('cash-gift', CashGiftController::class);
    //赠金领取列表
    $router->resource('user-gift-log', UserGiftLogController::class);

    // 一键归集
    $router->resource('money-join', MoneyJoinController::class);
    Route::post('upload', 'SystemPostsController@upload');//images


});
