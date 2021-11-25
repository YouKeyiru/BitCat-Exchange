<?php

namespace App\Admin\Controllers;

use App\Models\FbShopApply as ShopApply;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FbShopApplyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商家申请';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ShopApply());
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
//                $filter->equal('email', __('Email'));
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('action', __('Action'))->select(ShopApply::SHOP_ACTION_TYPE);
                $filter->equal('status', __('Status'))->select(ShopApply::SHOP_APPLY_STATUS);
            });
        });
        $grid->column('action', __('Action'))->display(function ($value) {
            return ShopApply::SHOP_ACTION_TYPE[$value];
        });

        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
        $grid->column('user.name', '姓名');
        $grid->column('status', __('Status'))->display(function ($value) {
            return ShopApply::SHOP_APPLY_STATUS[$value];
        });
        $grid->column('created_at', '申请时间');
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            $actions->disableView();

            if (in_array($actions->row->status, [ShopApply::SHOP_APPLY_CHECK, ShopApply::SHOP_CANCEL_CHECK])) {
                $actions->add(new \App\Admin\Actions\ShopApply\Agree);
                $actions->add(new \App\Admin\Actions\ShopApply\Refuse);
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
        $show = new Show(FbShopApply::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('money', __('Money'));
        $show->field('action', __('Action'));
        $show->field('status', __('Status'));
        $show->field('remark', __('Remark'));
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
        $form = new Form(new FbShopApply());

        $form->number('uid', __('Uid'));
        $form->decimal('money', __('Money'))->default(0.00000000);
        $form->switch('action', __('Action'))->default(1);
        $form->switch('status', __('Status'))->default(1);
        $form->text('remark', __('Remark'));

        return $form;
    }
}
