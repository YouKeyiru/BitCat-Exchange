<?php
return [
    'btc_series' => [
        'rpc_ip'   => '127.0.0.1',
        'rpc_port' => '8338',
        'rpc_user' => '',
        'rpc_pwd'  => '',
        'confirmations' => 3
    ],

    'eth_series' => [
        'mode'       => 'production', //local,production
        'check_trans_url' => [
            'production' => 'http://api.etherscan.io/api?', //正式链
            'local'      => 'http://api-ropsten.etherscan.io/api?', //测试链
            'token'      => ''
        ],
        'confirmations' => 6,
        'ignore_address' => []
    ]
];
