<?php

namespace App\Admin\Controllers;

use App\Models\UserWithdrawAddress;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserWithdrawAddressController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '提币地址';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserWithdrawAddress());

        $grid->disableCreateButton();


        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('address', __('Address'));
            });
        });


//        $grid->column('id', __('Id'));
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));


        $grid->column('address', __('Address'));
        $grid->column('notes', __('Notes'));

//        $grid->column('type', __('Type'));

        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->disableActions();
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
        $show = new Show(UserWithdrawAddress::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('address', __('Address'));
        $show->field('notes', __('Notes'));
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
        $form = new Form(new UserWithdrawAddress());

        $form->number('uid', __('Uid'));
        $form->text('address', __('Address'));
        $form->text('notes', __('Notes'));
        $form->switch('type', __('Type'))->default(2);

        return $form;
    }
}
