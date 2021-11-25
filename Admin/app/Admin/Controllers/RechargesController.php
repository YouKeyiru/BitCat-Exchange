<?php

namespace App\Admin\Controllers;

use App\Models\AgentUser;
use App\Models\Recharge;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\WalletCode;
use App\Services\AssetService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\User;
use UserAssets;

class RechargesController extends AdminController
{

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户充值';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Recharge);
        $grid->disableExport(false);

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
                $filter->equal('wallet_address', __('Address'));

            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('type', __('Rec Type'))->select(Recharge::TYPE_STATUS);
                $filter->equal('account', '账户')->select(UserAsset::ACCOUNT_TYPE);
                $filter->equal('wid', '币种')->select(WalletCode::pluck('code', 'id'));
            });
            $filter->column(1 / 3, function ($filter) {

                $filter->between('created_at', __('Created at'))->datetime();
                $filter->between('updated_at', __('Updated at'))->datetime();
            });
        });

        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'));
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
        $grid->column('user.name', __('User name'));
        $grid->column('order_no', __('Ordnum'));

        $grid->column('account', '账户')->using(UserAsset::ACCOUNT_TYPE);

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
        $grid->column('mark', __('Mark'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        // 全部关闭
        $grid->disableActions();
//        $grid->exporter(new RechargesExporter());

        return $grid;
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Recharge);
        $form->ignore(['recharge_type']);
        $form->text('uid', '账号\手机号')->required();
        $form->currency('amount', __('Money'))
            ->rules('required|min:1')
            ->default(0.0000)
            ->digits(6)
            ->symbol('$');
        $form->hidden('status', __('Status'))->default(2);
        $form->hidden('type', __('Type'))->default(1);

        $form->select('account', '账户')->options(UserAsset::ACCOUNT_TYPE)->rules('required', [
            'required' => '账户必须',
        ]);
        $form->select('wid', '币种')->options(WalletCode::pluck('code', 'id'))->rules('required', [
            'required' => '币种必须',
        ]);

        $form->text('mark', __('Mark'))->default('后台充值');

        $recharge_type = [1 => '充值', 2 => '扣除'];
        $form->radio('recharge_type', __('Recharge type'))
            ->options($recharge_type)
            ->rules('required')
            ->default(1);

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
            if ($form->amount <= 0) {
                return back()->with(admin_toastr('充值金额大于0', 'error'));
            }

            if (!array_key_exists($form->account,UserAsset::ACCOUNT_TYPE)){
                return back()->with(admin_toastr('选择账户不存在'.$form->account, 'error'));
            }

            $account = $form->uid;
            $user = User::where('account', $account)
                ->orWhere('phone', $account)
                ->orWhere('email', $account)
                ->first();
            if (empty($user)) {
                return back()->with(admin_toastr('用户不存在', 'error'));
            }
            $form->uid = $user->id;
            if (request('recharge_type') == 2) {
                $form->amount = $form->amount * (-1);
            }

        });

        //保存后回调
        $form->saved(function (Form $form) {

            \DB::beginTransaction();
            try {
                $id = $form->model()->id;
                $form->model()->order_no = 'RE' . date('YmdHis') . $id . rand(1000, 9999);
                $form->model()->arrival_at = now();
                $form->model()->save();

                $asset = AssetService::_getBalance($form->model()->uid, $form->model()->wid, $form->account);
                $money = $form->model()->amount;
                $asset->total_recharge += $form->model()->amount;
                $asset->save();

                $assetService = new AssetService();

                $assetService->writeBalanceLog($form->model()->uid, $id, $form->model()->wid, $form->account, $money,
                    UserMoneyLog::ADMIN_RECHARGE, $form->mark);
                \DB::commit();
            }catch (\Exception $exception){

                \DB::rollBack();
                return back()->with(admin_toastr($exception->getMessage(), 'error'));
            }
        });

        return $form;
    }
}
