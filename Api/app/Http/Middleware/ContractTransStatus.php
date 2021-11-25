<?php

namespace App\Http\Middleware;

use App\Admin\Controllers\ProductsContractController;
use App\Http\Traits\ApiResponse;
use App\Models\ProductsContract;
use Closure;
use Exception;

class ContractTransStatus
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle($request, Closure $next)
    {
        // 合约交易状态 1为开放 2为关闭
//        $status = config('contract.start_down');
//        if ($status == 2) {
//            return $this->failed('交易服务维护中');
//        }

//        $code = $request->code;
//        $otype = $request->otype;
//
//        $trans_type = $request->trans_type;
//
//        $codeInfo = ProductsContract::query()->where('code',$code)->first();
//        $codeInfo->buy_up;
//        $codeInfo->buy_down;
//
//
//        if ($trans_type == 2){
//            // 控制单边方向  0 不限制 1 买入  2 卖出
//            if (!$codeInfo->buy_up && $otype == 1){
//                return $this->failed('限制单边买入');
//            }
//
//            if (!$codeInfo->buy_down && $otype == 2){
//                return $this->failed('限制单边卖出');
//            }
//        }


        return $next($request);
    }
}
