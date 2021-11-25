<?php

return [
    'mode'       => 'production', //local,production
    'production' => env('ETH_API','http://127.0.0.1:8989'), //正式链
    'local'      => env('ETH_API','http://127.0.0.1:8989'), //测试链

    'url' => [
        'checkAccount' => '/api/account/checkAccount',     //检测是否是以太坊地址
        'createWallet' => '/api/account/createwallet',     //创建以太坊钱包地址
        'tokenTrans'   => '/api/transaction/token',        //以太坊代币转账交易 (Token)
        'ethTrans'     => '/api/transaction/eth',          //以太坊转账交易 (ETH)
        'tokenBalance' => '/api/contract/tokenBalance',    //查询地址代币余额 (Token)
        'ethBalance'   => '/api/account/getBalance',       //查询地址余额 (ETH)
        'dealDetail'   => '/api/transaction/detail',       //查询交易详情
    ],


    'sendFeeAddr' => '',//发送手续费地址
    'togetherAddr' => ''//归拢地址

];
