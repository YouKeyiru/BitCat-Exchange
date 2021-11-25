<?php


namespace App\Services;


use Illuminate\Support\Facades\Redis;

class MarketService
{

    /**
     * 获取redis连接实例
     * @param string $name
     * @return \Illuminate\Redis\Connections\Connection
     */
    public static function getConnect(string $name = 'market')
    {
        return Redis::connection($name);
    }

    /**
     * 获取通道名
     * @param string $name
     * @return string
     */
    public static function getChannel(string $name): string
    {
        return config('market_channel.' . $name);
    }

    /**
     * 获取当前行情价格
     * @param $code
     * @return string
     */
    public static function getCodePrice(string $code)
    {
        $redis = self::getConnect();
        $code = strtolower($code);
        $redisKey = "vb:ticker:newprice:{$code}";
        return $redis->get($redisKey) ?? 0;
    }

    /**
     * 获取当前行情数据
     * @param $code
     * @return array
     */
    public static function getNewItemByCode(string $code)
    {
        $redis = self::getConnect();
        $code = strtolower($code);
        $redisKey = "vb:ticker:newitem:{$code}";
        return $redis->get($redisKey) ?? [

            ];
    }

    public static function getCnyRateByCode(string $code = 'USDT')
    {
        //'vb:indexTickerAll:usd2cny';
        $redis = self::getConnect();
        $redisKey = "";
        return $redis->get($redisKey) ?? 0;
    }
}
