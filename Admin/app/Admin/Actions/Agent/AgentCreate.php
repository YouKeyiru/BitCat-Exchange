<?php

namespace App\Admin\Actions\Agent;

use App\Models\AgentUser;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use DB;

class AgentCreate extends Action
{
    protected $selector = '.agent-create';

    public function handle(Request $request)
    {
        $account_type = $request->get('account_type');
        $recommend_get = $request->get('recommend');

        $recommend = AgentUser::where('username',$recommend_get)->first();
        //如果不是添加运营中心 并且没有推荐人
        if($account_type != AgentUser::ACCOUNT_CENTER && empty($recommend)){
            return $this->response()->error('推荐人不存在');
        }

        if (!empty($recommend) && $account_type <= $recommend->account_type){
            return $this->response()->error('只能推荐下级');
        }


//        if($account_type > 2 && !empty($recommend)){
//            if($account_type > ($recommend->account_type - 1)){
//                return $this->response()->error('只能推荐下级');
//            }
//            if($account_type != ($recommend->account_type - 1)){
//                return $this->response()->error('只能推荐下级');
//            }
//            if($request->get('profit_ratio') > $recommend->profit_ratio){
//                return $this->response()->error('盈利比例不能大于上级');
//            }
//        }

        if(empty($recommend) && $account_type == AgentUser::ACCOUNT_CENTER){
            $recommend_id = 0;
            $agent_id = 0;
            $unit_id = 0;
            $center_id = 0;
        } else {
            $recommend_id = $recommend->id;
            $agent_id = $recommend->agent_id;
            $unit_id = $recommend->unit_id;
            $center_id = $recommend->center_id;
            if($recommend->account_type == AgentUser::ACCOUNT_CENTER){
                $center_id = $recommend->id;
            }
            if($recommend->account_type == AgentUser::ACCOUNT_UNIT){
                $unit_id = $recommend->id;
            }
            if($recommend->account_type == AgentUser::ACCOUNT_AGENT){
                $agent_id = $recommend->id;
            }
        }

        $agent = AgentUser::create([
            'username' => $request->get('username'),
            'password' => bcrypt($request->get('password')),
            'encrypt_password' => base64_encode($request->get('password')),
            'avatar' => $request->get('avatar'),
            'name' => $request->get('name'),
            'account_type' => $account_type,
            'recommend_id' => $recommend_id,
            'agent_id' => $agent_id,
            'unit_id' => $unit_id,
            'center_id' => $center_id,
//            'profit_ratio' => $request->get('profit_ratio'),
//            'fee_ratio' => $request->get('fee_ratio'),

        ]);

//        $agent->assets()->create([
//            'uid' => $agent->id
//        ]);

        //添加角色
        //agent_role_users
//        if($account_type == 4){
//            $role_id = 2;
//        }
//        if($account_type == 3){
//            $role_id = 3;
//        }
//        if($account_type == 2){
//            $role_id = 4;
//        }
        DB::table('agent_role_users')->insert([
            'role_id' => $account_type,
            'user_id' => $agent->id,
        ]);

        if(!empty($recommend)){
            $agent->relationship = $recommend->relationship.','.$agent->id;
            $agent->save();
        }

        return $this->response()->success('新增成功')->refresh();
    }

    public function form()
    {
//        $this->hidden('avatar')->default('images/avatar.jpg');
        $this->text('username', __('Username'))->rules(['required',"regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{5,20}$/","min:6","max:16","unique:agent_users"],
            ['regex' => '用户名必须是字母+数字','min' => '用户名必须大于6位','max' => '用户名必须小于16位',]);
        $this->password('password', __('Password'))->rules('required');
        $this->text('name', __('User name'))->rules('required');
        $this->text('recommend', __('Recommend'));
        // 单选框
        $account_type = AgentUser::ACCOUNT_TYPE;
//        unset($account_type[0]);
//        unset($account_type[1]);

        $this->select('account_type', __('Account type'))->options($account_type);

//        $this->text('profit_ratio', __('Profit ratio'))
//        ->rules('required|numeric|min:0|max:100')
//        ->default(0);
//
//        $this->text('fee_ratio', __('Fee rate'))
//        ->rules('required|numeric|min:0|max:100')
//        ->default(0);
    }

    public function html()
    {
        return "<a class='agent-create btn btn-sm btn-success'><i class='fa fa-user-plus'></i>新增</a>";
    }
}
