<?php


namespace App\Services;


use App\Models\UserAsset;
use App\Models\UserMoneyLog;
use App\Models\WalletCode;
use Exception;

class AssetService
{

    /**
     * 获取余额
     * @param $uid
     * @param $wid
     * @param int $account
     * @return mixed
     */
    public static function _getBalance($uid, $wid, $account = 2)
    {
        $asset = UserAsset::where([
            'uid' => $uid,
            'wid' => $wid,
            'account' => $account
        ])->first();
        //没有钱包就创建
        if (!$asset) {
            $asset = UserAsset::create([
                'uid' => $uid,
                'wid' => $wid,
                'account' => $account,
                'balance' => 0,
                'frost' => 0,
//                'keep' => 0,
                'total_recharge' => 0,
                'total_withdraw' => 0,
            ]);
        }
        return $asset;
    }

    /**
     * 获取余额
     * @param $uid
     * @param $account
     * @return mixed
     */
    public static function _getAssetByAccount($uid, $account)
    {
        start:
        $asset = UserAsset::where([
            'uid' => $uid,
            'account' => $account
        ])->withOnly('walletCode', ['code'])->get();
        if ($asset->isEmpty()) {
            $wids = WalletCode::pluck('code', 'id');
            foreach ($wids as $wid => $code) {
                UserAsset::create([
                    'uid' => $uid,
                    'wid' => $wid,
                    'account' => $account,
                    'balance' => 0,
                    'frost' => 0,
                    'total_recharge' => 0,
                    'total_withdraw' => 0,
                ]);
            }
            goto start;
        }
        return $asset;
    }

    /**
     * 资产写入
     * @param $uid
     * @param $target_id
     * @param $wid
     * @param $account
     * @param $amount
     * @param $type
     * @param $mark
     * @param int $times
     * @return mixed
     * @throws Exception
     */
    public function writeBalanceLog($uid, $target_id, $wid, $account, $amount, $type, $mark, $times = 1)
    {
        if ($times > 5) {
            throw new Exception(trans('asset.change_balance_failed'));
        }
        if (!$amount) {
            throw new Exception(trans('asset.change_amount_is_0'));
        }

        $asset = self::_getBalance($uid, $wid, $account);
        if (!$asset) {
            throw new Exception(trans('asset.user_not_found'));
        }

        $before_amount = $asset->balance;
        $after_amount = bcMath($asset->balance, $amount, '+');
        if ($after_amount < 0) {
            throw new Exception(trans('asset.balance_not_enough'));
        }

        $after_version = $asset->version + 1;
        $result = UserAsset::where([
            'uid' => $uid,
            'wid' => $wid,
            'account' => $account,
            'version' => $asset->version
        ])->update([
            'balance' => $after_amount,
            'version' => $after_version,
        ]);
        if (!$result) {
            $times++;
            sleep(1);
            return $this->writeBalanceLog($uid, $target_id, $wid, $account, $amount, $type, $mark, $times);
        }

        //写入用户资金日志
        UserMoneyLog::create([
            'uid' => $asset->uid,
            'wid' => $wid,
            'account' => $account,
            'target_id' => $target_id,
            'ymoney' => $before_amount,
            'money' => $amount,
            'nmoney' => $after_amount,
            'type' => $type,
            'mark' => $mark,
            'wt' => 1,
        ]);


        #========
        if ($account == UserAsset::ACCOUNT_CONTRACT) {

            //累计除保持金额
            if (in_array($type, [
                UserMoneyLog::PROFIT_BACK,
                UserMoneyLog::PROFIT_BACK_DAY,
                UserMoneyLog::BUSINESS_TYPE_ACTIVITY_PROFIT
            ])) {
                //单独处理保持的余额
                $asset->keep = $asset->keep + $amount;
                $asset->save();
            }

            //非合约下单时验证余额
            if ($amount < 0 && $type != UserMoneyLog::CONTRACT) {
                //
                $sure = bcMath($asset->balance, $asset->keep, '-');
                if (abs($amount) > $sure) {
                    throw new Exception('可用余额不足，保持金额'.floatval($asset->keep));
                }
            }else{

                //合约下单，可能会消费掉
                if ($after_amount < $asset->keep){
                    $asset->keep = $after_amount;
                    $asset->save();
                }
            }
        }
        #========

    }

    /**
     * @throws Exception
     */
    public function writeFrostLog($uid, $target_id, $wid, $account, $amount, $type, $mark, $times = 1)
    {
        if ($times > 5) {
            throw new Exception(trans('asset.change_balance_failed'));
        }
        if (!$amount) {
            throw new Exception(trans('asset.change_amount_is_0'));
        }

        $asset = self::_getBalance($uid, $wid, $account);
        if (!$asset) {
            throw new Exception(trans('asset.user_not_found'));
        }

        $before_amount = $asset->frost;
        $after_amount = bcMath($asset->frost, $amount, '+');
        if ($after_amount < 0) {
            throw new Exception(trans('asset.frost_not_enough'));
        }

        $after_version = $asset->version + 1;
        $result = UserAsset::where([
            'uid' => $uid,
            'wid' => $wid,
            'account' => $account,
            'version' => $asset->version
        ])->update([
            'frost' => $after_amount,
            'version' => $after_version,
        ]);
        if (!$result) {
            $times++;
            return $this->writeFrostLog($uid, $target_id, $wid, $account, $amount, $type, $mark, $times);
        }

        //写入用户资金日志
        UserMoneyLog::create([
            'uid' => $asset->uid,
            'wid' => $wid,
            'account' => $account,
            'target_id' => $target_id,
            'ymoney' => $before_amount,
            'money' => $amount,
            'nmoney' => $after_amount,
            'type' => $type,
            'mark' => $mark,
            'wt' => 2,
        ]);
    }
}
