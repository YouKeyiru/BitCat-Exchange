<?php

namespace App\Services;

use Earnp\GoogleAuthenticator\Facades\GoogleAuthenticator as Google;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GoogleAuthenticatorService
{
    /**
     * 验证
     * @param $secret
     * @param $oneCode
     * @return mixed
     */
    public static function CheckCode($secret, $oneCode)
    {
        return Google::CheckCode($secret, $oneCode);
    }

    /**
     * 生成谷歌验证码
     * @return array
     */
    public static function CreateSecret()
    {

        $array = Google::CreateSecret();//创建一个Secret
        $qrCode = QrCode::encoding('UTF-8')
            ->format('png')
            ->size(200)
            ->margin(1)
            ->generate($array['codeurl']);

        $code_url = 'data:image/png;base64,' . base64_encode($qrCode);
        return [
            'secret'   => $array['secret'],
            'code_url' => $code_url
        ];
    }

}
