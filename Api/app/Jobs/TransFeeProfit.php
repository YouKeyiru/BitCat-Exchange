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

class TransFeeProfit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $order;

    /**
     * 合约交易手续费返佣
     *
     * @param $order
     */
    public function __construct($order)
    {
        //
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        //
        $user = User::find($this->order->uid);
        $uids = array_filter(explode(',', $user->relationship));

        foreach ($uids as $uid) {
            try {

                $rate = CommissionService::getUserTransFeeRate($uid);

                $amount = bcMath($this->order->fee, $rate * 0.01, '*');
                if ($amount <= 0) {
                    \Log::info('合约交易手续费返佣,' . json_encode(['uid' => $uid, 'amount' => $amount, 'rate' => $rate, 'fee' => $this->order->fee]));
                    continue;
                    return;
                }

                //到佣金账户里
                $assetService = new AssetService();
                $assetService->writeBalanceLog($uid, $user->id, ContractTransService::WID, UserAsset::ACCOUNT_COMMISSION,
                    $amount, UserMoneyLog::BUSINESS_TYPE_TRANS_FEE_PROFIT,
                    sprintf('合约交易手续费返佣[%s]金额[%s]', $user->account, $amount));

            } catch (\Exception $exception) {

                \Log::error(sprintf('合约交易手续费返佣，[%s]', $exception->getMessage()));
            }
        }

    }


}
