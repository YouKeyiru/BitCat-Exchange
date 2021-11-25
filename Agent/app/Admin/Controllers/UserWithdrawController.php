<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\UserWithdrawExporter;
use App\Models\UserWithdraw;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserWithdrawController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '出金明细';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserWithdraw);
        $grid->disableExport(false);
        $grid->disableCreateButton();
        $grid->fixColumns(4, -2);

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
                $filter->between('created_at',__('Created at'))->datetime();
                
            });
            $filter->column(1/2, function ($filter) {
                $filter->between('money',__('Money'));
                $filter->equal('status', __('Status'))->select(config('system.withdraw_status'));
            });
        });
        $user = Admin::user();
        $types = [
            1 => 'staff_id',
            2 => 'agent_id',
            3 => 'unit_id',
            4 => 'center_id'];
        $grid->model()->whereHas('user',function ($query) use ($user,$types)
            {
                $query->where($types[$user->account_type],$user->id);
            })
        ->orderBy('id','desc');

//        $grid->column('id', __('Id'));
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
        $grid->column('user.name', __('User name'));
        $grid->column('with_num', __('Ordnum'));
        $grid->column('address', __('Address'));
        $grid->column('money', __('Money'))->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });
        $grid->column('handling_fee', __('Fee'))->totalRow(function ($fee) {
            return "<span class='text-danger text-bold'>{$fee} </span>";
        });
        $grid->column('actual', __('Actual'));
        $grid->column('status', __('Status'))->display(function($status){
            return config('system.withdraw_status')[$status];
        });
        $grid->column('mark', __('Mark'));
        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));
        $grid->column('checked_at', __('Checked at'));

        $grid->exporter(new UserWithdrawExporter());

        // 全部关闭
        $grid->disableActions();

        return $grid;
    }

}
