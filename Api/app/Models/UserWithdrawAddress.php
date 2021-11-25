<?php

namespace App\Models;

class UserWithdrawAddress extends Model
{
    protected $title = '用户提币地址';
    protected $guarded = ['id'];
    protected $table = 'user_withdraw_address';

    const BTC_SERIES = 1;
    const ETH_SERIES = 2;
    const SERIES_TYPE = [
        self::BTC_SERIES => 'BTC系列',
        self::ETH_SERIES => 'ETH系列',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }
}
