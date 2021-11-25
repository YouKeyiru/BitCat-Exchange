<?php

namespace App\Console\Commands;

use App\Services\DepthPctService;
use App\Services\MarketService;
use App\Models\ProductsExchange;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class MakePct extends Command
{
    protected $signature = 'pct-generate';

    protected $description = '深度图生成';//自选币


    public function __construct()
    {
        parent::__construct();

    }

    public function handle()
    {

        //获取自选币列表
        // $product = ProductsExchange::where(['type'=>'2','state'=>'1'])->select('id', 'code','pname')->get()->toArray();
        $codes = ProductsExchange::where(['type'=>'2','state'=>'1'])->pluck('code')->toArray();
        // $codes = [
        //     'btc/usdt',
        //     'eth/usdt',
        //     'etc/usdt',
        // ];

        while (1) {

            foreach ($codes as $code) {
                $code = strtolower($code);
                try {
                    DepthPctService::MakePct($code);
                } catch (\Exception $exception) {
                    Log::error('MakePct=>' . $exception);
                }
            }

            sleep(5);
        }


    }


}
