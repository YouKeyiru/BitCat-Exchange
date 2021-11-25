<?php


namespace App\Services;

class ImageService
{

    public static function setHost($host='api')
    {
//        $http = sprintf('http%s://', config('admin.https') ? 's' : '');
//        return $http . config('filesystems.disks.oss.cdnDomain');
//

        if ($host == 'api'){
            return config('app.url');
        }else{
            return config('app.admin_url');
        }
    }

}
