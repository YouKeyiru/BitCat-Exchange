<?php

namespace App\Admin\Controllers;

use App\Models\ContractTrans;
use App\Models\ProductsContract;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ContractTransController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '合约交易-平仓';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ContractTrans());
        $grid->disableCreateButton();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
//                $filter->equal('user.email', __('Email'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('order_no', __('Order no'));
                $filter->equal('pc_type', __('Pc type'))->select(ContractTrans::TYPE_CLOSE);
                $filter->equal('pid', __('Code'))->select(ProductsContract::pluck('code', 'id'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->between('created_at', __('Created at'))->datetime();
            });
        });

        $grid->column('order_no', __('Order no'));
        $grid->column('user.account', __('Account'));
        $grid->column('Phone Email', __('Phone Email'))->display(function () {
            $value = $this->user->phone . '/' . $this->user->email;
            return trim($value,'/');
        });

        $grid->column('name', __('Code name'));
        $grid->column('buy_price', __('Buy price'));
        $grid->column('buy_num', __('Buy num'));

//        $grid->column('sheets', __('Sheets'));

        $grid->column('total_price', __('Total price'));
        $grid->column('otype', __('Otype'))->display(function ($otype) {
            return $otype == 1 ? '买涨' : '买跌';
        });
        $grid->column('stop_win', __('Stop win'));
        $grid->column('stop_loss', __('Stop loss'));
        $grid->column('sell_price', __('Sell price'));
        $grid->column('profit', __('Profit'));
        $grid->column('fee', __('Fee'));
        $grid->column('dayfee', __('Day fee'));
//        $grid->column('deposit', __('Deposit'));
        $grid->column('pc_type', __('Pc type'))->using(ContractTrans::TYPE_CLOSE);
        $grid->column('leverage', __('Leverage'));
//        $grid->column('source', __('Source'));
        $grid->column('jiancang_at', __('Jiancang at'));
        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(ContractTrans::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_no', __('Order no'));
        $show->field('uid', __('Uid'));
        $show->field('pid', __('Pid'));
        $show->field('name', __('Name'));
        $show->field('code', __('Code'));
        $show->field('buy_price', __('Buy price'));
        $show->field('buy_num', __('Buy num'));
        $show->field('sheets', __('Sheets'));
        $show->field('price', __('Price'));
        $show->field('total_price', __('Total price'));
        $show->field('otype', __('Otype'));
        $show->field('stop_win', __('Stop win'));
        $show->field('stop_loss', __('Stop loss'));
        $show->field('sell_price', __('Sell price'));
        $show->field('profit', __('Profit'));
        $show->field('fee', __('Fee'));
        $show->field('dayfee', __('Dayfee'));
        $show->field('deposit', __('Deposit'));
        $show->field('pc_type', __('Pc type'));
        $show->field('leverage', __('Leverage'));
        $show->field('source', __('Source'));
        $show->field('jiancang_at', __('Jiancang at'));
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
        $form = new Form(new ContractTrans());

        $form->text('order_no', __('Order no'));
        $form->number('uid', __('Uid'));
        $form->number('pid', __('Pid'));
        $form->text('name', __('Name'));
        $form->text('code', __('Code'));
        $form->decimal('buy_price', __('Buy price'))->default(0.00000000);
        $form->decimal('buy_num', __('Buy num'))->default(0.00000000);
        $form->number('sheets', __('Sheets'));
        $form->decimal('price', __('Price'))->default(0.00000000);
        $form->decimal('total_price', __('Total price'))->default(0.00000000);
        $form->switch('otype', __('Otype'));
        $form->decimal('stop_win', __('Stop win'))->default(0.00000000);
        $form->decimal('stop_loss', __('Stop loss'))->default(0.00000000);
        $form->decimal('sell_price', __('Sell price'))->default(0.00000000);
        $form->decimal('profit', __('Profit'))->default(0.00000000);
        $form->decimal('fee', __('Fee'))->default(0.00000000);
        $form->decimal('dayfee', __('Dayfee'))->default(0.0000);
        $form->decimal('deposit', __('Deposit'))->default(0.00000000);
        $form->switch('pc_type', __('Pc type'));
        $form->number('leverage', __('Leverage'))->default(50);
        $form->switch('source', __('Source'))->default(1);
        $form->datetime('jiancang_at', __('Jiancang at'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
