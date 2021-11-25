<?php

namespace App\Http\Middleware;

use App\Entities\Notification\Email\VerifyMailHandel;
use App\Http\Traits\ApiResponse;
use Closure;
use Exception;

class VerifyMailCode
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

            $result = VerifyMailHandel::check($user->email, $request->v_code);

            if (!$result) {
                return $this->failed(trans('sms.code_verify_error'));
            }

        } catch (Exception $exception) {
            return $this->failed($exception->getMessage());
        }
        return $next($request);
    }
}
