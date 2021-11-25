<?php

namespace App\Services;

use App\Events\AfterConfirmOrder;
use App\Events\AfterCreateTransOrder;
use App\Models\FbAppeal;
use App\Models\FbBuying;
use App\Models\FbPay;
use App\Models\FbSell;
use App\Models\FbTrans;
use App\Models\FbShopApply;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use Carbon\Carbon;
use Exception;

class FbTransService
{

    /**
     * 下单
     * @param $user
     * @param $post
     * @return mixed
     * @throws Exception
     */
    public function createTransOrder($user, $post)
    {
        if ($post['order_type'] == 1) {
            $model = new FbSell();
        } else {
            $model = new FbBuying();
        }

        $chu_order = $model->whereOrderNo($post['order_no'])->first();
        if (!$chu_order) {
            throw new Exception(trans('fb.order_not_found'));
        }

        if ($user->id == $chu_order->uid) {
            throw new Exception(trans('fb.cannot_operate_own_order'));
        }

        //成交数量等于交易数量
        if ($post['total_num'] > ($chu_order->surplus_num) || $chu_order->deals_num == $chu_order->trans_num) {
            throw new Exception(trans('fb.amount_not_enough'));
        }
        $total_price = bcMath($post['total_num'], $chu_order->price, '*', 4);

//        if ($total_price != round($post['total_price'], 2)) {
//            throw new Exception('数量计算异常');
//        }

        $oid = $chu_order->id;
        //获取手续费
        $fee = $this->getTransFee();
        $sxFee = bcMath($post['total_num'], $fee * 0.01, '*');

//        $chu_user = User::find($chu_order->uid);
        if ($post['order_type'] == 1) {
            //下单人是买家
            $order_info['chu_uid'] = $chu_order->uid;
            $order_info['gou_uid'] = $user->id;
//            $order_info['tpath1'] = $chu_user->relationship;
//            $order_info['tpath2'] = $user->relationship;
        } else {
            $order_info['chu_uid'] = $user->id;
            $order_info['gou_uid'] = $chu_order->uid;
//            $order_info['tpath1'] = $chu_user->relationship;
//            $order_info['tpath2'] = $user->relationship;
        }
        $order_info['wid'] = $chu_order->wid;
        $order_info['jy_order'] = $chu_order->order_no;
        $order_info['price'] = $chu_order->price;
        $order_info['total_num'] = $post['total_num'];
        $order_info['total_price'] = $total_price;
        $order_info['refer'] = mt_rand(1000, 9999);
        $order_info['order_type'] = $post['order_type'];
        $order_info['pay_method'] = $post['pay_method'];
        $order_info['min_price'] = $chu_order->min_price;
        $order_info['max_price'] = $chu_order->max_price;
        $order_info['sxfee'] = $sxFee;
        //订单数据
        $create = FbTrans::create($order_info);


        $inc = $model->where('id', $oid)->increment('deals_num', $order_info['total_num']);
        if (!$inc) {
            throw new Exception(trans('fb.inc_cj_amount_failed'));
        }

        $inc = $model->where('id', $oid)->decrement('surplus_num', $order_info['total_num']);
        if (!$inc) {
            throw new Exception(trans('fb.inc_sy_amount_failed'));
        }

        if ($order_info['sxfee'] > 0) {
            //减成交手续费
            $dec = $model->where('id', $oid)->decrement('sxfee', $order_info['sxfee']);
            if (!$dec) {
                throw new Exception(trans('fb.dec_cj_fee_failed'));
            }
        }

        //出售下单 扣除自己的钱包余额
        if ($post['order_type'] == 2) {
            $assetService = new AssetService();

            $assetService->writeBalanceLog($user->id, $create->id, $chu_order['wid'], UserAsset::ACCOUNT_LEGAL, -$post['total_num'],
                UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '出售下单-减少余额');

            $assetService->writeFrostLog($user->id, $create->id, $chu_order['wid'], UserAsset::ACCOUNT_LEGAL, $post['total_num'],
                UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '出售下单-增加冻结');

            //手续费
            if ($order_info['sxfee'] > 0) {
                $assetService->writeBalanceLog($user->id, $create->id, $chu_order['wid'], UserAsset::ACCOUNT_LEGAL, -$order_info['sxfee'],
                    UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '出售下单-手续费');
            }
        }

        $this->afterCreateTransOrder($create);

        return $create;
    }

    /**
     * 取消订单
     * @param $user
     * @param $order
     * @throws Exception
     */
    public function cancelOrder($user, $order)
    {
        $assetService = new AssetService();
        $result = FbTrans::query()->where([
            'id'      => $order->id,
            'status'  => FbTrans::ORDER_APPEAL,
            'version' => $order->version
        ])->update([
            'status'     => FbTrans::ORDER_CANCEL,
            'cancel_at'  => now(),
            'cancel_uid' => $user->id,
            'version'    => $order->version + 1
        ]);
        if ($result === false) {
            throw new Exception(trans('fb.update_failed'));
        }

        if ($order->order_type == 2) {
            $assetService->writeBalanceLog($order->chu_uid, $order->id, $order->wid, UserAsset::ACCOUNT_LEGAL,
                bcMath($order->total_num, $order->sxfee, '+'), UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '交易单取消-增加余额');
            $assetService->writeFrostLog($order->chu_uid, $order->id, $order->wid, UserAsset::ACCOUNT_LEGAL,
                -$order->total_num, UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '交易单取消-减少冻结');
        }
        if ($order->order_type == 1) {
            $model = new FbSell();
        } else {
            $model = new FbBuying();
        }
        $jyOrder = $model->where('order_no', $order->jy_order)->first();
        // 减成交数量
        $dec = $jyOrder->decrement('deals_num', $order->total_num);
        // 加剩余数量
        $inc = $jyOrder->increment('surplus_num', $order->total_num);
        if (!$dec || !$inc) {
            throw new Exception(trans('fb.dec_cj_failed'));
        }
        if ($order->sxfee > 0) {
            // 返还手续费
            $inc_fee = $jyOrder->increment('sxfee', $order->sxfee + 0);
            if (!$inc_fee) {
                throw new Exception(trans('fb.back_fee_failed'));
            }
        }
    }

    /**
     * 确认订单
     * @param $user
     * @param $order
     * @throws Exception
     */
    public function confirmOrder($user, $order)
    {
        $assetService = new AssetService();
        $result = FbTrans::query()->where([
            'id'      => $order->id,
            'status'  => FbTrans::ORDER_APPEAL,
            'version' => $order->version
        ])->update([
            'status'     => FbTrans::ORDER_OVER,
            'checked_at' => now(),
            'version'    => $order->version + 1
        ]);

        if ($result === false) {
            throw new Exception(trans('fb.update_failed'));
        }

        //给购买人加余额
        $assetService->writeBalanceLog($order->gou_uid, $order->id, $order->wid, UserAsset::ACCOUNT_LEGAL,
            $order->total_num, UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '交易购买-增加余额');
        //减出售人冻结金额
        $assetService->writeFrostLog($order->chu_uid, $order->id, $order->wid, UserAsset::ACCOUNT_LEGAL,
            -$order->total_num, UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '交易出售-扣除冻结');

        $this->afterConfirmOrder($order);
    }

    /**
     * 撤销发布单
     * @param $user
     * @param $post
     * @throws Exception
     */
    public function revokeOrder($user, $post)
    {
        $assetService = new AssetService();
        if ($post['order_type'] == 1) {
            $model = new FbSell();
        } else {
            $model = new FbBuying();
        }
        // status 1 进行中 2完成 3撤单
        $order = $model->where('order_no', $post['order_no'])
            ->where('uid', $user->id)
            ->where('status', 1)
            ->first();
        if (empty($order)) {
            throw new Exception(trans('fb.order_not_found'));
        }

        $trans = FbTrans::query()
            ->where('jy_order', $order->order_no)
            ->whereIn('status', [1, 2, 4])
            ->get();
        if ($trans->isNotEmpty()) {
            throw new Exception(trans('fb.have_order_not_trans'));
        }

        $result = $model->where([
            'id'      => $order->id,
            'version' => $order->version
        ])->update([
            'status'    => 3,
            'cancel_at' => now(),
            'version'   => $order->version + 1
        ]);
        if (!$result) {
            throw new Exception(trans('fb.update_failed'));
        }
        if ($post['order_type'] == 1) {
            $dec = bcMath($order->trans_num, $order->deals_num, '-');
            $inc = bcMath($dec, $order->sxfee, '+');

            $assetService->writeBalanceLog($user->id, $order->id, $order->wid, UserAsset::ACCOUNT_LEGAL,
                $inc, UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '出售下单撤单-增加余额');
            $assetService->writeFrostLog($user->id, $order->id, $order->wid, UserAsset::ACCOUNT_LEGAL,
                -$dec, UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '出售下单撤单-减少冻结');
        }
    }

    /**
     * 发布买单
     * @param $user
     * @param $post
     */
    public function createBuyOrder($user, $post)
    {
        FbBuying::create([
            'uid'         => $user->id,
            'trans_num'   => $post['trans_num'],
            'surplus_num' => $post['trans_num'],
            'price'       => $post['price'],
            'total_price' => $post['totalPrice'],
            'sxfee'       => $post['sxFee'],
            'min_price'   => $post['min_price'],
            'max_price'   => $post['max_price'],
            'pay_method'  => $post['pay_method'],
            'notes'       => isset($post['notes']) ? $post['notes'] : '',
            'wid'         => $post['wid'],
        ]);
    }

    /**
     * 发布卖单
     * @param $user
     * @param $post
     * @throws Exception
     */
    public function createSellOrder($user, $post)
    {
        $assetService = new AssetService();
        $create = FbSell::create([
            'uid'         => $user->id,
            'trans_num'   => $post['trans_num'],
            'surplus_num' => $post['trans_num'],
            'price'       => $post['price'],
            'total_price' => $post['totalPrice'],
            'sxfee'       => $post['sxFee'],
            'min_price'   => $post['min_price'],
            'max_price'   => $post['max_price'],
            'pay_method'  => $post['pay_method'],
            'notes'       => isset($post['notes']) ? $post['notes'] : '',
            'wid'         => $post['wid'],
        ]);

        //操作资产
        $assetService->writeBalanceLog($user->id, $create->id, $post['wid'], UserAsset::ACCOUNT_LEGAL,
            -$post['trans_num'], UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '交易出售发单-扣除余额');

        $assetService->writeFrostLog($user->id, $create->id, $post['wid'], UserAsset::ACCOUNT_LEGAL,
            $post['trans_num'], UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '交易出售发单-转入冻结');

        if ($post['sxFee'] > 0) {
            $assetService->writeBalanceLog($user->id, $create->id, $post['wid'], UserAsset::ACCOUNT_LEGAL,
                -$post['sxFee'], UserMoneyLog::BUSINESS_TYPE_FB_ORDER, '交易出售发单手续费');
        }
    }

    /**
     * 交易手续费
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|int|mixed
     */
    public function getTransFee()
    {
        return config('fb.fee') ?? 0;
    }

    /**
     * 获取配置倒计时
     * @param $type
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|int|mixed
     */
    public static function getCountDown($type)
    {
        if ($type == 1) {
            $down_time = config('fb.qx_time') ?? 1;//自动取消时间
        } else {
            $down_time = config('fb.qr_time') ?? 1;//自动确认时间
        }
        return $down_time;
    }

    /**
     * 计算倒计时
     * @param $order_info
     * @return float|int
     * @throws Exception
     */
    public function countDown($order_info): int
    {
        $countDown = 1;
        switch ($order_info->status) {
            case 1:
                $down_time = self::getCountDown(1);//自动取消时间
                $created_at = Carbon::parse($order_info->created_at)->timestamp;
                $countDown = $created_at + $down_time * 60 - time();
                if ($countDown <= 0) {
                    $countDown = 0;
                }
                break;
            case 2:
                $down_time = self::getCountDown(2);//自动确认时间
                $pay_at = Carbon::parse($order_info->pay_at)->timestamp;
                $countDown = $pay_at + $down_time * 60 - time();
                if ($countDown <= 0) {
                    $countDown = 0;
                }
                break;
        }
        return $countDown;
    }

    /**
     * 查询收款方式
     * @param $uid
     * @param $pay_method
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getPayment($uid, $pay_method)
    {
        return FbPay::query()
            ->select('id as payment_id', 'payment_type', 'auth_name', 'bank', 'branch', 'card_num', 'qrcode')
            ->where('uid', $uid)
            ->where('payment_type', $pay_method)
            ->first();
    }

    /**
     * 查询申诉信息
     * @param $order_no
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|\stdClass
     */
    public function getAppealInfo($order_no)
    {
        $appeal = FbAppeal::whereOrderNo($order_no)
            ->select('pan_reason', 'command')
            ->first();
        if (!$appeal) {
            $appeal = new \stdClass();
            $appeal->pan_reason = '';
            $appeal->command = '';
        }
        return $appeal;
    }

    /**
     * 法币下单后业务
     * @param FbTrans $order
     */
    protected function afterCreateTransOrder(FbTrans $order)
    {
        //发送短信
        event(new AfterCreateTransOrder($order));

        //开启定时器  自动取消
        $this->c2c_auto_job($order, 2);
    }

    /**
     * 订单确认后业务
     * @param FbTrans $order
     */
    protected function afterConfirmOrder(FbTrans $order)
    {
        event(new AfterConfirmOrder($order));

        if ($order->order_type == 1) {
            $model = FbSell::whereOrderNo($order->jy_order)->first();
        } else {
            $model = FbBuying::whereOrderNo($order->jy_order)->first();
        }
        $t_order = FbTrans::query()->where('jy_order', $order->jy_order)
            ->where('status', FbTrans::ORDER_OVER)
            ->sum('total_num');
        if ($t_order == $model->trans_num) {
            $model->status = 2;
            $model->save();
        }
    }


    #=========商家========

    /**
     * 成为商家
     * @param $user
     * @throws Exception
     */
    public function shopApply($user)
    {
        //成为商家费用 TODO
        $amount = config('fb.fb_shop_money') ?? 1;
        $wid = 1;

        $assetService = new AssetService();
        $config = $user->config;
        //法币交易商家 1提交审核 2同意 3拒绝 4撤销审核 5同意 6拒绝
        if ($config->fbshop == FbShopApply::SHOP_APPLY_CHECK) {
            throw new Exception(trans('fb.shop_examine_ing'));
        }
        if ($config->fbshop == FbShopApply::SHOP_APPLY_AGREE ||
            $config->fbshop == FbShopApply::SHOP_CANCEL_REFUSE) {
            throw new Exception(trans('fb.yes_shop'));
        }
        if ($config->fbshop == FbShopApply::SHOP_CANCEL_CHECK) {
            throw new Exception(trans('fb.cancel_shop_ing'));
        }

        //生成申请成为商家的订单
        $apply = FbShopApply::create([
            'uid'   => $user->id,
            'money' => $amount,
        ]);
        $assetService->writeBalanceLog($user->id, $apply->id, $wid, UserAsset::ACCOUNT_LEGAL, -$amount,
            UserMoneyLog::BUSINESS_TYPE_FB_SHOP, '成为商家-扣除金额');

        $config->fbshop = FbShopApply::SHOP_APPLY_CHECK;
        $config->fbshop_bond = $amount;
        $config->save();
    }

    /**
     * 撤销商家
     * @param $user
     * @throws Exception
     */
    public function shopCancel($user)
    {
        $config = $user->config;

        //法币交易商家 1提交审核 2同意 3拒绝 4撤销审核 5同意 6拒绝
        $buying = FbBuying::where('uid', $user->id)->where('status', 1)->first();
        $sell = FbSell::where('uid', $user->id)->where('status', 1)->first();

        if ($buying || $sell) {
            throw new Exception(trans('fb.have_order_not_cancel_shop'));
        }
        if ($config->fbshop == FbShopApply::SHOP_APPLY_CHECK) {
            throw new Exception(trans('fb.shop_examine_ing'));
        }
        //审核通过才能成为商家
        if ($config->fbshop == FbShopApply::SHOP_APPLY_REFUSE) {
            throw new Exception(trans('fb.no_shop'));
        }
        if ($config->fbshop == FbShopApply::SHOP_CANCEL_CHECK) {
            throw new Exception(trans('fb.cancel_shop_ing'));
        }
        if ($config->fbshop == FbShopApply::SHOP_CANCEL_AGREE) {
            throw new Exception(trans('fb.cancel_shop_over'));
        }
        if (!\in_array($config->fbshop, [FbShopApply::SHOP_APPLY_AGREE, FbShopApply::SHOP_CANCEL_REFUSE])) {
            throw new Exception(trans('fb.no_shop'));
        }

        //成为商家费用
        $amount = $config->fbshop_bond;
        FbShopApply::create([
            'uid'    => $user->id,
            'action' => FbShopApply::SHOP_ACTION_CANCEL,
            'status' => FbShopApply::SHOP_CANCEL_CHECK,
            'money'  => $amount
        ]);
        $config->fbshop = FbShopApply::SHOP_CANCEL_CHECK;
        $config->save();
    }

}
