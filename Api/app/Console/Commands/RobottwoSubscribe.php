<?php

namespace App\Console\Commands;

// use App\Jobs\Common;
use App\Http\Traits\Job;
use App\Models\ProductsExchange;
use App\Models\ProductsExchangeext;
// use App\Models\UserExchange;
use App\Models\ExchangeOrder;
use App\Services\MatchEngineService;
use App\Models\WalletCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Services\MarketService;

class RobottwoSubscribe extends Command
{
    use Job;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'robottwo:subscribe';

    /**
     * The console command description. 自选币机器人 买单
     *
     * @var string
     */
    protected $description = 'robot two Subscribe';

    protected $currency;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // $productext = ProductsExchangeext::get()->toArray();
        // $result = [];
        // foreach ($productext as $item => $value) {
        //     $product = ProductsExchange::where(['id'=>$value['code_id']])->first()->toArray();
        //     if(!$product){
        //         continue;
        //     }
        //     $codes = ProductsExchange::coinCut($product['code']);
        //     $result[] = [ //$product['code']
        //         'id' => $product['id'],
        //         'pname' => $product['pname'],
        //         'code'  => $product['code'],
        //         'l_code' => $codes[0],
        //         'r_code' => $codes[1],
        //         'l_wid' => WalletCode::getWidByCode($codes[0]),
        //         'r_wid' => WalletCode::getWidByCode($codes[1]),
        //         'start_price'  => $value['start_price'],
        //         'limit_low'    => $value['limit_low'],
        //         'limit_high'   => $value['limit_high'],
        //         'limit_delta'  => $value['limit_delta'],
        //         'limit_decimal'=> $value['limit_decimal'],
        //         'min_quantity' => $value['min_quantity'],
        //         'max_quantity' => $value['max_quantity'],
        //         'act_type'     => $value['act_type'],
        //         'order_type'   => $value['order_type'],
        //     ];
        // }
        // $this->currency = $result;
    }

    protected function randFloatUp(array $c): float
    {
        $x = 0;
        $bx = random_int(0, $c['ri64']);
        if ($bx * 1e4 > $c['ri64'] * 9000) {
            $x = random_int(0, $c['limitDeltaNumber']); // 3000
        } else if ($bx * 1e4 > $c['ri64'] * 8000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 4 / 5); // 2400
        } else if ($bx * 1e4 > $c['ri64'] * 7000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 7 / 10); // 2100
        } else if ($bx * 1e4 > $c['ri64'] * 6000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 3 / 5); //1800
        } else if ($bx * 1e4 > $c['ri64'] * 5000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 1 / 2); // 1500
        } else if ($bx * 1e4 > $c['ri64'] * 4000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 2 / 5); // 1200
        }
        if ($x == 0) {
            $x = random_int(0, $c['limitDeltaNumber'] * 3 / 10); // 900
        }
        if (random_int(0, $c['ri64']) * 1e4 > $c['ri64'] * 7500) {
            $x = -1 * $x;
        }

        return $x / pow(10, $c['limitDecimal']) + $c['startPrice'];
    }

    protected function randFloatDown(array $c): float
    {
        $x = 0;
        $bx = random_int(0, $c['ri64']);
        if ($bx * 1e4 > $c['ri64'] * 9000) {
            $x = random_int(0, $c['limitDeltaNumber']); // 3000
        } else if ($bx * 1e4 > $c['ri64'] * 8000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 4 / 5); // 2400
        } else if ($bx * 1e4 > $c['ri64'] * 7000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 7 / 10); // 2100
        } else if ($bx * 1e4 > $c['ri64'] * 6000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 3 / 5); //1800
        } else if ($bx * 1e4 > $c['ri64'] * 5000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 1 / 2); // 1500
        } else if ($bx * 1e4 > $c['ri64'] * 4000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 2 / 5); // 1200
        }
        if ($x == 0) {
            $x = random_int(0, $c['limitDeltaNumber'] * 3 / 10);
        }
        if (random_int(0, $c['ri64']) * 1e4 <= $c['ri64'] * 7500) {
            $x = -1 * $x;
        }

        return $x / pow(10, $c['limitDecimal']) + $c['startPrice'];
    }

    protected function randFloatMv(array $c): float
    {
        $x = 0;
        $bx = random_int(0, $c['ri64']);
        if ($bx * 1e4 > $c['ri64'] * 9000) {
            $x = random_int(0, $c['limitDeltaNumber']); // 3000
        } else if ($bx * 1e4 > $c['ri64'] * 8000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 4 / 5); // 2400
        } else if ($bx * 1e4 > $c['ri64'] * 7000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 7 / 10); // 2100
        } else if ($bx * 1e4 > $c['ri64'] * 6000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 3 / 5); //1800
        } else if ($bx * 1e4 > $c['ri64'] * 5000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 1 / 2); // 1500
        } else if ($bx * 1e4 > $c['ri64'] * 4000) {
            $x = random_int(0, $c['limitDeltaNumber'] * 2 / 5); // 1200
        }
        if ($x == 0) {
            $x = random_int(0, $c['limitDeltaNumber'] * 3 / 10);
        }
        if (random_int(0, $c['ri64']) * 1e4 >= $c['ri64'] * 5000) {
            $x = -1 * $x;
        }

        return $x / pow(10, $c['limitDecimal']) + $c['startPrice'];
    }

    protected function getPrice(int $act, array $c): float
    {
        if ($act == 1) {
            $price = $this->randFloatUp($c);
        } elseif ($act == 2) {
            $price = $this->randFloatDown($c);
        } else {
            $price = $this->randFloatMv($c);
        }
        if ($price > $c['limitHigh']) {
            $price = $this->randFloatDown($c);
        } elseif ($price < $c['limitLow']) {
            $price = $this->randFloatUp($c);
        }
        return $price;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $list  = $this->currency;
        //
        while (1) {

            $productext = ProductsExchangeext::get()->toArray();
            $list = [];
            foreach ($productext as $item => $value) {
                $product = ProductsExchange::where(['id'=>$value['code_id']])->first()->toArray();
                if(!$product){
                    continue;
                }
                $codes = ProductsExchange::coinCut($product['code']);
                $list[] = [ //$product['code']
                    'id' => $product['id'],
                    'pname' => $product['pname'],
                    'code'  => $product['code'],
                    'l_code' => $codes[0],
                    'r_code' => $codes[1],
                    'l_wid' => WalletCode::getWidByCode($codes[0]),
                    'r_wid' => WalletCode::getWidByCode($codes[1]),
                    'start_price'  => $value['start_price'],
                    'limit_low'    => $value['limit_low'],
                    'limit_high'   => $value['limit_high'],
                    'limit_delta'  => $value['limit_delta'],
                    'limit_decimal'=> $value['limit_decimal'],
                    'min_quantity' => $value['min_quantity'],
                    'max_quantity' => $value['max_quantity'],
                    'act_type'     => $value['act_type'],
                    'order_type'   => $value['order_type'],
                ];
            }

            foreach ($list as $key => $value) {
                try {
                    $curPrice = MarketService::getCodePrice($value['code']);//. '/usdt'
                    $newPrice = $curPrice?$curPrice:$value['start_price'];//初始价格
                    $real_limit_decimal = '1e'.$value['limit_decimal'];
                    $limitDeltaNumber = $value['limit_delta'] * $real_limit_decimal;
                    $cparams = [
                        'startPrice'       => $newPrice, //起始价格，上一个价格
                        'limitHigh'        => $value['limit_high'], //最高
                        'limitLow'         => $value['limit_low'], //最低
                        'limitDelta'       => $value['limit_delta'], //最小波动价
                        'limitDeltaNumber' => $limitDeltaNumber,
                        'limitDecimal'     => $value['limit_decimal'], //小数位
                        'ri64'             => 1e6
                    ];
                    $min_quantity = $value['min_quantity'];
                    $max_quantity = $value['max_quantity'];
                    $act_type = $value['act_type']; //1涨 2跌 3自动
                    $quantity = random_int($min_quantity, $max_quantity);
                    $price = $this->getPrice($act_type,$cparams);
                    // bid 1买入; ask 2卖出 买单bid,卖单ask
                    $depth = [
                        'code' => $value['code'],
                        'name' => $value['code'],
                    ];
                    $side  =  'bid'; //买单
                    if($value['order_type']=='2'){
                        $side  =  'ask'; //卖单
                    }
                    $this->crOrder($side, $price, $quantity, $value);//$depth
                } catch (\Exception $exception) {
                    \Log::error('RobottwoSubscribe=>' . $exception);
                }
            }
            sleep(5);
        }
        // try {
        //     foreach ($list as $key => $value) {
        //         $curPrice = MarketService::getCodePrice($value['code']);//. '/usdt'
        //         $newPrice = $curPrice?$curPrice:$value['start_price'];//初始价格
        //         $real_limit_decimal = '1e'.$value['limit_decimal'];
        //         $limitDeltaNumber = $value['limit_delta'] * $real_limit_decimal;
        //         $cparams = [
        //             'startPrice'       => $newPrice, //起始价格，上一个价格
        //             'limitHigh'        => $value['limit_high'], //最高
        //             'limitLow'         => $value['limit_low'], //最低
        //             'limitDelta'       => $value['limit_delta'], //最小波动价
        //             'limitDeltaNumber' => $limitDeltaNumber,
        //             'limitDecimal'     => $value['limit_decimal'], //小数位
        //             'ri64'             => 1e6
        //         ];
        //         $min_quantity = $value['min_quantity'];
        //         $max_quantity = $value['max_quantity'];
        //         $act_type = $value['act_type']; //1涨 2跌 3自动
        //         $quantity = random_int($min_quantity, $max_quantity);
        //         $price = $this->getPrice($act_type,$cparams);
        //         // bid 1买入; ask 2卖出 买单bid,卖单ask
        //         $depth = [
        //             'code' => $value['code'],
        //             'name' => $value['code'],
        //         ];
        //         $side  =  'bid'; //买单
        //         if($value['order_type']=='2'){
        //             $side  =  'ask'; //卖单
        //         }
        //         $this->crOrder($side, $price, $quantity, $value);//$depth
        //     }
        // } catch (\Exception $exception) {
        //     $log = sprintf('[%s] robot Subscribe Faild ==> %s ,line=>%s',
        //         date('Y-m-d H:i:s'), $exception->getMessage(), $exception->getLine()
        //     );
        //     echo $log, PHP_EOL;
        // }
    }

    protected function crOrder($side, $price, $quantity, $depth)
    {
        $data = [
            'side' => $side,
            'code' => $depth['code'],//$depth['name'],
            'price' => $price,
            'quantity' => $quantity
        ];
        // echo json_encode($data),PHP_EOL;

        $uid = 0;

        if (empty($depth)) {
            return;
        }

        // if (!isset($this->currency[$depth['code']])) {
        //     return;
        // }

        // $currency = $this->currency[$depth['code']];
        $currency = $depth;
        $orderInfo = ExchangeOrder::create([
            'uid'         => $uid,
            'pid'         => $currency['id'],
            'code'        => $currency['code'],
            'symbol'      => $currency['pname'],//$depth['name'],
            'wtprice'     => $price,
            'wtprice1'    => $price,
            'wtnum'       => $quantity,
            'total_price' => $price * $quantity,
            'fee'         => 0,
            'type'        => $side == 'bid' ? 1 : 2,
            'otype'       => 1,
            'l_code'      => $currency['l_code'],
            'r_code'      => $currency['r_code'],
            'l_wid'       => $currency['l_wid'],
            'r_wid'       => $currency['r_wid'],
        ]);


        $params = json_encode([
            'type' => 'order',
            'data' => [
                'type' => 'limit', //限价单limit,市价单market
                'side' => $side, //买单bid,卖单ask
                'quantity' => $quantity, //订单数量
                'price' => $price, //价格  如果是市价这里是总金额
                'market' => $currency['code'],//$depth['code'], //交易市场
                'user_id' => $uid,
                'order_id' => $orderInfo->id,
            ]
        ]);
        //echo $orderInfo->id . '完成', PHP_EOL;
        // Common::MatchOrder($params);
        $this->MatchOrder($params);
//        $result = MatchEngineService::run($params);
//        OrderHandle::dispatch($result)->onQueue('OrderHandle');

    }

}
