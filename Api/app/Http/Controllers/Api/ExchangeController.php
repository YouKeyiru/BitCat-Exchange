<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ExchangeCreateOrderRequest;
use App\Http\Requests\Api\ExchangeOrderListRequest;
use App\Models\ExchangeOrder;
use App\Models\ProductsExchange;
use App\Models\UserAsset;
use App\Models\WalletCode;
use App\Services\AssetService;
use App\Services\ExchangeTransService;
use App\Services\MarketService;
use App\Services\MatchEngineService;
use Exception;
use Illuminate\Http\Request;

class ExchangeController extends BaseController
{
    protected $title = '币币交易控制器';

    public function __construct()
    {
        parent::__construct();
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

        $listInfo = ProductsExchange::select('code', 'pname as name', 'image')
            ->where(['state' => ProductsExchange::DIS_TYPE])
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
                $jsonData = MarketService::getNewItemByCode($item['code']);
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
     * 各币种详情
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function symbolsData(Request $request)
    {
        $user = \Auth::user();
        $code = $request->get('code', '');
        $codeInfo = ProductsExchange::whereCode($code)->first();
        if (!$codeInfo) {
            return $this->failed(trans('exchange.code_no_existent'));
        }
        $code = ProductsExchange::coinCut($codeInfo->code);
        $sellAsset = AssetService::_getBalance($user->id, WalletCode::getWidByCode($code[0]), UserAsset::ACCOUNT_CURRENCY);
        $buyAsset = AssetService::_getBalance($user->id, WalletCode::getWidByCode($code[1]), UserAsset::ACCOUNT_CURRENCY);
        $result = [
            'left_balance' => $sellAsset->balance,
            'right_balance' => $buyAsset->balance,
        ];
        return $this->success($result);
    }

    /**
     * 下单
     * @param ExchangeCreateOrderRequest $request
     * @param ExchangeTransService $exchangeTransService
     * @return mixed
     * @throws Exception
     */
    public function createOrder(ExchangeCreateOrderRequest $request, ExchangeTransService $exchangeTransService)
    {
        $input = $request->post();
        $user = \Auth::user();
        \DB::beginTransaction();
        try {
            $exchangeTransService->createTransOrder($user, $input);
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 撤单接口
     * @param Request $request
     * @param ExchangeTransService $exchangeTransService
     * @return mixed
     * @throws Exception
     */
    public function revokeOrder(Request $request, ExchangeTransService $exchangeTransService)
    {
        $order_no = $request->post('order_no', '');
        $user = \Auth::user();
        \DB::beginTransaction();
        try {
            $exchangeTransService->revokeOrder($user, $order_no);
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 币币交易记录
     * @param ExchangeOrderListRequest $request
     * @return array
     */
    public function orderList(ExchangeOrderListRequest $request)
    {
        $user = \Auth::user();
        $input = $request->input();
        $model = $user->userExchange();
        $model->select('created_at', 'symbol as name', 'type', 'otype', 'wtprice', 'wtnum', 'total_price', 'cjprice', 'cjnum', 'status', 'order_no');
        if (isset($input['code'])) {
            $model->where(['code' => $input['code']]);
        }
        if ($input['order_type'] == 1) {
            $model->whereIn('status', [ExchangeOrder::WAIT_TRANS, ExchangeOrder::ING_TRANS]);
        } else {
            $model->whereIn('status', [ExchangeOrder::OVER_TRANS, ExchangeOrder::REVOKE_TRANS]);
        }
        $model->orderByDesc('id');
        return $this->success($model->paginate(15));
    }


    /**
     * 币种信息
     * @param Request $request
     * @return array
     */
    public function getCodeInfo(Request $request)
    {
        $data = $request->all();
        if (!isset($data['code'])) {
            return $this->failed(trans('money.currency_parameter_error'));
        }
        $find['code'] = $data['code'];

        $product = ProductsExchange::where($find)
            ->select('pname', 'code', 'mark_cn', 'fxtime', 'fxnum', 'fxprice', 'fxweb', 'fxbook', 'memo')
            ->first();
        return $this->success($product);
    }

    /**
     * 实时成交数据
     * @param Request $request
     * @return array
     */
    public function RealTimeDeal(Request $request)
    {
        $code = $request->get('code', '');
        if (!$code) {
            return $this->failed(trans('money.currency_parameter_error'));
        }
        $jsonData = json_decode(MarketService::getTradeDataByCode($code), true);
        return $this->success($jsonData);
    }

    /**
     * 获取盘口数据
     * @param Request $request
     * @return array
     */
    public function getHandicap(Request $request)
    {
        $code = $request->get('code', '');
        if (!$code) {
            return $this->failed(trans('money.currency_parameter_error'));
        }
        $jsonData = json_decode(MarketService::getHandicapDataByCode($code), true);
        return $this->success($jsonData);
    }

    /**
     * 获取深度数据
     * @param Request $request
     * @return array
     */
    public function getCodeDepth(Request $request)
    {
        $code = $request->get('code', '');
        if (!$code) {
            return $this->failed(trans('money.currency_parameter_error'));
        }
        $jsonData = json_decode(MarketService::getDepthDataByCode($code), true);
        return $this->success($jsonData);
    }

    /**
     * 盘口数据
     * @param Request $request
     * @return array
     */
    public function getDepth(Request $request)
    {
        $code = $request->get('code');
        $slice = $request->get('slice', 20);

        try {
            $params = [
                'type' => 'handicap',
                'data' => [
                    'market' => $code, //交易市场
                    'slice' => $slice, //交易市场
                ]
            ];
            $result = MatchEngineService::run(json_encode($params));
            if ($result['code'] != 1) {
                throw new \Exception($result['err']);
            }
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage());
        }
        return $this->success($result['data']);
    }

    /**
     * 清除撮合引擎内数据
     * @return array
     */
    public function cleanAll()
    {
        try {
            $params = json_encode([
                'type' => 'empty',
                'data' => []
            ]);

            $result = MatchEngineService::run($params);
            if ($result['code'] != 1) {
                throw new \Exception($result['err']);
            }
        } catch (Exception $exception) {
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }
}
