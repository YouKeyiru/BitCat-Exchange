<?php

namespace App\Admin\Controllers;

use App\Models\ProductsExchangeext;
use App\Models\ProductsExchange;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProductsExchangeextController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '自选币扩展';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProductsExchangeext());

        $grid->column('id', __('Id'));
        $grid->column('code_id', '币种')->display(function($code_id) {
            return ProductsExchange::find($code_id)->code;
        });
        $grid->column('start_price', '起始价格');
        $grid->column('limit_low', '最低波动');
        $grid->column('limit_high','最高波动');
        $grid->column('limit_delta', '最小波动价');
        $grid->column('limit_decimal', '小数位精度');
        $grid->column('min_quantity', '交易最小数量');
        $grid->column('max_quantity', '交易最大数量');
        $grid->column('act_type', '涨跌类型')->using([1 => '涨',2 => '跌', '3'=>'自动']);
        $grid->column('order_type', '方向类型')->using([1 => '买入',2 => '卖出']);

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ProductsExchangeext());

        $list = ProductsExchange::where(['type'=>'2'])->pluck('code', 'id')->toArray();
        $form->select('code_id', '币种')->options($list);
        $form->decimal('start_price', '起始价格')->default(0.000000);
        $form->decimal('limit_low', '最低波动')->default(0.000000);
        $form->decimal('limit_high','最高波动')->default(0.000000);
        $form->decimal('limit_delta', '最小波动价')->default(0.00000000);
        $form->number('limit_decimal', '小数位精度');
        $form->number('min_quantity', '交易最小数量');
        $form->number('max_quantity', '交易最大数量');
        $form->select('act_type', '涨跌类型')->options([1 => '涨',2 => '跌', '3'=>'自动']);
        $form->select('order_type', '方向类型')->options([1 => '买入',2 => '卖出']);



        $form->tools(function (Form\Tools $tools) {
            // 去掉`列表`按钮
            // $tools->disableList();

            // 去掉`删除`按钮
            // $tools->disableDelete();

            // 去掉`查看`按钮
            $tools->disableView();
        });

        return $form;
    }
}
