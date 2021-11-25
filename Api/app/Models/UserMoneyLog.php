<?php

namespace App\Models;

class UserMoneyLog extends Model
{

    protected $guarded = ['id'];
    protected $table = 'user_money_log';

    const EXCHANGE = 100; //币币交易
    const CONTRACT = 150; // 合约交易
    const OVERNIGHT = 152; // 合约交易 过夜费
    const PROFIT_BACK = 153; // 盈利 立返
    const PROFIT_BACK_DAY = 154; // 亏损 日返
    const BUSINESS_TYPE_TRANS_FEE_PROFIT = 155;//手续费返佣
    const CASH_TANS = 200; // 提币
    const ADMIN_RECHARGE = 300; // 管理员充值
    const ADDRESS_RECHARGE = 203; //地址充值

    const BUSINESS_TYPE_TRANSFER = 20; //账户划转
    const BUSINESS_TYPE_TRANS_REWARD = 40; //交易佣金
    const BUSINESS_TYPE_FB_ORDER = 50; //法币交易单
    const BUSINESS_TYPE_FB_SHOP = 51; //法币商家
    const BUSINESS_TYPE_ACTIVITY_IN = 95;//质押
    const BUSINESS_TYPE_ACTIVITY_OUT = 96;//抽取
    const BUSINESS_TYPE_ACTIVITY_PROFIT = 97;//收益

    const BUSINESS_TYPE_ACTIVITY_PROFIT_BACK = 98;//质押收益返佣


    const BUSINESS_TYPE_INCOME_OUT = 99;//佣金抽取

    const RECHARGE_REBATE = 202; //充值返佣



    const BUSINESS_TYPE = [
        self::EXCHANGE => '币币交易',
        self::CONTRACT => '合约交易',
        self::OVERNIGHT => '合约交易-过夜费',
//        self::PROFIT_BACK => '盈利 立返',
//        self::PROFIT_BACK_DAY => '亏损 日返',
//        self::BUSINESS_TYPE_TRANS_FEE_PROFIT => '手续费返佣',
        self::CASH_TANS => '提币',
        self::ADMIN_RECHARGE => '管理员充值',
        self::ADDRESS_RECHARGE => '地址充值',
        self::BUSINESS_TYPE_TRANSFER => '账户划转',
//        self::BUSINESS_TYPE_TRANS_REWARD => '交易佣金',
        self::BUSINESS_TYPE_FB_ORDER => '法币交易单',
        self::BUSINESS_TYPE_FB_SHOP => '法币商家',
//        self::BUSINESS_TYPE_ACTIVITY_IN => '质押',
//        self::BUSINESS_TYPE_ACTIVITY_OUT => '抽取',
//        self::BUSINESS_TYPE_ACTIVITY_PROFIT => '日息收益',

        self::BUSINESS_TYPE_INCOME_OUT => '佣金划转',
//        self::RECHARGE_REBATE            => '充值返佣',
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

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id', 'id');
    }

    public function wallet()
    {
        return $this->belongsTo(WalletCode::class, 'wid', 'id');
    }


}
