<?php

namespace App\Services;

use App\Events\AfterCreateContractOrder;
use App\Http\Traits\Job;
use App\Models\ContractEntrust;
use App\Models\ContractPosition;
use App\Models\ContractTrans;
use App\Models\ProductsContract;
use App\Models\User;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use Exception;
use Illuminate\Support\Facades\Redis;

class ContractTransService
{
    use Job;

    const WID = 1;//USDT

    /**
     * 持仓数量
     * @param User $user
     * @param int $pid
     * @return int|string|null
     */
    public static function getPosition(User $user, int $pid)
    {
        // position
        // entrust
        $num1 = ContractPosition::where('uid', $user->id)
            ->where('pid', $pid)
            ->sum('buy_num');

        $num2 = ContractEntrust::where('uid', $user->id)
            ->where('pid', $pid)
            ->sum('buy_num');

        return bcMath($num1, $num2, '+');
    }

    /**
     * 个人冻结保证金
     * @param User $user
     * @return mixed
     */
    public static function getDeposit(User $user)
    {
        // 持仓单保证金
        return $user->userPositions()->sum('total_price');
    }

    /**
     * 浮动盈亏
     * @param User $user
     * @return float
     */
    public static function getProfit(User $user)
    {
        $data = $user->userPositions()->select('code', 'buy_num', 'buy_price', 'otype')->get()->toArray();
        $total_profit = 0;
        foreach ($data as $k => $v) {
            $new_price = MarketService::getCodePrice($v['code']);
            if ($v['otype'] == 1) {
                $profit = ($new_price - $v['buy_price']) * $v['buy_num'];
            } else {
                $profit = ($v['buy_price'] - $new_price) * $v['buy_num'];
            }
            $total_profit += $profit;
        }
        return round($total_profit, 4);
    }

    /**
     * 平仓盈亏
     * @param User $user
     * @return mixed
     */
    public static function getTransProfit(User $user)
    {
        return $user->userTrans()->sum('profit');
    }


    /**
     * 设置直止盈止损参数验证
     * @param $otype
     * @param $zy_price
     * @param $zs_price
     * @param $act_price
     * @throws Exception
     */
    public static function checkZyZsParam($otype, $zy_price, $zs_price, $act_price)
    {
        if ($zy_price != 0 && $zs_price != 0) {
            if ($zy_price == $zs_price) {
                throw new Exception(trans('contract.zy_not_eq_zs'));
            }
        }

        // 1 做多  2做空   做多时：止损不能高于现价，止盈不能低于现价。做空时：止损不能低于现价，止盈不能高于现价
        if ($otype == 1) {
            if ($zy_price != 0 && $zy_price < $act_price) {
                throw new Exception(trans('contract.zy_gt_now'));
            }
            if ($zs_price != 0 && $zs_price > $act_price) {
                throw new Exception(trans('contract.zs_lt_now'));
            }
        } else {
            if ($zy_price != 0 && $zy_price > $act_price) {
                throw new Exception(trans('contract.zy_lt_now'));
            }
            if ($zs_price != 0 && $zs_price < $act_price) {
                throw new Exception(trans('contract.zs_gt_now'));
            }
        }
    }

    /**
     * 下单
     * @param $user
     * @param $input
     * @throws Exception
     */
    public function createTransOrder($user, $input)
    {

        $buy_price = $input['buy_price']; //最新价格
        $type = $input['type'];     //1市价 2 限价
        $otype = $input['otype'];    //1涨 2跌

        //$buy_num = $input['buy_num'];   //买入数量

        $code = $input['code']; //产品名称
        $zy_price = isset($input['zy']) ? $input['zy'] : 0; //止盈
        $zs_price = isset($input['zs']) ? $input['zs'] : 0; //止损
        $leverage = $input['leverage'];

        $codeInfo = ProductsContract::where(['code' => $code])
            ->select('id', 'pname', 'code', 'leverage', 'handling_fee', 'sheet_num', 'min_order',
                'max_order', 'max_chicang', 'spread', 'var_price', 'buy_up', 'buy_down')
            ->first();
        if (!$codeInfo){
            throw new Exception(trans('contract.code_no_existent'));
        }
        $codeInfo->toArray();

//        if (empty($codeInfo)) {
//            throw new Exception(trans('contract.code_no_existent'));
//        }

//        if ($otype == 1 && !$codeInfo['buy_up']) {
//            throw new Exception(trans('contract.buy_up_not_allowed'));
//        }
//
//        if ($otype == 2 && !$codeInfo['buy_down']) {
//            throw new Exception(trans('contract.buy_down_not_allowed'));
//        }

        $buy_num = $input['buy_num'] * $codeInfo['sheet_num'];   //买入数量
        if ($buy_num < $codeInfo['min_order']) {
            throw new Exception(trans('contract.min_order', ['min' => $codeInfo['min_order']]));
        }

        if ($buy_num > $codeInfo['max_order']) {
            throw new Exception(trans('contract.max_order', ['max' => $codeInfo['max_order']]));
        }

        if (!in_array($leverage, explode(',', $codeInfo['leverage']))) {

            throw new Exception(trans('contract.leverage_not_allowed', ['lever' => $leverage]));
        }

        $act_price = MarketService::getCodePrice($codeInfo['code']);
        if ($act_price == 0) {
            throw new Exception($codeInfo['code'] . trans('contract.network_price_error'));
        }

        //设置止盈止损参数验证
        self::checkZyZsParam($otype, $zy_price, $zs_price, $act_price);

        $spread = $codeInfo['spread'];
//        $spread = bcMath($codeInfo['var_price'], $codeInfo['spread'], '*');
        if ($type == 1) {  //市价
            //加上点差
            if ($otype == 1) {
                $buy_price = bcMath($act_price, $spread, '+');
            } else {
                $buy_price = bcMath($act_price, $spread, '-');
            }
        }
        //如果下限价单  价格和最新价格相等就转为市价单  1市价 2 限价
        if ($type == 2 && $buy_price == $act_price) {
            //加上点差
            if ($otype == 1) {
                $buy_price = bcMath($act_price, $spread, '+');
            } else {
                $buy_price = bcMath($act_price, $spread, '-');
            }
        }

        //最大持仓限制
        $num1 = self::getPosition($user, $codeInfo['id']);
        if (bcMath($buy_num, $num1, '+') > $codeInfo['max_chicang']) {
            throw new Exception(trans('contract.have_max_order', ['max' => $codeInfo['max_chicang']]));
        }

        //$trans_fee = config('contract.trans_fee') ?? 0; //手续费比例
        $trans_fee = $codeInfo['handling_fee'];

        $total_price = bcMath(($buy_price * $buy_num), $leverage, '/'); //总金

        // $sxfee = bcMath($buy_price, $buy_num, '*') * $trans_fee * 0.01;//建仓手续费
        $sxfee = '0';//tonyang 建仓不收取手续费 平仓收取手续费
        $createData = [
            'uid' => $user->id,
            'pid' => $codeInfo['id'],
            'name' => $codeInfo['pname'],
            'code' => $codeInfo['code'],
            'buy_price' => $buy_price,
            'buy_num' => $buy_num,
            'price' => bcMath($buy_price , $leverage, '/'),
            'total_price' => $total_price,
            'leverage' => $leverage,
            'otype' => $otype,
            'stop_win' => $zy_price,
            'stop_loss' => $zs_price,
            'fee' => $sxfee,
            'deposit' => 0,
            'spread' => $spread,
            'market_price' => $act_price,
            'sheets' => $input['buy_num'],
            'created_at' => now(),
        ];

        //市价单
        if ($type == 1) {
            $model = ContractPosition::query();
        } else {
            $model = ContractEntrust::query();
        }
        $create = $model->create($createData);
        if (!$create) {
            throw new Exception(trans('contract.order_create_failed'));
        }

        $assetService = new AssetService();
        if ($sxfee) {
            $assetService->writeBalanceLog($user->id, $create->id, self::WID, UserAsset::ACCOUNT_CONTRACT, -$sxfee,
                UserMoneyLog::CONTRACT, '合约交易手续费');
        }
        $assetService->writeBalanceLog($user->id, $create->id, self::WID, UserAsset::ACCOUNT_CONTRACT, -$total_price,
            UserMoneyLog::CONTRACT, '合约交易扣款');

        $create['type'] = $type;
        $this->afterCreateTransOrder($create);
    }

    /**
     * 合约下单后业务
     * @param $order
     */
    protected function afterCreateTransOrder($order)
    {
        event(new AfterCreateContractOrder($order));
    }

    /**
     * 撤单
     * @param User $user
     * @param $order_no
     * @throws Exception
     */
    public function revokeOrder(User $user, $order_no)
    {
        $order = $user->userEntrusts()
            ->select('id', 'total_price', 'fee', 'version')
            ->where(['order_no' => $order_no, 'status' => ContractEntrust::STATE_ING])
            ->first();

        if (!$order) {
            throw new Exception(trans('contract.order_not_found'));
        }

        $update = ContractEntrust::query()->where(['id' => $order->id, 'version' => $order->version])
            ->update([
                'status' => ContractEntrust::STATE_REV,
                'version' => $order->version + 1
            ]);
        if ($update === false) {
            throw new Exception(trans('contract.update_failed'));
        }

        $amount = bcMath($order['total_price'], $order['fee'], '+');

        $assetService = new AssetService();
        $assetService->writeBalanceLog($user->id, $order->id, self::WID, UserAsset::ACCOUNT_CONTRACT, $amount,
            UserMoneyLog::CONTRACT, '合约交易撤单');

        $key = 'contract:order:entrusts:' . $order->code;
        self::delCacheOrder($order_no, $key);
    }

    /**
     * 平仓
     * @param User $user
     * @param $order_no
     * @param $pc_type
     * @param $pc_price
     * @throws Exception
     */
    public static function closePosition(User $user, string $order_no, $pc_type = ContractTrans::CLOSE_MANUAL, $pc_price = null)
    {
        $hold_data = $user->userPositions()->where('order_no', $order_no)->first();
        if (!$hold_data) {
            throw new Exception(trans('contract.order_not_found'));
        }
//        $codeInfo = ProductsContract::find($hold_data->pid);
//
//        if (!$codeInfo) {
//            throw new Exception('该币种暂未发布，不能平仓');
//        }
        if (!$pc_price) {
            $pc_price = MarketService::getCodePrice($hold_data->code);
            if (!$pc_price) {
                throw new Exception($hold_data->code . trans('contract.network_price_error'));
            }
        }
        $queue_data['pc_type'] = $pc_type;
        $queue_data['pc_price'] = $pc_price;
        $queue_data['position'] = $hold_data;
        $queue_data['memo'] = ContractTrans::TYPE_CLOSE[$pc_type];

        //  进平仓队列处理
        self::close_position($queue_data);
    }

    /**
     * 委托转持仓
     * @param User $user
     * @param string $order_no
     * @param $new_price
     * @throws Exception
     */
    public static function entrustsToPositions(User $user, string $order_no, $new_price)
    {
        $hold_data = $user->userEntrusts()->where('order_no', $order_no)->first();
        if (!$hold_data) {
            throw new Exception(trans('contract.order_not_found'));
        }

        $queue_data['new_price'] = $new_price;
        $queue_data['entrust'] = $hold_data;

        //  进委托转持仓队列处理
        self::entrusts_positions($queue_data);
    }


    /**
     * 持仓单设置止盈止损
     * @param User $user
     * @param array $input ['zy', 'zs', 'order_no']
     * @throws Exception
     */
    public function setPoint(User $user, array $input)
    {
        $order = $user->userPositions()->whereOrderNo($input['order_no'])->first();

        if (!$order) {
            throw new Exception(trans('contract.order_not_found'));
        }

        $new_price = MarketService::getCodePrice($order->code);
        if (!$new_price) {
            throw new Exception($order->code . trans('contract.network_price_error'));
        }

        self::checkZyZsParam($order->otype, $input['zy'], $input['zs'], $new_price);

        $update = ContractPosition::query()->where(['id' => $order->id, 'version' => $order->version])
            ->update([
                'stop_win' => $input['zy'],
                'stop_loss' => $input['zs'],
                'version' => $order->version + 1
            ]);
        if ($update === false) {
            throw new Exception(trans('contract.update_failed'));
        }

        self::updateCachePoint($input['zy'], $input['zs'], $order->order_no);
    }

    /**
     * 下单加入订单缓存
     * @param $order
     */
    public static function setCacheOrder($order)
    {
        // 订单编号   $order->order_no
        // 币种      $order->code
        // 止盈价     $order->stop_win
        // 止损价     $order->stop_loss
        // 做单时市价  $order->market_price
        // 做单价      $order->buy_price
        // 做单方向     $order->otype
        $redis = Redis::connection('contract');
        $order_no_prefix = substr($order->order_no,0,5);
        if($order_no_prefix == 'ENNUM'){
            //委托单
            $prefix = 'contract:order:entrusts:';
        }else{
            //持仓单
            $prefix = 'contract:order:positions:';
        }
        // if ($order->type == 1) {
        //     //持仓单
        //     $prefix = 'contract:order:positions:';
        // } else {
        //     //委托单
        //     $prefix = 'contract:order:entrusts:';
        // }

        $key = $prefix . $order->code;
        $data = [
            'order_no' => $order->order_no,
            'uid' => $order->uid,
            'code' => $order->code,
            'buy_price' => $order->buy_price,
            'buy_num' => $order->buy_num,
            'stop_win' => $order->stop_win,
            'stop_loss' => $order->stop_loss,
            'market_price' => $order->market_price,
            'otype' => $order->otype,
        ];
        $redis->hMset($data['order_no'], $data);
        $redis->sAdd($key, $data['order_no']);
    }

    /**
     * 删除缓存订单
     * @param string $order_no
     * @param string $key
     */
    public static function delCacheOrder(string $order_no, string $key)
    {
        $redis = Redis::connection('contract');
        $redis->sRem($key, $order_no);
        $redis->del([$order_no]);
    }

    /**
     * 更新止盈止损到缓存订单
     * @param $stop_win
     * @param $stop_loss
     * @param $order_no
     */
    public static function updateCachePoint($stop_win, $stop_loss, $order_no)
    {
        if (!$stop_win && !$stop_loss) {
            return;
        }
        $redis = Redis::connection('contract');
        $redis->hMset($order_no, [
            'stop_win' => $stop_win,
            'stop_loss' => $stop_loss
        ]);
    }
}
