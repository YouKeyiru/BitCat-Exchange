<?php

namespace App\Http\Middleware;

use App\Entities\Notification\Email\VerifyMailHandel;
use App\Entities\Notification\SmsHandel;
use App\Http\Traits\ApiResponse;
use Closure;
use Exception;

class VerifySmsCode
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = \Auth::user();

            if ($user->phone) {
                $result = SmsHandel::check($user->phone, $request->v_code);
            } else {
                $result = VerifyMailHandel::check($user->email, $request->v_code);
            }
            if (!$result) {
                return $this->failed(trans('sms.code_verify_error'));
            }

//            if (!$request->code_type) {
//                return $this->failed(trans('sms.sms_code_type_error'));
//            }
//            if ($request->code_type == 1) {
//                // 手机短信验证码验证
//                $result = SmsHandel::check($user->phone, $request->v_code);
//            }
//
//            if ($request->code_type == 2) {
//                // 邮箱验证码验证
//                $result = VerifyMailHandel::check($user->email, $request->v_code);
//            }
//
//            if ($request->code_type == 3) {
//                // 谷歌验证
//                $result = GoogleAuthenticatorService::CheckCode($user->config->google_secret, $request->v_code);
//            }
//
//            if (!$result) {
//                return $this->failed(trans('sms.code_verify_error'));
//            }
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage());
        }
        return $next($request);
    }
}
