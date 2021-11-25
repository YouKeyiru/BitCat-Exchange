<?php

namespace App\Http\Controllers\Api;


use App\Models\Activity;
use App\Models\ActivityUser;
use App\Models\UserMoneyLog;
use App\Services\ActivityServices;
use App\Services\AssetService;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends BaseController
{

    /**
     * 活动页面
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        //
        $user = \Auth::user();

//        $wid = $request->wid;
        $wid = 1;

        $info = Activity::query()
            ->where('status', 1)
            ->select('id as activity_id', 'min_num', 'max_num', 'multiple', 'cycle', 'day_rate', 'damages_rate', 'describe')
            ->get();


        $asset = AssetService::_getBalance($user->id, $wid, ActivityServices::ACCOUNT);

        $result = [
            'balance' => $asset->balance,
            'pledge_asset' => ActivityServices::userPledge($user, $wid),
            'income' => ActivityServices::cumulativeIncome($user, $wid),
            'activity' => $info
        ];
        return $this->success($result);

    }

    /**
     * 质押
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function store(Request $request)
    {
        //
        $amount = $request->input('amount', 0);
        $activity_id = $request->input('activity_id', 0);

        if ($amount <= 0) {
            return $this->failed('请输入正确的操作数量');
        }

        $user = \Auth::user();
//        $activity = Activity::whereWid($activity_id)->first();
        $activity = Activity::find($activity_id);
        if (!$activity) {
            return $this->failed('活动不存在');
        }
        if (!$activity->status) {
            return $this->failed('活动未开启');
        }
        if ($amount > $activity->max_num) {
            return $this->failed('参与数量大于最大限制' . $activity->max_num);
        }
        if ($amount < $activity->min_num) {
            return $this->failed('参与数量小于最小限制' . $activity->min_num);
        }

        DB::beginTransaction();
        try {

            $result = $user->activity()->create([
                'activity_id' => $activity->id,
                'uid' => $user->id,
                'wid' => $activity->wid,
                'amount' => $amount,
                'cycle' => $activity->cycle,
                'day_rate' => $activity->day_rate,
                'damages_rate' => $activity->damages_rate,
            ]);
            $id = $result->id;


            if (!$result) {
                throw new \Exception('创建失败');
            }

            $assetService = new AssetService();

            $assetService->writeBalanceLog($user->id, $id, $activity->wid, ActivityServices::ACCOUNT,
                -($amount), UserMoneyLog::BUSINESS_TYPE_ACTIVITY_IN, '参与质押');


            DB::commit();

            ActivityServices::afterJoinActivity($user, $amount);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->failed($exception->getMessage());
        }

        return $this->success();
    }

    /**
     * 抽取
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function out(Request $request)
    {
        $user = \Auth::user();
        $record_id = $request->input('record_id', 0);

        $joinInfo = $user->activity()->where(['id' => $record_id])->first();
        if (!$joinInfo) {
            return $this->failed('尚未质押进入');
        }

        if ($joinInfo->status != ActivityUser::PROFIT_ING) {
            return $this->failed('质押记录已结束');
        }

        DB::beginTransaction();
        try {

            $damages = bcMath($joinInfo->amount, $joinInfo->damages_rate * 0.01, '*');

            //退还数量 = 质押数量 - 违约金
            $after_amount = bcMath($joinInfo->amount, $damages, '-');

            $result = $user->activity()->where([
                'id' => $record_id,
                'version' => $joinInfo->version
            ])->update([
                'status' => ActivityUser::PROFIT_OVER_W,
                'damages' => $damages,
                'version' => $joinInfo->version + 1,
            ]);

            if ($result === false) {
                throw new \Exception('更新失败');
            }

            if ($after_amount > 0) {
                $assetService = new AssetService();
                $assetService->writeBalanceLog($user->id, $record_id, $joinInfo->wid, ActivityServices::ACCOUNT,
                    $after_amount, UserMoneyLog::BUSINESS_TYPE_ACTIVITY_OUT, '抽取');
            }

            DB::commit();

            ActivityServices::afterOutActivity($user);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 参与记录
     * @param Request $request
     * @return mixed
     */
    public function show_record(Request $request)
    {
        $user = \Auth::user();

        $result = $user->activity()
            ->select('id as record_id', 'activity_id', 'created_at', 'amount', 'cycle', 'days', 'profit', 'day_rate', 'damages_rate', 'status')
            ->with('activity:id,describe')
            ->orderByDesc('id')
            ->get();

        return $this->success($result);
    }

    /**
     * 收益记录
     * @param Request $request
     * @return mixed
     */
    public function show_profit(Request $request)
    {
        $user = \Auth::user();
        $record_id = $request->input('record_id', 0);

        $model = $user->moneyLog();
        $model->select('money', 'mark', 'created_at');
        $model->where('target_id', $record_id);
        $model->whereIn('type', [
            UserMoneyLog::BUSINESS_TYPE_ACTIVITY_PROFIT,
        ]);
        $logs = $model->orderByDesc('id')->simplePaginate(15);

        return $this->success($logs);
    }


}
