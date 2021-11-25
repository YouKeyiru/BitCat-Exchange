<?php

namespace App\Admin\Controllers;

use App\Models\Transfer;
use App\Models\UserAsset;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TransferController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '划转记录';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Transfer());
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
//                $filter->equal('user.email', __('Email'));
            });
            $filter->column(1 / 3, function ($filter) {
//                $filter->equal('account', __('账户'))->select(UserAsset::ACCOUNT_TYPE);
                $filter->between('created_at', __('Created at'))->datetime();
            });
        });

        $grid->column('id', __('Id'));
        $grid->column('user.account', __('Account'));
        $grid->column('Phone Email', __('Phone Email'))->display(function () {
            $value = $this->user->phone . '/' . $this->user->email;
            return trim($value,'/');
        });
//        $grid->column('uid', __('Uid'));
//        $grid->column('wid', __('Wid'));
        $grid->column('walletCode.code', __('Code'));

        $grid->column('from_account', '划出账户')->using(UserAsset::ACCOUNT_TYPE);
        $grid->column('to_account', '划入账户')->using(UserAsset::ACCOUNT_TYPE);

        $grid->column('amount', '划转数量');
        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));
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
        $show = new Show(Transfer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('wid', __('Wid'));
        $show->field('from_account', __('From account'));
        $show->field('to_account', __('To account'));
        $show->field('amount', __('Amount'));
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
        $form = new Form(new Transfer());

        $form->number('uid', __('Uid'));
        $form->number('wid', __('Wid'));
        $form->switch('from_account', __('From account'));
        $form->switch('to_account', __('To account'));
        $form->decimal('amount', __('Amount'))->default(0.0000);

        return $form;
    }
}
