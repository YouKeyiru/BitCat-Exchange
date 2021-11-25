<?php

namespace App\Admin\Controllers;

use App\Models\CashGift;
use App\Models\WalletCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CashGiftController extends AdminController
{

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '赠金列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CashGift);
        $grid->disableExport(false);

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('secret_key', __('密钥'));
                $filter->equal('wid', '币种')->select(WalletCode::pluck('code', 'id'));
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->between('created_at', __('Created at'))->datetime();
                $filter->between('updated_at', __('Updated at'))->datetime();
            });
        });

        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'));
        $grid->column('secret_key', __('密钥'));
        $grid->column('total_times', __('总次数'));
        $grid->column('used_times', __('已领次数'));
        $grid->column('money_min', __('领取最小金额'));
        $grid->column('money_max', __('领取最大金额'));
        $grid->column('wallet.code', '领取币种');
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        // 全部关闭
        // $grid->disableActions();
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            // 去掉编辑
            // $actions->disableEdit();
            // 去掉查看
            $actions->disableView();
        });
        return $grid;
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CashGift);

        $form->number('total_times', '总次数');
        //currency number
        // $form->number('money_min', __('领取最小金额范围'))
        //     ->rules('required|min:1')
        //     ->default(0);

        $form->decimal('money_min', __('领取最小金额范围'))
            ->rules('required|min:0.000001')
            ->default('0.000000');
        // $form->number('money_max', __('领取最大金额范围'))
        //     ->rules('required|min:1')
        //     ->default(0);
        $form->decimal('money_max', __('领取最大金额范围'))
            ->rules('required|min:0.000001')
            ->default(0);
        $form->select('wid', '领取币种')->options(WalletCode::pluck('code', 'id'))->rules('required', [
            'required' => '币种必须',
        ]);
        //毙掉一些按钮
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });
        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        //保存前回调
        $form->saving(function (Form $form) {
            if ($form->money_min <= 0) {
                return back()->with(admin_toastr('领取最小金额大于0', 'error'));
            }
            if ($form->money_min > $form->money_max) {
                return back()->with(admin_toastr('领取最小金额不能大于最大金额', 'error'));
            }
        });
        //保存后回调
        $form->saved(function (Form $form) {
            \DB::beginTransaction();
            try {
                $id = $form->model()->id;
                $secret_key = $form->model()->secret_key;
                if(!$secret_key){
                    $form->model()->secret_key = 'G' . date('YmdHis') . $id . rand(1000, 9999);
                }
                $form->model()->save();
                \DB::commit();
            }catch (\Exception $exception){
                \DB::rollBack();
                return back()->with(admin_toastr($exception->getMessage(), 'error'));
            }
        });
        return $form;
    }
}
