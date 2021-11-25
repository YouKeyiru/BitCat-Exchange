<?php

namespace App\Models;


class ActivityUser extends Model
{
    //
    protected $title = '用户参与活动记录';

    protected $table = 'activity_user';
    protected $guarded = ['id'];

    // 1 '收益中 2 收益结束 3 提前撤离
    const PROFIT_ING = 1;
    const PROFIT_OVER = 2;
    const PROFIT_OVER_W = 3;

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id')->select('id', 'phone', 'email', 'name');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id', 'id');
    }
}
