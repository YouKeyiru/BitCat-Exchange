<?php
return [
    'btc_series' => [
        'rpc_ip'   => env('RPC_IP'),
        'rpc_port' => env('RPC_PORT'),
        'rpc_user' => env('RPC_USER'),
        'rpc_pwd'  => env('RPC_PWD'),
        'confirmations' => 3
    ],

    'eth_series' => [
        'mode'       => env('ETH_RECHARGE_MODEL','local'), //local,production
        'check_trans_url' => [
            'production' => 'http://api.etherscan.io/api?', //正式链
            'local'      => 'http://api-ropsten.etherscan.io/api?', //测试链
            'token'      => env('ETH_RECHARGE_TOKEN')
        ],
        'confirmations' => 3,
        'ignore_address' => []
    ]
];
