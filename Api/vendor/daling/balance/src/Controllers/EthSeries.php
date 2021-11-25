<?php

namespace Daling\Balance\Controllers;

use Daling\Balance\Event\RechargeEvent;
use Daling\Balance\Models\Recharge;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class EthSeries
{
    public $baseUrl;

    public $requestUrl;

    private $config;

    /**
     * 查询参数
     * @var array
     */
    private $param;

    /**
     * 查询apiKey
     * @var string
     */
    private $token;

    /**
     * 查询用户ID
     * @var int
     */
    private $uid;

    /**
     * 查询地址
     * @var string
     */
    private $address;

    /**
     * 合约地址，默认空，如果查询ETH不需要该值
     * @var string
     */
    private $contractAddress;

    /**
     * http请求超时时间
     * @var int
     */
    private $timeout = 10;

    /**
     * 确认次数，默认6次
     * @var int
     */
    private $confirmations = 6;

    /**
     * 资产币种code标识
     * @var string
     */
    private $code;

    /**
     * 资产币种ID
     * @var int
     */
    private $wid;

    /**
     * 是否开启事件通知
     * @var bool
     */
    private $isEvent;

    /**
     * EthSeries constructor.
     * @param bool $isEvent
     */
    public function __construct($isEvent = false)
    {
        $this->config = config('recharge.eth_series');
        $this->baseUrl = $this->config['check_trans_url'][$this->config['mode']];
        $this->token = $this->config['check_trans_url']['token'];
        $this->confirmations = $this->config['confirmations'] ?? $this->confirmations;
        $this->isEvent = $isEvent;
    }

    /**
     * 执行查询
     * @param int $uid
     * @param int $wid
     * @param string $code
     * @param string $address
     * @param string $contractAddress
     * @return array
     */
    public function handle(int $uid, int $wid, string $code, string $address, string $contractAddress = '')
    {
        $updateAsset = [];
        try {
            $this->uid = $uid;
            $this->wid = $wid;
            $this->code = $code;
            $this->address = $address;
            $this->contractAddress = $contractAddress;
            $this->setParam();
            $this->setRequestUrl();
            $client = new Client(['timeout' => $this->timeout]);
            $httpClientRequest = $client->request('GET', $this->requestUrl);
            $rawBody = $httpClientRequest->getBody()->getContents();
            $result = \GuzzleHttp\json_decode($rawBody, true);
            $updateAsset = $this->processingData($result);
            if ($this->isEvent) {
                event(new RechargeEvent($updateAsset));
            }
            Log::info(json_encode([
                'baseUrl' => $this->baseUrl,
                'param'   => $this->param
            ]));
        } catch (\Exception $exception) {
            Log::error(json_encode([
                'baseUrl' => $this->baseUrl,
                'param'   => $this->param,
                'error'   => $exception->getMessage()
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
        if (is_array($result) && $result['status'] != 0) {
            foreach ($result['result'] as $k => $val) {
                //检测from是否是指定忽略的地址
                if (in_array($val['from'], $this->config['ignore_address'])) {
                    continue;
                }
                //对比地址和确认次数
                if (strcasecmp($val['to'], $this->address) != 0 || $val['confirmations'] < $this->confirmations) {
                    continue;
                }
                //是否已经记录过
                $recharge = Recharge::where(['uid' => $this->uid, 'address' => $val['to'], 'hash' => $val['hash']])->first();
                if ($recharge) {
                    continue;
                }
                //币种数量小数位,ETH默认18位
                $x = 18;
                if (isset($val['tokenDecimal']) && $val['tokenDecimal'] != '') {
                    $x = $val['tokenDecimal'];
                }
                $amount = $val['value'] / pow(10, $x); //10的18次方

                $create = Recharge::create([
                    'uid'     => $this->uid,
                    'address' => $this->address,
                    'hash'    => $val['hash'],
                    'amount'  => $amount,
                    'status'  => Recharge::PAYED,
                    'wid'     => $this->wid,
                    'code'    => $this->code
                ]);

                if ($create) {
                    //记录充值成功的条目
                    array_push($updateAsset, $create->toArray());
                    Log::info(sprintf('用户[%s]在线充值[%s]插入成功,数量[%s],哈希[%s]', $this->uid, $this->code, $amount, $val['hash']));
                }
            }
        }
        //需要更新资产的记录
        return $updateAsset;
    }

    /**
     * 设置请求参数
     */
    protected function setParam(): void
    {
        if ($this->contractAddress != '') {
            $this->param = [
                'module'          => 'account',
                'action'          => 'tokentx',
                'contractaddress' => $this->contractAddress,
                'address'         => $this->address,
                'tag'             => 'latest',
                'apikey'          => $this->token
            ];
        } else {
            $this->param = [
                'module'     => 'account',
                'action'     => 'txlist',
                'address'    => $this->address,
                'startblock' => 0,
                'endblock'   => 999999999999,
                'sort'       => 'desc',
                'apikey'     => $this->token
            ];
        }
    }

    /**
     * 获取请求参数
     * @return mixed
     */
    public function getParam(): array
    {
        return $this->param;
    }

    /**
     * 拼接请求地址
     * @return mixed
     */
    protected function setRequestUrl(): void
    {
        $this->requestUrl = $this->baseUrl . http_build_query($this->param);
    }

    /**
     * 获取请求地址
     * @return string
     */
    public function getRequestUrl(): string
    {
        return $this->requestUrl;
    }


}
