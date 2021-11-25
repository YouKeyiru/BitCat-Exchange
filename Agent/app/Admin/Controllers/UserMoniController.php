<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Moni\MoniRecharge;
use App\Models\AgentUser;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;

class UserMoniController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客户管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User);
        $grid->disableCreateButton();
        $admin = Admin::user();

        $grid->filter(function ($filter) use ($admin) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('account', __('Account'));
                $filter->equal('phone', __('Phone'));
                $filter->equal('recommend.account', __('Recommend'));
                $filter->between('created_at', '创建时间')->datetime();

            });
            $filter->column(1 / 2, function ($filter) use ($admin) {
                if ($admin->account_type == AgentUser::ACCOUNT_CENTER) {
                    $filter->equal('unit_id', __('Unit'))
                        ->select(AgentUser::where('account_type',3)->where('center_id', $admin->id)->pluck('username', 'id'))
                        ->load('agent_id','/api/agent');
                    $filter->equal('agent_id', __('Agent'))
                        ->select()
                        ->load('staff_id','/api/agent');
                    $filter->equal('staff_id', __('Staff'))
                        ->select();
                }

                if ($admin->account_type == AgentUser::ACCOUNT_UNIT) {
                    $filter->equal('agent_id', __('Agent'))
                        ->select(AgentUser::where('account_type',4)->where('unit_id', $admin->id)->pluck('username', 'id'))
                        ->load('staff_id','/api/agent');
                    $filter->equal('staff_id', __('Staff'))
                        ->select();
                }

                if ($admin->account_type == AgentUser::ACCOUNT_AGENT) {
                    $filter->equal('staff_id', __('Staff'))
                        ->select(AgentUser::where('account_type',5)->where('agent_id', $admin->id)->pluck('username', 'id'));
                }

            });
        });
        $grid->model()->where('is_moni', 1);
        //  2运营中心 3会员单位 4代理商 5合伙人
        $types = [
            2 => 'center_id',
            3 => 'unit_id',
            4 => 'agent_id',
            5 => 'staff_id'];
        $grid->model()->where($types[$admin->account_type], $admin->id);

        if ($admin->account_type == AgentUser::ACCOUNT_CENTER) {
            $grid->model()->where('center_id', $admin->id)->orderBy('id', 'desc');
        }
        if ($admin->account_type == AgentUser::ACCOUNT_UNIT) {
            $grid->model()->where('unit_id', $admin->id)->orderBy('id', 'desc');
        }
        if ($admin->account_type == AgentUser::ACCOUNT_AGENT) {
            $grid->model()->where('agent_id', $admin->id)->orderBy('id', 'desc');
        }
        if ($admin->account_type == AgentUser::ACCOUNT_PARTNER) {
            $grid->model()->where('staff_id', $admin->id)->orderBy('id', 'desc');
        }

//        $grid->column('id', __('Id'));
        $grid->column('account', __('Account'));

        $grid->column('Phone Email', __('Phone Email'))->display(function () {
            $phone = $this->phone ? substr_replace($this->phone,'****',3,4) : '';
            $value = $phone . '/' . $this->email;
            return trim($value,'/');
        });

        $grid->column('name', __('User name'));
//        $grid->column('nickname', __('Nickname'));

        if ($admin->account_type == AgentUser::ACCOUNT_CENTER) {
            $grid->column('staff.username', __('Staff'));
            $grid->column('agent.username', __('Agent'));
            $grid->column('unit.username', __('Unit'));
        }
        if ($admin->account_type == AgentUser::ACCOUNT_UNIT) {
            $grid->column('agent.username', __('Agent'));
            $grid->column('staff.username', __('Staff'));
        }
        if ($admin->account_type == AgentUser::ACCOUNT_AGENT) {
            $grid->column('staff.username', __('Staff'));
        }

        $grid->column('recommend.name', __('Recommend name'))->label();
        $grid->column('recommend.account', __('Recommend account'))->label();
        $grid->column('authentication', __('Authentication'))->display(function ($authentication) {
            // 认证状态0未认证1初级认证2高级认证待审核3高级认证通过4高级认证拒绝
            $auth_type = [
                0 =>  '未认证',
                1 =>  '初级认证',
                2 =>  '高级认证待审核',
                3 =>  '高级认证通过',
                4 =>  '高级认证拒绝',
            ];
            return $auth_type[$authentication];
        });
        $grid->column('created_at', __('Created at'));

        // 全部关闭
        if(in_array($admin->account_type, [2, 4])){
            $grid->actions(function ($actions) {
                // 去掉删除
                $actions->disableDelete();
                $actions->disableView();
                $actions->disableEdit();

                $actions->add(new MoniRecharge());
            });
        } else {
            $grid->disableActions();
        }

        return $grid;
    }

}
