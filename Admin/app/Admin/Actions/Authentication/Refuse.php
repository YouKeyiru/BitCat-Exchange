<?php

namespace App\Admin\Actions\Authentication;

use App\User;
use Illuminate\Http\Request;
use App\Models\Authentication;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Refuse extends RowAction
{
    public $name = '拒绝';

    public function handle(Model $model, Request $request)
    {
	    //更新身份认证状态
        $model->status = Authentication::ADVANCED_CHECK_REFUSE;
        $model->checked_at = now();
        $model->refuse_reason = $request->get('reason');;
        $model->save();

        //更改会员表状态
        $user = User::find($model->uid);
        $user->authentication = Authentication::ADVANCED_CHECK_REFUSE;
        $user->save();

	    return $this->response()->success('认证已拒绝')->refresh();
    }

    public function form()
	{
	    $this->textarea('reason', '原因')->rules('required');
	}

}