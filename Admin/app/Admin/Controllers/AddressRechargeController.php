<?php

namespace App\Admin\Controllers;

use App\Models\AddrRecharge;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AddressRechargeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '地址充值';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AddrRecharge);
        $grid->disableRowSelector();
        $grid->disableActions();
        $grid->disableCreateButton();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
                $filter->equal('wallet_address', __('Address'));
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal('hash', __('Hash'));
                $filter->between('created_at',__('Created at'))->datetime();
            });

        });

        $grid->model()->orderBy('id','desc');
//        $grid->column('id', __('Id'));
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
//        $grid->column('uid', __('Uid'));
//        $grid->column('wid', __('Wid'));
        $grid->column('code', __('Code'));
        $grid->column('address', __('Address'));
        $grid->column('hash', __('Hash'));
        $grid->column('amount', __('Amount'))->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });
//        $grid->column('status', __('Status'));
        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(AddrRecharge::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('wid', __('Wid'));
        $show->field('code', __('Code'));
        $show->field('address', __('Address'));
        $show->field('hash', __('Hash'));
        $show->field('amount', __('Amount'));
        $show->field('status', __('Status'));
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
        $form = new Form(new AddrRecharge);

        $form->number('uid', __('Uid'));
        $form->number('wid', __('Wid'));
        $form->text('code', __('Code'));
        $form->text('address', __('Address'));
        $form->text('hash', __('Hash'));
        $form->decimal('amount', __('Amount'))->default(0.00000000);
        $form->switch('status', __('Status'))->default(1);

        return $form;
    }
}
