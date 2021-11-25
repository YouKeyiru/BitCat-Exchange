<?php


namespace App\Entities\Notification;

use Exception;

class SmsHandel
{
    /**
     * 发送短信
     * @param $phone
     * @param int $contentType
     * @param int $area
     * @return bool
     * @throws Exception
     */
    public static function send($phone, $contentType = 1, $area = 86)
    {
//        return true;
        $class = self::getClass();
        $result = $class->send($phone, $contentType, $area);
        if (!$result) {
            throw new Exception(sprintf('发送失败,错误代码' . $class->errCode));
        }
        return true;
    }

    /**
     * 校验
     * @param $phone
     * @param $code
     * @return bool
     * @throws Exception
     */
    public static function check($phone, $code)
    {
        $systemCode = config('site.powerful_phone_code');
        if ($systemCode && $code == $systemCode) {
            return true;
        }

//        return true;
        $class = self::getClass();
        $result = $class->check($phone, $code);
        if (!$result) {
            return false;
            throw new Exception(trans('common.verification_code_error'));
        }
        return true;
    }


    /**
     * 获取配置类
     * @return mixed
     * @throws Exception
     */
    private static function getClass()
    {
        $config = config('notification');
        $class = $config['providers'][$config['network']]['class'];
        if (!isset($config['providers'][$config['network']])) {
            throw new Exception($config['network'] . '  providers error !');
        }
        if (!class_exists($class)) {
            throw new Exception($config['network'] . '  method configuration error !');
        }
        return new $class($config['providers'][$config['network']]);
    }


}
