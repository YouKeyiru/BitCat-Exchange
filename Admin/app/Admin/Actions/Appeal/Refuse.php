<?php

namespace App\Admin\Actions\Appeal;

use App\Models\FbTrans;
use App\Services\FbTransService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Refuse extends RowAction
{
    public $name = '完成订单';

    public function handle(Model $model, Request $request)
    {
        \DB::beginTransaction();
        try {
            $reason = $request->get('reason');

            $trans = FbTrans::where('order_no', $model->order_no)->first();
            if (empty($trans)) {
                throw new \Exception('查询订单失败');
            }
//            $user = User::find($trans->gou_uid);

            (new FbTransService())->confirmOrder($trans);

            //更新申诉记录的信息
            $model->status = 2;
            $model->win_uid = $trans->chu_uid;
            $model->pan_at = now();
            $model->reason = $reason;
            $model->save();

            \DB::commit();
            return $this->response()->success('完成订单')->refresh();
        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->response()->error($exception->getMessage());
        }
    }

//    public function form()
//    {
//        $this->textarea('reason', '原因')->rules('required');
//    }

}
