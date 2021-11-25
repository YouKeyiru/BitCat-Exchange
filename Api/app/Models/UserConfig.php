<?php

namespace App\Models;

class UserConfig extends Model
{
    //
    protected $table = 'user_config';
    protected $guarded = ['id'];

    protected $hidden = [
        'id', 'uid', 'google_secret', 'created_at', 'updated_at'
    ];
}
