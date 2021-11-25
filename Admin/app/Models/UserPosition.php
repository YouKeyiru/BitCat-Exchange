<?php

namespace App\Models;

class UserPosition extends Model
{
    //
    protected $table = 'user_position';

    public function user() {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

}
