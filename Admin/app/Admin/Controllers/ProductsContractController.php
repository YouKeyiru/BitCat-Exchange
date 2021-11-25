<?php

namespace App\Admin\Controllers;

use App\Models\ProductsContract;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Redis;

class ProductsContractController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '合约币种';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProductsContract());
        $grid->disableCreateButton();

//        $grid->column('id', __('Id'));
        $grid->column('pname', __('Pname'));
        $grid->column('code', __('Code'));
        $grid->column('image', __('Logo'))->lightbox(['width' => 50, 'height' => 50]);
//        $grid->column('mark_cn', __('Mark cn'));
        $grid->column('spread', __('Spread'));
//        $grid->column('var_price', __('Var price'));
        $grid->column('leverage', __('Leverage'));
        $grid->column('handling_fee', __('Handling fee'))->editable();
        $grid->column('max_order', __('Max order'))->editable();
        $grid->column('min_order', __('Min order'))->editable();
        $grid->column('max_chicang', __('Max chicang'))->editable();
//        $grid->column('sheet_num', __('Sheet num'));
        $grid->column('state', __('Status'))->switch();
//        $grid->column('type', __('Type'));
//        $grid->column('sort', __('Sort'));

//        $grid->column('buy_up', __('Buy up'))->switch();
//        $grid->column('buy_down', __('Buy down'))->switch();

//        $grid->column('fxtime', __('Fxtime'));
//        $grid->column('fxnum', __('Fxnum'));
//        $grid->column('fxprice', __('Fxprice'));
//        $grid->column('fxweb', __('Fxweb'));
//        $grid->column('fxbook', __('Fxbook'));
//        $grid->column('memo', __('Memo'));

        $grid->actions(function ($actions) {
            // 去掉删除
//            $actions->disableDelete();
//            $actions->disableView();
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
        $show = new Show(ProductsContract::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('pname', __('Pname'));
        $show->field('code', __('Code'));
        $show->field('image', __('Image'));
        $show->field('mark_cn', __('Mark cn'));
        $show->field('spread', __('Spread'));
        $show->field('var_price', __('Var price'));
        $show->field('leverage', __('Leverage'));
        $show->field('handling_fee', __('Handling fee'));
        $show->field('max_order', __('Max order'));
        $show->field('min_order', __('Min order'));
        $show->field('max_chicang', __('Max chicang'));
        $show->field('sheet_num', __('Sheet num'));
        $show->field('state', __('State'));
        $show->field('type', __('Type'));
        $show->field('sort', __('Sort'));
        $show->field('buy_up', __('Buy up'));
        $show->field('buy_down', __('Buy down'));
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
        $form = new Form(new ProductsContract());

        $form->text('pname', __('Pname'));
        $form->text('code', __('Code'))->readonly();
        $form->image('image', __('Image'))->removable();
//        $form->text('mark_cn', __('Mark cn'));
        $form->decimal('spread', __('Spread'))->default(0.00000000);

//        $form->decimal('var_price', __('Var price'))->default(1);

//        $form->decimal('var_price', __('Var price'))->default(1);
        $form->decimal('var_price', __('Var price'))->default(1);

        $form->text('leverage', __('Leverage'))->default('50');
        $form->decimal('handling_fee', __('Handling fee'))->default(0.0000)->help('% 手续费比率');
        $form->decimal('max_order', __('Max order'))->default(0.00000000);
        $form->decimal('min_order', __('Min order'))->default(0.00000000);
        $form->decimal('max_chicang', __('Max chicang'))->default(0.00000000);
        // $form->number('sheet_num', __('Sheet num'));
        $form->number('sheet_num', '张数量');
        $form->switch('state', __('Status'))->help('上下架');
//        $form->switch('type', __('Type'))->default(2);
        $form->number('sort', __('Sort'));
        $form->switch('buy_up', __('Buy up'))->default(1);
        $form->switch('buy_down', __('Buy down'))->default(1);
        $form->date('fxtime', __('Fxtime'))->default(date('Y-m-d'));
        $form->text('fxnum', __('Fxnum'));
        $form->decimal('fxprice', __('Fxprice'))->default(0.00000000);
        $form->text('fxweb', __('Fxweb'));
        $form->text('fxbook', __('Fxbook'));
        $form->textarea('memo', __('Memo'));




        # -----------
        # fkJson = {
        #     'minUnit':0.001,  #最小波动价->精度
        #     'count':50,  #整数类型，涨跌点数, 负值表示降  正直表示升
        #     'enabled': 1,  #1：可用，其他值：不可用
        # }
        # -----------


        // $form->radio('enabled','是否开启')->options([
        //     1 => '开启',
        //     0 => '关闭',
        // ])->default(1);

        // $form->decimal('min_unit', '最小波动价-精度')->default(0.001);
        // $form->text('count', '涨跌点数')
        //     ->default(50)
        //     ->help('整数类型，涨跌点数, 负值表示降  正值表示升');

        // //保存前回调
        // $form->saving(function (Form $form) {
        //     $array = [
        //         'minUnit' => (float)$form->min_unit, #最小波动价->精度
        //         'count' => (int)$form->count,#整数类型，涨跌点数, 负值表示降  正直表示升
        //         'enabled' => $form->enabled ? 1: 2,#1：可用，其他值：不可用
        //     ];
        //     $json = json_encode($array);
        //     $subscribeRedis = Redis::connection('subscribe');
        //     $bool = $subscribeRedis->set('vb:ticker:fkJson:params:'.$form->code,$json);
        //     $bool = $subscribeRedis->set('vb:ticker:fkJson:params:'.strtoupper($form->code),$json);

        // });

        //毙掉一些按钮
        $form->tools(function (Form\Tools $tools) {
 //           $tools->disableDelete();
 //           $tools->disableView();
        });

        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
            $footer->disableViewCheck();
        });

        return $form;
    }
}
