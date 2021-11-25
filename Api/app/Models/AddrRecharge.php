<?php

namespace App\Models;

use App\User;
use Daling\Balance\Controllers\BtcSeries;
use Daling\Balance\Controllers\EthSeries;

class AddrRecharge extends \Daling\Balance\Models\Recharge
{
    //

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

    public function wallet()
    {
        return $this->belongsTo(WalletCode::class, 'wid', 'id');
    }

    public static function checkEthSeries(int $uid, int $wid, string $code, string $address, string $contractAddress = ''): array
    {
        $obj = new EthSeries(false);
        return $obj->handle($uid, $wid, $code, $address, $contractAddress);
    }

    /**
     * @param int $uid 用户ID
     * @param int $wid 资产币种ID
     * @param string $code 资产币种code
     * @param string $account 用户账号
     * @param string $address 查询地址 ，查询USDT时必传
     * @return array
     */
    public function checkBtcSeries(int $uid, int $wid, string $code, string $account, string $address = ''): array
    {
        $obj = new BtcSeries();
        return $obj->handel($uid, $wid, $code, $account, $address);
    }
}
