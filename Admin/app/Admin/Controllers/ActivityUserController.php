<?php

namespace App\Admin\Controllers;

use App\Models\Activity;
use App\Models\ActivityUser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ActivityUserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '参与';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ActivityUser());
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
//                $filter->equal('email', __('Email'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('activity_id', __('Activity Describe'))->select(Activity::query()->pluck('describe','id'));
                $filter->equal('status', __('ActivityUser Status'))->select(ActivityUser::PROFIT_STATUS);
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->between('created_at', __('Created at'))->datetime();
            });
        });

        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
        $grid->column('activity.describe', __('Activity Describe'));
        $grid->column('amount', __('ActivityUser Amount'))->sortable();
        $grid->column('cycle', __('Activity Cycle'));
        $grid->column('day_rate', __('Activity Day rate'));
        $grid->column('damages_rate', __('Activity Damages rate'));
        $grid->column('days', __('Activity Days'));
        $grid->column('profit', __('ActivityUser Profit'))->sortable();
        $grid->column('status', __('ActivityUser Status'))->using(ActivityUser::PROFIT_STATUS);
        $grid->column('damages', __('Activity Damages'));

        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(ActivityUser::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('activity_id', __('Activity id'));
        $show->field('uid', __('Uid'));
        $show->field('wid', __('Wid'));
        $show->field('amount', __('Amount'));
        $show->field('cycle', __('Cycle'));
        $show->field('day_rate', __('Day rate'));
        $show->field('damages_rate', __('Damages rate'));
        $show->field('days', __('Days'));
        $show->field('profit', __('Profit'));
        $show->field('status', __('Status'));
        $show->field('damages', __('Damages'));
        $show->field('version', __('Version'));
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
        $form = new Form(new ActivityUser());

        $form->number('activity_id', __('Activity id'));
        $form->number('uid', __('Uid'));
        $form->number('wid', __('Wid'));
        $form->decimal('amount', __('Amount'))->default(0.000000);
        $form->number('cycle', __('Cycle'));
        $form->decimal('day_rate', __('Day rate'))->default(0.00);
        $form->decimal('damages_rate', __('Damages rate'))->default(0.00);
        $form->number('days', __('Days'));
        $form->decimal('profit', __('Profit'))->default(0.000000);
        $form->switch('status', __('Status'))->default(1);
        $form->decimal('damages', __('Damages'))->default(0.00);
        $form->number('version', __('Version'))->default(1);

        return $form;
    }
}
