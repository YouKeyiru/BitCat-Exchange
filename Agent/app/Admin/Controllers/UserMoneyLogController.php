<?php

namespace App\Admin\Controllers;

use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\WalletCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
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
        $grid = new Grid(new UserMoneyLog);
        $grid->disableCreateButton();
        $admin = Admin::user();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
                $filter->equal('user.email', __('Email'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('wid', __('Code'))->select(WalletCode::pluck('code', 'id'));
                $filter->equal('type', __('Type'))->select(UserMoneyLog::BUSINESS_TYPE);
//                $filter->equal('wt', '余额类型')->select([
//                    1=>'可用余额',
//                    2=>'冻结余额',
//                ]);
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->between('created_at', __('Created at'))->datetime();
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
        })->orderBy('id', 'desc');

//        $grid->column('id', __('Id'));
        $grid->column('user.account', __('User account'));

        $grid->column('Phone Email', __('Phone Email'))->display(function () {
            $phone = $this->user->phone ? substr_replace($this->user->phone, '****', 3, 4) : '';
            $value = $phone . '/' . $this->user->email;
            return trim($value, '/');
        });

        $grid->column('wallet.code', __('Code'));
        $grid->column('account', __('Account'))->display(function ($value) {
            return UserAsset::ACCOUNT_TYPE[$value];
        });
//        $grid->column('target_id', __('Target id'));
        $grid->column('ymoney', __('Ymoney'));
        $grid->column('money', __('Money'));
        $grid->column('nmoney', __('Nmoney'));
        $grid->column('type', __('Type'))->display(function ($value) {
            return UserMoneyLog::BUSINESS_TYPE[$value];
        });
        $grid->column('mark', __('Mark'));
        $grid->column('created_at', __('Created at'));
//        $grid->column('wt', __('Wt'))->display(function ($status) {
//            if ($status == 1)
//                $status = '<span class="label label-info">可用余额</span>';
//            if ($status == 2)
//                $status = '<span class="label label-warning">冻结余额</span>';
//            return $status;
//        });
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
