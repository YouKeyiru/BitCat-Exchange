<?php

return [
    'network' => 'chinese',

    'providers' => [
        'chinese' => [
            'class' => \App\Entities\Notification\Sms\Chinese::class,
            'uid' => 'hjq22961701',
            'key' => 'b845fdd5281455909e5ed',
            'signature' => '',
            'ttl' => 900, //过期时间 单位秒
        ],
        '106' => [
            'class' => \App\Entities\Notification\Sms\SmsNormal::class,
            'uid' => '',
            'key' => '',
            'ttl' => 60, //过期时间 单位秒
        ],
    ]


];
