<?php


namespace App\Entities\Notification\Sms;


use App\Models\SmsLog;

class Content
{
    public static function getMsg($type, $code, $lang)
    {
        if (!$lang) $lang = 'zh-CN';
        switch ($type) {
            case SmsLog::VERIFY_CODE:
                $cn = '您的验证码是' . $code . '，该验证码15分钟内有效，请勿泄漏于他人';
                $en = 'Your verification code' . $code . '，is valid for 15 minutes. Do not leak it to others';
                break;
            case SmsLog::RESET_PASSWORD:
                $cn = '您的验证码为：' . $code . '，您正在进行密码重置操作，若非您本人操作，请忽略本短信。';
                $en = 'Your verification code is:' . $code . ', you are doing password reset operation, if not your own operation, please ignore this short message.';
                break;
            case SmsLog::AUTHENTICATION_CODE:
                $cn = '您的验证码：' . $code . '，您正进行身份验证，若非您本人操作，请及时修改密码。';
                $en = 'Your authentication code:' . $code . ', you are authenticating, if not your own operation, please modify the password in time.';
                break;
            case SmsLog::CHANGE_USERINFO_CODE:
                $cn = '您验证码为：' . $code . '，您正在尝试变更重要信息，请妥善保管账户信息。若非本人操作，请及时修改密码。';
                $en = 'Your verification code is:' . $code . ', you are trying to change important information, please keep account information properly. If it is not my operation, please change the password in time.';
                break;

            default:
                $cn = '您的验证码是' . $code . '该验证码15分钟内有效，请勿泄漏于他人';
                $en = 'Your verification code' . $code . '，is valid for 15 minutes. Do not leak it to others';
                break;
        }
        return $lang == 'zh-CN' ? $cn : $en;
    }
}
