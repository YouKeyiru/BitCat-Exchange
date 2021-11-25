<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\IncomeOut\CheckHandel;
use App\Models\FeedBack;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FeedBackController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '反馈';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FeedBack());
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
            });
            $filter->column(1 / 3, function ($filter) {
//                $filter->equal('address', __('Address'));
//                $filter->equal('status', __('Status'))->select(FeedBack::STATUS_TYPE);
            });
        });

//        $grid->column('id', __('Id'));
//        $grid->column('uid', __('Uid'));
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));

        $grid->column('content', __('Content'));

//        $grid->column('status', __('Status'));

//        $grid->column('1', __('Content'))->modal(__('Content'), function ($model) {
//            return $model->content;
//        });

        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->actions(function ($actions) {
            // 去掉删除
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
        $show = new Show(FeedBack::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('content', __('Content'));
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
        $form = new Form(new FeedBack());

//        $form->number('uid', __('Uid'));
        $form->text('content', __('Content'));

        $form->textarea('reply', __('回复'));

        return $form;
    }
}
