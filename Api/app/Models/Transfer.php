<?php

namespace App\Models;

class Transfer extends Model
{
    //
    protected $table = 'transfer';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

    public function walletCode()
    {
        return $this->hasOne(WalletCode::class, 'id', 'wid');
    }
}
