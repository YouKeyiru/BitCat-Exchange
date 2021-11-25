<?php

namespace App\Admin\Actions\Withdraw;

use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\UserWithdrawRecord;
use App\Services\AssetService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Refuse extends RowAction
{

    public $name = '拒绝';

    public function handle(Model $model, Request $request)
    {
        $wid = $model->wid;

        \DB::beginTransaction();
        try {
            $model->status = UserWithdrawRecord::CHECK_REFUSE;
            $model->checked_at = now();
            $model->refuse_reason = $request->get('reason');
            $model->save();
            $assetService = new AssetService();
//            if ($model->handling_fee){
//                $assetService->writeBalanceLog($model->uid, $model->id, $wid, UserAsset::ACCOUNT_LEGAL, $model->handling_fee,
//                    UserMoneyLog::CASH_TANS, '提币手续费退回');
//            }
            $assetService->writeBalanceLog($model->uid, $model->id, $wid, UserAsset::ACCOUNT_CURRENCY, $model->amount,
                UserMoneyLog::CASH_TANS, '提币金额退回');
            $assetService->writeFrostLog($model->uid, $model->id, $wid, UserAsset::ACCOUNT_CURRENCY, -($model->amount),
                UserMoneyLog::CASH_TANS, '提币金额冻结退回');

            \DB::commit();
            return $this->response()->success('已拒绝')->refresh();
        } catch (\Exception $exception) {

            \DB::rollBack();
            return $this->response()->error('操作失败：' . $exception->getMessage())->refresh();
        }
    }

    public function form()
    {
        $this->textarea('reason', '原因')->rules('required');
    }

}
