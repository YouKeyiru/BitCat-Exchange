<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Services\AssetService;
use App\Services\CommissionService;
use App\Services\ContractTransService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MiningProfit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $profit;
    private $uid;
    private $target_id;

    /**
     * 挖矿收益返佣
     *
     * @param $uid
     * @param $profit
     */
    public function __construct($uid, $target_id, $profit)
    {
        //
        $this->uid = $uid;
        $this->profit = $profit;
        $this->target_id = $target_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        //
        $user = User::find($this->uid);
        $uids = array_filter(explode(',', $user->relationship));

        foreach ($uids as $uid) {
            try {

                $rate = CommissionService::getUserMiningRate($uid);

                $amount = bcMath($this->profit, $rate * 0.01, '*');
                if (!$amount) {
                    \Log::info('挖矿收益返佣,' . json_encode(['uid' => $uid, 'amount' => $amount, 'rate' => $rate, 'profit' => $this->profit]));
                    return;
                }

                //到佣金账户里
                $assetService = new AssetService();
                $assetService->writeBalanceLog($uid, $user->id, ContractTransService::WID, UserAsset::ACCOUNT_COMMISSION,
                    $amount, UserMoneyLog::BUSINESS_TYPE_ACTIVITY_PROFIT_BACK,
                    sprintf('挖矿收益返佣'));

            } catch (\Exception $exception) {

                \Log::error(sprintf('挖矿收益返佣，[%s]', $exception->getMessage()));
            }
        }

    }
}
