<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\Job;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\WalletCode;
use App\Services\AssetService;
use App\Services\MarketService;
use Dingo\Api\Http\Request;

/**
 * @Resource("Account")
 * Class AccountController
 * @package App\Http\Controllers\Api
 */
class AccountController extends BaseController
{
    use Job;

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 获取账户资产信息
     * @Get("/account/index")
     * @Request({"asset_account": "1"})
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $asset_account = $request->input('account', 1);
        if (!array_key_exists($asset_account, UserAsset::ACCOUNT_TYPE)) {
            return $this->failed('账户不存在');
        }

        $asset_codes = WalletCode::query()
            ->select('id', 'icon', 'code', 'c_type', 'start_c', 'start_t')
            ->whereRaw("FIND_IN_SET(?,`belong`)", $asset_account)
            ->get();

        $total_usdt = 0;
        $total_cny = 0;
        $info = [];
        $user = \Auth::user();
        $usdt_cny = MarketService::getCnyRateByCode();
        foreach ($asset_codes as $code) {
            $asset = AssetService::_getBalance($user->id, $code->id, $asset_account);
            $tmp = [];
            $tmp['wid'] = $code->id;
            $tmp['code'] = $code->code;
            $tmp['logo'] = $code->icon;
            $tmp['balance'] = $asset->balance;

            if ($asset_account == UserAsset::ACCOUNT_LEGAL){
                $tmp['frost'] = bcMath($asset->frost , $user->config->fbshop_bond,'+');
            }else{
                $tmp['frost'] = $asset->frost;
            }
//            $tmp['frost'] = $asset->frost;

            $tmp['c_type'] = $code->c_type;
            $tmp['start_c'] = $asset_account != UserAsset::ACCOUNT_CURRENCY ? 0 : $code->start_c;
            $tmp['start_t'] = $asset_account != UserAsset::ACCOUNT_CURRENCY ? 0 : $code->start_t;


            if ($tmp['code'] == 'USDT') {
                $total_usdt += $asset->balance;
                $tmp['cny'] = bcMath($usdt_cny, $tmp['balance'], '*');
            } else {
                $usdt = MarketService::getCodePrice(strtolower($tmp['code']) . '/usdt');
                $balance = bcMath($usdt, $tmp['balance'], '*');
                $total_usdt += $balance;
                $tmp['cny'] = bcMath($usdt_cny, $balance, '*', 2);
            }
            $total_cny += $tmp['cny'];
            array_push($info, $tmp);
        }

        $result = [
            'total_usdt' => bcMath($total_usdt, 0, '+'),
            'total_cny' => bcMath($total_cny, 0, '+', 2),
            'asset_list' => $info,
        ];

        $this->addr_recharge($user);

        return $this->success($result);
    }

    /**
     * 获取余额
     * @Get("/account/account_asset")
     * @Request({"account": "1","wid":"1"})
     * @param Request $request
     * @return mixed
     */
    public function account_asset(Request $request)
    {
        $user = \Auth::user();
        $account = $request->get('account');
        $wid = $request->get('wid');

        $asset = AssetService::_getBalance($user->id, $wid, $account);
        if (!$asset) {
            return $this->failed('资产币种不存在');
        }
        $wallet = WalletCode::find($wid);
        if (!$wallet) {
            return $this->failed('资产币种不存在1');
        }
        $usdt_cny = MarketService::getCnyRateByCode();

        if ($account == UserAsset::ACCOUNT_LEGAL){
            $frost = bcMath($asset->frost , $user->config->fbshop_bond,'+');
        }else{
            $frost = $asset->frost;
        }

        $result = [
            'balance' => $asset->balance,
            'frost' => $frost,
            'cny' => bcMath($usdt_cny, $asset->balance, '*')
        ];
        if ($wallet->code == 'USDT') {
            //usdt
            $result['cny'] = bcMath($usdt_cny, $asset->balance, '*', 2);
        } else {
            $usdt = MarketService::getCodePrice(strtolower($wallet->code) . '/usdt');
            $balance = bcMath($usdt, $asset->balance, '*');
            $result['cny'] = bcMath($usdt_cny, $balance, '*', 2);
        }

        return $this->success($result);
    }

    /**
     * 流水类型
     * @return mixed
     */
    public function businessType()
    {
        $types = UserMoneyLog::getBusinessType();
        $result = [];
        foreach ($types as $k => $value) {
            array_push($result, ['type' => $k, 'type_name' => $value]);
        }
        return $this->success($result);
    }

    /**
     * 资产记录
     * @param Request $request
     * @return mixed
     */
    public function deal_flow(Request $request)
    {
        $user = \Auth::user();
        $type = $request->input('type', '');
        $wid = $request->input('wid', 1);
        $account = $request->input('account', 1);
        if (!$type) {
            $type = array_keys(UserMoneyLog::getBusinessType());
        } else {
            $type = array_filter(explode(',', $type));
        }

        $result = $user->moneyLog()
            ->select('mark', 'money', 'wid', 'created_at')
            ->with('wallet:id,code')
            ->where('wt', 1)
//            ->where('money', '!=', '0')
            ->where(['wid' => $wid, 'account' => $account])
            ->whereIn('type', $type)
            ->orderByDesc("id")
            ->paginate($request->input('page_size', 15));
        return $this->success($result);
    }

}
