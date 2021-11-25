<?php

namespace App\Admin\Actions\Withdraw;

use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\UserWithdrawRecord;
use App\Services\AssetService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;


class Agree extends RowAction
{
    public $name = '同意';

    public function handle(Model $model)
    {
        \DB::beginTransaction();
        try {
            $wid = $model->wid;
            $model->status = UserWithdrawRecord::CHECK_AGREE;
            $model->checked_at = now();
            $model->save();

            $asset = AssetService::_getBalance($model->uid, $wid, UserAsset::ACCOUNT_CURRENCY);;

            //累计提币
            $asset->total_withdraw += $model->amount;
            $asset->total_withdraw += $model->handling_fee;
            $asset->save();

            $assetService = new AssetService();
            $assetService->writeFrostLog($model->uid, 0, $wid, UserAsset::ACCOUNT_CURRENCY, -($model->amount),
                UserMoneyLog::CASH_TANS, '用户提币');


            \DB::commit();
            return $this->response()->success('已同意')->refresh();
        }catch (\Exception $exception){
            \DB::rollBack();
            return $this->response()->error('操作失败：' . $exception->getMessage())->refresh();
        }
    }
}
