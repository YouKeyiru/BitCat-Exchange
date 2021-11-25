<?php


namespace App\Http\Controllers\Api;


use App\Entities\Notification\Email\VerifyMailHandel;
use App\Entities\Notification\SmsHandel;
use App\Models\SmsLog;
use App\Models\User;
use Dingo\Api\Http\Request;

class SendMessageController extends BaseController
{
    /**
     * SendMessage constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 发送短信
     * @param Request $request
     * @return mixed
     */
    public function send_sms(Request $request)
    {
        try {
            // phone
            // captcha_code
            // key_str
            // from
            $phone = $request->input('phone', '');
            $from = $request->input('from', '');
            $area_code = $request->input('area_code', 86);
            $user = User::wherePhone($phone)->first();
            if (!in_array($from, ['register'])) {
                //非注册
                if (!$user) {
                    throw new \Exception(trans('user.username_notfound'));
                }
                $area_code = $user->area_code ?? $area_code;
                $phone = $user->phone ?? $phone;
            } else {
                //注册
                if ($user) {
                    throw new \Exception(trans('user.username_registered'));
                }
            }
            SmsHandel::send($phone, SmsLog::VERIFY_CODE, $area_code);
        } catch (\Exception $exception) {
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    public function send_email(Request $request)
    {
        $email = $request->input('email');
        $mail = new VerifyMailHandel($email);
        $ret = $mail->send();
        if (!$ret) {
            return $this->failed($mail->getError()['message']);
        }
        return $this->success();
    }
}
