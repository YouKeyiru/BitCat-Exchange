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

class Agree extends RowAction
{
    public $name = '同意';

    public function handle(Model $model, Request $request)
    {
        $wid = 1;
        if ($model->action == ShopApply::SHOP_ACTION) {
            //申请商家已同意
            $model->status = ShopApply::SHOP_APPLY_AGREE;
            $model->save();
            return $this->response()->success('申请商家已同意')->refresh();
        }

        if ($model->action == ShopApply::SHOP_ACTION_CANCEL) {
            \DB::beginTransaction();
            try {
                //撤销商家已同意
                $model->status = ShopApply::SHOP_CANCEL_AGREE;
                $model->save();

                $config = UserConfig::where('uid', $model->uid)->first();
                //保证金重置
                $config->fbshop_bond = 0;
                $config->save();

                $assetService = new AssetService();
                $assetService->writeBalanceLog($model->uid, 0, $wid, UserAsset::ACCOUNT_LEGAL, $model->money,
                    UserMoneyLog::BUSINESS_TYPE_FB_SHOP, '同意撤销商家');

                \DB::commit();
                return $this->response()->success('撤销商家已同意')->refresh();
            } catch (\Exception $exception) {

                \DB::rollBack();
                return $this->response()->error('操作失败：' . $exception->getMessage())->refresh();
            }

        }

    }

}
