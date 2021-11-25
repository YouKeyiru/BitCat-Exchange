<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FbSell extends Model
{
    protected $title = '法币交易出售';

    protected $table = 'fb_sell';
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class, 'uid','id')->select(['id','account','avatar','phone','name']);
    }
}
