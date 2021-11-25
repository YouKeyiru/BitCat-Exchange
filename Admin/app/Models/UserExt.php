<?php

namespace App\Models;


class UserExt extends Model
{
    //
    protected $table = 'user_ext';
    protected $guarded = ['id'];


    public function getMarketInvestmentAttribute($value)
    {
        return floatval($value);
    }


}
