<?php

namespace App\Models;
use App\Services\ImageService;

class SystemSlides extends Model
{
    protected $title = '轮播图';
    protected $table = 'system_slides';
    protected $guarded = ['id'];

    public function getImageAttribute($value)
    {
        if ($value) {
            $value = ImageService::setHost('admin') .'storage/' . $value;
        }
        return $value;
    }

}
