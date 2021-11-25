<?php

namespace App\Models;

use App\User;
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
}
