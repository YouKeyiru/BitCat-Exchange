<?php

namespace App\Console\Commands;

use App\Models\ContractTrans;
use App\Models\User;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Services\AssetService;
use App\Services\ContractTransService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OvernightExpenses extends Command
{
    protected $signature = 'positions:Overnight';

    protected $description = '持仓过夜费';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $gljrate = config('contract.daily_interest');
        $assetService = new AssetService();
        DB::table('contract_positions')
            ->chunkById(100, function ($orders) use ($gljrate, $assetService) {
                foreach ($orders as $order) {
                    DB::beginTransaction();
                    try {
                        $bouns = $order->buy_price * $order->buy_num * $gljrate * 0.01;
                        $userBalance = AssetService::_getBalance($order->uid, ContractTransService::WID, UserAsset::ACCOUNT_CONTRACT);
                        if ($bouns <= 0) {
                            continue;
                        }

                        if ($userBalance->balance > $bouns) {

                            $inc = DB::table('contract_positions')->where(['id' => $order->id])->increment('dayfee', $bouns);
                            if (!$inc) {
                                throw new \Exception('扣除失败');
                            }
                            $assetService->writeBalanceLog($order->uid, $order->id, ContractTransService::WID, UserAsset::ACCOUNT_CONTRACT,
                                -$bouns, UserMoneyLog::OVERNIGHT, '利息');
                        } else {
                            //余额不够扣除日息
                            ContractTransService::closePosition(User::find($order->uid), $order->order_no, ContractTrans::CLOSE_FORCE);
                        }

                        DB::commit();
                    } catch (\Exception $exception) {

                        var_dump($exception->getMessage());
                        DB::rollBack();
                        \Log::error(sprintf('[%s]过夜日息异常 => %s', $order->id, $exception->getMessage()));
                    }
                }
            });
    }
}
