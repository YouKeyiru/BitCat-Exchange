<?php

namespace App\Models;

class UserAddress extends Model
{
    protected $title = '用户充币地址';
    protected $guarded = ['id'];
    protected $table = 'user_address';

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

}
