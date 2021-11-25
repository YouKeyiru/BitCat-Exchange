<?php

namespace App\Console\Commands;

use App\Services\DepthPctService;
use App\Services\MarketService;
use App\Models\ProductsExchange;
use App\Models\SecondInfoToken;
use App\Models\XyDayInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class MaketTicket extends Command
{

    private $publishRedis;
    protected $signature = 'ticketmake-generate';

    protected $description = '行情k线';//自选币

    public function __construct()
    {
        parent::__construct();
        // REDIS_MARKET_HOST=192.168.0.155
        // REDIS_MARKET_PASSWORD=q4npZDIcN5Yl6DrE
        // REDIS_MARKET_PORT=9736
        //连接redis
        if (class_exists('Redis')) {
            $this->publishRedis = new \Redis();
            $this->publishRedis->pconnect(env('REDIS_MARKET_HOST','119.8.115.26'), env('REDIS_MARKET_PORT','9736'));
            $auth = env('REDIS_MARKET_PASSWORD','q46DrEda2');
            if (strlen($auth)) {
                $this->publishRedis->auth($auth);
            }
            if (!$this->publishRedis->ping()) {
                exit('redis cannot connect');
            }
            $this->publishRedis->setOption(\Redis::OPT_READ_TIMEOUT, -1);
        } else {
            exit('redis扩展不存在');
        }
    }

    public function handle()
    {
        //获取自选币列表
        // $codes = ProductsExchange::where(['type'=>'2','state'=>'1'])->select('id', 'code','pname')->toArray();
        // $codes = ProductsExchange::where(['type'=>'2','state'=>'1'])->pluck('code','id')->toArray();
        do {
            $codes = ProductsExchange::where(['type'=>'2','state'=>'1'])->select('id', 'code','pname')->get()->toArray();
            if ($codes) {
                foreach ($codes as $val) {
                    $this->ticket($val,config('market_channel.ticker'));//'vb:ticker:chan:PROJECT0405')
                }
            }
            //$sleep = mt_rand(0.5, 2);
            sleep(1);
        } while (true);

        // while (1) {
        //     foreach ($codes as $code) {
        //         $code = strtolower($code);
        //         try {
        //             DepthPctService::MakePct($code);
        //         } catch (\Exception $exception) {
        //             \Log::error('MakePct=>' . $exception);
        //         }
        //     }
        //     sleep(5);
        // }
    }

    public function ticket($val, $ticket)
    {
        // 如果交易完成make second_info 的时候在Redis里插入，这里就可以取Redis的了 ,包括卖一买一 , 最高最低
        //-----实时数据beg
        // $proInfo = SecondInfoToken::where(['pid'=>$val['id']])->orderBy('id', 'desc')->first();
        $proInfo = SecondInfoToken::where(['pid'=>$val['id']])->orderBy('id', 'desc')->first();//->toArray();
        if ($proInfo) {
            // $price   = $proInfo['price'];
            $price   = $proInfo->price;
            // $myprice = number_format($price, $config['token_decimal'], '.', '');
            $myprice = number_format($price, 8, '.', '');
            $nowday  = date('Y-m-d');
            // $dayset = XyDayInfo::where(['pid'=>$val['id'],'date'=>$nowday])->select('openingPrice','highestPrice','lowestPrice','volume')->first()->toArray();
            $dayset = XyDayInfo::where(['pid'=>$val['id'],'date'=>$nowday])->select('openingPrice','highestPrice','lowestPrice','volume')->first();
            // $dayset  = $db->select('openingPrice,highestPrice,lowestPrice,volume')->from('xy_dayk_info')->where("pid={$val['pid']} and date='{$nowday}'")->row();
            if ($dayset && $myprice > 0) {
                $rate_cny = MarketService::getCnyRateByCode();//json_decode($redis->get('vb:indexTickerAll:usd2cny'), true);
                unset($current);
                $current['code']     = $val['code'];
                $current['name']     = $val['pname'];
                $current['date']     = date('Y-m-d');
                $current['time']     = date('H:i:s');
                $current['price']    = $myprice;
                // $current['cnyPrice'] = number_format($rate_cny['USDT'] * $myprice, 2);
                $current['cnyPrice'] = number_format($rate_cny * $myprice, 2);
                // $current['open']     = $dayset['openingPrice'];
                $current['open']     = $dayset->openingPrice;
                $current['close']    = $myprice;
                // $current['high']     = $dayset['highestPrice'];
                $current['high']     = $dayset->highestPrice;
                // $current['low']      = $dayset['lowestPrice'];
                $current['low']     = $dayset->lowestPrice;
                // $current['volume']   = $dayset['volume'];
                $current['volume']     = $dayset->volume;
                $current['buy']  = '';
                $current['sell'] = '';
                // $current['change']     = number_format($myprice - $dayset['openingPrice'], 4, '.', '');
                $current['change']     = number_format($myprice - $dayset->openingPrice, 4, '.', '');
                // $current['changeRate'] = number_format(($myprice - $dayset['openingPrice']) / $dayset['openingPrice'] * 100, 2, '.', '') . '%';
                $current['changeRate'] = number_format(($myprice - $dayset->openingPrice) / $dayset->openingPrice * 100, 2, '.', '') . '%';

                $newitem_key  = 'vb:ticker:newitem:' . $val['code'];
                $newprice_key = 'vb:ticker:newprice:' . $val['code'];
                $currentjson  = json_encode($current);

                // var_dump($ticket);
                $this->publishRedis->set($newitem_key, $currentjson);
                $this->publishRedis->set($newprice_key, $current['price']);
                $this->publishRedis->publish($ticket, $currentjson);
            }
        }
        //-----实时数据beg
    }
}
