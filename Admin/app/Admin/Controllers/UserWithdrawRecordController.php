<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Withdraw\Agree;
use App\Admin\Actions\Withdraw\Refuse;
use App\Models\UserWithdrawRecord;
use App\Models\WalletCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserWithdrawRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '提币记录';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserWithdrawRecord());
        $grid->disableCreateButton();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('address', __('Address'));
                $filter->equal('code', __('Code'))->select(WalletCode::query()->where('start_t', 1)->pluck('code', 'id'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('status', __('Status'))->select(UserWithdrawRecord::STATUS);
            });
        });
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
//        $grid->column('wid', __('Wid'));
        $grid->column('code', __('Code'));
//        $grid->column('account', __('Account'));
        $grid->column('order_no', __('Order no'));
        $grid->column('address', __('Address'));
        $grid->column('amount', __('Amount'))->sortable()->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });
        $grid->column('handling_fee', __('Handling fee'))->sortable()->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });
        $grid->column('actual', __('Actual'))->sortable()->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });
        $grid->column('status', __('Status'))->display(function ($value) {
            return UserWithdrawRecord::STATUS[$value];
        });


        $grid->column('mark', __('Mark'));
        $grid->column('refuse_reason', __('Refuse reason'));
        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));
        $grid->column('checked_at', __('Checked at'));
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            $actions->disableView();
            $actions->disableEdit();

            if ($actions->row->status == 1) {
                $actions->add(new Agree);
                $actions->add(new Refuse);
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
        $show = new Show(UserWithdrawRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('wid', __('Wid'));
        $show->field('code', __('Code'));
        $show->field('account', __('Account'));
        $show->field('order_no', __('Order no'));
        $show->field('address', __('Address'));
        $show->field('amount', __('Amount'));
        $show->field('handling_fee', __('Handling fee'));
        $show->field('actual', __('Actual'));
        $show->field('status', __('Status'));
        $show->field('mark', __('Mark'));
        $show->field('refuse_reason', __('Refuse reason'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('checked_at', __('Checked at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserWithdrawRecord());

        $form->number('uid', __('Uid'));
        $form->number('wid', __('Wid'));
        $form->text('code', __('Code'));
        $form->switch('account', __('Account'))->default(1);
        $form->text('order_no', __('Order no'));
        $form->text('address', __('Address'));
        $form->decimal('amount', __('Amount'))->default(0.00000000);
        $form->decimal('handling_fee', __('Handling fee'))->default(0.00000000);
        $form->decimal('actual', __('Actual'))->default(0.00000000);
        $form->switch('status', __('Status'))->default(1);
        $form->text('mark', __('Mark'));
        $form->text('refuse_reason', __('Refuse reason'));
        $form->datetime('checked_at', __('Checked at'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
