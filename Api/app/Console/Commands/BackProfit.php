<?php

namespace App\Console\Commands;

use App\Models\DayBackProfit;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Services\AssetService;
use App\Services\ContractTransService;
use Illuminate\Console\Command;

class BackProfit extends Command
{

    protected $signature = 'day:backProfit';

    protected $description = '亏损每天返还';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //
        $assetService = new AssetService();
        DayBackProfit::query()->where('status', 1)->chunkById(100, function ($members) use ($assetService) {
            foreach ($members as $data) {
                try {
                    $amount = bcMath($data->amount, $data->rate * 0.01, '*');

                    $raw_back_price = $data->back_price;

                    $data->back_price = bcMath($data->back_price, $amount, '+');
                    if ($data->back_price >= $data->amount) {
                        $data->back_price = $data->amount;
                        $amount = bcMath($data->amount, $raw_back_price, '-');
                        $data->status = 2;
                    }

                    $data->back_num++;
                    $data->save();

                    if ($amount) {
                        //操作资产
                        $assetService->writeBalanceLog($data->uid, $data->id, ContractTransService::WID, UserAsset::ACCOUNT_CONTRACT,
                            $amount, UserMoneyLog::PROFIT_BACK_DAY, '亏损每天返还');

                        //TODO　只用于交易的部分余额


                    }
                } catch (\Exception $exception) {

                    \Log::error('亏损每天返还 异常=>' . $exception->getMessage());
                }
            }
        });
    }
}
