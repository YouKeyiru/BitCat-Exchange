<?php


return [
    'status' => [
        'timeout' => false,
        'sign'    => true,
        'nonce'   => true
    ],
    'timeout'    => 10, //失效时间，单位: 秒
    'secret_key' => '3P4E9F0J1uolYRspvRZker8ju7IGk6eV',

    /*

        假设传送的参数如下：

            [
                'param1' => 1,
                'param2' => 1,
                'param3' => 1,
                'nonce_str' => 'ibuaiVcKdpRxkhJA',
            ]

        第一步：对参数按照key=value的格式，并按照参数名ASCII字典序排序如下：
            $stringA = "param1=1&param2=2&param3=3&nonce_str=ibuaiVcKdpRxkhJA";

        第二步：拼接API密钥：
            //注：key为约定的密钥key
            $string = $stringA . "&key=192006250b4c09247ec02edce69f6a2d"

            //注：HMAC-SHA256签名方式 ,部分语言的hmac方法生成结果二进制结果，需要调对应函数转化为十六进制字符串。
            sign = strtoupper(hash_hmac("sha256", $string, $key))

        最终得到最终发送的数据:
            [
                'param1' => 1,
                'param2' => 1,
                'param3' => 1,
                'nonce_str' => 'ibuaiVcKdpRxkhJA',
                'sign' => '817B9D173F072E5C7F6DCDF8948BDFD12372F0C8BFD329DB0FC95B05A5D7A213'
            ]

    接口协议中包含字段nonce_str，主要保证签名不可预测。我们推荐生成随机数算法如下：调用随机数函数生成，将得到的值转换为字符串。


    */
];
