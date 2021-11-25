<?php

namespace App\Models;

class SystemAgree extends Model
{
    protected $title = '平台协议';

    protected $table = 'system_agree';
    protected $guarded = ['id'];

    const TYPE_AGREE = [
        1 => '关于我们',
        2 => '免责声明',
        3 => '法律声明',
        4 => '隐私条款',
        5 => '服务协议',
        6 => '用户协议',
        7 => '关于反洗钱',
        8 => '新手教程',
        9 => '注册协议',
        10 => '帮助中心',
    ];

//1 关于我们,
//2 免责声明，
//3 法律声明，
//4 隐私条款
//5 服务协议，
//6 注册协议,
//7 关于反洗钱,
//8 新手教程,
//9 用户协议，
//10 帮助中心，
//11 合约费率,
//12 合约指南,
//13 操作帮助,
//14 申诉协议,
}
