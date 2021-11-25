<?php

namespace App\Http\Middleware;

use App\Http\Traits\ApiResponse;
use App\Models\Authentication;
use Closure;
use Exception;

class AuthUser
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
            if ($user->stoped) {
                return $this->failed('账号已冻结',401);
            }

        } catch (Exception $exception) {
            return $this->failed($exception->getMessage());
        }
        return $next($request);
    }
}
