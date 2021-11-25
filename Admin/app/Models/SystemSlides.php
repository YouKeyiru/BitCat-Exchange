<?php

namespace App\Models;
use App\Services\ImageService;

class SystemSlides extends Model
{
    protected $title = '轮播图';
    protected $table = 'system_slides';
    protected $guarded = ['id'];

    const INDEX_SHOW = 1;//首页显示


    public function getImageAttribute($value)
    {
        if ($value) {
//            $value = ImageService::setHost('admin') . $value;
            $value = ImageService::setHost('admin') .'storage/' . $value;
        }
        return $value;
    }

}
