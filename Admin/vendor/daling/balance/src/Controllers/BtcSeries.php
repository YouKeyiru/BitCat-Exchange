<?php

namespace Daling\Balance\Controllers;

use Daling\Balance\Event\RechargeEvent;
use Daling\Balance\Models\Recharge;
use Daling\Balance\Wallet\BtcInterface;
use Illuminate\Support\Facades\Log;

class BtcSeries
{
    public $btcObj;

    private $config;

    /**
     * 确认次数，默认3次
     * @var int
     */
    private $confirmations = 3;

    /**
     * 查询用户ID
     * @var int
     */
    private $uid;

    /**
     * 资产币种ID
     * @var int
     */
    private $wid;

    /**
     * 资产币种code标识
     * @var string
     */
    private $code;

    /**
     * 查询地址
     * @var string
     */
    private $address;

    /**
     * 是否开启事件通知
     * @var bool
     */
    private $isEvent;

    public function __construct($isEvent = false)
    {
        $this->config = config('recharge.btc_series');
        $this->btcObj = new BtcInterface($this->config);
        $this->confirmations = $this->config['confirmations'] ?? $this->confirmations;
        $this->isEvent = $isEvent;
    }

    /**
     * 执行查询
     * @param int $uid
     * @param int $wid
     * @param string $code
     * @param string $address
     * @param string $account
     * @return array
     */
    public function handel(int $uid, int $wid, string $code, string $account, string $address = ''): array
    {
        $this->uid = $uid;
        $this->wid = $wid;
        $this->code = strtolower($code);
        $this->address = $address;
        $result = [];
        $updateAsset = [];
        try {
            if ($this->code == 'btc') {
                $result = $this->btcObj->btcTransList($account);
            }
            if ($this->code == 'usdt') {
                $result = $this->btcObj->usdtTransList($this->address);
            }
            $updateAsset = $this->processingData($result);
            if ($this->isEvent) {
                event(new RechargeEvent($updateAsset));
            }
        } catch (\Exception $exception) {
            Log::error(json_encode([
                'error' => $exception->getMessage()
            ]));
        }
        return $updateAsset;
    }

    /**
     * 处理查询到的结果
     * @param array $result
     * @return array
     */
    protected function processingData(array $result): array
    {
        $updateAsset = [];
        if (is_array($result)) {
            foreach ($result as $k1 => $val) {
                $amount = $val['amount'];
                if ($amount <= 0 || $val['confirmations'] >= $this->confirmations) {
                    continue;
                }
                if ($this->code == 'btc') {
                    $recharge = Recharge::where(['uid' => $this->uid, 'address' => $this->address, 'hash' => $val['txid']])->first();
                } else {
                    //usdt 记录里区分发送地址和接收地址
                    $recharge = Recharge::where(['uid' => $this->uid, 'address' => $val['referenceaddress'], 'hash' => $val['txid']])->first();
                }

                if ($recharge) {
                    continue;
                }
                $create = Recharge::create([
                    'uid'     => $this->uid,
                    'address' => $this->address,
                    'hash'    => $val['txid'],
                    'amount'  => $amount,
                    'status'  => Recharge::PAYED,
                    'wid'     => $this->wid,
                    'code'    => $this->code
                ]);
                if ($create) {
                    //记录充值成功的条目
                    array_push($updateAsset, $create->toArray());
                    Log::info(sprintf('用户[%s]在线充值[%s]插入成功,数量[%s],哈希[%s]', $this->uid, $this->code, $amount, $val['txid']));
                }
            }
        }
        return $updateAsset;
    }
}
