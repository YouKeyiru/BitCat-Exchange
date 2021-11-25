<?php

namespace App\Jobs;

use App\Models\FbBuying;
use App\Models\FbSell;
use App\Models\FbTrans;
use App\Models\User;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Services\AssetService;
use App\Services\FbTransService;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class AutoConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    public $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order, $type)
    {
        $this->order = $order;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $trans = FbTrans::find($this->order->id);
            //如果订单不是待付款状态直接return

            //1自动确认 2自动取消
            if ($this->type == 1) {
                if ($trans->status != FbTrans::ORDER_PAID) {
                    Log::info('AutoConfirmation status not 2 id' . $trans->id . ' status ' . $trans->status);
                    return;
                }
                $this->confirm($trans);
            }

            if ($this->type == 2) {
                if ($trans->status != FbTrans::ORDER_PENDING) {
                    Log::info('AutoConfirmation status not 1 ' . $trans->id . ' status ' . $trans->status);
                    return;
                }
                $this->cancel($trans);
            }

        } catch (Exception $exception) {
            Log::info('AutoConfirmation catch Exception' . $exception->getMessage() . $exception->getLine());
        }

    }

    public function confirm($trans)
    {
        DB::beginTransaction();

        try {
            $FbTransService = new FbTransService();

            $FbTransService->confirmOrder($trans);

//            //购买人加余额
//            $assetService->writeBalanceLog($trans->gou_uid, $trans->id, $trans->wid, UserAsset::ACCOUNT_LEGAL, $trans->total_num,
//                UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '系统自动确认-增加余额');
//
//            $assetService->writeFrostLog($trans->chu_uid, $trans->id, $trans->wid, UserAsset::ACCOUNT_LEGAL, -$trans->total_num,
//                UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '系统自动确认-扣除冻结');
//
//            //更新订单状态为已完成
//
//            $trans->status = 3;
//            $trans->save();

            DB::commit();
            return;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info('AutoConfirmation confirm rollBack' . $exception->getMessage() . $exception->getLine());
            return;
        }
    }

    public function cancel($trans)
    {
        try {
            $FbTransService = new FbTransService();

            $chu = User::find($trans->chu_uid);
            $FbTransService->cancelOrder($chu,$trans);

            DB::commit();
            return;
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info('AutoConfirmation cancel rollBack' . $exception->getMessage() . $exception->getLine());
            return;
        }



//        if ($trans->order_type == 1) {
//            $Fbquery = new FbSell();
//        } else {
//            $Fbquery = new FbBuying();
//        }
//
//        DB::beginTransaction();
//        $assetService = new AssetService();
//
//        try {
//            $Fbquery->where('order_no', $trans->jy_order)
//                ->decrement('deals_num', $trans->total_num);
//
//            $Fbquery->where('order_no', $trans->jy_order)
//                ->increment('sxfee', $trans->sxfee);
//
//            $Fbquery->where('order_no', $trans->jy_order)
//                ->increment('surplus_num', $trans->total_num);
//
//            if ($trans->order_type == 2) {
//                $amount = bcMath($trans->total_num, $trans->sxfee, '+');
//                $assetService->writeBalanceLog($trans->chu_uid, $trans->id, $trans->wid, UserAsset::ACCOUNT_LEGAL, $amount,
//                    UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '系统自动取消-增加余额');
//                $assetService->writeFrostLog($trans->chu_uid, $trans->id, $trans->wid, UserAsset::ACCOUNT_LEGAL, -$trans->total_num,
//                    UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '系统自动取消-减少冻结');
//            }
//
//            $trans->status = FbTrans::ORDER_CANCEL;
//            $trans->cancel_uid = $trans->chu_uid;
//            $trans->cancel_at = now();
//            $trans->save();
//
//            DB::commit();
//            return;
//        } catch (\Exception $exception) {
//            DB::rollBack();
//            Log::info('AutoConfirmation cancel rollBack' . $exception->getMessage() . $exception->getLine());
//            return;
//        }


    }

}
