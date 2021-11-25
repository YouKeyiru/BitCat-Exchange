<?php

namespace Daling\Balance\Models;

use Daling\Balance\Controllers\BtcSeries;
use Daling\Balance\Controllers\EthSeries;
use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    const WAIT_PAY = 1; //充值中
    const PAYED = 2; //充值成功

    protected $table = 'address_recharges';
    protected $guarded = ['id'];

    /**
     * @param int $uid 用户ID
     * @param int $wid 资产币种ID
     * @param string $code 资产币种code
     * @param string $address 查询地址
     * @param string $contractAddress 合约地址
     * @return array
     */
    public static function checkEthSeries(int $uid, int $wid, string $code, string $address, string $contractAddress = ''): array
    {
        $obj = new EthSeries(true);
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
