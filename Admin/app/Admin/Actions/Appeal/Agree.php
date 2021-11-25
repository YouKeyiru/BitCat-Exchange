<?php

namespace App\Admin\Actions\Appeal;

use App\Models\FbTrans;
use App\Services\FbTransService;
use App\User;
use DB;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Agree extends RowAction
{
    public $name = '取消订单';

    public function handle(Model $model, Request $request)
    {
        \DB::beginTransaction();
        try {
            $trans = FbTrans::where('order_no', $model->order_no)->first();
            $user = User::find($trans->gou_uid);
            (new FbTransService())->cancelOrder($user, $trans);

            $model->status = 2;
            $model->win_uid = $trans->gou_uid;
            $model->pan_at = now();
            $model->reason = '同意';
            $model->save();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->response()->error('更新交易订单失败' . $exception->getMessage());
        }

        return $this->response()->success('已同意')->refresh();
    }

}
