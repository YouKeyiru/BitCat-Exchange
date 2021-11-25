<?php

namespace App\Http\Controllers\Api;

use App\Models\CashGift;
use App\Models\UserGiftAsset;
use App\Models\WalletCode;
use App\Models\Authentication;
use App\Services\GiftService;
use App\Models\UserGiftLog;
use Illuminate\Support\Carbon;
use App\Services\MarketService;
use Exception;
use Illuminate\Http\Request;

class CashGiftController extends BaseController
{
    protected $title = '赠金控制器';

    public function __construct()
    {
        parent::__construct();
    }
  
    /**
     * 领取赠金
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function createGift(Request $request)
    {
        $input = $request->post();
        $user = \Auth::user();
        // if($user->authentication!=Authentication::ADVANCED_CHECK_AGREE){
        //     return $this->failed('请先实名认证');//trans('user.pay_pwd_error')
        // }
        $info = CashGift::where('secret_key',$input['secret_key'])->first();
        if(!$info){
            return $this->failed(trans('gift.wrong_password'));//trans('user.pay_pwd_error')
        }
        //判断今天是否已经领取了
        $start = Carbon::today();
        $end = Carbon::tomorrow();
        $count = UserGiftLog::where(['uid'=>$user->id,'wid'=>$info->wid,'type'=>UserGiftLog::CASH_GIFT_RECEIVE])->whereBetween('created_at',[$start,$end])->count();
        if($count>0){
            return $this->failed(trans('gift.received'));//trans('user.pay_pwd_error')
            // throw new Exception(trans('fb.dec_cj_fee_failed'));
        }
        //判断是否已经领取完了
        if($info->total_times <= $info->used_times){
            return $this->failed(trans('gift.invalid_password'));// 已经领取完毕
        }
        //获取随机值
        $money = randcount($info->money_min,$info->money_max,'6');
        \DB::beginTransaction();
        try {
            $giftService = new GiftService();
            $giftService->writeBalanceLog($user->id, 0, $info->wid, $money,UserGiftLog::CASH_GIFT_RECEIVE, '赠金领取');
            CashGift::increment('used_times');
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        $result['money'] = $money;
        return $this->success($result);
    }


    /**
     * 赠金资产信息
     * @param Request $request
     * @return array
     */
    public function giftAsset(Request $request)
    {
        $user = \Auth::user();
        $wid = $request->input('wid', 1);
        $total_balance = $user->userGiftLog()->where(['wid' => $wid,'type'=>UserGiftLog::CASH_GIFT_RECEIVE])->sum('money'); //总领取
        // $balanceAsset = UserGiftAsset::where(['wid'=>$wid, 'uid'=>$user->id])->select('balance','frost')->first();
        $balanceAsset = GiftService::_getBalance($user->id,$wid);
        $wallet = WalletCode::find($wid);
        if (!$wallet) {
            return $this->failed('资产币种不存在');
        }
        $usdt_cny = MarketService::getCnyRateByCode();
        $assetInfo = [
            'balance' => $balanceAsset->balance,
            'frost' => $balanceAsset->frost,
            'cny' => bcMath($usdt_cny, $balanceAsset->balance, '*')
        ];
        if ($wallet->code == 'USDT') {
            //usdt
            $assetInfo['cny'] = bcMath($usdt_cny, $balanceAsset->balance, '*', 2);
        } else {
            $usdt = MarketService::getCodePrice(strtolower($wallet->code) . '/usdt');
            $balance = bcMath($usdt, $balanceAsset->balance, '*');
            $assetInfo['cny'] = bcMath($usdt_cny, $balance, '*', 2);
        }
        $assetInfo['total_balance'] = $total_balance;
        return $this->success($assetInfo);
    }

    /**
     * 赠金领取记录
     * @param Request $request
     * @return array
     */
    public function giftList(Request $request)
    {
        $user = \Auth::user();
        $type = $request->input('type', '');
        $wid = $request->input('wid', 1);
        if (!$type) {
            $type = array_keys(UserGiftLog::getBusinessType());
        } else {
            $type = array_filter(explode(',', $type));
        }
        $result = $user->userGiftLog()
            ->select('mark', 'money', 'wid', 'created_at')
            ->with('wallet:id,code')
            ->where(['wid' => $wid])
            ->whereIn('type', $type)
            ->orderByDesc("id")
            ->paginate($request->input('page_size', 15));
        return $this->success($result);
    }

}
