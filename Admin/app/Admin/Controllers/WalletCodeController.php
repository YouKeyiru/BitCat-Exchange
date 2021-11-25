<?php

namespace App\Admin\Controllers;

use App\Models\WalletCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class WalletCodeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '资产币种';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WalletCode());
//        $grid->disableCreateButton();

//        $grid->column('id', __('Id'));
        $grid->column('icon', __('Icon'))->lightbox(['width' => 50, 'height' => 50]);
        $grid->column('code', __('Code'));
        $grid->column('withdraw_min', __('Withdraw min'))->editable();
        $grid->column('withdraw_max', __('Withdraw max'))->editable();
        $grid->column('withdraw_handling_fee', __('Withdraw handling fee'))->editable();
       $grid->column('exchange_fee', __('Exchange fee'));
    //    $grid->column('c_type', __('C type'));
       $grid->column('start_c', __('Start c'))->switch();
       $grid->column('start_t', __('Start t'))->switch();
//        $grid->column('contract_address', __('Contract address'))->editable();
//        $grid->column('coin_type', __('Coin type'));
//        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));



        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            $actions->disableView();
//            $actions->disableEdit();
        });


        // $grid->disableActions();
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
        $show = new Show(WalletCode::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('icon', __('Icon'));
        $show->field('code', __('Code'));
        $show->field('withdraw_min', __('Withdraw min'));
        $show->field('withdraw_max', __('Withdraw max'));
        $show->field('withdraw_handling_fee', __('Withdraw handling fee'));
        $show->field('exchange_fee', __('Exchange fee'));
        $show->field('c_type', __('C type'));
        $show->field('start_c', __('Start c'));
        $show->field('start_t', __('Start t'));
        $show->field('contract_address', __('Contract address'));
        $show->field('coin_type', __('Coin type'));
        $show->field('created_at', __('Created at'));
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
        $form = new Form(new WalletCode());

        $form->image('icon', __('Icon'))->removable();
        $form->text('code', __('Code'));
        $form->decimal('withdraw_min', __('Withdraw min'))->default(0.00000000);
        $form->decimal('withdraw_max', __('Withdraw max'))->default(0.00000000);
        $form->decimal('withdraw_handling_fee', __('Withdraw handling fee'))->default(0.00000000);
        $form->decimal('exchange_fee', __('Exchange fee'))->default(0.00);
//        $form->switch('c_type', __('C type'))->default(2);
        $form->switch('start_c', __('Start c'))->default(1);
        $form->switch('start_t', __('Start t'))->default(1);
        $form->text('contract_address', __('Contract address'));
        // $form->switch('coin_type', __('Coin type'));

            //毙掉一些按钮
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
                $tools->disableView();
            });
            $form->footer(function ($footer) {
                $footer->disableEditingCheck();
                $footer->disableCreatingCheck();
                $footer->disableViewCheck();
            });

        return $form;
    }
}
