<?php

namespace App\Admin\Controllers;

use App\Models\FbPay;
use App\Models\FbTrans;
use App\Models\WalletCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FbTransController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '匹配';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FbTrans());
        $grid->disableCreateButton();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('chu.account', __('Chu Account'));
                $filter->equal('gou.account', __('Gou Account'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('order_no', __('Order no'));
                $filter->equal('jy_order', __('Jy order'));

                $filter->between('created_at', __('Created at'))->datetime();
            });
        });
        $grid->column('order_no', __('Order no'));
        $grid->column('jy_order', __('Jy order'));
        $grid->column('chu.account', __('Chu Account'));
        $grid->column('gou.account', __('Gou Account'));

        $grid->column('price', __('Price'));
        $grid->column('total_num', __('Total num'));
        $grid->column('total_price', __('Total price'));
        $grid->column('sxfee', __('Sxfee'));
        $grid->column('status', __('Status'))->display(function ($value) {
            return FbTrans::TYPE_ORDER[$value];
        });
        $grid->column('min_price', __('Fb min price'));
        $grid->column('max_price', __('Fb max price'));
        $grid->column('pay_method', __('Pay method'))->display(function ($value) {
            $arr = explode(',',$value);
            $pay_arr =[];
            foreach ($arr as $v){
                array_push($pay_arr,FbPay::PAYMENT_TYPE[$v]);
            }
            return implode(',',$pay_arr);
        });
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
        $show = new Show(FbTrans::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('wid', __('Wid'));
        $show->field('order_no', __('Order no'));
        $show->field('jy_order', __('Jy order'));
        $show->field('chu_uid', __('Chu uid'));
        $show->field('gou_uid', __('Gou uid'));
        $show->field('cancel_uid', __('Cancel uid'));
        $show->field('price', __('Price'));
        $show->field('total_num', __('Total num'));
        $show->field('total_price', __('Total price'));
        $show->field('sxfee', __('Sxfee'));
        $show->field('status', __('Status'));
        $show->field('min_price', __('Min price'));
        $show->field('max_price', __('Max price'));
        $show->field('order_type', __('Order type'));
        $show->field('pay_method', __('Pay method'));
        $show->field('refer', __('Refer'));
        $show->field('created_at', __('Created at'));
        $show->field('pay_at', __('Pay at'));
        $show->field('checked_at', __('Checked at'));
        $show->field('cancel_at', __('Cancel at'));
        $show->field('freeze_at', __('Freeze at'));
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
        $form = new Form(new FbTrans());

        $form->number('wid', __('Wid'));
        $form->text('order_no', __('Order no'));
        $form->text('jy_order', __('Jy order'));
        $form->number('chu_uid', __('Chu uid'));
        $form->number('gou_uid', __('Gou uid'));
        $form->number('cancel_uid', __('Cancel uid'));
        $form->decimal('price', __('Price'))->default(0.00);
        $form->decimal('total_num', __('Total num'))->default(0.00000000);
        $form->decimal('total_price', __('Total price'))->default(0.00);
        $form->decimal('sxfee', __('Sxfee'))->default(0.000000);
        $form->switch('status', __('Status'))->default(1);
        $form->decimal('min_price', __('Min price'))->default(0.00);
        $form->decimal('max_price', __('Max price'))->default(0.00);
        $form->switch('order_type', __('Order type'));
        $form->text('pay_method', __('Pay method'));
        $form->number('refer', __('Refer'));
        $form->datetime('pay_at', __('Pay at'))->default(date('Y-m-d H:i:s'));
        $form->datetime('checked_at', __('Checked at'))->default(date('Y-m-d H:i:s'));
        $form->datetime('cancel_at', __('Cancel at'))->default(date('Y-m-d H:i:s'));
        $form->datetime('freeze_at', __('Freeze at'))->default(date('Y-m-d H:i:s'));
        $form->number('version', __('Version'));

        return $form;
    }
}
