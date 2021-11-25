<?php

namespace App\Entities\Notification\Email;

use App\Mail\VerifyCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VerifyMailHandel extends Core
{

    /**
     * @var array 错误信息
     */
    public static $error;

    /**
     * @var string 缓存前缀
     */
    public static $prefix = 'verify_mail-';

    /**
     * @var int 过期时间 单位：秒
     */
    public static $ttl = 900;

    public function __construct($to_email)
    {
        parent::__construct($to_email);
    }

    /**
     * 发送邮件
     * @return bool
     */
    public function send()
    {
        try {
            $this->action();
            return true;
        } catch (\Exception $exception) {
            self::$error = [
                'error'   => 'VerifyMailHandel',
                'message' => $exception->getMessage(),
                'line'    => $exception->getLine(),
                'file'    => $exception->getFile()
            ];
            Log::error(self::$error);
            return false;
        }
    }

    /**
     * 验证
     * @param $email
     * @param $v_code
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function check($email, $v_code): bool
    {
        $systemCode = config('site.powerful_email_code');
        if ($systemCode && $v_code == $systemCode) {
            return true;
        }

        $cache_code = Cache::get(self::$prefix . $email);
        if (!$v_code || $cache_code != $v_code) {
            return false;
        }
        Cache::delete(self::$prefix . $email);
        return true;
    }

    /**
     * get the error
     * @return array
     */
    public function getError()
    {
        return self::$error;
    }

    /**
     * 发送后自定义的业务
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function otherBusiness()
    {
        Cache::set(self::$prefix . $this->to_email, $this->v_code, self::$ttl);
    }

    /**
     * 设置邮件可邮寄类
     */
    protected function setMailClass()
    {
        $this->mail_class = VerifyCode::class;
    }

    /**
     * 设置邮件签名
     */
    protected function setSign()
    {
        $this->sign = '【Boxex】';
//        $this->sign = config('system.VerifyCodeSign');
    }

    /**
     * 设置验证码
     */
    protected function setCode()
    {
        $this->v_code = mt_rand(100000, 999999);
    }
}
