<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace'  => 'App\Http\Controllers\Api',
    'middleware' => [
        'enableCrossRequests',
        'throttle:1000,1'
    ]
], function ($api) {
    $api->any('run/{command}', 'IndexController@run')->name('run');

    $api->any('test', 'IndexController@recharge')->name('test');
    $api->any('test1', 'IndexController@test')->name('test1');;// api.security

    // 用户注册
    $api->post('register', 'UsersController@store')->name('api.users.store');
    // 用户登录
    $api->post('login', 'UsersController@login')->name('api.users.login');
    // 重置登录密码
    $api->post('user/reset_login_pwd', 'UserLoginPwdController@reset_login_pwd')->name('api.user_login_pwd.index');

    // 版本更新
    $api->get('app/version', 'SystemVersionsController@softwareUpdate')->name('api.app.version');
    $api->get('app/download', 'SystemVersionsController@downloadLink')->name('api.app.download');


    // 发送手机验证码
    $api->post('send_sms', 'SendMessageController@send_sms')->middleware('captcha_verify');
    // 发送邮箱验证码
    $api->post('send_email', 'SendMessageController@send_email');
    // 图片验证码
    $api->get('captcha', 'CaptchaController@show')->name('api.slides.show');

    //轮播图
    $api->get('slides/show', 'SystemSlidesController@slides')->name('api.slides.show');
    //公告列表
    $api->get('posts/posts_list', 'SystemPostsController@posts_list')->name('api.posts.slides');
    //资讯列表
    $api->get('posts/blocks_msg', 'SystemPostsController@blocks_msg')->name('api.posts.slides');
    //公告/资讯 详情
    $api->get('posts/show', 'SystemPostsController@posts_info')->name('api.posts.show');
    //协议列表
    $api->get('agree/agree_list', 'SystemAgreeController@agree_list')->name('api.agree.agree_list');
    //协议详情
    $api->get('agree/show', 'SystemAgreeController@show')->name('api.agree.show');



    //合约获取币种配置信息
    $api->get('contract/code_info', 'ContractController@codeConfigInfo')->name('api.contract.code_info');
    //合约币种列表
    $api->get('contract/symbols', 'ContractController@symbols')->name('api.contract.symbols');
    //币种详情数据
    $api->get('contract/symbol_detail', 'ContractController@symbolDetail')->name('api.exchange.symbol_detail');
    //合约币种盘口
    $api->get('contract/depth', 'ContractController@depth')->name('api.contract.depth');
    //合约币种深度图
    $api->get('contract/pct', 'ContractController@pct')->name('api.contract.pct');
    //合约实时成交
    $api->get('contract/trader', 'ContractController@trader')->name('api.contract.trader');
    //K线历史数据
    $api->get('kline/index', 'KLineController@index');

    //币种列表
    $api->get('exchange/symbols', 'ExchangeController@symbols')->name('api.exchange.symbols');
    //币币详情数据
    $api->get('exchange/get_code_info', 'ExchangeController@getCodeInfo')->name('api.exchange.get_code_info');
    //币币实时交易
    $api->get('exchange/realtime_trade', 'ExchangeController@RealTimeDeal')->name('api.exchange.realtime_trade');
    //币币盘口
    $api->get('exchange/get_handicap', 'ExchangeController@getHandicap')->name('api.exchange.get_handicap');
    //币币深度
    $api->get('exchange/get_code_depth', 'ExchangeController@getCodeDepth')->name('api.exchange.get_code_depth');

    //交易大厅列表
    $api->get('fbTrade/trading', 'FbTradeController@trading')->name('api.fbTrade.trading');


    //参数配置 客服
    $api->get('about/content', 'SoftwareController@content')->name('api.about.content');

    $api->get('country/codes', 'AreaCodesController@areaCodes');




    // 需要验证token. ,'auth_user'
    $api->group(['middleware' => ['api.auth','auth_user']], function ($api) {
//        $api->any('test1', 'IndexController@test')->name('test1')->middleware('api.security');;// api.security

        // 上传图片
        $api->post('upload/images', 'UploadController@images')->name('api.upload.images');

        //反馈
        $api->post('feedback', 'FeedbackController@store')->name('api.feedback');
        //反馈记录
        $api->get('feedback/record', 'FeedbackController@record')->name('api.feedback.record');


        //=======用户部分 20=======
        // 用户详情
        $api->get('user/info', 'UsersController@show')->name('api.users.show');
        //认证信息
        $api->get('authentication/info', 'AuthenticationController@index')->name('api.authentication.index');
        //初级认证
        $api->post('authentication/primary_certification', 'AuthenticationController@primaryCertification')->name('api.authentication.primaryCertification');
        //高级认证
        $api->post('authentication/advanced_certification', 'AuthenticationController@advancedCertification')->name('api.authentication.advancedCertification');
        // 设置支付密码
        $api->post('user/set_pay_pwd', 'UserPayPwdController@set_pay_pwd')->name('api.users.set_pay_pwd')
            ->middleware('sms_verify');
        // 修改支付密码
        $api->post('user/modify_pay_pwd', 'UserPayPwdController@modify_pay_pwd')->name('api.users.modify_pay_pwd')
            ->middleware('sms_verify');
        // 重置支付密码
        $api->post('user/reset_pay_pwd', 'UserPayPwdController@reset_pay_pwd')->name('api.users.reset_pay_pwd')
            ->middleware('sms_verify');

        // 修改登录密码
        $api->post('user/modify_login_pwd', 'UserLoginPwdController@modify_login_pwd')->name('api.users.modify_login_pwd')
            ->middleware('sms_verify');

        // 修改用户名称
        $api->post('user/set_name', 'UsersController@updateName')->name('api.users.updateName');
        // 设置头像
        $api->post('user/set_avatar', 'UsersController@updateAvatar')->name('api.users.updateAvatar');
        // 绑定手机号
        $api->post('user/set_phone', 'UsersController@updatePhone')->name('api.users.updatePhone');

        // 绑定邮箱号
        $api->post('user/set_email', 'UsersController@updateEmail')->name('api.users.updateEmail');

        // 我的推广页面信息
        $api->get('invite/index', 'InviteController@index')->name('api.invite.index');
        // 我的推广海报
        $api->get('invite/poster', 'InviteController@poster')->name('api.invite.poster');
        // 我的客户
        $api->get('invite/push', 'InviteController@push')->name('api.invite.push');
        // 佣金明细
        $api->get('invite/income_flow', 'InviteController@income_flow')->name('api.invite.income_flow');
        //佣金提取
        $api->post('invite/income_out', 'InviteController@income_out')->name('api.users.income_out')
            ->middleware('pwd_verify');
        //提取记录
        $api->get('invite/income_out_flow', 'InviteController@income_out_flow')->name('api.invite.income_out_flow');


        // 资产信息
        $api->get('account/index', 'AccountController@index')->name('api.account.index');
        // 账户余额
        $api->get('account/account_asset', 'AccountController@account_asset')->name('api.transfer.account_asset');
        //资产明细
        $api->get('account/deal_flow', 'AccountController@deal_flow')->name('api.transfer.deal_flow');
        //流水类型
        $api->get('account/business_type', 'AccountController@businessType')->name('api.transfer.business_type');

        //=======谷歌验证码 3=======
        //创建谷歌验证码
        $api->get('google/create', 'GoogleAuthenticatorController@createGoogleSecret')->name('api.google.create');
        //绑定
        $api->post('google/bind', 'GoogleAuthenticatorController@updateGoogle')->name('api.google.bind');
        //切换状态
        $api->post('google/update_status', 'GoogleAuthenticatorController@googleVerifyStart')->name('api.google.update_status');


        //=======划转 3=======
        // 可划转币种
        $api->get('transfer/allow_code', 'TransferController@allow_code')->name('api.transfer.allow_code');
        //划转页面信息
        $api->get('transfer/info', 'TransferController@index')->name('api.transfer.index');
        // 划转
        $api->post('transfer/action', 'TransferController@store')->name('api.transfer.store')
            ->middleware('pwd_verify');
        // 划转明细
        $api->get('transfer/flow', 'TransferController@flow')->name('api.transfer.flow');

        //=======提币地址=======
        //添加地址
        $api->post('address/add', 'WithdrawAddressController@add')->name('api.address.add');
        //地址列表
        $api->get('address/show', 'WithdrawAddressController@show')->name('api.address.show');
        //删除地址
        $api->post('address/delete', 'WithdrawAddressController@delete')->name('api.address.delete');

        //充币币种列表
        $api->get('recharge/code_list', 'RechargeController@codeList')->name('api.recharge.code_list');
        //获取充币地址
        $api->get('recharge/address', 'RechargeController@walletRecharge')->name('api.recharge.address');
        //充币记录
        $api->get('recharge/record', 'RechargeController@record')->name('api.recharge.record');


        //=======提币=======
        //提币页面信息
        $api->get('withdraw/show', 'ApplyWithdrawController@show')->name('api.withdraw.show');
        //提币币种列表
        $api->get('withdraw/code_list', 'ApplyWithdrawController@codeList')->name('api.withdraw.code_list');
        //提币记录
        $api->get('withdraw/withdraw_log', 'ApplyWithdrawController@withdrawLog')->name('api.withdraw.withdraw_log');
        //提币
        $api->post('withdraw/apply_withdraw', 'ApplyWithdrawController@applyWithdraw')->name('api.withdraw.apply_withdraw')
            ->middleware('pwd_verify', 'sms_verify', 'google_verify', 'advanced');
        //提币撤销
        $api->post('withdraw/revoke_withdraw', 'ApplyWithdrawController@revokeWithdraw')->name('api.withdraw.revoke_withdraw');



        //=======法币交易 19=======
        //法币交易公共信息
        $api->get('fbTrade/common_data', 'FbTradeController@commonData')->name('api.fbTrade.common_data');
        //发布交易单
        $api->post('fbTrade/issue_order', 'FbTradeController@issueOrder')->name('api.fbTrade.issue_order')
            ->middleware('pwd_verify', 'fb.create_order');

//        //交易大厅列表
//        $api->get('fbTrade/trading', 'FbTradeController@trading')->name('api.fbTrade.trading');

        //下单
        $api->post('fbTrade/create_order', 'FbTradeController@createOrder')->name('api.fbTrade.create_order');
//            ->middleware('pwd_verify');
        //订单详情
        $api->get('fbTrade/order_detail', 'FbTradeController@orderDetail')->name('api.fbTrade.order_detail');
        //标记付款
        $api->post('fbTrade/pay_order', 'FbTradeController@setOrderStatus')->name('api.fbTrade.pay_order');
        //确认放币
        $api->post('fbTrade/confirm', 'FbTradeController@confirm')->name('api.fbTrade.confirm')
            ->middleware('pwd_verify');
        //取消订单
        $api->post('fbTrade/cancel_order', 'FbTradeController@cancelOrder')->name('api.fbTrade.cancel_order');
        //交易明细
        $api->get('fbTrade/order_list', 'FbTradeController@orderList')->name('api.fbTrade.order_list');
        //发布单明细
        $api->get('fbTrade/issue_order_list', 'FbTradeController@issueOrderList')->name('api.fbTrade.issue_order_list');
        //申诉
        $api->post('fbTrade/appeal', 'FbTradeController@appeal')->name('api.fbTrade.appeal');
        //撤销发布订单
        $api->post('fbTrade/revoke_order', 'FbTradeController@revokeOrder')->name('api.fbTrade.revoke_order');
        //添加支付方式
        $api->post('fbTrade/payment_add', 'FbTradeController@paymentAdd')->name('api.fbTrade.payment_add');
        //支付方式信息
        $api->get('fbTrade/payment_info', 'FbTradeController@paymentInfo')->name('api.fbTrade.payment_info');
        //改变支付方式状态
        $api->post('fbTrade/set_status', 'FbTradeController@setPayStatus')->name('api.fbTrade.set_status');
        //支付方式列表
        $api->get('fbTrade/pay_list', 'FbTradeController@payList')->name('api.fbTrade.pay_list');
        //商家申请页面
        $api->get('fbTrade/shop_apply_index', 'FbTradeController@shopApplyIndex')->name('api.fbTrade.shop_apply_index');
        //成为商家
        $api->post('fbTrade/shop_apply', 'FbTradeController@shopApply')->name('api.fbTrade.shop_apply');
        //撤销商家
        $api->post('fbTrade/shop_cancel', 'FbTradeController@shopCancel')->name('api.fbTrade.shop_cancel');

        //=======合约交易 9=======
        //交易信息-风险率等
        $api->get('contract/info', 'ContractController@info')->name('api.contract.info');

        //下单
        $api->post('contract/create_order', 'ContractController@createOrder')->name('api.contract.create_order')
            ->middleware('contract_trans');

        //持仓单设置止盈止损
        $api->post('contract/set_point', 'ContractController@setPoint')->name('api.contract.set_point');
        //撤单
        $api->post('contract/revoke_order', 'ContractController@revokeOrder')->name('api.contract.revoke_order');
        //持仓/委托
        $api->get('contract/trans_data', 'ContractController@transData')->name('api.contract.trans_data');
        //平仓
        $api->post('contract/close_position', 'ContractController@closePosition')->name('api.contract.close_position');
        //全平仓
        $api->post('contract/close_all', 'ContractController@allClosePosition')->name('api.contract.close_all');
        //成交列表
        $api->get('contract/order_list', 'ContractController@orderList')->name('api.contract.order_list');


        //=======币币交易 6=======
//        //币种列表
//        $api->get('exchange/symbols', 'ExchangeController@symbols')->name('api.exchange.symbols');
//        //币种详情
        $api->get('exchange/symbols_data', 'ExchangeController@symbolsData')->name('api.exchange.symbols_data');
//        //下单
        $api->post('exchange/create_order', 'ExchangeController@createOrder')->name('api.exchange.create_order');
//        //撤单
        $api->post('exchange/revoke_order', 'ExchangeController@revokeOrder')->name('api.exchange.revokeOrder');
//        //交易列表
        $api->get('exchange/order_list', 'ExchangeController@orderList')->name('api.exchange.order_list');
//
//        //盘口数据
//        $api->get('exchange/depth', 'ExchangeController@getDepth')->name('api.exchange.depth');


        //=======质押挖矿=======
        //质押
        $api->post('activity/store', 'ActivityController@store')->name('api.activity.store')
            ->middleware('pwd_verify');
        //抽取
        $api->post('activity/out', 'ActivityController@out')->name('api.activity.out')
            ->middleware('pwd_verify');
        //收益记录
        $api->get('activity/show_profit', 'ActivityController@show_profit')->name('api.activity.show_profit');
        //参与记录
        $api->get('activity/show_record', 'ActivityController@show_record')->name('api.activity.show_record');
        //页面信息
        $api->get('activity/index', 'ActivityController@index')->name('api.activity.index');

        //========赠金=========
        //领取赠金
        $api->post('cashgift/receive', 'CashGiftController@createGift')->name('api.cashgift.createGift');
        //赠金流水记录
        $api->get('cashgift/gift_list', 'CashGiftController@giftList')->name('api.cashgift.giftList');
        //赠金资产
        $api->get('cashgift/gift_asset', 'CashGiftController@giftAsset')->name('api.cashgift.gift_asset');

    });


});
