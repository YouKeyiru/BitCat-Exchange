<?php


namespace App\Services;

use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Support\Facades\Cache;

class CaptchaService
{
    const KEY = 'captcha-';

    // 生成验证码
    public static function store($key_str)
    {

        $key = self::KEY . $key_str;

        $captchaBuilder = new CaptchaBuilder();

        $captchaBuilder->setBackgroundColor(220, 210, 230);

        $captcha = $captchaBuilder->build();
        $expiredAt = now()->addMinutes(1);

        // $captcha->getPhrase(); 获取验证码：abcd

        Cache::put($key, ['key_str' => $key_str, 'code' => $captcha->getPhrase()], $expiredAt);

//        $captcha->output();

        return [
            'captcha_key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
            // $captcha->inline(); 将验证码转成 base64 图片
            'captcha_image_content' => $captcha->inline()
        ];
    }

    /**
     * 检测
     * @param $key_str
     * @param $code
     * @return bool
     */
    public static function check($key_str, $code)
    {
//        return true;
        if (!$code || !$key_str) {
            return false;
        }
//        $key = self::KEY . $key_str;
        $data = Cache::get($key_str);
        if (!$data) {
            return false;
        }
        return strtolower($code) == strtolower($data['code']) ?? false;
    }
}
