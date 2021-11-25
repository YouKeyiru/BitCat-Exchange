<?php


namespace App\Services;


use App\Models\UserGiftAsset;
use App\Models\UserGiftLog;
use App\Models\WalletCode;
use Exception;

class GiftService
{

    /**
     * 获取余额
     * @param $uid
     * @param $wid
     * @param int $account
     * @return mixed
     */
    public static function _getBalance($uid, $wid)
    {
        $asset = UserGiftAsset::where([
            'uid' => $uid,
            'wid' => $wid,
        ])->first();
        //没有钱包就创建
        if (!$asset) {
            $asset = UserGiftAsset::create([
                'uid' => $uid,
                'wid' => $wid,
                'balance' => 0,
                'frost' => 0,
                // 'keep' => 0,
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
    public static function _getAssetByAccount($uid)
    {
        start:
        $asset = UserGiftAsset::where([
            'uid' => $uid,
        ])->withOnly('walletCode', ['code'])->get();
        if ($asset->isEmpty()) {
            $wids = WalletCode::pluck('code', 'id');
            foreach ($wids as $wid => $code) {
                UserGiftAsset::create([
                    'uid' => $uid,
                    'wid' => $wid,
                    'balance' => 0,
                    'frost' => 0,
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
    public function writeBalanceLog($uid, $target_id, $wid, $amount, $type, $mark, $times = 1)
    {
        if ($times > 5) {
            throw new Exception(trans('asset.change_balance_failed'));
        }
        if (!$amount) {
            throw new Exception(trans('asset.change_amount_is_0'));
        }

        $asset = self::_getBalance($uid, $wid);
        if (!$asset) {
            throw new Exception(trans('asset.user_not_found'));
        }

        $before_amount = $asset->balance;
        $after_amount = bcMath($asset->balance, $amount, '+');
        if ($after_amount < 0) {
            throw new Exception(trans('asset.balance_not_enough'));
        }

        $after_version = $asset->version + 1;
        $result = UserGiftAsset::where([
            'uid' => $uid,
            'wid' => $wid,
            'version' => $asset->version
        ])->update([
            'balance' => $after_amount,
            'version' => $after_version,
        ]);
        if (!$result) {
            $times++;
            return $this->writeBalanceLog($uid, $target_id, $wid, $amount, $type, $mark, $times);
        }

        //写入用户资金日志
        UserGiftLog::create([
            'uid' => $asset->uid,
            'wid' => $wid,
            'target_id' => $target_id,
            'ymoney' => $before_amount,
            'money' => $amount,
            'nmoney' => $after_amount,
            'type' => $type,
            'mark' => $mark,
            'wt' => 1,
        ]);
    }

    /**
     * @throws Exception
     */
    public function writeFrostLog($uid, $target_id, $wid, $amount, $type, $mark, $times = 1)
    {
        if ($times > 5) {
            throw new Exception(trans('asset.change_balance_failed'));
        }
        if (!$amount) {
            throw new Exception(trans('asset.change_amount_is_0'));
        }
        $asset = self::_getBalance($uid, $wid);
        if (!$asset) {
            throw new Exception(trans('asset.user_not_found'));
        }
        $before_amount = $asset->frost;
        $after_amount = bcMath($asset->frost, $amount, '+');
        if ($after_amount < 0) {
            throw new Exception(trans('asset.frost_not_enough'));
        }
        $after_version = $asset->version + 1;
        $result = UserGiftAsset::where([
            'uid' => $uid,
            'wid' => $wid,
            'version' => $asset->version
        ])->update([
            'frost' => $after_amount,
            'version' => $after_version,
        ]);
        if (!$result) {
            $times++;
            return $this->writeFrostLog($uid, $target_id, $wid, $amount, $type, $mark, $times);
        }
        //写入用户资金日志
        UserGiftLog::create([
            'uid' => $asset->uid,
            'wid' => $wid,
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
