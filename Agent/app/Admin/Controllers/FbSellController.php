<?php

namespace App\Admin\Controllers;

use App\Models\FbSell;
use App\Models\AgentUser;
use App\Admin\Extensions\FbSellExporter;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FbSellController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '法币出售';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FbSell);

        $grid->fixColumns(3, -3);

        $admin = Admin::user();

        $grid->disableExport(false);
        $grid->disableCreateButton();

        $grid->filter(function ($filter) use ($admin) {
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) use ($admin) {
                if($admin->account_type == 4){
                    $filter->equal('user.unit_id', __('Unit'))
                        ->select(AgentUser::where('account_type',3)->pluck('username', 'id'))
                        ->load('user.agent_id','/api/agent');
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select()
                        ->load('user.staff_id','/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();

                }

                if($admin->account_type == 3){
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select(AgentUser::where('account_type',2)->pluck('username', 'id'))
                        ->load('user.staff_id','/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }

                if($admin->account_type == 2){
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select(AgentUser::where('id',$admin->id)->pluck('username', 'id'))
                        ->load('user.staff_id','/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }
            });

            $filter->column(1/2, function ($filter) {
                $filter->equal('order_no', __('Ordnum'));
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
                $filter->between('created_at','创建时间')->datetime();
            });
        });

        //  2运营中心 3会员单位 4代理商 5合伙人
        $types = [
            2 => 'center_id',
            3 => 'unit_id',
            4 => 'agent_id',
            5 => 'staff_id'];
        $grid->model()->whereHas('user', function ($query) use ($admin, $types) {
            $query->where($types[$admin->account_type], $admin->id);
        });

        $grid->model()->orderBy('id','desc');

//        $grid->column('id', __('Id'));
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
        $grid->column('order_no', __('Ordnum'));
        $grid->column('trans_num', __('Trans num'));
        $grid->column('deals_num', __('Deals num'));
        $grid->column('price', __('Price'));
        $grid->column('totalprice', __('Totalprice'));
        $grid->column('sxfee', __('Fee'));
        $grid->column('min_price', __('Min order'));
        $grid->column('max_price', __('Max order'));
        $grid->column('pay_bank', __('Bank'))->bool();
        $grid->column('pay_alipay', __('Alipay'))->bool();
        $grid->column('pay_wx', __('Wx'))->bool();
        $grid->column('notes', __('Mark'));
        $grid->column('cancel_at', __('Cancel at'));
        //1 进行中 2完成 3撤单
        $grid->column('status', __('Status'))->using([
            1 => '进行中',
            2 => '完成',
            3 => '撤单',
        ], '未知')->label([
            1 => 'info ',
            2 => 'success',
            3 => 'danger',
        ], 'warning');
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));


        // 全部关闭
        $grid->disableActions();

        return $grid;
    }


}
