<?php

namespace App\Http\Middleware;

use App\Exceptions\SecurityException;
use App\Http\Traits\ApiResponse;
use App\Models\FbPay;
use Closure;

class FbCreateOrder
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
        $user = \Auth::user();

        if ($request->order_type == 1) {
            if (!$user->config->fbshop) {
                return $this->failed('先认证商家才可发布出售订单');
            }

            $payment = $user->payment()->where('payment_type', $request->pay_method)->first();
            if (!$payment) {
                return $this->failed(sprintf('请先添加%s支付/收款方式', FbPay::PAYMENT_TYPE[$request->pay_method]));
            }
        }


        return $next($request);
    }
}
