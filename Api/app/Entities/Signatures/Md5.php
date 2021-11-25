<?php

namespace App\Entities\Signatures;


class Md5 implements SignatureInterface
{
    public static function sign($string, string $secret): string
    {
        return md5($string . $secret);
    }

    public static function check($string, string $secret, string $signature): bool
    {
        return static::sign($string, $secret) === $signature;
    }

}
