<?php

namespace App\Admin\Controllers;

use App\Models\AgentUser;
use App\Models\Recharge;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\User;

class RechargesController extends AdminController
{

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '入金明细';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Recharge);
        $grid->disableCreateButton();
        $grid->disableExport(false);

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
                $filter->equal('wallet_address', __('Address'));
//                $filter->equal('wid', '币种')->select(WalletCode::pluck('code', 'id'));

            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('type', __('Rec Type'))->select(Recharge::TYPE_STATUS);

//                $filter->equal('status', __('Status'))->select([
//                    Recharge::WAIT_PAY => '未支付',
//                    Recharge::PAYED    => '已支付',
//                ]);

                $filter->between('created_at', __('Created at'))->datetime();
                $filter->between('updated_at', __('Updated at'))->datetime();
            });
        });
        $admin = Admin::user();
        //  2运营中心 3会员单位 4代理商 5合伙人
        $types = [
            2 => 'center_id',
            3 => 'unit_id',
            4 => 'agent_id',
            5 => 'staff_id'];
        $grid->model()->whereHas('user', function ($query) use ($admin, $types) {
            $query->where($types[$admin->account_type], $admin->id)->where('is_moni', 0);
        });

        $grid->model()->orderBy('id', 'desc');
//        $grid->column('id', __('Id'));
        $grid->column('user.account', __('Account'));
        $grid->column('Phone Email', __('Phone Email'))->display(function () {
            $phone = $this->user->phone ? substr_replace($this->user->phone,'****',3,4) : '';
            $email = $this->user->email ?? '';
            $value = $phone . '/' . $email;
            return trim($value, '/');
        });
        $grid->column('user.name', __('User name'));
        $grid->column('order_no', __('Ordnum'));
        $grid->column('type', __('Type'))->display(function($type){
            $check1 = '<span class="label label-danger">ONMI</span>';
            $check2 = '<span class="label label-success">ERC20</span>';
            $check3 = '<span class="label label-success">后台充值</span>';
            if ($type == 1) {
                $address = $check3;
            } else {
                $first = substr($this->address , 0 , 1);
                if($first == '0') {
                    // erc20
                    $address = $check2;
                } else {
                    // omni
                    $address = $check1;
                }
            }
            return $address;
        });

        $grid->column('wallet_address', __('Address'))->modal(__('Address'), function ($model) {

            return $model->hash;
        });

        $grid->column('wallet.code', '币种');
        $grid->column('amount', __('Money'))->totalRow(function ($money) {
            return "<span class='text-danger text-bold'>{$money} </span>";
        });

        $grid->column('status', __('Status'))->display(function ($status) {
            $type0 = '<span class="label label-warning">确认中</span>';
            $type1 = '<span class="label label-danger">未支付</span>';
            $type2 = '<span class="label label-success">已支付</span>';
            switch ($status) {
                case 1:
                    if ($this->hash) {
                        return $type0;
                        break;
                    }
                    return $type1;
                    break;
                case 2:
                    return $type2;
                    break;
                default:
                    return 'ERROR';
                    break;
            }
        });
        $grid->column('mark', __('Mark'))->display(function($mark){
            if(!$mark){
                $mark = '地址充值';
            }

            return $mark;
        });
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        // 全部关闭
        $grid->disableActions();
//        $grid->exporter(new RechargesExporter());

        return $grid;
    }

}
