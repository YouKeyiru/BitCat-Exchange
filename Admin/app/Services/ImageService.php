<?php


namespace App\Services;

class ImageService
{

    public static function setHost($host = 'admin')
    {
//        $http = sprintf('http%s://', config('admin.https') ? 's' : '');
//        return $http . config('filesystems.disks.oss.cdnDomain');
//
        if ($host == 'api'){
            return config('app.api_url');
        }else{
            return config('app.url');
        }

    }

}
