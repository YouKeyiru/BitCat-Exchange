<?php


namespace App\Services;


use App\Models\ContractPosition;
use App\Models\ContractTrans;
use App\Models\User;
use App\Models\UserAsset;
use Illuminate\Support\Facades\Log;

class BurstService
{

    public static function floatProfit(int $uid): array
    {
        $userPositions = ContractPosition::where(['uid' => $uid])->get();

        $minprofit = 0;
        $allprofit = 0;
        $alldeposit = 0;
        $minid = 0;

        //计算总保证金，总浮动盈亏，最小浮动盈亏
        foreach ($userPositions ?? [] as $userPosition) {
            $newPrice = MarketService::getCodePrice($userPosition->code);
            if (!$newPrice) {
                Log::info($userPosition->code . '没有得到最新价');
                break;
            }

            if ($userPosition->otype == 1) {
                $profit = ($newPrice - $userPosition->buy_price) * $userPosition->buy_num;
            } else {
                $profit = ($userPosition->buy_price - $newPrice) * $userPosition->buy_num;
            }

            if ($profit < $minprofit) {
                $minprofit = $profit;
                $minid = $userPosition->id;
            }

            $allprofit += $profit;
            $alldeposit += $userPosition->total_price;
        }

        return [$minprofit, $alldeposit, $allprofit, $minid];
    }

    /**
     * 爆仓检测
     * @param $contract_redis
     * @param $subscribe
     */
    public static function doBurstPositions($contract_redis, $subscribe)
    {
        $key = 'contract:order:positions:' . $subscribe['code'];

        $members = $contract_redis->smembers($key);
        foreach ($members ?? [] as $order_no) {

            try {
                //订单信息
                $order_info = $contract_redis->hgetall($order_no);
                if (!$order_info) continue;

                list($minprofit, $alldeposit, $allprofit, $minid) = self::floatProfit($order_info['uid']);
                if ($alldeposit <= 0) {
                    continue;
                }

                $asset = AssetService::_getBalance($order_info['uid'], ContractTransService::WID, UserAsset::ACCOUNT_CONTRACT);

                //计算爆仓率  （余额 + 保证金 + 浮动盈亏）/ 保证金
                $risk = round(($asset->balance + $alldeposit + $allprofit) / $alldeposit, 2);

                //取到后台设置爆仓率  计算爆仓率 <= 爆仓率 触发 爆仓
                $bcRate = self::getRatesSet();

                if (!$bcRate) {
                    Log::error('bcRate error');
                    break;
                }

                if ($risk > ($bcRate * 0.01)) {
                    continue;
                }

                $model = ContractPosition::query();
                if ($minid) {
                    $model->where('id', $minid);
                } else {
                    $model->where('uid', $order_info['uid']);
                }
                $minPosition = $model->first();

                $key = 'contract:order:positions:' . $minPosition->code;
                $user = User::find($minPosition->uid);
                ContractTransService::closePosition($user, $minPosition->order_no, ContractTrans::CLOSE_BURST);
                ContractTransService::delCacheOrder($minPosition->order_no, $key);
            } catch (\Exception $exception) {
                var_dump($exception->getLine());
                Log::error('burst=>' . $exception->getMessage());
            }
        }
    }

    /**
     * 获取系统爆仓率
     * @return mixed
     */
    public static function getRatesSet()
    {
        return \DB::table('admin_config')
            ->where('name', 'contract.burst_rate')
            ->value('value');
    }
}
