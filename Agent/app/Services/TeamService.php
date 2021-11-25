<?php


namespace App\Services;


use App\Models\User;

class TeamService
{

    /**
     * 我的团队人数
     * @param User $user
     * @return int
     */
    public static function total(User $user)
    {
        return User::query()->where('recommend_id', $user->id)->count() ?? 0;
    }

    /**
     * 我的收益
     * @param User $user
     * @return int
     */
    public static function income(User $user)
    {
        return 0;
    }

}
