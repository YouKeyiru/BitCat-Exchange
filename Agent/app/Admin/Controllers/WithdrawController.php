<?php

namespace App\Admin\Controllers;

use App\Models\AgentWithdraw;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class WithdrawController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '我的提币';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $admin = Admin::user();

        $grid = new Grid(new AgentWithdraw);
        $grid->disableCreateButton();
        $grid->disableFilter();

        $grid->model()->where('uid',$admin->id);
//        $grid->column('id', __('Id'));
        $grid->column('with_num', __('Ordnum'));
        $grid->column('address', __('Address'));
        $grid->column('money', __('Money'));
        $grid->column('handling_fee', __('Fee'));
        $grid->column('actual', __('Actual'));
        $grid->column('status', __('Status'))->display(function($status){
            return config('system.withdraw_status')[$status];
        });
        $grid->column('mark', __('Mark'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('checked_at', __('Checked at'));

        $grid->disableActions();

        return $grid;
    }

}
