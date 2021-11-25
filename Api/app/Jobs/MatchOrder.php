<?php

namespace App\Jobs;

use App\Models\ExchangeOrder;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\WalletCode;
use App\Models\UserGiftAsset;
use App\Models\UserGiftLog;
use App\Services\GiftService;
use App\Services\AssetService;
use App\Services\MatchEngineService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\Job;

class MatchOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Job;

    public $params;

    /**
     * Create a new job instance.
     *
     * @param $params
     */
    public function __construct($params)
    {
        //
        $this->params = $params;
    }

    /**
     * Execute the job.
     * @throws Exception
     */
    public function handle()
    {
        // php artisan queue:work --queue=MatchOrder
        try {
            $result = MatchEngineService::run($this->params);
            //var_dump($result);
            if ($result['code'] = 1) {
                $this->orderHandle($result);
            } else {
                Log::error('撮合失败==>' . json_encode($result, JSON_UNESCAPED_UNICODE));
            }
        } catch (Exception $e) {
            Log::error('Job:MatchOrder==>' . json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE));
            throw new Exception('任务重试');
        }

    }

    /**
     * @param $result
     * @throws \Exception
     */
    protected function orderHandle($result)
    {
        if (!isset($result['data']['updateArr'])) {
            throw new Exception($result['data']);
        }

        //需要更新的单子
        $updateArr = $result['data']['updateArr'];
        //匹配上的单子  实时成交
        $matchArr = $result['data']['matchArr'];
        //新生成的单子
        $newArr = $result['data']['newArr'];

        if (!empty($matchArr)) {
            $this->doMatchArr($matchArr);
        }

        if (!empty($updateArr)) {
            $this->doUpdateArr($updateArr);
        }

        if (!empty($newArr)) {
            //something
        }
        $this->Markline($matchArr); //跑自选币的K线
    }

    //更新成交数量和成交均价
    private function doMatchArr(array $matchArr): void
    {

        foreach ($matchArr as $item => $value) {
            $buy_order = ExchangeOrder::find($value['buy_order']);
            $sell_order = ExchangeOrder::find($value['sell_order']);

            // 成交均价 = (已成交价*已成交数量 + 本次成交价*本次成交数量) / (已成交数量+本次成交数量)
            $cjprice = ($value['price'] * $value['quantity'] + $buy_order->cjnum * $buy_order->cjprice) / ($value['quantity'] + $buy_order->cjnum);
            $flag = ExchangeOrder::query()->where(['id' => $buy_order->id, 'version' => $buy_order->version])
                ->update([
                    'cjprice' => $cjprice,
                    'cjnum'   => bcMath($buy_order->cjnum, $value['quantity'], '+'),
                    'version' => $buy_order->version + 1
                ]);
            if ($flag === false) {
                throw new Exception(sprintf('[%s] buy_order 订单更新失败', $buy_order->id));
            }


            $cjprice = ($value['price'] * $value['quantity'] + $sell_order->cjnum * $sell_order->cjprice) / ($value['quantity'] + $sell_order->cjnum);
            $flag = ExchangeOrder::query()->where(['id' => $sell_order->id, 'version' => $sell_order->version])
                ->update([
                    'cjprice' => $cjprice,
                    'cjnum'   => bcMath($sell_order->cjnum, $value['quantity'], '+'),
                    'version' => $sell_order->version + 1
                ]);
            if ($flag === false) {
                throw new Exception(sprintf('[%s] sell_order 订单更新失败', $sell_order->id));
            }
        }
    }

    //更新成交状态
    private function doUpdateArr(array $updateArr): void
    {
        $assetService = new AssetService();
        $giftService  = new GiftService();
        foreach ($updateArr as $item => $value) {
            $orderInfo = ExchangeOrder::find($value['order_id']);
            //该订单已完成
            if ($value['sellout'] == 1) {
                $orderInfo->status = ExchangeOrder::OVER_TRANS;
                $deduct_fee = 0; //抵扣多少手续费
                if ($orderInfo->uid != 0) {
                    if ($orderInfo->type == 2) {
                        //卖单结算
                        $total = bcMath($orderInfo->cjprice, $orderInfo->cjnum, '*');
                        // 手续费
                        $fee_rate = WalletCode::getExchangeFeeById($orderInfo->r_wid) * 0.01;
                        //实际结算数量
                        $fee = bcMath($total, $fee_rate, '*');
                        //处理赠送的U抵扣交易手续费 tonyang
                        $giftRow = UserGiftAsset::where(['uid'=>$orderInfo->uid,'wid'=>$orderInfo->r_wid])->first();
                        if($giftRow){ //存在
                            if($giftRow->balance > 0){
                                if($giftRow->balance >= $fee){
                                    $deduct_fee = $fee; //抵扣的手续费
                                }else{
                                    $deduct_fee = $giftRow->balance; //抵扣的手续费
                                }
                            }
                        }
                        if($deduct_fee > 0){
                            // $newfee = $fee-$deduct_fee;
                            $newfee = bcMath($fee, $deduct_fee, '-');
                            $total -= $newfee;
                            $giftService->writeBalanceLog($orderInfo->uid, $orderInfo->id, $orderInfo->r_wid, -$deduct_fee,UserGiftLog::EXCHANGE_SERVICE_FEE, '抵扣手续费');
                        }else{
                            $total -= $fee;
                        }//tonyang end
                        $assetService->writeBalanceLog($orderInfo->uid, $orderInfo->id, $orderInfo->r_wid, UserAsset::ACCOUNT_CURRENCY, $total,
                            UserMoneyLog::EXCHANGE, '币币交易卖出收益');

                    } else {
                        //买单结算  可能会涉及退一部分钱
                        // 手续费
                        $fee_rate = WalletCode::getExchangeFeeById($orderInfo->l_wid) * 0.01;
                        // $fee = bcMath($orderInfo->cjnum, $fee_rate, '*');
                        $fee = 0; //买单不收取手续费
                        $cjnum = bcMath($orderInfo->cjnum, $fee, '-');
                        $back_price = $orderInfo->total_price - ($orderInfo->cjprice * $orderInfo->cjnum);
                        $assetService->writeBalanceLog($orderInfo->uid, $orderInfo->id, $orderInfo->l_wid, UserAsset::ACCOUNT_CURRENCY, $cjnum,
                            UserMoneyLog::EXCHANGE, '币币买入收益');
                        if ($back_price > 0) {
                            $assetService->writeBalanceLog($orderInfo->uid, $orderInfo->id, $orderInfo->r_wid, UserAsset::ACCOUNT_CURRENCY, $back_price,
                                UserMoneyLog::EXCHANGE, '币币买入交易退还');
                        }
                    }
                } else {
                    //机器人账户
                    $fee = 0;
                }
                $orderInfo->deduct_fee = $deduct_fee;
                $orderInfo->fee = $fee;
            } else {
                $orderInfo->status = ExchangeOrder::ING_TRANS;
            }

            $orderInfo->save();
        }
    }

}
