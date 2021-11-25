<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FbBuying extends Model
{
    protected $title = '法币交易求购';

    protected $table = 'fb_buying';
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class, 'uid','id')->select(['id','avatar','account','phone','name']);
    }
}
