<?php

namespace App\Admin\Actions\Moni;

use App\Models\AgentAssets;
use App\Http\Traits\WriteAgentMoneyLog;
use App\Models\Recharge;
use App\Models\UserMoneyLog;
use App\Services\AssetService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class MoniRecharge extends RowAction
{
    use WriteAgentMoneyLog;

    public $name = '充值';

    public function handle(Model $model, Request $request)
    {
        $amount = $request->get('money');
        $account = $request->get('account');
    	if($request->get('recharge_type') == 2){
            $amount = $amount * (-1);
    	}
        
        $recharge = Recharge::create([
                'order_no' => 'RE' . date('YmdHis') . $model->id . rand(1000, 9999),
                'uid' => $model->id,
                'wid' => 1,
                'code' => 'usdt',
                'status' => $request->get('status'),
                'type' => 1, // 后台充值
                'amount' => $amount,
                'account' => $account,
                'mark' => $request->get('mark'),
                'arrival_at' => now(),
        ]);

        $asset = AssetService::_getBalance($model->id, 1, $account);
        $money = $amount;
        $asset->total_recharge += $amount;
        $asset->save();

        $assetService = new AssetService();


        $assetService->writeBalanceLog($model->id, $recharge->id, 1, $account, $money,
            UserMoneyLog::ADMIN_RECHARGE, '后台充值');
        
        return $this->response()->success('充值成功')->refresh();
    }

    public function form()
	{
	    $this->hidden('status', __('Status'))->default(2);
        $this->hidden('type', __('Type'))->default(1);
        $this->text('money', __('Money'))
        ->rules('required|min:1')
        ->default(0.00);
        $this->text('mark', __('Mark'))->default('模拟会员充值');

        $account = [1 => '资金账户', 2 => '合约账户', 3=>'法币账户'];
        $this->radio('account','充值账户')
            ->options($account)
            ->rules('required')
            ->default(1);

        $recharge_type = [1 => '充值', 2 => '扣除'];
        $this->radio('recharge_type',__('Recharge type'))
            ->options($recharge_type)
            ->rules('required')
            ->default(1);
	}

}