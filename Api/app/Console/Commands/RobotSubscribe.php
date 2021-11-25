<?php

namespace App\Console\Commands;

// use App\Jobs\Common;
use App\Http\Traits\Job;
use App\Models\ProductsExchange;
// use App\Models\UserExchange;
use App\Models\ExchangeOrder;
use App\Services\MatchEngineService;
use App\Models\WalletCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RobotSubscribe extends Command
{
    use Job;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'robot:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'robot Subscribe';

    protected $currency;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // $product = ProductsExchange::select('id', 'code','pname')->get()->toArray();
        $product = ProductsExchange::where(['type'=>'1','state'=>'1'])->select('id', 'code','pname')->get()->toArray();
        $result = [];
        foreach ($product as $item => $value) {
            $codes = ProductsExchange::coinCut($value['code']);
            $result[$value['code']] = [
                'id' => $value['id'],
                'pname' => $value['pname'],
                'code'  => $value['code'],
                'l_code' => $codes[0],
                'r_code' => $codes[1],
                'l_wid' => WalletCode::getWidByCode($codes[0]),
                'r_wid' => WalletCode::getWidByCode($codes[1]),
            ];
        }
        $this->currency = $result;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $channel = config('system.channel.depth');
        try {
            $redis = Redis::connection('subscribe');
            $redis_depth = Redis::connection('market');
            // $redis_depth = Redis::connection('robot');
            $redis->subscribe([$channel], function ($message) use ($redis_depth) {

                $depth = json_decode($message, true);

                $key = 'vb:depth:newitem:' . $depth['code'];
                // $key = $depth['code'];
                // return;
                // print_r($key);
                $data = $redis_depth->get($key);
                // print_r($data);
                // echo $data,PHP_EOL;
                $data = json_decode($data, true);
                if(!$data){
                    echo $key,PHP_EOL;
                    return;
                }
//                var_dump($data);
                // echo json_encode($data['asks']),PHP_EOL;
                // echo json_encode($data['bids']),PHP_EOL;

                $a1 = $b1 = 0;
                // $data['asks'] = !empty($data['asks'])?$data['asks']:[];
                if ($data && count($data['asks']) != 0) {
                    $a1 = end($data['asks'])['price'];
                }
                // $data['bids'] = !empty($data['bids'])?$data['bids']:[];
                if ($data && count($data['bids']) != 0) {
                    $b1 = $data['bids'][0]['price'];
                }
                $asks = $depth['asks'][0];
                $bids = $depth['bids'][0];
                // echo json_encode($bids),PHP_EOL;
                // echo json_encode($asks),PHP_EOL;

				$ripple = 0.1;
                if ($depth['code'] == 'btc/usdt') {
                    $ripple = 3;
                }
                if ($depth['code'] == 'eth/usdt') {
                    $ripple = 1;
                }
                if ($depth['code'] == 'xrp/usdt') {
                    $ripple = 0.0001;
                }
                
//                if ($depth['code'] == 'btc/usdt'){
                if ($bids['price'] != $b1) {
                    if (abs($bids['price'] - $b1) > $ripple) {
                        if ($bids['price'] - $b1 > 0) {
                            $this->crOrder('bid', $bids['price'], $bids['totalSize'] * 3, $depth);
                        } else {

                            $x = 3;
                            // if ($depth['code'] == 'btc/usdt'){
                            //         # code...
                            //     $x = 100;
                            // }
                            // if(!isset($data['bids'][0]['totalSize'])){
                            //     $data['bids'][0]['totalSize'] = 0;
                            // }
                            $this->crOrder('ask', $asks['price'], $data['bids'][0]['totalSize'], $depth);
                            // $this->crOrder('ask', $asks['price'], $asks['totalSize'] * $x, $depth);
                        }
                    } else {
                        //下买单
                        $this->crOrder('bid', $bids['price'], $bids['totalSize'], $depth);
                    }
                        // echo $depth['code'].' bid ok',PHP_EOL;
                }
                sleep(1);
                if ($asks['price'] != $a1) {
                    if (abs($bids['price'] - $b1) > $ripple) {
                        if ($bids['price'] - $b1 > 0) {
                            // if(!isset($data['asks'][0]['totalSize'])){
                            //     $data['asks'][0]['totalSize'] = 0;
                            // }
                            $this->crOrder('bid', $bids['price'], $data['asks'][0]['totalSize'], $depth);
                            // $this->crOrder('bid', $bids['price'], $bids['totalSize'] * 3, $depth);
                        } else {
                            $this->crOrder('ask', $asks['price'], $asks['totalSize'] * 3, $depth);
                        }
                    } else {
                        //下卖单
                        $this->crOrder('ask', $asks['price'], $asks['totalSize'], $depth);
                    }
                        // echo $depth['code'].' ask ok',PHP_EOL;
                }
                sleep(1);
//                }
            });
        } catch (\Exception $exception) {
            $log = sprintf('[%s] robot Subscribe Faild ==> %s ,line=>%s',
                date('Y-m-d H:i:s'), $exception->getMessage(), $exception->getLine()
            );
            echo $log, PHP_EOL;
        }
    }


    protected function crOrder($side, $price, $quantity, $depth)
    {
        $data = [
            'side' => $side,
            'code' => $depth['name'],
            'price' => $price,
            'quantity' => $quantity
        ];
         echo json_encode($data),PHP_EOL;

        $uid = 0;

        if (!isset($this->currency[$depth['code']])) {
            return;
        }

        $currency = $this->currency[$depth['code']];

        // $orderInfo = UserExchange::create([
        // //  'order_num' => $model->createSN(),
        //     'uid' => $uid,
        //     'currency_id' => $currency['id'],
        //     'symbol' => $depth['name'],
        //     'wtprice' => $price,
        //     'wtprice1' => $price,
        //     'wtnum' => $quantity,
        //     'totalprice' => $price * $quantity,
        //     'fee' => 0,
        //     'type' => $side == 'bid' ? 1 : 2,
        //     'otype' => 1,
        //     'l_code' => $currency['l_code'],
        //     'r_code' => $currency['r_code'],
        //     'l_wid' => $currency['l_wid'],
        //     'r_wid' => $currency['r_wid'],
        // ]);
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
                'market' => $depth['code'], //交易市场
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
