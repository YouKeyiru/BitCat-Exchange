<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Authentication\Agree;
use App\Admin\Actions\Authentication\AuthEdit;
use App\Admin\Actions\Authentication\Refuse;
use App\Models\Authentication;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AuthenticationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '实名认证';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Authentication());
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('status', __('Status'))->select(Authentication::STATUS);
                $filter->between('created_at', __('Created at'))->datetime();
            });
        });
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
        $grid->column('name', __('User name'));
        $grid->column('card_id', __('Card id'));

        $grid->column('front_img', __('Front img'))->lightbox(['width' => 50, 'height' => 50]);
        $grid->column('back_img', __('Back img'))->lightbox(['width' => 50, 'height' => 50]);
        $grid->column('handheld_img', __('Handheld img'))->lightbox(['width' => 50, 'height' => 50]);


        $grid->column('status', __('Status'))->display(function ($value) {
            return Authentication::STATUS[$value];
        });
        $grid->column('1', __('Refuse reason'))->modal(__('Refuse reason'), function ($model) {
            return $model->refuse_reason;
        });
        $grid->column('created_at', __('Created at'));
        $grid->column('checked_at', __('Checked at'));

        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            $actions->disableView();

            $actions->add(new AuthEdit);
            if($actions->row->status == 2){
                $actions->add(new Agree);
                $actions->add(new Refuse);
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
        $show = new Show(Authentication::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uid', __('Uid'));
        $show->field('name', __('Name'));
        $show->field('card_id', __('Card id'));
        $show->field('front_img', __('Front img'));
        $show->field('back_img', __('Back img'));
        $show->field('handheld_img', __('Handheld img'));
        $show->field('status', __('Status'));
        $show->field('real_name_result', __('Real name result'));
        $show->field('refuse_reason', __('Refuse reason'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('checked_at', __('Checked at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Authentication());

        $form->number('uid', __('Uid'));
        $form->text('name', __('Name'));
        $form->text('card_id', __('Card id'));
        $form->text('front_img', __('Front img'));
        $form->text('back_img', __('Back img'));
        $form->text('handheld_img', __('Handheld img'));
        $form->switch('status', __('Status'))->default(1);
        $form->text('real_name_result', __('Real name result'));
        $form->text('refuse_reason', __('Refuse reason'));
        $form->datetime('checked_at', __('Checked at'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
