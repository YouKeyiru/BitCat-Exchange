<?php

namespace App\Models;

class WalletCode extends Model
{
    //
    protected $table = 'wallet_code';

    public static function getWidByCode($code)
    {
        return self::where(['code' => trim($code)])->value('id');

    }

    public static function getExchangeFeeById($id)
    {
        return self::where('id', $id)->value('exchange_fee') ?? 0;
    }


    public function getWithdrawMinAttribute($value)
    {
        return floatval($value);
    }

    public function getWithdrawMaxAttribute($value)
    {
        return floatval($value);
    }
    public function getWithdrawHandlingFeeAttribute($value)
    {
        return floatval($value);
    }
}
