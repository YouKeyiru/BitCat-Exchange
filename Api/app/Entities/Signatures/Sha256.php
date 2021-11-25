<?php

namespace App\Entities\Signatures;

class Sha256 implements SignatureInterface
{

    public static function sign($param, string $secret): string
    {
        // TODO: Implement sign() method.
        return self::MakeSign($param, $secret);
    }

    public static function check($param, string $secret, string $signature): bool
    {
        // TODO: Implement check() method.
        $sign = self::MakeSign($param, $secret);
        return hash_equals($signature, $sign);
    }

    /**
     * 生成签名
     * @param array $param
     * @param string $secret
     * @return string
     */
    private static function MakeSign(array $param, string $secret): string
    {
        //签名步骤一：按字典序排序参数
        ksort($param);
        $string = self::ToUrlParams($param);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $secret;
        //签名步骤三：HMAC-SHA256
        $string = hash_hmac("sha256", urlencode($string), $secret);
        //签名步骤四：所有字符转为大写
        return strtoupper($string);
    }

    /**
     * 格式化参数格式化成url参数
     * @param array $param
     * @return string
     */
    private static function ToUrlParams(array $param): string
    {
        $buff = "";
        foreach ($param as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
}
