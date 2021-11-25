<?php

namespace App\Admin\Controllers;

use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\WalletCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserMoneyLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '资金流水';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserMoneyLog());
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
//                $filter->equal('user.email', __('Email'));
            });
            $filter->column(1 / 3, function ($filter) {
//                $filter->equal('wid', __('Code'))->select(WalletCode::pluck('code','id'));
                $filter->equal('type', __('Type'))->select(UserMoneyLog::BUSINESS_TYPE);
                $filter->equal('wt', '余额类型')->select([
                    1=>'可用余额',
                    2=>'冻结余额',
                ]);
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('account', __('账户'))->select(UserAsset::ACCOUNT_TYPE);
                $filter->between('created_at', __('Created at'))->datetime();
            });
        });

        $grid->column('id', __('Id'));
        $grid->column('user.account', __('Account'));

        $grid->column('Phone Email', __('Phone Email'))->display(function () {
            $value = $this->user->phone . '/' . $this->user->email;
            return trim($value,'/');
        });

        $grid->column('wallet.code', __('Code'));
//        $grid->column('account', __('Account'))->display(function ($value) {
//            return UserAsset::ACCOUNT_TYPE[$value];
//        });
        $grid->column('account', __('Account'))->using(UserAsset::ACCOUNT_TYPE);
//        $grid->column('target_id', __('Target id'));
        $grid->column('ymoney', __('Ymoney'));
        $grid->column('money', __('Op money'));
        $grid->column('nmoney', __('Nmoney'));
        $grid->column('type', __('Type'))->display(function ($value) {

            return array_key_exists($value,UserMoneyLog::BUSINESS_TYPE) ? UserMoneyLog::BUSINESS_TYPE[$value] : '未定义类型'.$value;
        });
        $grid->column('mark', __('Mark'));
        $grid->column('created_at', __('Created at'));
        $grid->column('wt', __('Wt'))->display(function ($status) {
            if ($status == 1)
                $status = '<span class="label label-info">可用余额</span>';
            if ($status == 2)
                $status = '<span class="label label-warning">冻结余额</span>';
            return $status;
        });
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
        $show = new Show(UserMoneyLog::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('wid', __('Wid'));
        $show->field('account', __('Account'));
        $show->field('target_id', __('Target id'));
        $show->field('ymoney', __('Ymoney'));
        $show->field('money', __('Money'));
        $show->field('nmoney', __('Nmoney'));
        $show->field('type', __('Type'));
        $show->field('mark', __('Mark'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('wt', __('Wt'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserMoneyLog());

        $form->number('uid', __('Uid'));
        $form->number('wid', __('Wid'));
        $form->switch('account', __('Account'))->default(1);
        $form->number('target_id', __('Target id'));
        $form->decimal('ymoney', __('Ymoney'))->default(0.00000000);
        $form->decimal('money', __('Money'))->default(0.00000000);
        $form->decimal('nmoney', __('Nmoney'))->default(0.00000000);
        $form->number('type', __('Type'))->default(1);
        $form->text('mark', __('Mark'));
        $form->switch('wt', __('Wt'))->default(1);

        return $form;
    }
}
