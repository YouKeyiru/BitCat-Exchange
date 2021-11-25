<?php

namespace App\Entities\Signatures;

interface SignatureInterface
{
    public static function sign($param, string $secret): string;

    public static function check($param, string $secret,string $signature): bool;

}
