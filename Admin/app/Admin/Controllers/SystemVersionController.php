<?php

namespace App\Admin\Controllers;

use App\Models\SystemVersion;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SystemVersionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '版本更新';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SystemVersion);
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('content', __('Content'));
        $grid->column('type', '更新范围')->display(function ($type) {
            $type1 = '<span class="label label-warning">Android</span>';
            $type2 = '<span class="label label-primary">IOS</span>';
            switch ($type){
                case 1:
                    return $type1;
                    break;
                case 2:
                    return $type2;
                    break;
                default:
                    return 'ERROR';
                    break;
            }
        });
        $grid->column('uptype', '更新类型')->display(function ($uptype) {
            $type1 = '<span class="label label-danger">强制更新</span>';
            $type2 = '<span class="label label-warning">不强制更新</span>';
            switch ($uptype){
                case 1:
                    return $type1;
                    break;
                case 2:
                    return $type2;
                    break;
                default:
                    return 'ERROR';
                    break;
            }
        });
        $grid->column('address', __('Address'));
        $grid->column('vercode', __('Vercode'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));


        $grid->actions(function ($actions) {
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
        $show = new Show(SystemVersion::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('content', __('Content'));
        $show->field('type', __('Type'));
        $show->field('uptype', __('Uptype'));
        $show->field('address', __('Address'));
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
        $form = new Form(new SystemVersion);

        $form->text('title', __('Title'));
        $form->text('vercode', __('Vercode'));
        $form->text('address', __('Link Address'));
        $form->radio('type','更新范围')->options([
            1 => 'Android',
            2 => 'IOS',
        ])->default(1);
        $form->radio('uptype','更新类型')->options([
            1 => '强制更新',
            2 => '不强制更新',
        ])->default(1);
        $form->textarea('content', __('Content'));

        return $form;
    }
}
