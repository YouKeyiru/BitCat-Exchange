<?php

namespace App\Admin\Controllers;

use App\Models\AgentUser;
use App\Models\ExchangeOrder;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ExchangeOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '币币交易';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ExchangeOrder());
        $grid->disableCreateButton();

        $admin = Admin::user();
        $grid->filter(function ($filter) use ($admin) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) use ($admin) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
                $filter->between('created_at', '创建时间')->datetime();

            });
            $filter->column(1 / 2, function ($filter) use ($admin) {
                if ($admin->account_type == 4) {
                    $filter->equal('user.unit_id', __('Unit'))
                        ->select(AgentUser::where('account_type', 3)->pluck('username', 'id'))
                        ->load('user.agent_id', '/api/agent');
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select()
                        ->load('user.staff_id', '/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();

                }

                if ($admin->account_type == 3) {
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select(AgentUser::where('account_type', 2)->pluck('username', 'id'))
                        ->load('user.staff_id', '/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }

                if ($admin->account_type == 2) {
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select(AgentUser::where('id', $admin->id)->pluck('username', 'id'))
                        ->load('user.staff_id', '/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }
            });
        });

        $types = [
            2 => 'center_id',
            3 => 'unit_id',
            4 => 'agent_id',
            5 => 'staff_id'];
        $grid->model()->whereHas('user', function ($query) use ($admin, $types) {
            $query->where($types[$admin->account_type], $admin->id);
        });

        $grid->column('order_no', __('Order no'));
        $grid->column('user.account', __('Account'));
//        $grid->column('Phone Email', __('Phone Email'))->display(function () {
//            $value = $this->user->phone . '/' . $this->user->email;
//            return trim($value, '/');
//        });
        $grid->column('symbol', __('Symbol'));
        $grid->column('wtprice', __('Wtprice'));
//        $grid->column('wtprice1', __('Wtprice1'));
        $grid->column('wtnum', __('Wtnum'));
        $grid->column('total_price', __('Total price'));
        $grid->column('cjprice', __('Cjprice'));
        $grid->column('cjnum', __('Cjnum'));
        $grid->column('fee', __('Fee'));
        $grid->column('type', __('Type'))->using([
            1 => '买入',
            2 => '卖出',
        ], '未知')->label([
            1 => 'warning',
            2 => 'success',
        ], 'warning');
        //1限价 2市价
        // 设置颜色，默认`success`,可选`danger`、`warning`、`info`、`primary`、`default`、`success`
        $grid->column('otype', __('Otype'))->using([
            1 => '限价',
            2 => '市价',
        ], '未知')->label([
            1 => 'warning',
            2 => 'success',
        ], 'warning');
        //0待交易 1交易中 2交易完成(数据分离到记录表) 3撤单
        $grid->column('status', __('Status'))->using(ExchangeOrder::TYPE_STATUS, '未知');
        $grid->column('created_at', __('Created at'));
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
        $show = new Show(ExchangeOrder::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_no', __('Order no'));
        $show->field('uid', __('Uid'));
        $show->field('pid', __('Pid'));
        $show->field('code', __('Code'));
        $show->field('symbol', __('Symbol'));
        $show->field('wtprice', __('Wtprice'));
        $show->field('wtprice1', __('Wtprice1'));
        $show->field('wtnum', __('Wtnum'));
        $show->field('total_price', __('Total price'));
        $show->field('cjprice', __('Cjprice'));
        $show->field('cjnum', __('Cjnum'));
        $show->field('fee', __('Fee'));
        $show->field('type', __('Type'));
        $show->field('otype', __('Otype'));
        $show->field('status', __('Status'));
        $show->field('l_code', __('L code'));
        $show->field('r_code', __('R code'));
        $show->field('l_wid', __('L wid'));
        $show->field('r_wid', __('R wid'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('version', __('Version'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ExchangeOrder());

        $form->text('order_no', __('Order no'));
        $form->number('uid', __('Uid'));
        $form->number('pid', __('Pid'));
        $form->text('code', __('Code'));
        $form->text('symbol', __('Symbol'));
        $form->text('wtprice', __('Wtprice'));
        $form->decimal('wtprice1', __('Wtprice1'))->default(0.00000000);
        $form->decimal('wtnum', __('Wtnum'))->default(0.00000000);
        $form->decimal('total_price', __('Total price'))->default(0.00000000);
        $form->decimal('cjprice', __('Cjprice'))->default(0.00000000);
        $form->decimal('cjnum', __('Cjnum'))->default(0.00000000);
        $form->decimal('fee', __('Fee'))->default(0.00000000);
        $form->switch('type', __('Type'));
        $form->switch('otype', __('Otype'));
        $form->switch('status', __('Status'));
        $form->text('l_code', __('L code'));
        $form->text('r_code', __('R code'));
        $form->number('l_wid', __('L wid'));
        $form->number('r_wid', __('R wid'));
        $form->number('version', __('Version'));

        return $form;
    }
}
