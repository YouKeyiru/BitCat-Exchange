<?php

namespace App\Console\Commands;

use App\Models\ActivityUser;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Services\AssetService;
use App\Services\CommissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ActivityProfit extends Command
{

    protected $signature = 'activity:profit';


    protected $description = '质押发放收益';


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //
        ActivityUser::query()->where('status', ActivityUser::PROFIT_ING)->chunkById(1000, function ($datas) {
            foreach ($datas as $data) {
                DB::beginTransaction();
                try {
                    if ($data->days >= $data->cycle) {
                        //已完成的
                        $amount = 0;
                        $data->status = ActivityUser::PROFIT_OVER;

                    } else {
                        //日收益
                        $amount = bcMath($data->amount, $data->day_rate * 0.01, '*');

                        $data->profit += $amount;
                        $data->days++;
                        if ($data->days >= $data->cycle) {
                            $data->status = ActivityUser::PROFIT_OVER;
                        }
                    }

                    if ($amount) {
                        //分发收益
                        $this->incomeDistribution($data, $amount);
                    }

                    if ($data->status == ActivityUser::PROFIT_OVER) {
                        //本金返还
                        $this->backCap($data);
                    }


                    $data->save();
                    DB::commit();
                } catch (\Exception $exception) {

                    DB::rollBack();
                    echo $exception->getMessage().PHP_EOL;
//                    echo $exception->getFile().PHP_EOL;
//                    echo $exception->getLine().PHP_EOL;
                    \Log::error(sprintf('套餐发放收益异常，[%s]', $exception->getMessage()));
                }
            }
        });
    }


    public function incomeDistribution($data, $amount)
    {

        list($rate1, $rate2) = $this->getRate();

        $income1 = bcMath($amount, $rate1 * 0.01, '*');

        $income2 = bcMath($amount, $rate2 * 0.01, '*');

        $assetService = new AssetService();

        if ($income1) {
            $assetService->writeBalanceLog($data->uid, $data->id, $data->wid, UserAsset::ACCOUNT_LEGAL,
                $income1, UserMoneyLog::BUSINESS_TYPE_ACTIVITY_PROFIT,
                sprintf('[%s]%s日息', $data->activity->describe, UserAsset::ACCOUNT_TYPE[UserAsset::ACCOUNT_LEGAL]));
        }

        if ($income2) {
            $assetService->writeBalanceLog($data->uid, $data->id, $data->wid, UserAsset::ACCOUNT_CONTRACT,
                $income2, UserMoneyLog::BUSINESS_TYPE_ACTIVITY_PROFIT,
                sprintf('[%s]%s日息', $data->activity->describe, UserAsset::ACCOUNT_TYPE[UserAsset::ACCOUNT_CONTRACT]));

        }
        CommissionService::doMiningProfit($data->uid, $data->id, $amount);
    }

    public function backCap($data)
    {
        $assetService = new AssetService();
//        var_dump($data->amount);die;
        if ($data->amount) {
            $assetService->writeBalanceLog($data->uid, $data->id, $data->wid, UserAsset::ACCOUNT_LEGAL,
                $data->amount, UserMoneyLog::BUSINESS_TYPE_ACTIVITY_IN,
                sprintf('[%s]本金返还', $data->activity->describe));
        }
    }


    public function getRate(): array
    {
        return [
            config('activity.into_legal'), //法币账户
            config('activity.into_contract') //合约账户
        ];
    }
}
