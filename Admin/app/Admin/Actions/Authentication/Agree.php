<?php

namespace App\Admin\Actions\Authentication;

use App\User;
use App\Models\UserConfig;
use Illuminate\Http\Request;
use App\Models\Authentication;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Agree extends RowAction
{
    public $name = '同意';

    public function handle(Model $model, Request $request)
    {
        $model->status = Authentication::ADVANCED_CHECK_AGREE;
        $model->checked_at = now();
        $model->save();
        $user = User::find($model->uid);
        $user->name = $model->name;
        $user->authentication = Authentication::ADVANCED_CHECK_AGREE;
        $user->save();

        $config = UserConfig::where('uid',$model->uid)->first();
//        $config->security_level += 1;
        $config->save();

	    return $this->response()->success('已同意')->refresh();
    }

}
