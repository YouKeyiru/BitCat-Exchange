<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\FbTrans\RevokeSellOrder;
use App\Models\FbPay;
use App\Models\FbSell;
use App\Models\WalletCode;
use Encore\Admin\Controllers\AdminController;
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
    protected $title = '法币交易-出售';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FbSell());
        $grid->disableCreateButton();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
//                $filter->equal('user.email', __('Email'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('order_no', __('Order no'));
//                $filter->equal('wid', __('Code'))->select(WalletCode::pluck('code', 'id'));
                $filter->between('created_at', __('Created at'))->datetime();
            });
        });
        $grid->column('order_no', __('Order no'));
        $grid->column('user.account', __('Account'));
        $grid->column('Phone Email', __('Phone Email'))->display(function () {
            $value = $this->user->phone . '/' . $this->user->email;
            return trim($value, '/');
        });
        $grid->column('walletCode.code', __('Code'));
        $grid->column('trans_num', __('Trans num'));
        $grid->column('deals_num', __('Deals num'));
        $grid->column('surplus_num', __('Surplus num'));
        $grid->column('price', __('Price'));
        $grid->column('total_price', __('Total price'));
        $grid->column('sxfee', __('Sxfee'));
        $grid->column('min_price', __('Fb min price'));
        $grid->column('max_price', __('Fb max price'));
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

        $grid->column('pay_method', __('Pay method'))->display(function ($value) {
            $arr = explode(',',$value);
            $pay_arr =[];
            foreach ($arr as $v){
                array_push($pay_arr,FbPay::PAYMENT_TYPE[$v]);
            }
            return implode(',',$pay_arr);
        });
        $grid->column('1', __('Notes'))->modal(__('Content'), function ($model) {
            return $model->notes;
        });
        $grid->column('created_at', __('Created at'));
        $grid->column('cancel_at', __('Cancel at'));

//        $grid->disableActions();
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            $actions->disableView();
            $actions->disableEdit();

            if ($actions->row->status == 1) {
                $actions->add(new RevokeSellOrder());
            }
        });

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
        $show = new Show(FbSell::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('wid', __('Wid'));
        $show->field('order_no', __('Order no'));
        $show->field('trans_num', __('Trans num'));
        $show->field('deals_num', __('Deals num'));
        $show->field('surplus_num', __('Surplus num'));
        $show->field('price', __('Price'));
        $show->field('total_price', __('Total price'));
        $show->field('sxfee', __('Sxfee'));
        $show->field('min_price', __('Min price'));
        $show->field('max_price', __('Max price'));
        $show->field('status', __('Status'));
        $show->field('pay_method', __('Pay method'));
        $show->field('notes', __('Notes'));
        $show->field('created_at', __('Created at'));
        $show->field('cancel_at', __('Cancel at'));
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
        $form = new Form(new FbSell());

        $form->number('uid', __('Uid'));
        $form->number('wid', __('Wid'));
        $form->text('order_no', __('Order no'));
        $form->decimal('trans_num', __('Trans num'))->default(0.00000000);
        $form->decimal('deals_num', __('Deals num'))->default(0.00000000);
        $form->decimal('surplus_num', __('Surplus num'))->default(0.00000000);
        $form->decimal('price', __('Price'))->default(0.00);
        $form->decimal('total_price', __('Total price'))->default(0.00);
        $form->decimal('sxfee', __('Sxfee'))->default(0.00000000);
        $form->decimal('min_price', __('Min price'))->default(0.00);
        $form->decimal('max_price', __('Max price'))->default(0.00);
        $form->switch('status', __('Status'))->default(1);
        $form->text('pay_method', __('Pay method'));
        $form->textarea('notes', __('Notes'));
        $form->datetime('cancel_at', __('Cancel at'))->default(date('Y-m-d H:i:s'));
        $form->number('version', __('Version'));

        return $form;
    }
}
