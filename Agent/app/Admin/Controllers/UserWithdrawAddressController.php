<?php

namespace App\Admin\Controllers;

use App\Models\UserWithdrawAddress;
use App\Models\UserWithdrawRecord;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserWithdrawAddressController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户提币地址';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserWithdrawAddress());
        $admin = Admin::user();
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
        $grid->column('address', __('Address'));
        $grid->column('notes', __('备注'));
        $grid->column('type', __('Type'))->display(function ($type) {
            return UserWithdrawAddress::SERIES_TYPE[$type];
        });;
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
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
        $show = new Show(UserWithdrawAddress::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('address', __('Address'));
        $show->field('notes', __('Notes'));
        $show->field('type', __('Type'));
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
        $form = new Form(new UserWithdrawAddress());

        $form->number('uid', __('Uid'));
        $form->text('address', __('Address'));
        $form->text('notes', __('Notes'));
        $form->switch('type', __('Type'))->default(2);

        return $form;
    }
}
