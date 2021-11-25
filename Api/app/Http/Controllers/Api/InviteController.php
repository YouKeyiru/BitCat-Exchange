<?php

namespace App\Http\Controllers\Api;

use App\Models\IncomeOut;
use App\Models\User;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Services\AssetService;
use App\Services\TeamService;
use Dingo\Api\Http\Request;
use QrCode;
use sethink\swooleOrm\Db;

/**
 * @Resource("Invite")
 * Class InviteController
 * @package App\Http\Controllers\Api
 */
class InviteController extends BaseController
{
    /**
     * 生成分享二维码
     * @Get("/user/poster")
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function poster(Request $request)
    {
        $user = \Auth::user();
        $url = config('app.url') . '/reg/index.html';
        $querys = '';
        $querys .= '?invite_code=' . $user->account;
        $qrcode = QrCode::format('png')->size(368)->margin(0)
            ->generate($url . $querys);
        $result['account'] = $user->account;
        $result['url'] = $url . $querys;
        $result['qrcode'] = 'data:image/png;base64,' . base64_encode($qrcode);

        return $this->success($result);
    }

    /**
     * 我的推广页面信息
     * @Get("/user/set_avatar")
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $user = \Auth::user();

        $income = TeamService::income($user);
        $result = [
            'banner' => '',//
            'team_total' => TeamService::total($user),
            'income' => $income,
            'total_income' => $income
        ];
        return $this->success($result);
    }

    /**
     * 我的推广
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function push(Request $request)
    {
        $user = \Auth::user();
        $result = User::query()
            ->select('phone', 'email', 'name', 'created_at')
            ->where('recommend_id', $user->id)
            ->paginate($request->input('page_size', 15));
        return $this->success($result);
    }

    /**
     * 佣金明细
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function income_flow_del(Request $request)
    {
        $user = \Auth::user();
        $result = $user->moneyLog()
            ->with('target:id,name,nickname,account')
            ->select('target_id', 'money', 'mark', 'created_at')
            ->whereIn('type', [
                UserMoneyLog::BUSINESS_TYPE_ACTIVITY_PROFIT_BACK,
                UserMoneyLog::BUSINESS_TYPE_TRANS_FEE_PROFIT,
            ])->orderByDesc('id')->paginate(15);

        return $this->success($result);
    }


    /**
     * 佣金明细
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function income_flow(Request $request)
    {
        // TODO
        $user = \Auth::user();
        $result = $user->moneyLog()->whereIn('type', [
            UserMoneyLog::RECHARGE_REBATE
        ])->orderByDesc('id')->paginate($request->input('page_size', 15));
        $beginYesterday = date('Y-m-d H:i:s',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
        $endYesterday   = date('Y-m-d H:i:s',mktime(0,0,0,date('m'),date('d'),date('Y'))-1);
        $yesterdayTotal = $user->moneyLog()->whereIn('type', [
            UserMoneyLog::RECHARGE_REBATE
        ])->whereBetween('created_at', [$beginYesterday, $endYesterday])->sum('money');

        $moneyTotal = $user->moneyLog()->whereIn('type', [
            UserMoneyLog::RECHARGE_REBATE
        ])->sum('money');
        $data = [
            'yesterdayTotal' => $yesterdayTotal,
            'moneyTotal'     => $moneyTotal,
            'result'         => $result,
        ];
        return $this->success($data);
    }

    /**
     * 佣金提取
     * @param Request $request
     * @param AssetService $assetService
     * @return mixed
     * @throws \Exception
     */
    public function income_out(Request $request, AssetService $assetService)
    {
        $wid = 1;
        $user = \Auth::user();
        $amount = $request->post('amount', 0);
        if ($amount <= 0) {
            return $this->failed('提取金额异常');
        }

        \DB::beginTransaction();
        try {
            $asset = AssetService::_getBalance($user->id, $wid, UserAsset::ACCOUNT_COMMISSION);

            $assetService->writeBalanceLog($user->id, 0, $wid, UserAsset::ACCOUNT_COMMISSION, -$amount,
                UserMoneyLog::BUSINESS_TYPE_INCOME_OUT, '佣金提取'
            );

            //TODO 审核记录
            IncomeOut::query()->create([
                'uid' => $user->id,
                'amount' => $amount,
                'surplus' => bcMath($asset->balance, $amount, '-')
            ]);

            \DB::commit();
        } catch (\Exception $exception) {

            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }

        return $this->success();
    }

    /**
     * 提取记录
     * @param Request $request
     * @return mixed
     */
    public function income_out_flow(Request $request)
    {
        $user = \Auth::user();

        $result = IncomeOut::query()
            ->select('amount', 'surplus', 'status', 'created_at')
            ->where('uid',$user->id)
            ->orderByDesc('id')
            ->paginate(15);
//        $result = $user->incomeOut()
//            ->select('amount', 'surplus', 'status', 'created_at')
//            ->where('type', UserMoneyLog::BUSINESS_TYPE_INCOME_OUT)
//            ->orderByDesc('id')
//            ->paginate(15);

        return $this->success($result);
    }

}
