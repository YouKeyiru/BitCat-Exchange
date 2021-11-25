<?php

namespace App\Admin\Controllers;

use App\Models\ProfitRebates;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProfitRebatesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '盈亏返佣';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProfitRebates);
        $grid->disableCreateButton();
        $grid->disableExport(false);

        $admin = Admin::user();

        $grid->filter(function ($filter) use ($admin) {
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) use ($admin) {
                if($admin->account_type == 4){
                    $filter->equal('unit.username', __('Unit'));
                    $filter->equal('agent.username', __('Agent'));
                    $filter->equal('staff.username', __('Staff'));
                }

                if($admin->account_type == 3){
                    $filter->equal('agent.username', __('Agent'));
                    $filter->equal('staff.username', __('Staff'));
                }

                $filter->equal('from.account', __('From user'));
                $filter->equal('from.phone', __('From user').__('Phone'));

                
            });
            $filter->column(1/2, function ($filter) {
                $filter->between('created_at','创建时间')->datetime();
            });
        });
        if($admin->account_type == 1){
            $grid->model()->where('id',0);
        }

        $grid->column('id', __('Id'));
        $grid->column('from.account', __('From user'));
        $grid->column('from.phone', __('Phone'));
        $grid->column('fee', __('Profit'))->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });

        if($admin->account_type == 4){
            $grid->model()->where('center_id',$admin->id);
            $grid->column('center_yongjin', __('Center').__('Profit'))->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });
            $grid->column('unit.username', __('Unit'));
            $grid->column('unit_yongjin', __('Unit').__('Profit'))->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });
            $grid->column('agent.username', __('Agent'));
            $grid->column('agent_yongjin', __('Agent').__('Profit'))->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });
        }
        if($admin->account_type == 3){
            $grid->model()->where('unit_id',$admin->id);
            $grid->column('unit.username', __('Unit'));
            $grid->column('unit_yongjin', __('Unit').__('Profit'))->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });
            $grid->column('agent.username', __('Agent'));
            $grid->column('agent_yongjin', __('Agent').__('Profit'))->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });
        }
        if($admin->account_type == 2){
            $grid->model()->where('agent_id',$admin->id);
            $grid->column('agent.username', __('Agent'));
            $grid->column('agent_yongjin', __('Agent').__('Profit'))->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });
        }

        if($admin->account_type == 1){
            $grid->model()->where('staff_id',$admin->id);
        }
        
        $grid->column('created_at', __('Created at'));

        $grid->disableActions();

        return $grid;
    }

}
