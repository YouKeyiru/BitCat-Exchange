<?php

namespace App\Models;


class DayBackProfit extends Model
{
    //
    protected $title = '盈亏返佣返还';

    protected $table = 'day_back_profit';
    protected $guarded = ['id'];


    public static function getRate()
    {
        return \DB::table('admin_config')
            ->where('name', 'contract.day_back')
            ->value('value');
    }
}
