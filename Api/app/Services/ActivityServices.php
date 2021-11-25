<?php


namespace App\Services;


use App\Models\ActivityUser;
use App\Models\UserAsset;

class ActivityServices
{
    const ACCOUNT = UserAsset::ACCOUNT_LEGAL;

    //质押之后业务
    public static function afterJoinActivity($user, $amount)
    {

        //增加团队有效人数
        CommissionService::updateBecomeUser($user);

        //增加团队投资额
        CommissionService::updateInvestment($user, $amount);

        //更新等级
        CommissionService::updateLevel($user);
    }

    //质押撤出之后业务
    public static function afterOutActivity($user)
    {

        //增加团队投资额
//        CommissionService::updateInvestment($user, $amount);

        //更新等级
        CommissionService::updateLevel($user);
    }


    //质押的资产
    public static function userPledge($user, $wid)
    {
        return $user->activity()->where('wid', $wid)
                ->where('status', ActivityUser::PROFIT_ING)
                ->sum('amount') ?? 0;
    }

    //累计收益
    public static function cumulativeIncome($user, $wid = 1)
    {
        return $user->activity()->where('wid', $wid)
//                ->whereIn('status', [ActivityUser::PROFIT_OVER, ActivityUser::PROFIT_OVER_W])
                ->sum('profit') ?? 0;
    }
}
