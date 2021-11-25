<?php

namespace App\Models;

class SmsLog extends Model
{
    const VERIFY_CODE = 1; //验证码
    const RESET_PASSWORD = 2;//密码重置
    const AUTHENTICATION_CODE = 3;//身份验证
    const CHANGE_USERINFO_CODE = 4;//变更重要信息
    protected $guarded = ['id'];
}
