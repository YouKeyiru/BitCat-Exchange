<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    const VERIFY_CODE = 1; //验证码
    protected $guarded = ['id'];


}
