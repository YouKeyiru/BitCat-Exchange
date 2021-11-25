<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ContractTransService;
use App\Services\MarketService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class EntrustsSubscribe extends Command
{

    protected $signature = 'entrusts:subscribe';

    protected $description = '委托转持仓';

    private $contract_redis;

    private $subscribe_redis;

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
            $this->entrustedToPositions($message);
        });
    }

    /**
     * 委托转持仓逻辑
     * @param $message
     */
    public function entrustedToPositions($message)
    {
        try {
            $subscribe = json_decode($message);
            $now_price = $subscribe->price;

            if ($subscribe && $now_price) {

                $key = 'contract:order:entrusts:' . $subscribe->code;
                $members = $this->contract_redis->smembers($key);

                if (empty($members)) return;

                foreach ($members as $order_no) {
                    //订单信息
                    $order_info = $this->contract_redis->hgetall($order_no);
                    if (!$order_info) continue;

                    // 买入价大于买入时最新价，现在最新价大于等于买入价  --- 买涨
                    // 买入价小于买入时最新价，现在最新价小于等于买入价  --- 买跌
                    if (($order_info['buy_price'] > $order_info['market_price'] && $now_price >= $order_info['buy_price']) ||
                        ($order_info['buy_price'] < $order_info['market_price'] && $now_price <= $order_info['buy_price'])) {
                        //符合条件，转持仓
                        //echo 'ok=='.$order_no.PHP_EOL;
                        ContractTransService::delCacheOrder($order_no, $key);
                        ContractTransService::entrustsToPositions(User::find($order_info['uid']), $order_no, $now_price);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
