<?php

namespace App\Models;

class SystemPosts extends Model
{
    protected $title = '平台公告';

    protected $table = 'system_posts';
    protected $guarded = ['id'];

    //1开启 2关闭
    const DISPLAY_ON = 1;
    const DISPLAY_OFF = 2;

    const POSTS = 1;//公告
    const GUIDE = 2;//交易指南
    const COURSE = 3;//新手教程
//    const AGREE = 4;//用户协议
    const ALERT = 5;//弹窗公告
    const TYPE = [
        self::POSTS  => '公告',
        self::GUIDE  => '交易指南',
        self::COURSE => '新手教程',
//        self::AGREE  => '用户协议',
        self::ALERT  => '弹窗公告',
    ];
}
