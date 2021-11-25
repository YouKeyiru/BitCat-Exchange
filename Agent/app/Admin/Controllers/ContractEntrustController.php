<?php

namespace App\Admin\Controllers;

use App\Models\AgentUser;
use App\Models\ContractEntrust;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;

class ContractEntrustController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '会员委托单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ContractEntrust);
        $grid->disableExport(false);
        $grid->disableCreateButton();

        $admin = Admin::user();
        $grid->filter(function ($filter) use ($admin) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) use ($admin) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
                $filter->equal('recommend.account', __('Recommend'));
                $filter->between('created_at', '创建时间')->datetime();

            });
            $filter->column(1 / 2, function ($filter) use ($admin) {
                if ($admin->account_type == 4) {
                    $filter->equal('user.unit_id', __('Unit'))
                        ->select(AgentUser::where('account_type', 3)->pluck('username', 'id'))
                        ->load('user.agent_id', '/api/agent');
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select()
                        ->load('user.staff_id', '/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();

                }

                if ($admin->account_type == 3) {
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select(AgentUser::where('account_type', 2)->pluck('username', 'id'))
                        ->load('user.staff_id', '/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }

                if ($admin->account_type == 2) {
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select(AgentUser::where('id', $admin->id)->pluck('username', 'id'))
                        ->load('user.staff_id', '/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }

            });

        });

        $user = Admin::user();
        $types = [
            2 => 'center_id',
            3 => 'unit_id',
            4 => 'agent_id',
            5 => 'staff_id'];
        $grid->model()->whereHas('user', function ($query) use ($user, $types) {
            $query->where($types[$user->account_type], $user->id);
        });
        $grid->model()->orderBy('id', 'desc');
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
        $grid->column('user.name', __('User name'));
        $grid->column('order_no', __('Ordnum'));
        $grid->column('code', __('Pname'))->label();
        $grid->column('buy_price', __('Buy price'));
        $grid->column('buy_num', __('Buy num'));
        $grid->column('total_price', __('Deposit'))->totalRow(function ($total_price) {
            return "<span class='text-danger text-bold'>{$total_price} </span>";
        });
        $grid->column('deposit', __('Total price'))->display(function () {
            return bcmul($this->buy_num, $this->buy_price, 6);
        });
        $grid->column('fee', __('Fee'))->totalRow(function ($fee) {
            return "<span class='text-danger text-bold'>{$fee} </span>";
        });
        $grid->column('otype', __('Type'))->display(function ($otype) {
            return $otype == 1 ? '买涨' : '买跌';
        });
        $grid->column('status', __('Status'))->display(function ($status) {
            //1 委托中 2已完成 3已取消
            $type1 = '<span class="label label-primary">委托中</span>';
            $type2 = '<span class="label label-success">已完成</span>';
            $type3 = '<span class="label label-warning">已取消</span>';
            switch ($status) {
                case 1:
                    return $type1;
                    break;
                case 2:
                    return $type2;
                    break;
                case 3:
                    return $type3;
                    break;
                default:
                    return 'ERROR';
                    break;
            }
        });
        $grid->column('leverage', __('Leverage'));
        $grid->column('created_at', __('Created at'));
        $grid->column('handle_at', __('Trade at'));
//        $grid->exporter(new EntrustExporter());
        // 全部关闭
        $grid->disableActions();

        return $grid;
    }


}
