<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\FbAppealRequest;
use App\Http\Requests\Api\FbCreateOrderRequest;
use App\Http\Requests\Api\FbPayAddRequest;
use App\Http\Requests\Api\IssueOrderRequest;
use App\Http\Requests\Api\RevokeOrderRequest;
use App\Http\Traits\Job;
use App\Models\FbAppeal;
use App\Models\FbBuying;
use App\Models\FbPay;
use App\Models\FbSell;
use App\Models\FbTrans;
use App\Models\User;
use App\Models\FbShopApply;
use App\Services\FbTransService;
use App\Services\ImageService;
use App\Services\MarketService;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class FbTradeController extends BaseController
{
    use Job;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 法币交易公共信息
     * @return mixed
     */
    public function commonData()
    {
        $return = [
            'mark_price' => 7.1 //TODO 法币价格
        ];
        return $this->success($return);
    }

    /**
     * 发布订单
     * @param IssueOrderRequest $request
     * @param FbTransService $fbTransService
     * @return mixed
     * @throws Exception
     */
    public function issueOrder(IssueOrderRequest $request, FbTransService $fbTransService)
    {
        $input = $request->post();
        $user = \Auth::user();
        // if($user->config->fbshop == FbShopApply::SHOP_APPLY_CHECK){
        //     return $this->failed(trans('fb.shop_examine_ing'));
        // }
        if($user->config->fbshop != FbShopApply::SHOP_APPLY_AGREE){
            return $this->failed(trans('fb.no_shop'));
        }
        \DB::beginTransaction();
        try {
            $fee = $fbTransService->getTransFee();
            $input['sxFee'] = bcMath($input['trans_num'], $fee * 0.01, '*');
            $input['totalPrice'] = bcMath($input['trans_num'], $input['price'], '*');
            if ($input['order_type'] == 1) {
                $fbTransService->createSellOrder($user, $input);
            } else {
                $fbTransService->createBuyOrder($user, $input);
            }

            \DB::commit();
        } catch (Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 交易大厅
     * @param Request $request
     * @return mixed
     */
    public function trading(Request $request)
    {
        //订单类型 1出售 2购买
        $type = $request->get('order_type', 1);
        $pay_method = $request->get('pay_method', 0);
        $min_price = $request->get('min_price', 0);

        if ($type == 1) {
            $model = FbSell::query();
        } else {
            $model = FbBuying::query();
        }

        $model->with('user')
            ->select('uid', 'order_no', 'trans_num', 'deals_num', 'surplus_num', 'price', 'total_price', 'min_price', 'max_price', 'pay_method')
            ->where('status', 1)
            ->where('surplus_num', '>', 0);

        if ($pay_method) {
            $model->whereRaw("FIND_IN_SET(?,`pay_method`)", $pay_method);
        }
        if ($min_price) {
            $model->where('min_price', '>=', $min_price);
        }

        // 出售正序  求购倒序
        if ($type == 1) {
            $model->orderBy('price', 'asc');
        } else {
            $model->orderBy('price', 'desc');
        }
        $orders = $model->paginate($request->input('page_size', 15));
        foreach ($orders->items() as &$item) {
            $item['total_price'] = bcMath($item['amount'], $item['price'], '*');//已成交金额
            $item['order_type'] = $type;
            $item['pay_method'] = explode(',', $item['pay_method']);
            $data = FbTransService::getOverRate($item['uid'], $type);
            $item['over_num'] = $data['over_num']; // 完成单数
            $item['over_rate'] = $data['over_rate'] * 100;// 完成率
            unset($item['uid'], $item['user']['id']);
        }
        $return['usdt_cny'] = MarketService::getCnyRateByCode('USDT');
        $return['orders'] = $orders;
        return $this->success($return);
    }

    /**
     * 下单
     * @param FbCreateOrderRequest $request
     * @param FbTransService $fbTransService
     * @return mixed
     * @throws Exception
     */
    public function createOrder(FbCreateOrderRequest $request, FbTransService $fbTransService)
    {
        $user = \Auth::user();
        $post = $request->post();

        #=========撤单次数限制=========
//        $cancel_count = FbTrans::query()->where('status', FbTrans::ORDER_CANCEL)
//            ->where(function ($query) use ($user) {
//                $query->where('chu_uid', $user->id)->orWhere('gou_uid', $user->id);
//            })
//            ->where('status', FbTrans::ORDER_CANCEL)
//            ->whereBetween('created_at', [Carbon::today(), Carbon::tomorrow()])
//            ->count();
//
//        if ($cancel_count >= 10) {
//            return $this->failed('当天撤单次数大于限制，不能下单');
//        }
        #=========撤单次数限制=========

        \DB::beginTransaction();
        //出售单，我要购买，我是买家
        //求购单，我要出售，我是卖家
        if ($post['order_type'] == 2) {

            if (!\Hash::check($request->payment_password, $user->payment_password)) {
                return $this->failed(trans('user.pay_pwd_error'));
            }

            // 检查有没有支付方式
            $pay = $user->payment()->where(['payment_type' => $post['pay_method'], 'status' => 1])->first();
            if (!$pay) {
                return $this->failed('请先添加或开启选择的收款方式');
            }
        }
        try {
            $create = $fbTransService->createTransOrder($user, $post);
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success(['order_no' => $create->order_no]);
    }

    /**
     * 订单详情
     * @param Request $request
     * @param FbTransService $fbTransService
     * @return array
     */
    public function orderDetail(Request $request, FbTransService $fbTransService)
    {
        $user = \Auth::user();
        $order_no = $request->get('order_no');
        $order_info = FbTrans::whereOrderNo($order_no)->first();
        if (!$order_info) {
            return $this->failed(trans('fb.order_not_found'));
        }

        try {
            //1出售者 2购买者
            if ($order_info->chu_uid == $user->id) {
                $user = User::find($order_info->gou_uid);
                $backData['o_type'] = 1;
            } elseif ($order_info->gou_uid == $user->id) {
                $user = User::find($order_info->chu_uid);
                $backData['o_type'] = 2;
            } else {
                return $this->failed(trans('fb.order_not_found'));
            }
            $backData['oop_account'] = $user->account;//对方编号
//            $backData['oop_name'] = substr_cut($user->name);//对方姓名
            $backData['oop_name'] = $user->name;//对方姓名
            $backData['oop_mobile'] = $user->phone;//对方手机号

            $command = '';
            $pan_reason = '';
            if (in_array($order_info->status,[FbTrans::ORDER_OVER,FbTrans::ORDER_APPEAL,FbTrans::ORDER_CANCEL])) {
                $appeal = $fbTransService->getAppealInfo($order_info->order_no);
                if($appeal){
                    $command = $appeal->command;
                    $pan_reason = $appeal->pan_reason;
                }
            }

            $backData['pan_reason'] = $pan_reason;//判决原因
            $backData['command'] = $command;//申诉口令
            $backData['order_no'] = $order_info->order_no;//订单编号
            $backData['total_num'] = $order_info->total_num;//总数量
            $backData['price'] = $order_info->price;//单价
            $backData['total_price'] = $order_info->total_price;//总计
            $backData['refer'] = $order_info->refer;//付款参考号
            $backData['created_at'] = Carbon::parse($order_info->created_at)->toDateTimeString();
            $backData['status'] = $order_info->status;//1未确认待付款 2已付款 3已确认完成 4 申述中 5取消
            $backData['pay_at'] = $order_info->pay_at; //付款时间
            $backData['pay_screen'] = $order_info->pay_screen; //付款时间
            $backData['down_time'] = $fbTransService->countDown($order_info);

            $backData['pay_list'] = $fbTransService->getPayment($order_info->chu_uid, $order_info->pay_method);


            if ($order_info->type == 1) {
                $notes = FbSell::whereOrderNo($order_info->jy_order)->value('notes');
            } else {
                $notes = FbBuying::whereOrderNo($order_info->jy_order)->value('notes');
            }
            $backData['notes'] = $notes;
        } catch (\Exception $exception) {
            return $this->failed($exception->getMessage());
        }
        return $this->success($backData);
    }

    /**
     * 标记已付款
     * @param Request $request
     * @return array
     */
    public function setOrderStatus(Request $request)
    {
        $user = \Auth::user();

        $order_no = $request->post('order_no');
        $pay_screen = $request->post('pay_screen');

        if (!$pay_screen) {
            return $this->failed(trans('fb.input_pay_screen'));
        }

        $order = FbTrans::whereOrderNo($order_no)
            ->where('gou_uid', $user->id)
//            ->where('status', FbTrans::ORDER_PENDING)
            ->first();

        if (!$order) {
            return $this->failed(trans('fb.order_not_found'));
        }

        if ($order->status != FbTrans::ORDER_PENDING) {
            return $this->failed(trans('fb.order_not_found'));
        }

        $result = FbTrans::query()->where(['id' => $order->id, 'version' => $order->version])->update([
            'status'  => FbTrans::ORDER_PAID,
            'pay_at'  => now(),
            'pay_screen'  => $pay_screen,
            'version' => $order->version + 1
        ]);

        if (!$result) {
            return $this->failed(trans('common.operation_failed'));
        } else {
            //自动确认1 自动取消 2
            $this->c2c_auto_job($order, 1);

            //改变成功，发送短信
//            $chu_user = User::find($order->chu_uid);
//            if ($chu_user->phone) {
//                $this->doSendSms($chu_user->phone,
//                    $request->ip(),
//                    SmsLog::SELL_USERINFO_CODE,
//                    $chu_user->area_code,
//                    $request->header('locale'),
//                    $order->order_no
//                );
//            }
            return $this->success();
        }

    }

    /**
     * 确认放行
     * @param Request $request
     * @param FbTransService $fbTransService
     * @return mixed
     * @throws Exception
     */
    public function confirm(Request $request, FbTransService $fbTransService)
    {
        $user = \Auth::user();

        $order = FbTrans::whereOrderNo($request->post('order_no'))
            ->where('chu_uid', $user->id)
            ->where('status', FbTrans::ORDER_PAID)
            ->first();

        if (!$order) {
            return $this->failed(trans('fb.order_not_found'));
        }

        if ($order->status != FbTrans::ORDER_PAID) {
            return $this->failed(trans('fb.order_not_found'));
        }

        \DB::beginTransaction();
        try {
            $fbTransService->confirmOrder($order);
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 提交申诉
     * @param FbAppealRequest $request
     * @return mixed
     * @throws Exception
     */
    public function appeal(FbAppealRequest $request)
    {
        $user = \Auth::user();

        $post = $request->post();

        $order = FbTrans::whereOrderNo($post['order_no'])
            ->where('status', FbTrans::ORDER_PAID)
            ->first();

        if (!$order) {
            return $this->failed(trans('fb.order_not_found'));
        }

        if ($order->gou_uid != $user->id && $order->chu_uid != $user->id) {
            return $this->failed(trans('fb.no_appeal'));
        }

        try {
            $appeal = Fbappeal::create([
                'order_no'      => $order->order_no,
                'command'       => mt_rand(1000, 9999),
                'refer'         => $post['refer'],
                'appeal_uid'    => $order->gou_uid,
                'be_appeal_uid' => $order->chu_uid,
                'type'          => $order->order_type,
                'reason'        => $post['reason'],
                'order_status'  => $order->status,
            ]);

            $result = FbTrans::query()->where([
                'id'      => $order->id,
                'version' => $order->version
            ])->update([
                'status'  => FbTrans::ORDER_APPEAL,
                'version' => $order->version + 1,
            ]);
            if ($result === false) {
                throw new Exception(trans('fb.update_failed'));
            }

            \DB::commit();
        } catch (Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }

        return $this->success([
            'command' => $appeal->command,
            'refer'   => $appeal->refer,
        ]);
    }

    /**
     * 取消订单
     * @param Request $request
     * @param FbTransService $fbTransService
     * @return mixed
     * @throws Exception
     */
    public function cancelOrder(Request $request, FbTransService $fbTransService)
    {
        $user = \Auth::user();
        //1待付款 2已付款 3已确认完成 4 申述中 5取消 6冻结
        $order_no = $request->post('order_no');
        $order = FbTrans::whereOrderNo($order_no)
            ->first();
        if (!$order) {
            return $this->failed(trans('fb.order_not_found'));
        }
        if ($order->status != FbTrans::ORDER_PENDING) {
            return $this->failed(trans('fb.order_not_found'));
        }
        if ($order->gou_uid != $user->id && $order->chu_uid != $user->id) {
            return $this->failed(trans('fb.order_not_found'));
        }

        \DB::beginTransaction();
        try {
            $fbTransService->cancelOrder($user, $order);
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }

        return $this->success();
    }

    /**
     * 撤销订单
     * @param RevokeOrderRequest $request
     * @param FbTransService $fbTransService
     * @return mixed
     * @throws Exception
     */
    public function revokeOrder(RevokeOrderRequest $request, FbTransService $fbTransService)
    {
        $user = \Auth::user();
        $post = $request->only(['order_type', 'order_no']);
        DB::beginTransaction();
        try {
            $fbTransService->revokeOrder($user, $post);
            \DB::commit();
        } catch (Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 法币交易历史明细 get
     * @param Request $request
     * @return mixed
     */
    public function orderList(Request $request)
    {
        $user = \Auth::user();

        $model = FbTrans::query();

        $type = $request->get('order_type', 3);

        $status = $request->get('status', 0); // 20 未完成(1,2,4)   30 完成(3)  40 取消(5)

        if ($type == 1) {
            $model->where('gou_uid', $user->id);
        } else if ($type == 2) {
            $model->where('chu_uid', $user->id);
        } else {
            $model->where(function ($query) use ($user) {
                $query->where('chu_uid', $user->id)->orWhere('gou_uid', $user->id);
            });
        }

        //20 未完成(1,2,4)   30 完成(3)  40 取消(5)
        if ($status == 20) {
            $model->whereIn('status', [1, 2, 4]);
        } elseif ($status == 30) {
            $model->where('status', 3);
        } elseif ($status == 40)  {
            $model->where('status', 5);
        }

        $lists = $model
            ->select('chu_uid', 'gou_uid', 'order_no', 'order_type', 'status', 'price', 'total_num', 'total_price', 'min_price', 'max_price', 'pay_method', 'created_at')
            ->orderBy('id', 'desc')
            ->paginate($request->input('page_size', 15));
//        ->simplePaginate(15);
//            ->get();

        foreach ($lists->items() as $item) {
            if ($item->chu_uid == $user->id) {
                $item->o_type = 1;
            } elseif ($item->gou_uid == $user->id) {
                $item->o_type = 2;
            }
        }

        return $this->success($lists);
    }

    /**
     * 发布单明细
     * @param Request $request
     * @return mixed
     */
    public function issueOrderList(Request $request)
    {
        $user = \Auth::user();

        $order_type = $request->get('order_type', 3);
        if (in_array($order_type, [1, 2])) {
            if ($order_type == 1) {
                $model = FbSell::query();
            } else {
                $model = FbBuying::query();
            }

            $data = $model->select('order_no', 'status', 'price', 'trans_num',
                'surplus_num', 'total_price', 'min_price', 'max_price', 'pay_method', 'created_at',
                DB::raw("(case when 1=1 then {$order_type} end ) as order_type"))
                ->where('uid', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('page_size', 15));

        } else {
            DB::enableQueryLog();
            $querySell = FbSell::query()
                ->select('order_no', 'status', 'price', 'trans_num',
                    'surplus_num', 'total_price', 'min_price', 'max_price', 'pay_method', 'created_at',
                    DB::raw("(case when 1=1 then 1 end ) as order_type"))
                ->where('uid', $user->id);

            $queryBuy = FbBuying::query()
                ->select('order_no', 'status', 'price', 'trans_num',
                    'surplus_num', 'total_price', 'min_price', 'max_price', 'pay_method', 'created_at',
                    DB::raw("(case when 1=1 then 2 end ) as order_type"))
                ->where('uid', $user->id);

            $queryUnion = $querySell->unionAll($queryBuy);

            $data = DB::table(DB::raw('(' . $queryUnion->toSql() . ') as data'))
                ->mergeBindings($queryUnion->getQuery())
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('page_size', 15));
        }

        return $this->success($data);
    }

    #=========支付方式========

    /**
     * 添加/编辑 支付方式
     * @param FbPayAddRequest $request
     * @return mixed
     */
    public function paymentAdd(FbPayAddRequest $request)
    {
        $user = \Auth::user();
        if (!\Hash::check($request->payment_password, $user->payment_password)) {
            return $this->failed(trans('fb.pay_pwd_error'));
        }
        $post = $request->only(['payment_type', 'auth_name', 'bank', 'branch', 'card_num', 'act', 'qrcode']);
        $res = FbPay::query()
            ->select('id as payment_id', 'payment_type', 'auth_name', 'bank', 'branch', 'card_num', 'qrcode')
            ->where('uid', $user->id)
            ->where('payment_type', $post['payment_type'])
            ->first();

        $pay_type = FbPay::PAYMENT_TYPE[$post['payment_type']];

        if (($post['act'] == 'add' && $res) || ($post['act'] == 'edit' && !$res)) {
            return $this->failed(trans('fb.not_add_payment', ['payment' => $pay_type]));
        }

        if ($post['act'] == 'add') {
            $post['uid'] = $user->id;
            unset($post['act']);
            FbPay::create($post);
        } else if ($post['act'] == 'edit') {
            unset($post['act']);
            FbPay::where('uid', $user->id)->where(['payment_type' => $post['payment_type']])->update($post);
        }
        return $this->success();
    }

    /**
     * 支付方式信息
     * @param Request $request
     * @return mixed
     */
    public function paymentInfo(Request $request)
    {
        $user = \Auth::user();
        $payment_id = $request->input('payment_id');
        $payment_info = FbPay::query()
            ->select('id as payment_id', 'payment_type', 'auth_name', 'bank', 'branch', 'card_num', 'qrcode')
            ->where('id', $payment_id)
            ->where('uid', $user->id)
            ->first();

        if (!$payment_info) {
            return $this->failed(trans('fb.not_add_payment'));
        }

        $payment_info->qrcode1 = $payment_info->qrcode;
        if ($payment_info->qrcode) {
            $payment_info->qrcode = ImageService::setHost() . $payment_info->qrcode;
        }
        return $this->success($payment_info);
    }

    /**
     * 改变支付方式状态
     * @param Request $request
     * @return mixed
     */
    public function setPayStatus(Request $request)
    {
        $user = \Auth::user();
        $post = $request->post();
        try {
            if (!isset($post['status']) || !isset($post['payment_type'])) {
                return $this->failed(trans('fb.param_not_all'));
            }
            $fb_pay = FbPay::where('uid', $user->id)->where('payment_type', $post['payment_type'])->first();
            if (!$fb_pay) {
                return $this->failed(trans('fb.please_add_payment'));
            }

            if ($post['status']){
                //开启

            }else{
                //关闭

                $s = FbTrans::query()->where([
                    'chu_uid'    => $user->id,
                    'pay_method' => $fb_pay->payment_type,
                ])->whereIn('status', [1, 2])
                    ->first();
                if ($s) {
                    return $this->failed('进行中订单正在使用该支付方式');
                }
            }

            $fb_pay->status = $post['status'];
            $fb_pay->save();
        } catch (\Exception $exception) {
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 支付方式列表
     * @return mixed
     */
    public function payList(Request $request)
    {
        $user = \Auth::user();
        $status = [0,1]; //默认全部
        $statusParam = $request->input('status','');
        if($statusParam){
            $status = [1]; //只开启的
        }
        $list = FbPay::query()
            ->select('id as payment_id', 'payment_type', 'auth_name', 'bank', 'branch', 'card_num', 'status')
            ->where('uid', $user->id)->whereIn('status', $status)->get();
        return $this->success($list);
    }

    #=========商家========

    /**
     * 商家申请页面内容
     * @return mixed
     */
    public function shopApplyIndex()
    {
        $amount = config('fb.fb_shop_money') ?? 1;
        $result = [
            'content' => '',
            'amount'  => $amount
        ];
        return $this->success($result);
    }

    /**
     * 申请成为商家
     * @param FbTransService $fbTransService
     * @return mixed
     * @throws Exception
     */
    public function shopApply(FbTransService $fbTransService)
    {
        $user = \Auth::user();
        \DB::beginTransaction();
        try {
            $fbTransService->shopApply($user);
            \DB::commit();
        } catch (Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 撤销商家
     * @param FbTransService $fbTransService
     * @return mixed
     * @throws Exception
     */
    public function shopCancel(FbTransService $fbTransService)
    {
        $user = \Auth::user();
        \DB::beginTransaction();
        try {
            $fbTransService->shopCancel($user);
            \DB::commit();
        } catch (Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();

    }

}
