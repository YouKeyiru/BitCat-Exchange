<?php

namespace App\Models;

use App\Services\ImageService;

class Authentication extends Model
{
    //
    const PRIMARY_CHECK = 1;//初级认证
    const ADVANCED_WAIT_CHECK = 2;//高级认证待审核
    const ADVANCED_CHECK_AGREE = 3;//高级认证通过
    const ADVANCED_CHECK_REFUSE = 4;//高级认证拒绝

    const STATUS = [
        self::PRIMARY_CHECK         => '初级认证',
        self::ADVANCED_WAIT_CHECK   => '高级认证待审核',
        self::ADVANCED_CHECK_AGREE  => '高级认证通过',
        self::ADVANCED_CHECK_REFUSE => '高级认证拒绝',
    ];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }


    public function getFrontImgAttribute($value)
    {
//        if ($value) {
//            $value = ImageService::setHost() . '/storage' . $value;
//        }
        return $value;
    }

    public function getBackImgAttribute($value)
    {
//        if ($value) {
//            $value = ImageService::setHost() . '/storage' . $value;
//        }
        return $value;
    }

    public function getHandheldImgAttribute($value)
    {
//        if ($value) {
//            $value = ImageService::setHost() . '/storage' . $value;
//        }
        return $value;
    }
}
