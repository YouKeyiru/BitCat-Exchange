<?php

namespace App\Http\Controllers\Api;

use App\Models\Transfer;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\WalletCode;
use App\Services\AssetService;
use Dingo\Api\Http\Request;
use Exception;
use Illuminate\Support\Facades\Cache;

/**
 * @Resource("Transfer")
 * Class TransferController
 * @package App\Http\Controllers\Api
 */
class TransferController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    // 可划转币种
    public function allow_code()
    {

        $codes = [
            [
                'wid' => 1,
                'code' => 'USDT'
            ]
        ];

        return $this->success($codes);
    }
    /**
     * 划转页面信息
     * @Get("/transfer/info")
     * @return mixed
     */
    public function index()
    {
        $wid = 1;

        $result = Cache::remember('transfer_index', 2, function () use ($wid){
            $user = \Auth::user();
            $account = [];
            foreach (UserAsset::ACCOUNT_TYPE as $index => $item) {
                $asset = AssetService::_getBalance($user->id, $wid, $index);
                if ($index == UserAsset::ACCOUNT_CONTRACT){
                    $balance = bcMath($asset->balance, $asset->keep, '-');
                }else{
                    $balance = $asset->balance;
                }

                $account[] = [
                    'account_id'   => $index,
                    'account_name' => $item,
                    'balance' => $balance
                ];
            }
            return $account;
        });

        return $this->success($result);
    }

    /**
     * 划转记录
     * @Get("/transfer/flow")
     * @param Request $request
     * @return mixed
     */
    public function flow(Request $request)
    {
        $user = \Auth::user();
        $result = Transfer::query()
            ->select('wid', 'from_account', 'to_account', 'amount', 'created_at')
            ->where('uid', $user->id)
            ->with('walletCode:id,code')
            ->paginate($request->input('page_size', 15));
        foreach ($result as $index => $item) {
            $item->from_account_name = UserAsset::ACCOUNT_TYPE[$item->from_account];
            $item->to_account_name = UserAsset::ACCOUNT_TYPE[$item->to_account];
        }
        return $this->success($result);
    }

    /**
     * 账户划转
     * @Post("/transfer/action")
     * @Request({"from_account": "1","to_account": "1","wid": "1","amount": "100","payment_password":"123456aa"})
     * @param Request $request
     * @param AssetService $assetService
     * @return mixed
     * @throws Exception
     */
    public function store(Request $request, AssetService $assetService)
    {
        $from_account = $request->input('from_account', 0);
        $to_account = $request->input('to_account', 0);
        $wid = $request->input('wid', 0);
//        $wid = 1;
        $amount = $request->input('amount', 0);

        if (!array_key_exists($from_account, UserAsset::ACCOUNT_TYPE) || !array_key_exists($to_account, UserAsset::ACCOUNT_TYPE)) {
            return $this->failed(trans('asset.account_no_existent'));
        }
        $wallet = WalletCode::find($wid);
        if (!$wallet) {
            return $this->failed(trans('asset.code_no_existent'));
        }
        $amount = format_price($amount, $wallet->code);
        if ($amount <= 0) {
            return $this->failed(trans('asset.amount_exception'));
        }
        $user = \Auth::user();

        try {
            \DB::beginTransaction();

            $transfer = Transfer::create([
                'uid'          => $user->id,
                'wid'          => $wid,
                'from_account' => $from_account,
                'to_account'   => $to_account,
                'amount'       => $amount,
            ]);

            $assetService->writeBalanceLog($user->id, $transfer->id, $wid, $from_account, -$amount, UserMoneyLog::BUSINESS_TYPE_TRANSFER, '账户划转支出');

            $assetService->writeBalanceLog($user->id, $transfer->id, $wid, $to_account, $amount, UserMoneyLog::BUSINESS_TYPE_TRANSFER, '账户划转收入');

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->failed($e->getMessage());
        }
        return $this->success();
    }

}
