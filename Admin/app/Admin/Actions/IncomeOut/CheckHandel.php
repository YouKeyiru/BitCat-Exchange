<?php

namespace App\Admin\Actions\IncomeOut;

use App\Models\IncomeOut;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Services\AssetService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CheckHandel extends RowAction
{

    public $name = '审核';

    public function handle(Model $model, Request $request)
    {
        $wid = 1;
        \DB::beginTransaction();
        try {

            $assetService = new AssetService();

            $act = $request->act;
            $account = $request->account ?? 1;
            if ($act == 1) {
                //通过
                //
                $model->status = IncomeOut::SUCCESS;
                $assetService->writeBalanceLog($model->uid, $model->id, $wid, $account, $model->amount,
                    UserMoneyLog::BUSINESS_TYPE_INCOME_OUT, '佣金提取通过');
            } else {
                //拒绝
                $model->status = IncomeOut::REFUSE;
                $assetService->writeBalanceLog($model->uid, $model->id, $wid, UserAsset::ACCOUNT_COMMISSION, $model->amount,
                    UserMoneyLog::BUSINESS_TYPE_INCOME_OUT, '佣金提取拒绝');
            }

            $model->save();

            \DB::commit();
            return $this->response()->success('操作成功')->refresh();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->response()->error($e->getMessage());
        }
    }

    public function form()
    {
        $type = [
            1 => '通过',
            2 => '拒绝',
        ];
        // Radio
        $this->radio('act', '类型')->options($type)->required();

        $account = [
            UserAsset::ACCOUNT_CONTRACT => '合约账户',
            UserAsset::ACCOUNT_LEGAL => '法币账户',
//            UserAsset::ACCOUNT_COMMISSION => '佣金账户',
        ];
        // 单选框
        $this->select('account', '币种')->options($account)->default(2);
    }
}
