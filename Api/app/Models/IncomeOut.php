<?php

namespace App\Models;


class IncomeOut extends Model
{
    //
    protected $title = '佣金提取';

    protected $table = 'income_out';
    protected $guarded = ['id'];

    //1 待审核 2 通过 3 拒绝 refuse
    const CHECK = 1;
    const SUCCESS = 2;
    const REFUSE = 3;

    const STATUS_TYPE = [
        self::CHECK => '待审核',
        self::SUCCESS => '通过',
        self::REFUSE => '拒绝',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

//    public function getStatusAttribute($value)
//    {
//
//        return self::STATUS_TYPE[$value];
//    }

    public function getAmountAttribute($value)
    {

        return floatval($value);
    }

    public function getSurplusAttribute($value)
    {

        return floatval($value);
    }
}
