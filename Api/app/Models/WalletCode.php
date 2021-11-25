<?php

namespace App\Models;

use App\Services\ImageService;

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

    public function getIconAttribute($value)
    {
        if ($value) {
            $value = ImageService::setHost('admin') .'storage/' . $value;
        }
        return $value;
    }
}
