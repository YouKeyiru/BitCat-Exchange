<?php


namespace App\Services;


use App\Http\Traits\Job;
use App\Models\ActivityUser;
use App\Models\DayBackProfit;
use App\Models\User;
use App\Models\UserAsset;
use App\Models\UserExt;
use App\Models\UserMoneyLog;

class CommissionService
{
    use Job;

    //处理盈亏
    public static function doProfit($order)
    {
        // 1.盈利立即反现 30%
        // 2.亏损按每天 10% 返还，直至返完
        // 3.返现金额只能进行合约交易，无法提出/划转
        try {
            if ($order->profit < 0) {
                //亏损
                DayBackProfit::query()->create([
                    'uid' => $order->uid,
                    'amount' => abs($order->profit),
                    'rate' => DayBackProfit::getRate(),
                ]);
            } else {
                //盈利

                $rate = self::getBackRate() * 0.01;
                $amount = bcMath($order->profit, $rate, '*');

                if ($amount > 0) {
                    $assetService = new AssetService();
                    $assetService->writeBalanceLog($order->uid, $order->id, ContractTransService::WID, UserAsset::ACCOUNT_CONTRACT, $amount,
                        UserMoneyLog::PROFIT_BACK, sprintf('订单盈亏[%s]反现[%s]', $order->profit, $amount));

                }
            }
        } catch (\Exception $exception) {

            \Log::error(sprintf('合约交易盈亏返现部分 异常，[%s]', $exception->getMessage()));
        }

    }

    //盈利立即反现 比例
    protected static function getBackRate()
    {
        return \DB::table('admin_config')
            ->where('name', 'contract.back_rate')
            ->value('value');
    }

    //合约交易手续费返佣
    public static function doTransFee($order)
    {
        self::trans_fee_profit($order);

    }

    //质押挖矿收益返佣
    public static function doMiningProfit($uid, $target_id, $profit)
    {
        self::mining_profit($uid, $target_id, $profit);
    }

    //合约交易手续费返佣 比例
    public static function getUserTransFeeRate(int $uid)
    {
        $config = self::levelCondition();
        $rate = 0;
        $user = User::find($uid);
        if ($user) {
            $grade = $user->ext->grade;
            //不能取config
//            $rates = [
//                1 => 10,
//                2 => 20,
//                3 => 30,
//                4 => 40,
//                5 => 50,
//            ];
            $rates = [
                1 => $config['grade_1_trans_rate'],
                2 => $config['grade_2_trans_rate'],
                3 => $config['grade_3_trans_rate'],
                4 => $config['grade_4_trans_rate'],
                5 => $config['grade_5_trans_rate'],
            ];
            if (array_key_exists($grade, $rates)) {
                $rate = $rates[$grade];
            }
        }
        return $rate;
    }

    //挖矿收益返佣 比例
    public static function getUserMiningRate(int $uid)
    {
        $config = self::levelCondition();

        $rate = 0;
        $user = User::find($uid);
        if ($user) {
            $grade = $user->ext->grade;
            //不能取config
//            $rates = [
//                1 => 15,
//                2 => 25,
//                3 => 35,
//                4 => 45,
//                5 => 50,
//            ];
            $rates = [
                1 => $config['grade_1_mining_rate'],
                2 => $config['grade_2_mining_rate'],
                3 => $config['grade_3_mining_rate'],
                4 => $config['grade_4_mining_rate'],
                5 => $config['grade_5_mining_rate'],
            ];
            if (array_key_exists($grade, $rates)) {
                $rate = $rates[$grade];
            }
        }
        return $rate;
    }

    //给上级累计业绩
    public static function updateInvestment(User $user, $investment)
    {
        if (!$user) {
            return;
        }
        $path = array_filter(explode(',', $user->relationship));
        UserExt::whereIn('uid', $path)->increment('market_investment', $investment);
    }

    //更新有效用户
    public static function updateBecomeUser(User $user)
    {

        if (!$user) {
            return;
        }
        //首次作为有效用户
        if ($user->activity()->count() != 1) {
            return;
        }

        //团队有效人数
        $path = array_filter(explode(',', $user->relationship));
        if (is_array($path)) {
            UserExt::whereIn('uid', $path)->increment('team_user', 1);
        }

        if ($user->recommend_id) {
            //直推有效人数
            UserExt::where('uid', $user->recommend_id)->increment('push_user', 1);
        }

    }

    //直推有效人数
    public static function getPushUserCount(User $user)
    {
        return $user->ext->push_user ?? 0;
    }

    //团队有效人数
    public static function getTeamUserCount(User $user)
    {
        return $user->ext->team_user ?? 0;
    }

    //伞下总业绩
    public static function getTeamInvestment(User $user)
    {
        return $user->ext->market_investment ?? 0;
    }

    //质押数量  持仓账户
    public static function getActivityNum(User $user)
    {
        return $user->activity()
                ->where('status', ActivityUser::PROFIT_ING)
                ->sum('amount') ?? 0;
    }

    //更新等级
    public static function updateLevel(User $user)
    {
        //团队有效人数
        $path = array_filter(explode(',', $user->relationship));
        if (is_array($path)) {
            $path[]=strval($user->id);
            foreach ($path as $uid) {
                $user = User::find($uid);
                //直推有效会员
                $push_user = self::getPushUserCount($user);

                //团队有效会员
                $team_user = self::getTeamUserCount($user);

                //持仓账户
                $activity_num = self::getActivityNum($user);

                //伞下总业绩
                $team_investment = self::getTeamInvestment($user);

                //节点数
                $node_num = self::getUserCountByGrade($user, 3);

                //超级节点数
                $super_node_num = self::getUserCountByGrade($user, 4);

                //条件
                $levelCondition = self::levelCondition();
//                \Log::info($levelCondition);
                //等级
                $level = self::userLevel();

                $before_grade = $user->ext->grade;
                $last_grade = 0;
                foreach ($level as $v => $name) {
                    if (!$v) {
                        continue;
                    }

                    if ($push_user >= $levelCondition['grade_' . $v . '_push_user'] &&
                        $team_user >= $levelCondition['grade_' . $v . '_team_user'] &&
                        $activity_num >= $levelCondition['grade_' . $v . '_activity_num'] &&
                        $team_investment >= $levelCondition['grade_' . $v . '_team_investment'] &&
                        $node_num >= $levelCondition['grade_' . $v . '_node_num'] &&
                        $super_node_num >= $levelCondition['grade_' . $v . '_super_node_num']
                    ) {

                        $last_grade = $v;
                    } else {
                        \Log::info($user->id . ' 升' . $v . '级条件不符合' .
                            json_encode(compact('push_user', 'team_user', 'activity_num', 'team_investment', 'node_num', 'super_node_num')));
                        break;
                    }
                }
                if ($last_grade != $user->ext->grade) {
                    //升级、降级
                    $user->ext()->update(['grade' => $last_grade]);
                    \Log::info('升级、降级 成功=>' . json_encode(['uid' => $user->id, 'before_grade' => $before_grade, 'last_grade' => $last_grade]));
                    self::afterUpgrade($user, $before_grade, $last_grade);
                }
            }
        }
    }

    //升级后操作
    protected static function afterUpgrade(User $user, int $beforeGrade, int $afterGrade)
    {
        //更新上级的团队等级人数
        //团队有效人数
        $path = array_filter(explode(',', $user->relationship));
        if (!is_array($path)) {
            return;
        }

        if ($beforeGrade == 3) {
            UserExt::whereIn('uid', $path)->decrement('node_num', 1);
        }
        if ($beforeGrade == 4) {
            UserExt::whereIn('uid', $path)->decrement('super_node_num', 1);
        }

        if ($afterGrade == 3) {
            UserExt::whereIn('uid', $path)->increment('node_num', 1);
        }
        if ($afterGrade == 4) {
            UserExt::whereIn('uid', $path)->increment('super_node_num', 1);
        }

    }

    //团队 指定等级人数
    public static function getUserCountByGrade(User $user, int $grade)
    {
        $num = 0;
        if ($grade == 3) {
            $num = $user->ext->node_num ?? 0;
        }
        if ($grade == 4) {
            $num = $user->ext->super_node_num ?? 0;
        }
        return $num;
    }

    //级别
    public static function userLevel()
    {
        return [
            0 => '普通用户',
            1 => '普通会员',
            2 => 'VIP会员',
            3 => '节点会员',
            4 => '超级节点',
            5 => '社区',
        ];
    }

    //级别条件
    public static function levelCondition()
    {
        $data = \DB::table('admin_config')->where('name', 'like', 'grade%')->pluck('value', 'name')->toArray();
        $level = [];
        for ($i = 1; $i <= 5; $i++) {
            $level['grade_' . $i . '_push_user'] = $data['grade.condition_' . $i . '_1'];
            $level['grade_' . $i . '_team_user'] = $data['grade.condition_' . $i . '_2'];
            $level['grade_' . $i . '_activity_num'] = $data['grade.condition_' . $i . '_3'];
            $level['grade_' . $i . '_team_investment'] = $data['grade.condition_' . $i . '_4'];
            $level['grade_' . $i . '_node_num'] = $data['grade.condition_' . $i . '_5'];
            $level['grade_' . $i . '_super_node_num'] = $data['grade.condition_' . $i . '_6'];

            $level['grade_' . $i . '_trans_rate'] = $data['grade.condition_' . $i . '_7'];
            $level['grade_' . $i . '_mining_rate'] = $data['grade.condition_' . $i . '_8'];
        }
        return $level;

//        return [
//            'grade_1_push_user' => 1,
//            'grade_1_team_user' => 0,
//            'grade_1_activity_num' => 100,
//            'grade_1_team_investment' => 0,
//            'grade_1_node_num' => 0,
//            'grade_1_super_node_num' => 0,
//
//            'grade_2_push_user' => 3,
//            'grade_2_team_user' => 0,
//            'grade_2_activity_num' => 1000,
//            'grade_2_team_investment' => 3000,
//            'grade_2_node_num' => 0,
//            'grade_2_super_node_num' => 0,
//
//            'grade_3_push_user' => 5,
//            'grade_3_team_user' => 20,
//            'grade_3_activity_num' => 1500,
//            'grade_3_team_investment' => 30000,
//            'grade_3_node_num' => 0,
//            'grade_3_super_node_num' => 0,
//
//            'grade_4_push_user' => 10,
//            'grade_4_team_user' => 100,
//            'grade_4_activity_num' => 3000,
//            'grade_4_team_investment' => 100000,
//            'grade_4_node_num' => 3,
//            'grade_4_super_node_num' => 0,
//
//            'grade_5_push_user' => 15,
//            'grade_5_team_user' => 300,
//            'grade_5_activity_num' => 5000,
//            'grade_5_team_investment' => 300000,
//            'grade_5_node_num' => 0,
//            'grade_5_super_node_num' => 3,
//        ];
    }
}
