<?php

namespace App\Admin\Controllers;

use App\Models\ContractPosition;
use App\Models\ProductsContract;
use App\Models\UserMoneyLog;
use App\Models\WalletCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ContractPositionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '合约交易-持仓';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ContractPosition());
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
        $grid->column('buy_num', __('Buy num'));
        $grid->column('buy_price', __('Buy price'));
        $grid->column('total_price', __('Total price'));
        $grid->column('fee', __('Fee'));

        $grid->column('otype', __('Otype'))->display(function ($otype) {
            return $otype == 1 ? '买涨' : '买跌';
        });

        $grid->column('stop_win', __('Stop win'));
        $grid->column('stop_loss', __('Stop loss'));
        $grid->column('leverage', __('Leverage'));
        $grid->column('spread', __('Spread'));
        $grid->column('dayfee', __('Day fee'));
        $grid->column('created_at', __('Created at'));

        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            $actions->disableView();
            $actions->disableEdit();

//            $actions->add(new RevokeBuyOrder());

//            if ($actions->row->status == 1) {
////                $actions->add(new RevokeBuyOrder());
//            }
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
        $show = new Show(ContractPosition::findOrFail($id));

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
        $show->field('fee', __('Fee'));
        $show->field('otype', __('Otype'));
        $show->field('stop_win', __('Stop win'));
        $show->field('stop_loss', __('Stop loss'));
        $show->field('leverage', __('Leverage'));
        $show->field('spread', __('Spread'));
        $show->field('deposit', __('Deposit'));
        $show->field('dayfee', __('Dayfee'));
        $show->field('version', __('Version'));
        $show->field('source', __('Source'));
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
        $form = new Form(new ContractPosition());

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
        $form->decimal('fee', __('Fee'))->default(0.00000000);
        $form->switch('otype', __('Otype'));
        $form->decimal('stop_win', __('Stop win'))->default(0.00000000);
        $form->decimal('stop_loss', __('Stop loss'))->default(0.00000000);
        $form->number('leverage', __('Leverage'));
        $form->decimal('spread', __('Spread'))->default(0.00000000);
        $form->decimal('deposit', __('Deposit'))->default(0.00000000);
        $form->decimal('dayfee', __('Dayfee'))->default(0.00000000);
        $form->number('version', __('Version'));
        $form->switch('source', __('Source'))->default(1);

        return $form;
    }
}
