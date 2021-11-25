<?php

namespace App\Http\Controllers\Api;

use App\Http\Middleware\ContractTransStatus;
use App\Http\Requests\Api\ContractCreateOrderRequest;
use App\Http\Requests\Api\ContractSetPointRequest;
use App\Models\ContractEntrust;
use App\Models\ContractPosition;
use App\Models\ContractTrans;
use App\Models\ProductsContract;
use App\Models\UserAsset;
use App\Services\AssetService;
use App\Services\ContractTransService;
use App\Services\MarketService;
use DB;
use Exception;
use Illuminate\Http\Request;

class ContractController extends BaseController
{
    protected $title = '合约交易控制器';

    public function __construct()
    {
        parent::__construct();
//        $this->middleware(ContractTransStatus::class, [
//            'only' => ['createOrder']
//        ]);
    }

    /**
     * 币种列表
     * @param Request $request
     * @return mixed
     */
    public function symbols(Request $request)
    {
        $code = $request->get('code', '');
        $find = [];
        if ($code != '') {
            $find['code'] = $code;
        }
        $listInfo = ProductsContract::select('code', 'pname as name', 'image')
            ->where(['state' => ProductsContract::DIS_TYPE])
            ->where($find)
            ->orderBy('sort')
            ->get()
            ->toArray();
        foreach ($listInfo as &$item) {
            $jsonData = MarketService::getNewItemByCode($item['code']);
            if (empty($jsonData)) {
                $data = [
                    'date' => date('Y-m-d'),
                    'time' => date('H:i:s'),
                    'price' => 0,
                    'cnyPrice' => 0,
                    'open' => 0,
                    'close' => 0,
                    'high' => 0,
                    'low' => 0,
                    'volume' => 0,
                    'change' => 0,
                    'changeRate' => 0,
                ];
            } else {
                $jsonData = json_decode($jsonData, true);
                $data = [
                    'date' => date('Y-m-d'),
                    'time' => date('H:i:s'),
                    'price' => $jsonData['price'],
                    'cnyPrice' => $jsonData['cnyPrice'],
                    'open' => $jsonData['open'],
                    'close' => $jsonData['close'],
                    'high' => $jsonData['high'],
                    'low' => $jsonData['low'],
                    'volume' => $jsonData['volume'],
                    'change' => $jsonData['change'],
                    'changeRate' => $jsonData['changeRate'],
                ];
            }
            $item = array_merge($item, $data);
        }
        return $this->success($listInfo);
    }

    /**
     * 币种信息
     * @param Request $request
     * @return array
     */
    public function symbolDetail(Request $request)
    {
        $code = $request->get('code', '');
        if (!$code) {
            return $this->failed(trans('contract.code_no_existent'));
        }
        $product = ProductsContract::where(['code' => $code])
            ->select('pname', 'code', 'mark_cn', 'fxtime', 'fxnum', 'fxprice', 'fxweb', 'fxbook', 'memo')
            ->first();
        return $this->success($product);
    }

    /**
     * 盘口
     * @param Request $request
     * @return mixed
     */
    public function depth(Request $request)
    {
        $code = $request->get('code', '');
        if (!$code) {
            return $this->failed(trans('contract.code_no_existent'));
        }

        $jsonData = json_decode(MarketService::getCodeDepth($code));
        return $this->success($jsonData);
    }

    /**
     * 深度图
     * @param Request $request
     * @return mixed
     */
    public function pct(Request $request)
    {
        $code = $request->get('code', '');
        if (!$code) {
            return $this->failed(trans('contract.code_no_existent'));
        }
        $jsonData = json_decode(MarketService::getCodePct($code));
        return $this->success($jsonData);
    }

    /**
     * 实时成交
     * @param Request $request
     * @return mixed
     */
    public function trader(Request $request)
    {
        $code = $request->get('code', '');
        if (!$code) {
            return $this->failed(trans('contract.code_no_existent'));
        }
        $jsonData = json_decode(MarketService::getCodeTrader($code));
        return $this->success($jsonData);
    }

    /**
     * 获取币种配置信息
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function codeConfigInfo(Request $request)
    {
        $code = $request->get('code','');
        if (!$code) {
            return $this->failed(trans('contract.code_no_existent'));
        }
        $codeInfo = ProductsContract::whereCode($code)
            ->select('var_price', 'spread', 'leverage', 'min_order', 'max_order', 'handling_fee', 'sheet_num','buy_up','buy_down')
            ->first();

        if (!$codeInfo) {
            return $this->failed(trans('contract.code_no_existent'));
        }

        $codeInfo->burst_rate = config('contract.burst_rate');
        return $this->success($codeInfo);
    }

    /**
     * 交易信息 爆仓率等
     * @return mixed
     */
    public function info()
    {
        $user = \Auth::user();
        $asset = AssetService::_getBalance($user->id, ContractTransService::WID, UserAsset::ACCOUNT_CONTRACT);

        //余额
        $balance = $asset->balance;
        //冻结保证金
        $deposit = ContractTransService::getDeposit($user);
        //浮动盈亏
        $profit = ContractTransService::getProfit($user);
        //平仓盈亏
        $trans_profit = ContractTransService::getTransProfit($user);
        //动态权益
        $equity = $profit + $balance + $deposit;

        //风险率
        if ($deposit > 0) {
            $risk = bcMath($equity, $deposit, '/') * 100;
        } else {
            $risk = 0;
        }
        $risk .= '%';

        $result = [
            'balance' => $asset->balance,
            'risk' => $risk,
            'profit' => $profit,
            'deposit' => $deposit,
            'trans_profit' => $trans_profit,
            'equity' => $equity, //动态权益
        ];
        return $this->success($result);
    }

    /**
     * 下单
     * @param ContractCreateOrderRequest $request
     * @param ContractTransService $contractTransService
     * @return mixed
     * @throws Exception
     */
    function createOrder(ContractCreateOrderRequest $request, ContractTransService $contractTransService)
    {
        $user = \Auth::user();
        $input = $request->input();
        DB::beginTransaction();
        try {
            $contractTransService->createTransOrder($user, $input);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 持仓单设置止盈止损
     * @param ContractSetPointRequest $request
     * @param ContractTransService $contractTransService
     * @return mixed
     * @throws Exception
     */
    public function setPoint(ContractSetPointRequest $request, ContractTransService $contractTransService)
    {
        $user = \Auth::user();
        $input = $request->only(['zy', 'zs', 'order_no']);

        $zy_price = isset($input['zy']) ? $input['zy'] : 0;
        $zs_price = isset($input['zs']) ? $input['zs'] : 0;

        if ($zy_price == 0 && $zs_price == 0) {
            return $this->failed(trans('contract.input_zy_zs'));
        }

        $contractTransService->setPoint($user, $input);
        return $this->success();
    }

    /**
     * 撤单
     * @param Request $request
     * @param ContractTransService $contractTransService
     * @return mixed
     * @throws Exception
     */
    public function revokeOrder(Request $request, ContractTransService $contractTransService)
    {
        $user = \Auth::user();
        $order_no = $request->input('order_no', 0);

        DB::beginTransaction();
        try {
            $contractTransService->revokeOrder($user, $order_no);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 持仓/委托 数据接口
     * @param Request $request
     * @return array
     */
    public function transData(Request $request)
    {
        $user = \Auth::user();
        $data_type = $request->get('data_type', 1);
        $code = $request->get('code', '');
        $start_time = $request->get('start_time', '');
        $end_time = $request->get('end_time', '');

        // 1 持仓  2 委托
        if ($data_type == 1) {
            $model = ContractPosition::query();
        } else {
            $model = ContractEntrust::query()->where('status', ContractEntrust::STATE_ING);
        }

        $model->select('order_no', 'code', 'name', 'buy_price', 'buy_num', 'sheets',
            'stop_win', 'stop_loss', 'leverage', 'dayfee', 'otype', 'total_price', 'fee',
            'created_at'
        );

        $model->where('uid', $user->id);

        if ($start_time != '' && $end_time != '') {
            $model->whereBetween('created_at', [$start_time, $end_time]);
        }

        if ($code != '') {
            $model = $model->where('code', $code);
        }

        $hold_data = $model->orderBy('id', 'desc')
//            ->get();
            ->paginate(1000);

        foreach ($hold_data->items() as $item) {

            $new_price = MarketService::getCodePrice($item->code);
            if ($item->otype == 1) {
                $profit = round(($new_price - $item->buy_price) * $item->buy_num, 4);
            } else {
                $profit = round(($item->buy_price - $new_price) * $item->buy_num, 4);
            }
            $item->floating = $profit;
            $item->newprice = $new_price;
            $item->deposit = $item->total_price;
        }
        return $this->success($hold_data);
    }

    /**
     * 成交订单
     * @param Request $request
     * @return mixed
     */
    public function orderList(Request $request)
    {
        $user = \Auth::user();
        $code = $request->get('code', '');
        $start_time = $request->get('start_time', '');
        $end_time = $request->get('end_time', '');
        $model = $user->userTrans();

        $model->select('order_no', 'code', 'name', 'buy_price', 'buy_num', 'sheets',
            'stop_win', 'stop_loss', 'sell_price', 'leverage', 'dayfee', 'otype', 'total_price', 'pc_type', 'fee',
            'created_at', 'jiancang_at', 'profit', 'deposit'
        );

        if ($code != '') {
            $model = $model->where('code', $code);
        }
        if ($start_time != '' && $end_time != '') {
            $model->whereBetween('created_at', [$start_time, $end_time]);
        }

        $data = $model->orderBy('id', 'desc')->paginate($request->input('page_size', 15));
//        foreach ($data->items() as $item) {
////            $new_price = MarketService::getCodePrice($item->code);
////            if ($item->otype == 1) {
////                $profit = round(($new_price - $item->buy_price) * $item->buy_num, 4);
////            } else {
////                $profit = round(($item->buy_price - $new_price) * $item->buy_num, 4);
////            }
////            $item->floating = $profit;
////            $item->newprice = $new_price;
//            $item->deposit = $item->total_price;
//        }

        return $this->success($data);
    }

    /**
     * 平仓
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function closePosition(Request $request)
    {
        $user = \Auth::user();
        $order_no = $request->input('order_no', '');
        if ($order_no == '') {
            throw new Exception(trans('contract.input_order_no'));
        }
        DB::beginTransaction();
        try {
            ContractTransService::closePosition($user, $order_no, ContractTrans::CLOSE_MANUAL);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 一键全平仓
     * @return mixed
     * @throws Exception
     */
    public function allClosePosition()
    {
        $user = \Auth::user();

        $hold_data = $user->userPositions()->select('order_no')->get();
        if (!$hold_data->count()) {
            return $this->failed(trans('contract.order_not_found'));
        }

        $fail = 0;
        $success = 0;
        foreach ($hold_data as $item) {
            DB::beginTransaction();
            try {
                ContractTransService::closePosition($user, $item->order_no, ContractTrans::CLOSE_MANUAL);
                DB::commit();
                $success++;
            } catch (Exception $exception) {
                DB::rollBack();
                $fail++;
            }
        }
        $log = sprintf('fail %s,success %s', $fail, $success);
        return $this->success($log);
    }

}
