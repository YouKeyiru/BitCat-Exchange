<?php

namespace App\Http\Traits;

use App\Jobs\AutoConfirmation;
use App\Jobs\CheckBalance;
use App\Jobs\ClosePosition;
use App\Jobs\EntrustsToPositions;
use App\Jobs\MatchOrder;
use App\Models\UserAddress;
use App\Services\FbTransService;

trait Job
{
    /*
        AutoConfirmation
        ClosePosition
        EntrustsToPositions
        MatchOrder
    */

    // C2C交易 倒计时
    public function c2c_auto_job($order, $type)
    {
        $down_time = FbTransService::getCountDown($type);
        AutoConfirmation::dispatch($order, $type)
            ->delay(now()->addMinutes($down_time))
            ->onQueue('AutoConfirmation');
    }

    // 平仓类型 1手动平仓 2止盈平仓 3止损平仓 4爆仓
    public static function close_position($queue_data)
    {
        ClosePosition::dispatch($queue_data)->onQueue('ClosePosition');
    }

    //委托转持仓
    public static function entrusts_positions($queue_data)
    {
        EntrustsToPositions::dispatch($queue_data)->onQueue('EntrustsToPositions');
    }

    //充值到账
    public function addr_recharge($user)
    {
        $address = UserAddress::where(['uid' => $user->id])->get();

        foreach ($address as $addr) {

            CheckBalance::dispatch($user, $addr->address, $addr->type, 1)->onQueue('CheckBalance');

        }
    }
}
