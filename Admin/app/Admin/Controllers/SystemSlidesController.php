<?php

namespace App\Admin\Controllers;

use App\Models\SystemSlides;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SystemSlidesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '轮播图';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SystemSlides());

        $grid->column('id', __('Id'));
        $grid->column('image', __('Image'))->lightbox(['width' => 50, 'height' => 50]);
        $grid->column('href', __('Href'));
        $grid->column('lang', __('Lang'))->display(function ($locale) {
            return config('system.lang')[$locale];
        });
//        $grid->column('position', __('Position'))->display(function ($position) {
//            return config('system.position')[$position];
//        });
        $grid->column('type', __('Type'))->display(function ($type) {
            return config('system.pc_app')[$type];
        });
        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));


        $grid->actions(function ($actions){
            // 去掉删除
//            $actions->disableDelete();
            // 去掉编辑
//            $actions->disableEdit();
            // 去掉查看
            $actions->disableView();
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
        $show = new Show(SystemSlides::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('image', __('Image'));
        $show->field('href', __('Href'));
        $show->field('lang', __('Lang'));
        $show->field('position', __('Position'));
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
        $form = new Form(new SystemSlides());

        $form->image('image', __('Image'))->removable()
            ->rules('required')->uniqueName();

        $form->url('href', __('Href'));

        $form->select('lang', __('Lang'))
            ->options(config('system.lang'))
            ->rules('required')
            ->default('zh-CN');

//        $form->select('position', __('Position'))
//            ->options(config('system.position'))
//            ->rules('required')
//            ->default(1);

        $form->select('type', __('Type'))
            ->options(config('system.pc_app'))
            ->rules('required')
            ->default(1);

        return $form;

    }
}
