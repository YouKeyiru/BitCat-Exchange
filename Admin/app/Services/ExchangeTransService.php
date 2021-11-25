<?php


namespace App\Services;


use App\Http\Traits\Job;
use App\Models\ExchangeOrder;
use App\Models\ProductsExchange;
use App\Models\User;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\WalletCode;
use Exception;

class ExchangeTransService
{
    use Job;

    /**
     * 下单
     * @param $user
     * @param $input
     * @throws Exception
     */
    public function createTransOrder($user, $input)
    {
        $codeInfo = ProductsExchange::whereCode($input['code'])->first();
        if (!$codeInfo) {
            throw new Exception(trans('exchange.code_no_existent'));
        }

        $code = ProductsExchange::coinCut($input['code']);
        $left_code = $code[0];
        $right_code = $code[1];
        $sell_pid = WalletCode::getWidByCode($left_code);
        $buy_pid = WalletCode::getWidByCode($right_code);

        $sxfee = 0; //下单不处理手续费
        // 买入
        if ($input['type'] == 1) {
            //限价
            if ($input['otype'] == 1) {

                $buy_price = format_price($input['buy_price'], $input['code']);
                $buy_price1 = $buy_price;
                $buy_num = format_price($input['buy_num']);
                $total_price = format_price(bcMath($buy_price, $buy_num, '*'));
                $fee = format_price($total_price * $sxfee * 0.01);

            } else {
                //市价
                $buy_price = '市价';
                $buy_price1 = 0;
                $buy_num = 0;
                $total_price = format_price($input['total_price']);
            }
            $wid = $buy_pid;
            $amount = $total_price;
        } else {
            // 卖出
            if ($input['otype'] == 1) {
                //限价
                $buy_price = format_price($input['buy_price'], $input['code']);
                $buy_price1 = $buy_price;
                $buy_num = format_price($input['buy_num']);
                $total_price = 0;

            } else {
                //市价
                $buy_price = '市价';
                $buy_price1 = 0;
                $buy_num = format_price($input['total_num']);;
                $total_price = 0;

            }
            $wid = $sell_pid;
            $amount = $buy_num;
        }

        if ($amount <= 0) {
            throw new \Exception(trans('exchange.volume_too_small'));
        }

        $create = ExchangeOrder::create([
            'uid'         => $user->id,
            'pid'         => $codeInfo->id,
            'code'        => $codeInfo->code,
            'symbol'      => $codeInfo->pname,
            'wtprice'     => $buy_price,
            'wtprice1'    => $buy_price1,
            'wtnum'       => $buy_num,
            'total_price' => $total_price,
            'fee'         => 0,
            'type'        => $input['type'],
            'otype'       => $input['otype'],
            'l_code'      => $left_code,
            'r_code'      => $right_code,
            'l_wid'       => $sell_pid,
            'r_wid'       => $buy_pid,
        ]);
        if (!$create) {
            throw new \Exception(trans('exchange.order_create_failed'));
        }

        $assetService = new AssetService();
        $assetService->writeBalanceLog($user->id, $create->id, $wid, UserAsset::ACCOUNT_CURRENCY, -$amount,
            UserMoneyLog::EXCHANGE, '币币交易下单');

        $this->afterCreateTransOrder($create);
    }

    /**
     * 币币下单后业务
     * @param ExchangeOrder $order
     */
    protected function afterCreateTransOrder(ExchangeOrder $order)
    {
        $params = [
            'type' => 'order',
            'data' => [
                'type'     => $order->otype == 1 ? 'limit' : 'market', //限价单limit,市价单market
                'side'     => $order->type == 1 ? 'bid' : 'ask', //买单bid,卖单ask
                'quantity' => $order->wtnum, //订单数量
                'price'    => $order->otype == 1 ? $order->wtprice1 : $order->total_price, //价格  如果是市价这里是总金额
                'market'   => $order->code, //交易市场
                'user_id'  => $order->uid,
                'order_id' => $order->id,
            ]
        ];
        $this->MatchOrder(json_encode($params));

//        event(new AfterCreateExchangeOrder($order));
    }

    /**
     * 撤单
     * @param User $user
     * @param string $order_no
     * @throws Exception
     */
    public function revokeOrder(User $user, string $order_no)
    {
        $order = $user->userExchange()->whereOrderNo($order_no)
            ->whereIn('status', [ExchangeOrder::WAIT_TRANS, ExchangeOrder::ING_TRANS])
            ->first();
        if (!$order) {
            throw new \Exception(trans('exchange.order_not_found'));
        }

        $update = ExchangeOrder::query()->where(['id' => $order->id, 'version' => $order->version])
            ->update([
                'status'  => ExchangeOrder::REVOKE_TRANS,
                'version' => $order->version + 1
            ]);
        if ($update === false) {
            throw new Exception(trans('exchange.update_failed'));
        }
        $sell_pid = WalletCode::getWidByCode($order->l_code);
        $buy_pid = WalletCode::getWidByCode($order->r_code);

        $assetService = new AssetService();
        //买
        if ($order->type == 1) {
            //没有交易的
            if ($order->status == ExchangeOrder::WAIT_TRANS) {
                $amount = bcMath($order->totalprice, $order->fee, '+');
                $assetService->writeBalanceLog($user->id, $order->id, $buy_pid, UserAsset::ACCOUNT_CURRENCY, $amount,
                    UserMoneyLog::EXCHANGE, trans('exchange.order_cancel'));
            } else {
                //部分交易的，退部分金额
                $total_price = bcMath($order->cjprice, $order->cjnum, '*');
                $back_price = bcMath($order->totalprice, $total_price, '-');

                //币种手续费
                $sxfee = WalletCode::getExchangeFeeById($sell_pid);

                $cjfee = $order->cjnum * $sxfee * 0.01;
                $amount = bcMath($order->cjnum, $cjfee, '-');

                if ($back_price > 0) {
                    $assetService->writeBalanceLog($user->id, $order->id, $buy_pid, UserAsset::ACCOUNT_CURRENCY, $back_price,
                        UserMoneyLog::EXCHANGE, trans('exchange.buy_revoke_profit'));
                }
                if ($amount > 0) {
                    $assetService->writeBalanceLog($user->id, $order->id, $sell_pid, UserAsset::ACCOUNT_CURRENCY, $amount,
                        UserMoneyLog::EXCHANGE, trans('exchange.buy_profit'));
                }
                if ($cjfee > 0) {
                    $order->fee = $cjfee;
                    $order->save();
                }
            }
        } else {
            //卖单
            //无成交
            if ($order->status == ExchangeOrder::WAIT_TRANS) {
                //全部退还卖出数量
                $assetService->writeBalanceLog($user->id, $order->id, $sell_pid, UserAsset::ACCOUNT_CURRENCY, $order->wtnum,
                    UserMoneyLog::EXCHANGE, trans('exchange.sell_revoke_profit'));
            } else {
                //部分成交
                //退还数量 = 委托数量 - 成交数量
                $back_num = bcMath($order->wtnum, $order->cjnum, '-');
                if ($back_num > 0) {
                    $assetService->writeBalanceLog($user->id, $order->id, $sell_pid, UserAsset::ACCOUNT_CURRENCY, $back_num,
                        UserMoneyLog::EXCHANGE, trans('exchange.sell_revoke_profit'));
                }
                //成交收益 = (成交均价 * 成交数量) - 手续费
                $total_price = bcMath($order->cjprice, $order->cjnum, '*');
                $sxfee = WalletCode::getExchangeFeeById($buy_pid);
                $cjfee = $total_price * $sxfee * 0.01;
                $back_price = bcMath($total_price, $cjfee, '-');
                if ($back_price > 0) {
                    $assetService->writeBalanceLog($user->id, $order->id, $buy_pid, UserAsset::ACCOUNT_CURRENCY, $back_price,
                        UserMoneyLog::EXCHANGE, trans('exchange.sell_profit'));
                }
                $order->totalprice = $total_price;
                if ($cjfee > 0) {
                    $order->fee = $cjfee;
                }
                $order->save();
            }
        }
        $order->status = ExchangeOrder::REVOKE_TRANS;
        $order->save();

        /*
       {
           "type":"cancel",	//数据类型
           "data":{			//订单的详细内容
               "side":"ask",	//买单bid,卖单ask
               "market":"BTC/USDT",	//交易市场
               "order_id":"100002"	//要删除的订单编号
           }
       }
    */
//        if ($order->otype == 1) {
//            $params = [
//                'type' => 'cancel',
//                'data' => [
//                    'side'     => $order->type == 1 ? 'bid' : 'ask',
//                    'market'   => $order->product->code,
//                    'order_id' => $order->id,
//                ],
//            ];
//            $result = MatchEngineService::run(json_encode($params));
//            if ($result['code'] != 1) {
//                Log::error(json_encode($params) . $result['err']);
////                throw new \Exception($result['err']);
//            }
//        }
//
    }

}
