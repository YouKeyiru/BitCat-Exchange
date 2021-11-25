<?php

namespace App\Jobs;

use App\Models\ContractPosition;
use App\Models\ContractTrans;
use App\Models\DayBackProfit;
use App\Models\ProductsContract;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\UserGiftAsset;
use App\Models\UserGiftLog;
use App\Services\GiftService;
use App\Services\AssetService;
use App\Services\CommissionService;
use App\Services\ContractTransService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClosePosition implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 平仓类型
     * @var
     */
    public $pc_type;

    /**
     * 最新价格
     * @var
     */
    public $new_price;

    /**
     * 持仓单
     * @var
     */
    public $position;

    /**
     * 平仓备注
     * @var
     */
    public $memo;

    /**
     * Create a new job instance.
     *
     * @param $queue_data
     */
    public function __construct($queue_data)
    {
        //
        $this->pc_type = $queue_data['pc_type'];
        $this->new_price = $queue_data['pc_price'];
        $this->position = $queue_data['position'];
        $this->memo = $queue_data['memo'];
    }

    /**
     * Execute the job.
     * @throws Exception
     */
    public function handle()
    {
        //
        \DB::beginTransaction();

        $order = ContractPosition::query()->lockForUpdate()->find($this->position->id);
        \Log::error(json_encode($order));
        if (!$order) {
            \Log::error(sprintf('订单[%s]不存在', $this->position->id));
            return;
        }

        $codeInfo = ProductsContract::find($this->position->pid);
        if (!$codeInfo) {
            \Log::error(sprintf('币种[%s]不存在', $this->position->id));
            return;
        }

        //手续费
        $x = number_format($codeInfo->handling_fee * 0.01,8,".","");

        $sxfee = bcMath($this->new_price * $order->buy_num, $x, '*');

        if ($order->otype == 1) { //type 1涨 2跌
            $profit = ($this->new_price - $order->buy_price) * $order->buy_num;
        } else {
            $profit = ($order->buy_price - $this->new_price) * $order->buy_num;
        }

        $back_price = bcMath($profit, $order->total_price, '+');

//        if ($this->pc_type == ContractTrans::CLOSE_BURST) {
//            $sxfee = 0;
//            $profit = -$order->total_price;
//            $back_price = 0;
//        }

        //====
        if($back_price < 0
            && $this->pc_type == ContractTrans::CLOSE_BURST
        ){
            //TODO 获取余额
            $asset = AssetService::_getBalance($order->uid,ContractTransService::WID,UserAsset::ACCOUNT_CONTRACT);
            $balance = $asset->balance;
            //余额不够扣
            if(($back_price + $balance) < 0){
                if($balance > 0){
                    //亏 > 余额 把余额 扣光
                    $back_price = $balance * (-1);
                    $profit = $balance * (-1) + $order->total_price * (-1);
                } else {
                    $back_price = 0;
                    $profit = $order->total_price * (-1);
                }
                $sxfee = 0;
            }

        }
        //======




        $deduct_fee = 0; //抵扣多少手续费
        //处理赠送的U抵扣交易手续费 tonyang
        if($sxfee>0){
            $giftRow = UserGiftAsset::where(['uid'=>$order->uid,'wid'=>ContractTransService::WID])->first();
            if($giftRow){ //存在
                if($giftRow->balance > 0){
                    if($giftRow->balance >= $sxfee){
                        $deduct_fee = $sxfee; //抵扣的手续费
                    }else{
                        $deduct_fee = $giftRow->balance; //抵扣的手续费
                    }
                }
            }
        }//tonyang end

//        \DB::beginTransaction();
        try {
            $insert_data = [
                'jy_order' => $order->order_no,
                'uid' => $order->uid,
                'pid' => $order->pid,
                'name' => $order->name,
                'code' => $order->code,
                'sheets' => $order->sheets,
                'buy_num' => $order->buy_num,
                'buy_price' => $order->buy_price,
                'total_price' => $order->total_price,
                //'price' => $order->price,
                'otype' => $order->otype,
                'stop_win' => $order->stop_win,
                'stop_loss' => $order->stop_loss,
                'sell_price' => $this->new_price,
                'profit' => $profit,
                'fee' => bcMath($order->fee, $sxfee, '+'),
                'deduct_fee' => $deduct_fee, //抵扣手续费 tonyang
                'dayfee' => $order->dayfee,
                'pc_type' => $this->pc_type,
                'leverage' => $order->leverage,
                'source' => $order->source,
                'jiancang_at' => $order->created_at,
            ];
            $create = ContractTrans::create($insert_data);
            if (!$create) {
                throw new Exception('订单创建失败');
            }

            $delete = $order->delete();
            if (!$delete) {
                throw new Exception('订单删除失败 => ' . $order->order_no);
            }

            $key = 'contract:order:positions:' . $order->code;
            ContractTransService::delCacheOrder($order->order_no,$key);

            $assetService = new AssetService();
//            if ($back_price > 0) {
                $assetService->writeBalanceLog($order->uid, $create->id, ContractTransService::WID, UserAsset::ACCOUNT_CONTRACT, $back_price,
                    UserMoneyLog::CONTRACT, $this->memo);
//            }

            //写入抵扣记录 tonyang
            if($deduct_fee > 0){
                $giftService  = new GiftService();
                $sxfee = bcMath($sxfee, $deduct_fee, '-'); //去除抵扣后的实际扣除的手续费
                $giftService->writeBalanceLog($order->uid, $create->id, ContractTransService::WID, -$deduct_fee,UserGiftLog::CONTRACT_SERVICE_FEE, '抵扣手续费');
            }//tonyang end

            if ($sxfee > 0) {
                $assetService->writeBalanceLog($order->uid, $create->id, ContractTransService::WID, UserAsset::ACCOUNT_CONTRACT, -$sxfee,
                    UserMoneyLog::CONTRACT, $this->memo . '手续费');
            }
            \DB::commit();

            $this->afterClosePosition($create);

        } catch (Exception $exception) {
            \DB::rollBack();
            \Log::error(sprintf('[%s]平仓操作异常：%', $order->order_no, $exception->getMessage()));
            //回滚。数据回填
            ContractTransService::setCacheOrder($order);
        }
    }

    /**
     * 平仓后操作
     * @param $order
     */
    public function afterClosePosition($order)
    {
//        //处理盈亏
//        CommissionService::doProfit($order);
//
//        //合约交易手续费返佣
//        CommissionService::doTransFee($order);
    }



}
