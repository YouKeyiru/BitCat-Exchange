<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\IncomeOut\CheckHandel;
use App\Models\IncomeOut;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class IncomeOutController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '佣金提取';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new IncomeOut());
        $grid->disableCreateButton();
//
//        $grid->header(function ($query) {
//
//            return "<div>总计1：<span>{1}</span></div>
//                    <div>总计1：<span>{2}</span></div>
//                    <div>总计1：<span>{3}</span></div>
//                    <div>总计1：<span>{4}</span></div>
//                    <div>总计5：<span>{5}</span></div>";
//        });


        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
            });
            $filter->column(1 / 3, function ($filter) {
//                $filter->equal('address', __('Address'));
                $filter->equal('status', __('Status'))->select(IncomeOut::STATUS_TYPE);
            });
        });

//        $grid->column('id', __('Id'));
//        $grid->column('uid', __('Uid'));
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
        $grid->column('amount', '金额')->sortable();
        $grid->column('surplus', '结余');
        $grid->column('status', __('Status'))->using(IncomeOut::STATUS_TYPE);
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            $actions->disableView();
            $actions->disableEdit();
            if ($actions->row->status == IncomeOut::CHECK) {
                $actions->add(new CheckHandel);
            }
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
        $show = new Show(IncomeOut::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('amount', __('Amount'));
        $show->field('surplus', __('Surplus'));
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
        $form = new Form(new IncomeOut());

        $form->number('uid', __('Uid'));
        $form->decimal('amount', __('Amount'))->default(0.000000);
        $form->decimal('surplus', __('Surplus'))->default(0.000000);
        $form->switch('status', __('Status'))->default(1);

        return $form;
    }
}
