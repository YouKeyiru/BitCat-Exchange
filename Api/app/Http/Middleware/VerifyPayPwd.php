<?php

namespace App\Http\Middleware;

use App\Http\Traits\ApiResponse;
use Closure;
use Exception;

class VerifyPayPwd
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
            if (!$request->payment_password) {
                return $this->failed(trans('user.input_pay_pwd'));
            }
            $pay_pwd = $user->payment_password;
            if (!$pay_pwd) {
                return $this->failed(trans('user.pay_pwd_no_set'));
            }

            if (!\Hash::check($request->payment_password, $pay_pwd)) {
                return $this->failed(trans('user.pay_pwd_error'));
            }
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage());
        }
        return $next($request);
    }
}
