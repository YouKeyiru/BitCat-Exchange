<?php

namespace App\Models;

class UserGiftLog extends Model
{

    protected $guarded = ['id'];
    protected $table = 'user_gift_log';

    const CONTRACT_SERVICE_FEE  = 200; //合约交易手续费 抵扣
    const EXCHANGE_SERVICE_FEE  = 100; //币币交易手续费 抵扣
    const CASH_GIFT_RECEIVE     = 90;//赠金领取



    const BUSINESS_TYPE = [
        self::CONTRACT_SERVICE_FEE  => '抵扣合约交易手续费',
        self::EXCHANGE_SERVICE_FEE  => '抵扣币币交易手续费',
        self::CASH_GIFT_RECEIVE     => '赠金领取',
    ];

    public static function getBusinessType()
    {
        return self::BUSINESS_TYPE;
    }

    public function getMoneyAttribute($value)
    {
        return floatval($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

    public function wallet()
    {
        return $this->belongsTo(WalletCode::class, 'wid', 'id');
    }


}
