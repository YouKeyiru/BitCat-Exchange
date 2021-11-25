<?php

namespace App\Admin\Controllers;

use App\Models\AgentUser;
use App\Models\FeeReturn;
use App\Models\UserAsset;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;

class FeeReturnController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '手续费返佣';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FeeReturn());
        $grid->disableCreateButton();
        $admin = Admin::user();
        $grid->filter(function ($filter) use ($admin) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('from.account', __('From account'));
                $filter->equal('to.account', __('To account'));
                $filter->between('created_at', __('Created at'))->datetime();
            });
            $filter->column(1 / 2, function ($filter) use ($admin) {
                if ($admin->account_type == AgentUser::ACCOUNT_CENTER) {
                    $filter->equal('user.unit_id', __('Unit'))
                        ->select(AgentUser::where('account_type',3)->where('center_id', $admin->id)->pluck('username', 'id'))
                        ->load('user.agent_id','/api/agent');
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select()
                        ->load('user.staff_id','/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }

                if ($admin->account_type == AgentUser::ACCOUNT_UNIT) {
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select(AgentUser::where('account_type',4)->where('unit_id', $admin->id)->pluck('username', 'id'))
                        ->load('user.staff_id','/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }

                if ($admin->account_type == AgentUser::ACCOUNT_AGENT) {
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select(AgentUser::where('account_type',5)->where('agent_id', $admin->id)->pluck('username', 'id'));
                }
            });
        });

        //  2运营中心 3会员单位 4代理商 5合伙人
        $types = [
            2 => 'center_id',
            3 => 'unit_id',
            4 => 'agent_id',
            5 => 'staff_id'];
        $grid->model()->whereHas('user',function ($query) use ($admin,$types)
        {
            $query->where($types[$admin->account_type],$admin->id);
        });

        $grid->column('from.account', __('From account'));
        $grid->column('to.account', __('To account'));
        $grid->column('sxfee', __('Sxfee'))->totalRow(function ($sxfee) {
            return "<span class='text-danger text-bold'>{$sxfee} </span>";
        });
        $grid->column('ratio', __('Ratio'));
        $grid->column('return_amount', __('Commission'))->totalRow(function ($amount) {
            return "<span class='text-danger text-bold'>{$amount} </span>";
        });
        $grid->column('account', __('Account type'))->display(function ($value) {
            return UserAsset::ACCOUNT_TYPE[$value];
        });
        $grid->column('created_at', __('Created at'));
        $grid->disableActions();

        return $grid;
    }
}
