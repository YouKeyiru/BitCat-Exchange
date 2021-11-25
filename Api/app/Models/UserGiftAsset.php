<?php

namespace App\Models;


class UserGiftAsset extends Model
{
    //
    protected $table = 'user_gift_assets';
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
