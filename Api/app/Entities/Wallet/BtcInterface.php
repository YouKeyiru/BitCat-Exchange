<?php

namespace App\Entities\Wallet;

class BtcInterface
{

    private $user;
    private $pwd;
    private $ip;
    private $port;
    public $bitcoin;

    function __construct()
    {
        $this->user = config('btc.rpc_user');
        $this->pwd = config('btc.rpc_pwd');
        $this->ip = config('btc.rpc_ip');
        $this->port = config('btc.rpc_port');
        $this->bitcoin = new Bitcoin($this->user, $this->pwd, $this->ip, $this->port);
    }

    /**
     * 获取新的充值钱包地址
     * @param $account
     * @return mixed
     */
    public function createWallet($account)
    {
        $address = $this->bitcoin->getnewaddress($account);
        return $address ?? false;
    }

    /**
     * USDT记录
     * @param $address
     * @param $count
     * @return mixed
     */
    /**
     * @param $address
     * @param int $count
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
        $list = $this->bitcoin->listtransactions($account, $count);
        return $list;
    }

}