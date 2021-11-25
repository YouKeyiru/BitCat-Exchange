<?php

namespace App\Admin\Controllers;

use App\Models\ProductsExchange;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProductsExchangeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '币币币种';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProductsExchange());
        $grid->disableCreateButton();

        $grid->column('id', __('Id'));
        $grid->column('pname', __('Pname'));
        $grid->column('code', __('Code'));
        // $grid->column('image', __('Image'));
        $grid->column('image', __('Image'))->lightbox(['width' => 50, 'height' => 50]);
        $grid->column('mark_cn', __('Mark cn'));
        $grid->column('max_order', __('Max order'));
        $grid->column('min_order', __('Min order'));
        $grid->column('state', __('State'))->switch();
//        $grid->column('type', __('Type'));
//        $grid->column('sort', __('Sort'));
//        $grid->column('fxtime', __('Fxtime'));
//        $grid->column('fxnum', __('Fxnum'));
//        $grid->column('fxprice', __('Fxprice'));
//        $grid->column('fxweb', __('Fxweb'));
//        $grid->column('fxbook', __('Fxbook'));
//        $grid->column('memo', __('Memo'));
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            $actions->disableView();
//            $actions->disableEdit();

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
        $show = new Show(ProductsExchange::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('pname', __('Pname'));
        $show->field('code', __('Code'));
        $show->field('image', __('Image'));
        $show->field('mark_cn', __('Mark cn'));
        $show->field('max_order', __('Max order'));
        $show->field('min_order', __('Min order'));
        $show->field('state', __('State'));
        $show->field('type', __('Type'));
        $show->field('sort', __('Sort'));
        $show->field('fxtime', __('Fxtime'));
        $show->field('fxnum', __('Fxnum'));
        $show->field('fxprice', __('Fxprice'));
        $show->field('fxweb', __('Fxweb'));
        $show->field('fxbook', __('Fxbook'));
        $show->field('memo', __('Memo'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ProductsExchange());

        $form->text('pname', __('Pname'));
        $isCreate = $form->isCreating();
        if($isCreate){
            $form->text('code', __('Code'));
        }else{
            $form->text('code', __('Code'))->readonly();
        }
        $form->image('image', __('Image'));
        $form->text('mark_cn', __('Mark cn'));
        $form->decimal('max_order', __('Max order'))->default(0.00);
        $form->decimal('min_order', __('Min order'))->default(0.00);
        $form->switch('state', __('State'));
//        $form->switch('type', __('Type'))->default(2);
        $start_t = [
            1 => '系统币',
            2 => '自发币',
        ];
        $form->select('type', __('Type'))->options($start_t);
        $form->number('sort', __('Sort'));
        $form->text('fxtime', __('Fxtime'));
        $form->text('fxnum', __('Fxnum'));
        $form->decimal('fxprice', __('Fxprice'))->default(0.00000000);
        $form->text('fxweb', __('Fxweb'));
        $form->text('fxbook', __('Fxbook'));
        $form->textarea('memo', __('Memo'));

        //毙掉一些按钮
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
            $footer->disableViewCheck();
        });


        return $form;
    }
}
