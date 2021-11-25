<?php

namespace Daling\Balance\Wallet;

class BtcInterface
{
    public $bitcoin;

    function __construct($config)
    {
        $this->bitcoin = new Bitcoin($config['rpc_user'], $config['rpc_pwd'], $config['rpc_ip'], $config['rpc_port']);
    }

    /**
     * USDT记录
     * @param $address
     * @param $count
     * @return mixed
     */
    public function usdtTransList($address, $count = 20)
    {
        return $this->bitcoin->omni_listtransactions($address, $count);
    }

    /**
     *  BTC记录
     * @param $account
     * @param int $count
     * @return mixed
     */
    public function btcTransList($account, $count = 20)
    {
        return $this->bitcoin->listtransactions($account, $count);
    }

}
