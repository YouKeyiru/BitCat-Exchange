<?php

namespace App\Admin\Controllers;

use App\Models\On;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MoneyJoinController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '一键归集';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new On());

        $grid->column('desc', __('操作'));

        $states = [
            'on'  => ['value' => 1, 'text' => '执行中', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '已结束', 'color' => 'default'],
        ];
        $grid->column('value','状态')->switch($states);
        $grid->column('updated_at', __('上次操作时间'));

        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->disableActions();

        $grid->actions(function($actions) {
            $actions->disableView();
            $actions->disableDelete();
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
        $show = new Show(Banner::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('image', __('Image'));
        $show->field('url', __('Url'));
        $show->field('status', __('Status'));
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
        $form = new Form(new On());

        $form->text('desc', __('操作'))->required()->disable();

        $states = [
            'on'  => ['value' => 1, 'text' => '执行中', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '已结束', 'color' => 'default'],
        ];
        $form->switch('value', __('状态'))->states($states)->default('off');

        $form->saving(function(Form $form) {
            if($form->model()->value) {
                return response()->json([
                    'status'    => false,
                    'message'   => "执行中，勿操作",
                ]);
            }
        });
        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
            // 去掉`查看`按钮
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            // 去掉`重置`按钮
            $footer->disableReset();
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();
            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();

        });
        return $form;
    }
}