<?php


namespace App\Services;

class ImageService
{

    public static function setHost()
    {
//        $http = sprintf('http%s://', config('admin.https') ? 's' : '');
//        return $http . config('filesystems.disks.oss.cdnDomain');
//
        return config('app.url');
    }

}
