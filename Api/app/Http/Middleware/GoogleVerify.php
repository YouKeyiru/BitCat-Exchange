<?php

namespace App\Http\Middleware;

use App\Http\Traits\ApiResponse;
use App\Services\GoogleAuthenticatorService;
use Closure;
use Dingo\Api\Http\Request;
use Exception;

class GoogleVerify
{
    use ApiResponse;
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->input();
        try {
            $user = \Auth::user();
            $config = $user->config;
            //如果开启了谷歌验证
            if ($config->google_verify) {
                if (!isset($input['google_code'])) {
                    return $this->failed(trans('common.google_verification_code_error'));
                }

                if (!GoogleAuthenticatorService::CheckCode($config->google_secret, $input['google_code'])) {
                    return $this->failed(trans('common.google_verification_code_error'));
                }
            }
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage());
        }
        return $next($request);
    }
}
