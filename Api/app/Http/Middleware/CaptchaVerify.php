<?php

namespace App\Http\Middleware;

use App\Http\Traits\ApiResponse;
use App\Services\CaptchaService;
use Closure;

class CaptchaVerify
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $c_code = $request->input('captcha_code');
        $key_str = $request->input('key_str');

        if (!$c_code) {
            return $this->failed(trans('user.input_captcha_code'));
        }

        if (!CaptchaService::check($key_str,$c_code)){
            return $this->failed(trans('user.captcha_code_error'));
        }

        return $next($request);
    }
}
