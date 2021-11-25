<?php

namespace App\Admin\Actions\ShopApply;

use App\Models\FbShopApply as ShopApply;
use App\Models\UserAsset;
use App\Models\UserConfig;
use App\Models\UserMoneyLog;
use App\Services\AssetService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Refuse extends RowAction
{
    public $name = '拒绝';

    public function handle(Model $model, Request $request)
    {
        $wid = 1;

        if ($model->action == ShopApply::SHOP_ACTION) {
            DB::beginTransaction();
            try {
                //申请商家拒绝 退钱
                $config = UserConfig::where('uid', $model->uid)->first();
                //保证金重置
                $config->fbshop_bond = 0;
                $config->save();

                //状态更新
                $model->status = ShopApply::SHOP_APPLY_REFUSE;
                $model->save();

                $assetService = new AssetService();
                $assetService->writeBalanceLog($model->uid, 0, $wid, UserAsset::ACCOUNT_LEGAL, $model->money,
                    UserMoneyLog::BUSINESS_TYPE_FB_SHOP, '拒绝成为商家');

                DB::commit();
                return $this->response()->success('申请商家已拒绝')->refresh();
            } catch (\Exception $exception) {
                DB::rollBack();
                return $this->response()->error('操作失败：' . $exception->getMessage())->refresh();
            }

        }

        if ($model->action == ShopApply::SHOP_ACTION_CANCEL) {
            //撤销商家拒绝
            $model->status = ShopApply::SHOP_CANCEL_REFUSE;
            $model->save();
            return $this->response()->success('取消商家已拒绝')->refresh();
        }


    }

}
