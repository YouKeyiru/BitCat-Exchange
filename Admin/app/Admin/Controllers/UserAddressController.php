<?php

namespace App\Admin\Controllers;

//use App\Admin\Actions\Wallet\TxHandel;
use App\Models\UserAddress;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserAddressController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '地址';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserAddress());
        $grid->disableCreateButton();

        $grid->paginate(30);
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('address', __('Address'));
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal('user.phone', __('Phone'));
            });
        });

        $grid->column('id', __('Id'));
        $grid->column('uid', __('Uid'));
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
        $grid->column('user.name', __('User name'));
        $grid->column('address', __('Address'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('balance1', __('Balance1'));

        $grid->actions(function ($actions) {
            //添加抽取按钮
            if ($actions->row->type == 2) {
//                $actions->add(new TxHandel);
            }

            // 去掉删除
            $actions->disableDelete();

            // 去掉编辑
            $actions->disableEdit();

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
        $show = new Show(UserAddress::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('address', __('Address'));
        $show->field('private_key', __('Private key'));
        $show->field('word', __('Word'));
        $show->field('type', __('Type'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('balance1', __('Balance1'));
        $show->field('balance2', __('Balance2'));
        $show->field('balance3', __('Balance3'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserAddress());

        $form->number('uid', __('Uid'));
        $form->text('address', __('Address'));
        $form->textarea('private_key', __('Private key'));
        $form->textarea('word', __('Word'));
        $form->switch('type', __('Type'))->default(2);
        $form->decimal('balance1', __('Balance1'))->default(0.00000000);
        $form->decimal('balance2', __('Balance2'))->default(0.00000000);
        $form->decimal('balance3', __('Balance3'))->default(0.00000000);

        return $form;
    }
}
