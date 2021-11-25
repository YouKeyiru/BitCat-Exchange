<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FbAppeal extends Model
{
    protected $title = '用户申诉';

    protected $table = 'fb_appeal';
    protected $guarded = ['id'];

    public function appeal() {
        return $this->belongsTo(User::class, 'appeal_uid','id')->select(['id','avatar','account','phone','name']);
    }
    public function beappeal() {
        return $this->belongsTo(User::class, 'be_appeal_uid','id')->select(['id','avatar','account','phone','name']);
    }
    public function win() {
        return $this->belongsTo(User::class, 'win_uid','id')->select(['id','avatar','account','phone','name']);
    }

}
