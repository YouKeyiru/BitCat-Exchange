<?php

namespace App\Admin\Controllers;

use App\Models\Activity;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ActivityController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '套餐';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Activity());
//        $grid->disableCreateButton();
//        $grid->column('id', __('Id'));
//        $grid->column('wid', __('Wid'));
        $grid->column('describe', __('Activity Describe'));
        $grid->column('min_num', __('Activity Min num'));
        $grid->column('max_num', __('Activity Max num'));
//        $grid->column('multiple', __('Activity Multiple'));
        $grid->column('cycle', __('Activity Cycle'));
        $grid->column('day_rate', __('Activity Day rate'));
        $grid->column('damages_rate', __('Activity Damages rate'));
        $grid->column('status', __('Activity Status'))->switch();

//        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            $actions->disableView();
//            $actions->disableEdit();

        });
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
        $show = new Show(Activity::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('wid', __('Wid'));
        $show->field('min_num', __('Min num'));
        $show->field('max_num', __('Max num'));
        $show->field('multiple', __('Multiple'));
        $show->field('cycle', __('Cycle'));
        $show->field('day_rate', __('Day rate'));
        $show->field('damages_rate', __('Damages rate'));
        $show->field('status', __('Status'));
        $show->field('describe', __('Describe'));
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
        $form = new Form(new Activity());

//        $form->number('wid', __('Wid'))->default(1);
        $form->text('describe', __('Activity Describe'));
        $form->decimal('min_num', __('Activity Min num'))->default(0.000000);
        $form->decimal('max_num', __('Activity Max num'))->default(0.000000);
//        $form->number('multiple', __('Activity Multiple'));
        $form->number('cycle', __('Activity Cycle'));
        $form->decimal('day_rate', __('Activity Day rate'))->default(0.00);
        $form->decimal('damages_rate', __('Activity Damages rate'))->default(0.00);
        $form->switch('status', __('Activity Status'))->default(1);
        //毙掉一些按钮
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
            $footer->disableViewCheck();
        });
        return $form;
    }
}
