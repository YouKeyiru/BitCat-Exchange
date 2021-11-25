<?php

namespace App\Console\Commands;

use App\Services\BurstService;
use App\Services\MarketService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class Burst extends Command
{

    protected $signature = 'Burst';

    protected $description = '';

    private $subscribe_redis;
    private $contract_redis;

    public function __construct()
    {
        parent::__construct();

        $this->contract_redis = Redis::connection('contract');

        $this->subscribe_redis = MarketService::getConnect('subscribe');
    }

    public function handle()
    {
        $contract_redis = $this->contract_redis;

        $this->subscribe_redis->subscribe([MarketService::getChannel('ticker')], function ($subscribe) use ($contract_redis) {
            BurstService::doBurstPositions($contract_redis, json_decode($subscribe, true));
        });
    }
}
