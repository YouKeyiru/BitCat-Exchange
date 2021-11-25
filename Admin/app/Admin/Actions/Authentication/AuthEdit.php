<?php

namespace App\Admin\Actions\Authentication;

use Illuminate\Http\Request;
use App\Models\Authentication;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class AuthEdit extends RowAction
{
    public $name = '更新认证信息';

    public function handle(Model $model, Request $request)
    {
        $name = $request->get('name');

        if(!empty($name)){
            $model->name = $name;
            $model->save();
        }

        $card_id = $request->get('card_id');

        if(!empty($card_id)){
            $card = Authentication::where('card_id',$card_id)
            ->where('status','>=',Authentication::PRIMARY_CHECK)
            ->first();

            if(!empty($card)){
                return $this->response()->error('该身份证号已被认证');
            }
            $model->card_id = $card_id;
            $model->save();
        }

	    return $this->response()->success('已更新')->refresh();
    }

    public function form()
    {
        $this->text('name', __('User name'))->placeholder('用户姓名,不输入则不修改');
        $this->text('card_id', __('Card id'))->placeholder('用户身份证号,不输入则不修改');
    }

}