<?php

namespace App\Models;


class UserAsset extends Model
{
    //
    protected $table = 'user_assets';
    protected $guarded = ['id'];

    //1 币币账户 2 合约账户 3 法币账户
    const ACCOUNT_CURRENCY = 1;
    const ACCOUNT_CONTRACT = 2;
    const ACCOUNT_LEGAL = 3;
    const ACCOUNT_GIFT = 4;
    const ACCOUNT_TYPE = [
        self::ACCOUNT_CURRENCY => '资金账户',
        self::ACCOUNT_CONTRACT => '合约账户',
        self::ACCOUNT_LEGAL    => '法币账户',
        self::ACCOUNT_GIFT    => '赠金账户',
    ];

    //TODO 账户间划转问题
    const ACCOUNT_CODE = [
        self::ACCOUNT_CURRENCY => [],
        self::ACCOUNT_CONTRACT => [],
        self::ACCOUNT_LEGAL    => [''],
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

    public function walletCode()
    {
        return $this->hasOne(WalletCode::class, 'id', 'wid');
    }

}
