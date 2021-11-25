<?php

namespace App\Admin\Controllers;

use App\Models\AgentAssets;
use App\Admin\Actions\Agent\Withdraw;
use App\Admin\Actions\Agent\WithdrawList;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AgentAssetsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '我的资产';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $admin = Admin::user();
        $grid = new Grid(new AgentAssets);

        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disablePagination();

        $grid->model()->where('uid',$admin->id);
        $grid->column('user.username', __('Username'));
        $grid->column('deposit', __('Deposit'));
        $grid->column('balance', __('Balance'));
        $grid->column('frost', __('Frost'));
        $grid->column('total_recharge', __('Total recharge'));
        $grid->column('total_withdraw', __('Total withdraw'));
        $grid->column('total_commission', __('Fee'));
        $grid->column('profit_and_loss', __('Profit'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        
        $grid->disableActions();

        if($admin->account_type > 1){
            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(new Withdraw());
            });
            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(new WithdrawList());
            });
        }

        return $grid;
    }

}
