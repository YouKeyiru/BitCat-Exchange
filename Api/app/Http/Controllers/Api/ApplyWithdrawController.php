<?php

namespace App\Http\Controllers\Api;

use App\Entities\Notification\Email\VerifyMailHandel;
use App\Entities\Notification\SmsHandel;
use App\Http\Requests\Api\ApplyWithdraw;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\UserWithdrawRecord;
use App\Models\WalletCode;
use App\Services\AssetService;
use Exception;
use Illuminate\Http\Request;

class ApplyWithdrawController extends BaseController
{
    /**
     * 币币账户
     */
    const ACCOUNT = UserAsset::ACCOUNT_CURRENCY;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 提币币种列表
     * @return mixed
     */
    public function codeList()
    {
        $result = WalletCode::where('start_t', 1)->select('code', 'id as wid')->get();
        return $this->success($result);
    }

    /**
     * 获取账户余额/手续费等信息
     * @param Request $request
     * @return array
     */
    public function show(Request $request)
    {
        $user = \Auth::user();

        $wid = $request->get('wid', 0);

        $wallet = WalletCode::find($wid);
        if (!$wallet) {
            return $this->failed('提现币种不存在');
        }

        $assets = AssetService::_getBalance($user->id, $wid, self::ACCOUNT);
        return $this->success([
            'balance' => $assets->balance,
            'withdraw_min' => $wallet->withdraw_min,
            'withdraw_max' => $wallet->withdraw_max,
            'handling_fee' => $wallet->withdraw_handling_fee,
        ]);
    }

    /**
     * 提币记录
     * @param Request $request
     * @return mixed
     */
    public function withdrawLog(Request $request)
    {
        $user = \Auth::user();
        $code = $request->input('code', '');

        $model = $user->withdrawRecord();
        $model->select('order_no', 'code', 'address', 'amount', 'actual', 'handling_fee', 'created_at', 'checked_at', 'refuse_reason', 'status');
        if ($code != '') {
            $model->where('code', $code);
        }
        $lists = $model->paginate($request->input('page_size', 15));

        return $this->success($lists);
    }

    /**
     * 用户提币处理
     * @param ApplyWithdraw $request
     * @return mixed
     * @throws Exception
     */
    public function applyWithdraw(ApplyWithdraw $request)
    {
        $user = \Auth::user();
        $input = $request->post();
        $wid = $input['wid'];

        \DB::beginTransaction();
        try {
            $wallet = WalletCode::find($wid);

//            SmsHandel::check($user->phone, $input['v_code']);
            //VerifyMailHandel::check($user->email, $input['v_code']);

            // 检测最大提币数量   检测最小提币数量
            if (!$wallet) {
                throw new Exception('提现币种不存在');
            }
            // 检测最小提币数量
            if ($input['amount'] < $wallet->withdraw_min) {
                throw new Exception('最小提币数量为' . floatval($wallet->withdraw_min));
            }
            // 检测最大提币数量
            if ($input['amount'] > $wallet->withdraw_max) {
                throw new Exception('最大提币数量为' . floatval($wallet->withdraw_max));
            }

            //检测钱包地址是否是真实地址
            if (!(preg_match('/^(1|3)[a-zA-Z\d]{24,33}$/', $input['address']) &&
                preg_match('/^[^0OlI]{25,34}$/', $input['address']))) {
//                throw new Exception(trans('address.illegal_address'));
            }

            $create = $user->withdrawRecord()->create([
                'wid' => $wid,
                'code' => $wallet->code,
                'address' => $input['address'],
                'amount' => $input['amount'],
                'actual' => bcMath($input['amount'], $wallet->withdraw_handling_fee, '-'),
                'handling_fee' => $wallet->withdraw_handling_fee,
                'mark' => '用户提币',
            ]);

            $assetService = new AssetService();
            $assetService->writeBalanceLog($user->id, $create->id, $wid, self::ACCOUNT, -$input['amount'],
                UserMoneyLog::CASH_TANS, '用户提币');

            $assetService->writeFrostLog($user->id, $create->id, $wid, self::ACCOUNT, $input['amount'],
                UserMoneyLog::CASH_TANS, '用户提币冻结');

            \DB::commit();
        } catch (Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 提币撤销
     * @param Request $request
     * @return mixed
     */
    public function revokeWithdraw(Request $request)
    {
        $user = \Auth::user();
        $order_no = $request->post('order_no');

        $withdraw = $user->withdrawRecord()
            ->select('id', 'wid', 'amount', 'actual', 'updated_at')
            ->where(['order_no' => $order_no, 'status' => UserWithdrawRecord::WAIT_CHECK])
            ->first();

        if (!$withdraw) {
            return $this->failed('记录不存或已更新');
        }

        \DB::beginTransaction();
        try {
            //更新状态
            $update = UserWithdrawRecord::query()
                ->where('id', $withdraw->id)
                ->where('updated_at', $withdraw->updated_at)
                ->update([
                    'status' => UserWithdrawRecord::REVOKE,
                ]);

            if (!$update) {
                return $this->failed('操作失败，请刷新后尝试');
            }

            //更新金额
            $assetService = new AssetService();
            $assetService->writeBalanceLog($user->id, $withdraw->id, $withdraw->wid, self::ACCOUNT, $withdraw->amount,
                UserMoneyLog::CASH_TANS, '用户提币撤销');

            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }


        return $this->success();
    }
}
