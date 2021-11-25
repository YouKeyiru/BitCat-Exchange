<?php

namespace App\Admin\Actions\Agent;

use App\Models\AgentUser;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use DB;

class AgentCreate extends Action
{
    protected $selector = '.agent-create';

    public function handle(Request $request)
    {
        $admin = Admin::user();
        $account_type = $request->get('account_type');

        if($admin->account_type < 2){
            return $this->response()->error('权限不足');
        }

        $recommend_id = $admin->id;
        $agent_id = $admin->agent_id;
        $unit_id = $admin->unit_id;
        $center_id = $admin->center_id;
        if($admin->account_type == 4){
            $agent_id = $admin->id;
        }
        if($admin->account_type == 3){
            $unit_id = $admin->id;
        }
        if($admin->account_type == 2){
            $center_id = $admin->id;
        }
        $account_type = $admin->account_type + 1;


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
        ]);

//        if($account_type == 4){
//            $role_id = 2;
//        }
//        if($account_type == 3){
//            $role_id = 3;
//        }
//        if($account_type == 2){
//            $role_id = 4;
//        }
//        if($account_type == 1){
//            $role_id = 5;
//        }

        //添加角色
        //agent_role_users
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
        $admin = Admin::user();
        $this->hidden('avatar')->default('');
        $this->text('username', __('Username'))->rules(['required',"regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{5,20}$/","min:6","max:16","unique:agent_users"],
            ['regex' => '用户名必须是字母+数字','min' => '用户名必须大于6位','max' => '用户名必须小于16位',]);
        $this->password('password', __('Password'))->rules('required');
        $this->text('name', __('User name'))->rules('required');
        
    }

    public function html()
    {
        return "<a class='agent-create btn btn-sm btn-success'><i class='fa fa-user-plus'></i>新增</a>";
    }
}