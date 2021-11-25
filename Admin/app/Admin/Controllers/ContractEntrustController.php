<?php

namespace App\Admin\Controllers;

use App\Models\ContractEntrust;
use App\Models\ProductsContract;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ContractEntrustController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '合约交易-委托';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ContractEntrust());
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
                $filter->equal('pid', __('Code'))->select(ProductsContract::pluck('code','id'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->between('created_at', __('Created at'))->datetime();
            });
        });


        $grid->column('order_no', __('Order no'));
        $grid->column('user.account', __('Account'));
//        $grid->column('user.phone', __('Phone'));
//        $grid->column('user.email', __('Email'));

        $grid->column('Phone Email', __('Phone Email'))->display(function () {
            $value = $this->user->phone . '/' . $this->user->email;
            return trim($value,'/');
        });

//        $grid->column('productCode.code', __('Code'));
        $grid->column('name', __('Code name'));
//        $grid->column('sheets', __('Sheets'));
        $grid->column('buy_num', __('Buy num'));
        $grid->column('buy_price', __('Buy price'));
        $grid->column('price', __('Price'));
        $grid->column('total_price', __('Total price'));
        $grid->column('otype', __('Otype'));
        $grid->column('fee', __('Fee'));
        $grid->column('status', __('Status'));
        $grid->column('leverage', __('Leverage'));
        $grid->column('created_at', __('Created at'));

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
        $show = new Show(ContractEntrust::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_no', __('Order no'));
        $show->field('uid', __('Uid'));
        $show->field('pid', __('Pid'));
        $show->field('name', __('Name'));
        $show->field('code', __('Code'));
        $show->field('sheets', __('Sheets'));
        $show->field('buy_num', __('Buy num'));
        $show->field('buy_price', __('Buy price'));
        $show->field('market_price', __('Market price'));
        $show->field('price', __('Price'));
        $show->field('total_price', __('Total price'));
        $show->field('otype', __('Otype'));
        $show->field('stop_win', __('Stop win'));
        $show->field('stop_loss', __('Stop loss'));
        $show->field('fee', __('Fee'));
        $show->field('status', __('Status'));
        $show->field('deposit', __('Deposit'));
        $show->field('spread', __('Spread'));
        $show->field('leverage', __('Leverage'));
        $show->field('dayfee', __('Dayfee'));
        $show->field('version', __('Version'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('handle_at', __('Handle at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ContractEntrust());

        $form->text('order_no', __('Order no'));
        $form->number('uid', __('Uid'));
        $form->number('pid', __('Pid'));
        $form->text('name', __('Name'));
        $form->text('code', __('Code'));
        $form->number('sheets', __('Sheets'));
        $form->decimal('buy_num', __('Buy num'))->default(0.00000000);
        $form->decimal('buy_price', __('Buy price'))->default(0.00000000);
        $form->decimal('market_price', __('Market price'))->default(0.00000000);
        $form->decimal('price', __('Price'))->default(0.00000000);
        $form->decimal('total_price', __('Total price'))->default(0.00000000);
        $form->switch('otype', __('Otype'));
        $form->decimal('stop_win', __('Stop win'))->default(0.00000000);
        $form->decimal('stop_loss', __('Stop loss'))->default(0.00000000);
        $form->decimal('fee', __('Fee'))->default(0.00000000);
        $form->switch('status', __('Status'))->default(1);
        $form->decimal('deposit', __('Deposit'))->default(0.00000000);
        $form->decimal('spread', __('Spread'))->default(0.00000000);
        $form->number('leverage', __('Leverage'))->default(50);
        $form->decimal('dayfee', __('Dayfee'))->default(0.00000000);
        $form->number('version', __('Version'));
        $form->datetime('handle_at', __('Handle at'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
