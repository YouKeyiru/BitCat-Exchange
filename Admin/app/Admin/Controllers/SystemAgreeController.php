<?php

namespace App\Admin\Controllers;

use App\Models\SystemAgree;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SystemAgreeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '协议';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SystemAgree());

        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('content', __('Content'));
        $grid->column('lang', __('Lang'));
        $grid->column('type', __('Type'))->using(SystemAgree::TYPE_AGREE);
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
        $show = new Show(SystemAgree::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('content', __('Content'));
        $show->field('lang', __('Lang'));
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
        $form = new Form(new SystemAgree());

        $form->text('title', __('Title'));
        $form->textarea('content', __('Content'));
        $form->select('lang', __('Lang'))->options(config('system.lang'))->rules('required');

        $form->select('type', __('Type'))->options(SystemAgree::TYPE_AGREE)->rules('required');

        return $form;
    }
}
