<?php

namespace App\Admin\Controllers;

use App\Models\AgentUser;
use App\Models\UserAsset;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;

class UserAssetsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客户资产';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserAsset);

        $user = Admin::user();

        $grid->disableCreateButton();
        $grid->disableActions();

        $grid->filter(function ($filter) use ($user) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
                $filter->between('created_at', __('Created at'))->datetime();
                $filter->between('updated_at', __('Updated at'))->datetime();

            });
            $filter->column(1 / 3, function ($filter) {
                $filter->between('balance', __('Balance'));
                $filter->between('frost', __('Frost'));
                $filter->equal('user.recommend_id', __('Recommend'));
            });

            $filter->column(1 / 3, function ($filter) use ($user) {
                if ($user->account_type == AgentUser::ACCOUNT_CENTER) {
                    $filter->equal('user.unit_id', __('Unit'))
                        ->select(AgentUser::where('account_type', 3)->where('center_id', $user->id)->pluck('username', 'id'))
                        ->load('user.agent_id', '/api/agent');
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select()
                        ->load('user.staff_id', '/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }

                if ($user->account_type == AgentUser::ACCOUNT_UNIT) {
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select(AgentUser::where('account_type', 4)->where('unit_id', $user->id)->pluck('username', 'id'))
                        ->load('user.staff_id', '/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }

                if ($user->account_type == AgentUser::ACCOUNT_AGENT) {
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select(AgentUser::where('account_type', 5)->where('agent_id', $user->id)->pluck('username', 'id'));
                }
            });
        });

        //  2运营中心 3会员单位 4代理商 5合伙人
        $types = [
            2 => 'center_id',
            3 => 'unit_id',
            4 => 'agent_id',
            5 => 'staff_id'];
        $grid->model()->whereHas('user', function ($query) use ($user, $types) {
            $query->where($types[$user->account_type], $user->id)->where('is_moni', 0);
        });


        $grid->column('user.account', __('Account'));
        $grid->column('user.name', __('User name'));

        $grid->column('Phone Email', __('Phone Email'))->display(function () {
            $value = $this->user->phone . '/' . $this->user->email;
            return trim($value, '/');
        });

//        $grid->column('walletCode.code', __('Code'));
        $grid->column('account', __('Asset account'))->display(function ($value) {
            if ($value == UserAsset::ACCOUNT_CURRENCY) {
                $account_type = "<span class='label label-info'>资金账户</span>";
            } elseif ($value == UserAsset::ACCOUNT_CONTRACT) {
                $account_type = "<span class='label bg-green'>合约账户</span>";
            } else {
                $account_type = "<span class='label label-warning'>法币账户</span>";
            }
            return $account_type;
        });
        $grid->column('balance', __('Balance'))->sortable()->totalRow(function ($balance) {
            return "<span class='text-danger text-bold'>{$balance} </span>";
        });
        $grid->column('frost', __('Frost'))->sortable()->totalRow(function ($frost) {
            return "<span class='text-danger text-bold'>{$frost} </span>";
        });
        $grid->column('total_recharge', __('Total recharge'))->sortable()->totalRow(function ($total_recharge) {
            return "<span class='text-danger text-bold'>{$total_recharge} </span>";
        });
        $grid->column('total_withdraw', __('Total withdraw'))->sortable()->totalRow(function ($total_withdraw) {
            return "<span class='text-danger text-bold'>{$total_withdraw} </span>";
        });
//        $grid->column('profit_loss', __('Profit'))->sortable()->totalRow(function ($profit_loss) {
//            return "<span class='text-danger text-bold'>{$profit_loss} </span>";
//        });
//        $grid->column('total_fee', __('Fee'))->sortable()->totalRow(function ($total_fee) {
//            return "<span class='text-danger text-bold'>{$total_fee} </span>";
//        });

//        $grid->column('fb_buy', __('Fb buy'))->display(function (){
//            $fbBuy = 0;
//            if ($this->account == UserAsset::ACCOUNT_LEGAL) {
//                $fbBuy = FbTrans::query()->where(['chu_uid' => $this->uid, 'status' => 3])->sum('total_price');
//            }
//            return $fbBuy ?? 0;
//
//        });
//
//        $grid->column('fb_sell', __('Fb sell'))->display(function (){
//            $FbSell = 0;
//            if ($this->account == UserAsset::ACCOUNT_LEGAL) {
//                $FbSell = FbTrans::query()->where(['gou_uid'=> $this->uid, 'status'=> 3])->sum('total_price');
//            }
//
//            return $FbSell ?? 0;
//
//        });

//        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
//        $grid->column('version', __('Version'));
        $grid->disableActions();

        return $grid;
    }

}
