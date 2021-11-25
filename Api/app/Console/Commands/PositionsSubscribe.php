<?php

namespace App\Console\Commands;

use App\Models\ContractTrans;
use App\Models\User;
use App\Services\ContractTransService;
use App\Services\MarketService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PositionsSubscribe extends Command
{

    protected $signature = 'positions:subscribe';

    protected $description = '止盈止损平仓';

    private $subscribe_redis;

    private $contract_redis;

    public function __construct()
    {
        parent::__construct();

        $this->contract_redis = Redis::connection('contract');
        $this->subscribe_redis = MarketService::getConnect('subscribe');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->subscribe_redis->subscribe([MarketService::getChannel('ticker')], function ($message) {
            $this->closePositions($message);
        });
    }

    public function closePositions($message)
    {
        try {
            $subscribe = json_decode($message);

            $now_price = $subscribe->price;

            if ($subscribe && $now_price) {
                $key = 'contract:order:positions:' . $subscribe->code;
                $members = $this->contract_redis->smembers($key);

//                if ($subscribe->code == 'btc/usdt'){
//                    echo $subscribe->code.'=='.$now_price.'=='.json_encode($members).PHP_EOL;
//
//                }

                if (empty($members)) return;

                foreach ($members as $order_no) {
                    //订单信息
                    $order_info = $this->contract_redis->hgetall($order_no);

                    if (!$order_info) {

                        continue;
                    }

                    $flag_win = false;
                    $flag_loss = false;
                    if ($order_info['otype'] == 1) { //1涨 2跌
                        // 买涨止盈   当前价格 >= 止盈价格
                        if ($order_info['stop_win'] > 0 && $now_price >= $order_info['stop_win']) {
                            $flag_win = true;
                        }
                        // 买涨止损  当前价格 <= 止损价格
                        if ($order_info['stop_loss'] > 0 && $now_price <= $order_info['stop_loss']) {
                            $flag_loss = true;
                        }
                    } else {
                        // 买跌止盈   当前价格 <= 止盈价格
                        if ($order_info['stop_win'] > 0 && $now_price <= $order_info['stop_win']) {
                            $flag_win = true;
                        }
                        // 买跌止损  当前价格 >= 止损价格
                        if ($order_info['stop_loss'] > 0 && $now_price >= $order_info['stop_loss']) {
                            $flag_loss = true;
                        }
                    }
                    if ($flag_win) {
                        ContractTransService::closePosition(User::find($order_info['uid']), $order_no, ContractTrans::CLOSE_SURPLUS, $order_info['stop_win']);
                        ContractTransService::delCacheOrder($order_no, $key);
                    }
                    if ($flag_loss) {
                        ContractTransService::closePosition(User::find($order_info['uid']), $order_no, ContractTrans::CLOSE_LOSS, $order_info['stop_loss']);
                        ContractTransService::delCacheOrder($order_no, $key);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
