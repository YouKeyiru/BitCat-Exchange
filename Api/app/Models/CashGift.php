<?php

namespace App\Models;

class CashGift extends Model
{

    protected $table = 'cash_gift';
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
