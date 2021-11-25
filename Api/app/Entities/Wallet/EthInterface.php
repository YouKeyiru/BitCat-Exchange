<?php

namespace App\Entities\Wallet;


class EthInterface extends Base
{
    //检测地址
    public function checkAccount($address)
    {
        return $this->curl_get('checkAccount', ['address' => $address]);
    }

    //创建账户
    public function createWallet()
    {
        return $this->curl_get('createWallet');
    }

    //代币余额
    public function tokenBalance($address, $tokenAddress)
    {
        return $this->curl_get('tokenBalance', ['address' => $address, 'tokenaddress' => $tokenAddress]);
    }

    //eth余额
    public function ethBalance($address)
    {
        return $this->curl_get('ethBalance', ['address' => $address]);
    }

    //代币转账
    public function tokenTrans($privateKey, $contractAddress, $address, $amount, $gasLimit, $gasPrice)
    {
        $params = [
            "private_key"   => $privateKey,
            "token_address" => $contractAddress,
            "to_address"    => $address,
            "value"         => strval($amount),
            "gasLimit"      => $gasLimit,
            "gasPrice"      => $gasPrice,
            "isdecry"       => "1",
        ];
        return $this->curl_post('tokenTrans', $params);
    }

    //eth转账
    public function ethTrans($privateKey, $address, $amount, $gasLimit, $gasPrice)
    {
        $params = [
            "private_key" => $privateKey,
            "to_address"  => $address,
            "value"       => $amount,
            "gasLimit"    => $gasLimit,
            "gasPrice"    => $gasPrice,
            "isdecry"     => "0",
        ];
        return $this->curl_post('ethTrans', $params);
    }

    //查询交易详情
    public function transDetail($hash)
    {
        return $this->curl_get('dealDetail', ['txhash' => $hash]);
    }


}
