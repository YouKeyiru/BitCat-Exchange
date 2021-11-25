<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Withdraw\Agree;
use App\Admin\Actions\Withdraw\Refuse;
use App\Models\AgentUser;
use App\Models\UserWithdrawRecord;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
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
    protected $title = '用户提币记录';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserWithdrawRecord());
        $grid->disableCreateButton();
        $admin = Admin::user();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
                $filter->between('created_at',__('Created at'))->datetime();

            });
            $filter->column(1/3, function ($filter) {
                $filter->between('money',__('Money'));
                $filter->equal('status', __('Status'))->select(config('system.withdraw_status'));
            });
            $filter->column(1/3, function ($filter) {
                $filter->equal('user.center_id', __('Center'))
                    ->select(AgentUser::where('account_type',4)->pluck('username', 'id'))
                    ->load('user.unit_id','/api/agent');
                $filter->equal('user.unit_id', __('Unit'))
                    ->select()
                    ->load('user.agent_id','/api/agent');
                $filter->equal('user.agent_id', __('Agent'))
                    ->select()
                    ->load('user.staff_id','/api/agent');
                $filter->equal('user.staff_id', __('Staff'))
                    ->select();
            });
        });

        $grid->model()->orderBy('id','desc');
        //  2运营中心 3会员单位 4代理商 5合伙人
        $types = [
            2 => 'center_id',
            3 => 'unit_id',
            4 => 'agent_id',
            5 => 'staff_id'];
        $grid->model()->whereHas('user', function ($query) use ($admin, $types) {
            $query->where($types[$admin->account_type], $admin->id);
        });

//        $grid->column('id', __('Id'));
        $grid->column('uid', __('账户'))->display(function ($uid){
            return User::query()->where('id', $uid)->value('account');
        });
//        $grid->column('wid', __('Wid'));
        $grid->column('code', __('币种'));
//        $grid->column('account', __('Account'));
        $grid->column('order_no', '订单号');
        $grid->column('address', '提币地址');
        $grid->column('amount', __('金额'))->totalRow(function ($amount) {
            return "<span class='text-danger text-bold'>{$amount} </span>";
        });
        $grid->column('handling_fee', __('Fee'))->totalRow(function ($fee) {
            return "<span class='text-danger text-bold'>{$fee} </span>";
        });
        $grid->column('actual', __('Actual'));
        $grid->column('status', __('Status'))->using(UserWithdrawRecord::STATUS);
        $grid->column('mark', __('Mark'));
        $grid->column('type', __('Type'));

//        $grid->column('type', __('Type'))->using([
//            1 => 'OMNI',
//            2 => 'ERC20',
//        ], '未知')->label([
//            1 => 'danger',
//            2 => 'success',
//        ], 'info');
        $grid->column('refuse_reason', __('Refuse reason'));
        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));
        $grid->column('checked_at', __('Checked at'));
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
