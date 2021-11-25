<?php

namespace App\Jobs;

use App\Models\AddrRecharge;
use App\Models\UserAddress;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\WalletCode;
use App\Services\AssetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $wid;
    private $address;
    private $wallet;



    /**
     * Create a new job instance.
     * @param $user
     * @param $wid
     * @param $address
     * @return void
     */
    public function __construct($user, $wid, $address)
    {
        //
        $this->user = $user;
        $this->wid = $wid;
        $this->address = $address;
        $this->wallet = WalletCode::find($this->wid);;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        /*
  0 => array:10 [
    "uid" => 1
    "address" => "0x8867ebd661d9939ed9d217097048d58d09b674fd"
    "hash" => "0xd3e5d1340a1bd31d2a687126ed99c9a7c1df05c319ee5388da53b63b3ab26663"
    "amount" => 0.004
    "status" => 2
    "wid" => 1
    "code" => "ETH"
    "updated_at" => "2020-05-13T09:33:04.000000Z"
    "created_at" => "2020-05-13T09:33:04.000000Z"
    "id" => 1
  ]
         */
        try {
            if (!$this->wallet) {
                return;
            }

            $ret = [];
            if ($this->wallet->c_type == 2){
                //ETH系列
                $ret = AddrRecharge::checkEthSeries($this->user->id, $this->wallet->id, $this->wallet->code, $this->address, $this->wallet->contract_address ?? '');
            }else{

                $ret = AddrRecharge::checkBtcSeries($this->user->id, $this->wallet->id, $this->wallet->code, $this->user->account, $this->address);
            }

            if (!is_array($ret)) {
                Log::error('查询异常');
                return;
            }
            Log::info(json_encode($ret));
            foreach ($ret as $item) {
                $amount = intval($item['amount'] * 1e6) / 1e6;

                $assetService = new AssetService();
                $assetService->writeBalanceLog($this->user->id, $item['id'], $this->wid, UserAsset::ACCOUNT_CURRENCY, $amount, UserMoneyLog::ADDRESS_RECHARGE, '用户地址充值');

//                UserAddress::writeBalance([
//                    'uid' => $this->user->id,
//                    'wid' => $this->wid,
//                    'type' => 1,
//                    'symbol' => $this->wallet->code,
//                    'from_address' => '',
//                    'to_address' => $item['address'],
//                    'amount' => $amount,
//                    'hash' => $item['hash']
//                ]);

                Log::info(sprintf('[%s]%s,在线充值插入成功 %s', $this->user->account, $this->wallet->code, $item['amount']));
            }
        } catch (\Exception $exception) {
            Log::error(sprintf('[%s]%s,在线充值插入失败 %s', $this->user->account, $this->wallet->code, $exception->getMessage()));
        }
    }
}
