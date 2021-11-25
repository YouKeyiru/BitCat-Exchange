<?php

namespace App\Models;


class ActivityUser extends Model
{
    //
    protected $title = '用户参与活动记录';

    protected $table = 'activity_user';
    protected $guarded = ['id'];

    const PROFIT_ING = 1;
    const PROFIT_OVER = 2;
    const PROFIT_OVER_W = 3;
    //1  收益中 2 收益结束 3 提前撤离
    const PROFIT_STATUS = [
        self::PROFIT_ING => '收益中',
        self::PROFIT_OVER => '收益结束',
        self::PROFIT_OVER_W => '提前撤离',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id')->select('id', 'account','phone', 'email', 'name');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id', 'id');
    }
}
