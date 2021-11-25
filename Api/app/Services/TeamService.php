<?php


namespace App\Services;


use App\Models\User;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;

class TeamService
{

    /**
     * 我的团队人数
     * @param User $user
     * @return int
     */
    public static function total(User $user)
    {
//        return $user->ext->team_user;
        return User::query()->where('recommend_id', $user->id)->count() ?? 0;
    }

    /**
     * 我的收益
     * @param User $user
     * @return float
     */
    public static function income(User $user)
    {
        // $asset = AssetService::_getBalance($user->id,1,UserAsset::ACCOUNT_COMMISSION);
        // return floatval($asset->balance);
        // $asset = AssetService::_getBalance($user->id,1,UserAsset::ACCOUNT_COMMISSION);
        $moneyTotal = $user->moneyLog()->whereIn('type', [
            UserMoneyLog::RECHARGE_REBATE
        ])->sum('money');
        return floatval($moneyTotal);
    }

}
