<?php

namespace App\Admin\Controllers;

use App\Models\FbPay;
use App\Models\FbTrans;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;

class FbTransController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '交易订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FbTrans);
        $admin = Admin::user();
        $grid->fixColumns(3, -3);
        $grid->disableCreateButton();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('order_no', __('Ordnum'));
                $filter->equal('jy_order', __('Jy order'));
                $filter->equal('status', __('Status'))->select([
                    1 => '待付款',
                    2 => '已付款',
                    3 => '确认完成',
                    4 => '申诉中',
                    5 => '取消',
                    6 => '冻结',
                ]);
                $filter->between('created_at', '创建时间')->datetime();
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('chu.account', __('Chu user'));
                $filter->equal('gou.account', __('Gou user'));
            });
        });

        //  2运营中心 3会员单位 4代理商 5合伙人
        $types = [
            2 => 'center_id',
            3 => 'unit_id',
            4 => 'agent_id',
            5 => 'staff_id'];

        $ids = User::query()->where($types[$admin->account_type], $admin->id)->pluck('id')->toArray();
        $where_chu[] = [function ($query) use ($ids) {
            $query->whereIn('chu_uid', $ids)->orWhereIn('gou_uid', $ids);
        }];

        $grid->model()->where($where_chu);

        $grid->model()->orderBy('id', 'desc');
//        $grid->column('id', __('Id'));
        $grid->column('order_no', __('Ordnum'));
        $grid->column('jy_order', __('Jy order'));
        $grid->column('chu.account', __('Chu user'));
        $grid->column('gou.account', __('Gou user'));
        $grid->column('cancel.account', __('Cancel user'));
        //1待付款 2已付款 3已确认完成 4 申诉中 5取消 6冻结
        $grid->column('status', __('Status'))->using([
            1 => '待付款',
            2 => '已付款',
            3 => '确认完成',
            4 => '申诉中',
            5 => '取消',
            6 => '冻结',
        ], '未知')->label([
            1 => 'info',
            2 => 'primary',
            3 => 'success',
            4 => 'warning',
            5 => 'danger',
            6 => 'warning',
        ], 'warning');

        $grid->column('price', __('Price'));
        $grid->column('total_num', __('Total num'))->totalRow(function ($total_num) {
            return "<span class='text-danger text-bold'>{$total_num} </span>";
        });
        $grid->column('total_price', __('Total price'));
        $grid->column('sxfee', __('Fee'));
        $grid->column('pay_method', __('Pay method'))->display(function ($pay_method) {
            $pay_arr = explode(',', $pay_method);
            $pay_str = '';
            foreach ($pay_arr as $value) {
                $pay_str .= FbPay::PAYMENT_TYPE[$value] . '/';
            }
            return rtrim($pay_str, '/');
        });
//        $grid->column('refer', __('Refer'));
        $grid->column('min_price', __('Min order'));
        $grid->column('max_price', __('Max order'));
//        $grid->column('pay_bank', __('Bank'))->bool();
//        $grid->column('pay_alipay', __('Alipay'))->bool();
//        $grid->column('pay_wx', __('Wx'))->bool();
//        $grid->column('pay_at', __('Pay at'));
//        $grid->column('checked_at', __('Checked at'));
//        $grid->column('cancel_at', __('Cancel at'));
//        $grid->column('freeze_at', __('Freeze at'));
//        $grid->column('type', __('Type'))->using([
//            1 => '出售',
//            2 => '购买',
//        ], '未知')->label([
//            1 => 'danger ',
//            2 => 'success',
//        ], 'warning');
        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));


        // 全部关闭
        $grid->disableActions();

        return $grid;
    }


}
