<?php

namespace App\Models;

class Recharge extends Model
{
    //

    const SYSTEM_RECHARGE = 1;
    const ADDRESS_RECHARGE = 2;

    const WAIT_PAY = 1; //支付状态 未支付
    const PAYED = 2; //已支付

    const TYPE_STATUS = [
        self::SYSTEM_RECHARGE  => '后台充值',
        self::ADDRESS_RECHARGE => '地址充值',
    ];

    protected $table = 'recharges';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }
    public function wallet()
    {
        return $this->belongsTo(WalletCode::class, 'wid', 'id');
    }
}
