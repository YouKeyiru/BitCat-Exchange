<?php

namespace App\Admin\Controllers;

use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\WalletCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserAssetController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '资产';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserAsset());
        $grid->disableCreateButton();
        $grid->disableActions();
//        $grid->column('id', __('Id'));
//        $grid->column('uid', __('Uid'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
//                $filter->equal('user.email', __('Email'));

            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('account', __('Asset account'))->select(UserAsset::ACCOUNT_TYPE);
                $filter->equal('wid', __('Code'))->select(WalletCode::pluck('code','id'));
                $filter->between('updated_at', __('Updated at'))->datetime();
            });
        });


        $grid->column('user.account', __('Account'));

        $grid->column('Phone Email', __('Phone Email'))->display(function () {
            $value = $this->user->phone . '/' . $this->user->email;
            return trim($value,'/');
        });

        $grid->column('walletCode.code', __('Code'));
        $grid->column('account', __('Asset account'))->display(function ($value) {
            return UserAsset::ACCOUNT_TYPE[$value];
        });
        $grid->column('balance', __('Balance'))->sortable();
        $grid->column('frost', __('Frost'))->sortable();
//        $grid->column('total_recharge', __('Total recharge'))->sortable();
//        $grid->column('total_withdraw', __('Total withdraw'))->sortable();
//        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
//        $grid->column('version', __('Version'));

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
        $show = new Show(UserAsset::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('wid', __('Wid'));
        $show->field('account', __('Account'));
        $show->field('balance', __('Balance'));
        $show->field('frost', __('Frost'));
        $show->field('total_recharge', __('Total recharge'));
        $show->field('total_withdraw', __('Total withdraw'));
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
        $form = new Form(new UserAsset());

        $form->number('uid', __('Uid'));
        $form->number('wid', __('Wid'))->default(1);
        $form->switch('account', __('Account'))->default(1);
        $form->decimal('balance', __('Balance'))->default(0.00000000);
        $form->decimal('frost', __('Frost'))->default(0.00000000);
        $form->decimal('total_recharge', __('Total recharge'))->default(0.00000000);
        $form->decimal('total_withdraw', __('Total withdraw'))->default(0.00000000);
        $form->number('version', __('Version'))->default(1);

        return $form;
    }
}
