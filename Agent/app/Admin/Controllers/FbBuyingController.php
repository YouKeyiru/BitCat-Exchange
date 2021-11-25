<?php

namespace App\Admin\Controllers;

use App\Models\FbBuying;
use App\Models\AgentUser;
use App\Admin\Extensions\FbBuyingExporter;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class FbBuyingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '法币购买';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FbBuying);

        $grid->fixColumns(3, -3);

        $grid->disableExport(false);
        $grid->disableCreateButton();


        $admin = Admin::user();

        $grid->filter(function ($filter) use ($admin) {
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) use ($admin) {
                if($admin->account_type == AgentUser::ACCOUNT_CENTER){
                    $filter->equal('user.unit_id', __('Unit'))
                        ->select(AgentUser::where('account_type',AgentUser::ACCOUNT_UNIT)->pluck('username', 'id'))
                        ->load('user.agent_id','/api/agent');
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select()
                        ->load('user.staff_id','/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();

                }

                if($admin->account_type == AgentUser::ACCOUNT_UNIT){
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select(AgentUser::where('account_type',AgentUser::ACCOUNT_AGENT)->pluck('username', 'id'))
                        ->load('user.staff_id','/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }

                if($admin->account_type == AgentUser::ACCOUNT_AGENT){
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

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(FbBuying::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('order_no', __('Order no'));
        $show->field('trans_num', __('Trans num'));
        $show->field('deals_num', __('Deals num'));
        $show->field('price', __('Price'));
        $show->field('totalprice', __('Totalprice'));
        $show->field('sxfee', __('Sxfee'));
        $show->field('min_price', __('Min price'));
        $show->field('max_price', __('Max price'));
        $show->field('pay_bank', __('Pay bank'));
        $show->field('pay_alipay', __('Pay alipay'));
        $show->field('pay_wx', __('Pay wx'));
        $show->field('status', __('Status'));
        $show->field('notes', __('Notes'));
        $show->field('created_at', __('Created at'));
        $show->field('cancel_at', __('Cancel at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FbBuying);

        $form->number('uid', __('Uid'));
        $form->text('order_no', __('Order no'));
        $form->decimal('trans_num', __('Trans num'))->default(0.00000000);
        $form->decimal('deals_num', __('Deals num'))->default(0.00000000);
        $form->decimal('price', __('Price'))->default(0.00);
        $form->decimal('totalprice', __('Totalprice'))->default(0.00);
        $form->decimal('sxfee', __('Sxfee'))->default(0.00000000);
        $form->decimal('min_price', __('Min price'))->default(0.00);
        $form->decimal('max_price', __('Max price'))->default(0.00);
        $form->switch('pay_bank', __('Pay bank'));
        $form->switch('pay_alipay', __('Pay alipay'));
        $form->switch('pay_wx', __('Pay wx'));
        $form->switch('status', __('Status'))->default(1);
        $form->textarea('notes', __('Notes'));
        $form->datetime('cancel_at', __('Cancel at'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
