<?php

namespace App\Admin\Actions\FbTrans;

use App\Services\FbTransService;
use App\User;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RevokeBuyOrder extends RowAction
{
    public $name = '撤单';

    public function handle(Model $model)
    {
        $fbTransService = new FbTransService();
        $user = User::find($model->uid);

        DB::beginTransaction();
        try {
            $fbTransService->revokeOrder($user, ['order_type' => 2, 'order_no' => $model->order_no]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->response()->error($exception->getMessage());
        }

        DB::commit();
        return $this->response()->success('Success message.')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定撤单？');
    }

}
