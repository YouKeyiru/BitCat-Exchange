<?php

namespace App\Http\Middleware;

use App\Http\Traits\ApiResponse;
use App\Models\Authentication;
use Closure;
use Exception;

class Advanced
{
    use ApiResponse;

    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = \Auth::user();
            if ($user->authentication != Authentication::ADVANCED_CHECK_AGREE) {
                return $this->failed('高级身份认证未通过');
            }

        } catch (Exception $exception) {
            return $this->failed($exception->getMessage());
        }
        return $next($request);
    }
}
