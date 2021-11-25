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
     * 获取币币实时成交数据
     * @param $code
     * @return string
     */
    public static function getTradeDataByCode(string $code)
    {
        $redis = self::getConnect();
        $code = strtolower($code);
        $redisKey = "vb:trader:newitem:{$code}";
        return $redis->get($redisKey) ?? '';
    }

    /**
     * 获取币币盘口数据
     * @param $code
     * @return string
     */
    public static function getHandicapDataByCode(string $code)
    {
        $redis = self::getConnect();
        $code = strtolower($code);
        $redisKey = "vb:depth:newitem:{$code}";
        return $redis->get($redisKey) ?? '';
    }

    /**
     * 获取币币深度数据
     * @param $code
     * @return string
     */
    public static function getDepthDataByCode(string $code)
    {
        $redis = self::getConnect();
        $code = strtolower($code);
        $redisKey = "vb:depth:pct:newitem:{$code}";
        return $redis->get($redisKey) ?? '';
    }

    /**
     * 获取当前行情数据
     * @param $code
     * @return string
     */
    public static function getNewItemByCode(string $code)
    {
        $redis = self::getConnect();
        $code = strtolower($code);
        $redisKey = "vb:ticker:newitem:{$code}";
        return $redis->get($redisKey) ?? '';
    }

    /**
     * 盘口
     * @param string $code
     * @return string
     */
    public static function getCodeDepth(string $code)
    {
        $redis = self::getConnect();
        $code = strtolower($code);
        $redisKey = "vb:depth:newitem:{$code}";
        return $redis->get($redisKey);
    }

    /**
     * 深度图
     * @param string $code
     * @return string
     */
    public static function getCodePct(string $code)
    {
        $redis = self::getConnect();
        $code = strtolower($code);
        $redisKey = "vb:depth:pct:newitem:{$code}";
        return $redis->get($redisKey);
    }

    /**
     * 深度图
     * @param string $code
     * @return string
     */
    public static function getCodeTrader(string $code)
    {
        $redis = self::getConnect();
        $code = strtolower($code);
        $redisKey = "vb:trader:newitem:{$code}";
        return $redis->get($redisKey);
    }

    /**
     * usdt 汇率
     * @param string $code
     * @return int|string
     */
    public static function getCnyRateByCode(string $code = 'USDT')
    {
        //'vb:indexTickerAll:usd2cny';
        $redis = self::getConnect();
        $redisKey = "vb:indexTickerAll:usd2cny";
        $data = json_decode($redis->get($redisKey));
        if ($data) {
            $rate = $data->$code ?? 0;
        } else {
            $rate = 0;
        }
        return $rate;
    }

    /**
     * 获取盘口深度数据
     * @param string $code
     * @param string $type
     * @return string
     */
    public static function getDepthData(string $code, string $type)
    {
        $redis = self::getConnect();
        $code = strtolower($code);
        switch ($type) {
            case 'depth': //盘口
                $redisKey = "vb:depth:newitem:{$code}";
                break;
            case 'pct': //深度图
                $redisKey = "vb:depth:pct:newitem:{$code}";
                break;
            default:
                $redisKey = "vb:depth:newitem:{$code}";

        }
        return $redis->get($redisKey) ?? '';
    }

    /**
     * 设置盘口深度数据
     * @param string $code
     * @param string $type
     * @param string $data
     * @return mixed
     */
    public static function setDepthData(string $code, string $type, string $data)
    {
        $redis = self::getConnect();
        $code = strtolower($code);
        switch ($type) {
            case 'depth': //盘口
                $redisKey = "vb:depth:newitem:{$code}";
                break;
            case 'pct': //深度图
                $redisKey = "vb:depth:pct:newitem:{$code}";
                break;
            default:
                $redisKey = "vb:depth:newitem:{$code}";

        }
        return $redis->set($redisKey, $data);
    }

    /**
     * 推送数据
     * @param string $channel
     * @param string $data
     * @return mixed
     */
    public static function pubData(string $channel, string $data)
    {
        $redis = self::getConnect();

        return $redis->publish($channel, $data);
    }
}
