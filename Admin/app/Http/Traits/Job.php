<?php

namespace App\Http\Traits;

use App\Jobs\AutoConfirmation;
use App\Jobs\ClosePosition;
use App\Jobs\EntrustsToPositions;
use App\Jobs\MatchOrder;
use App\Jobs\MiningProfit;
use App\Jobs\TransFeeProfit;
use App\Services\FbTransService;

trait Job
{
    /*
        0611-AutoConfirmation
        0611-ClosePosition
        0611-EntrustsToPositions
        0611-TransFeeProfit
        0611-MiningProfit
    */

    // C2C交易 倒计时
    public function c2c_auto_job($order, $type)
    {
        $down_time = FbTransService::getCountDown($type);
        AutoConfirmation::dispatch($order, $type)
            ->delay(now()->addMinutes($down_time))
            ->onQueue(config('app.name').'-AutoConfirmation');
    }

    // 平仓类型 1手动平仓 2止盈平仓 3止损平仓 4爆仓
    public static function close_position($queue_data)
    {
        ClosePosition::dispatch($queue_data)->onQueue(config('app.name').'-ClosePosition');
    }

    //委托转持仓
    public static function entrusts_positions($queue_data)
    {
        EntrustsToPositions::dispatch($queue_data)->onQueue(config('app.name').'-EntrustsToPositions');
    }

    //合约交易手续费返佣
    public static function trans_fee_profit($order)
    {
        TransFeeProfit::dispatch($order)->onQueue(config('app.name').'-TransFeeProfit');
    }

    //挖矿收益返佣
    public static function mining_profit($uid, $target_id, $profit)
    {
        MiningProfit::dispatch($uid, $target_id, $profit)->onQueue(config('app.name').'-MiningProfit');
    }

    /**
     * 币币订单撮合
     * @param $params
     */
//    public function MatchOrder($params)
//    {
//        MatchOrder::dispatch($params)->onQueue('MatchOrder');
//    }

}
