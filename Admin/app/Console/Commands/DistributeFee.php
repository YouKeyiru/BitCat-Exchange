<?php

namespace App\Console\Commands;

use App\Entities\Wallet\EthInterface;
use App\Models\On;
use App\Models\UserAddress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DistributeFee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'join:fee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '分发矿工费';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        while(true) {
            $on = On::where('name','distribute_fee')->first();
            if($on && $on->value) {

                Log::info('开始分发矿工费');

                $this->business();//业务
                $on->value = 0;
                $on->save();
            }
            echo 'sleep' . time().PHP_EOL;
            sleep(2);
        }
    }

    protected function business()
    {
        $page = 1;
        $pageSize   = 1000;

        //TODO 这个可从config表里取
//        $config = ConfigService::get([
//            'charge_enter_address', //归集地址
//            'enter_value_limit', //  代币归拢阈值 ,大于这个数值才会进行归集操作
//            'distribute_miner_fee', // 分发矿工费,数值，
//            'distribute_miner_limit', // 分发矿工费阈值 ,代币余额大于这个数值才会进行分发矿工费操作
//            'miner_fee_private_key', // 分发矿工费 地址私钥
//            'usdt_contract_address', // USDT 合约地址
//        ]);
        $config = $this->getMoneySet();
        // \Log::info("后台配置项".json_encode($config));
        while(true) {
            $offset = ($page - 1)*$pageSize;
            //TODO 这里区分下 只查ERC20的USDT
            $lists  = UserAddress::where('type',2)->offset($offset)->limit($pageSize)->get();
            if($lists->isEmpty()) {
                \Log::info('地址为空');
                break;
            }
            $eth = new EthInterface();
            foreach($lists as $info) {
                // \Log::info(json_encode($info));


                $res = $eth->tokenBalance($info->address,$config['usdt_contract_address']);

                if(!$res['status']) {
                    \Log::info(sprintf('[%s]获取TOKEN余额失败',$info->address));
                    continue;
                }

                //归拢阈值判断
                $amount = $res['data']['balance'];
                // \Log::info('查询到余额==='.$amount);
                \Log::info(sprintf('[%s]获取TOKEN余额 [%s]',$info->address,$amount));

                if($amount < $config['enter_value_limit']) {
                    continue;
                }

                $result = $eth->ethBalance($info->address);
                // \Log::info($result);
                if(!$result['status']) {
                    \Log::info(sprintf('[%s]获取ETH余额失败',$info->address));

                    // \Log::info("获取用户矿工费失败");
                    continue;
                }
                //燃料余额
                $balance = $result['data']['balance'];
                // \Log::info('查询到手续费余额==='.$balance);
                \Log::info(sprintf('[%s]获取ETH余额 [%s]',$info->address,$balance));

                //分发矿工费阈值判断
                if($amount >= $config['distribute_miner_limit']) {

                    //交易燃料、分发矿工费
                    if($balance < $config['distribute_miner_fee']) {
                        // $privateKey, $address, $amount
                        $deal = $eth->ethTrans(
                            $config['miner_fee_private_key'],
                            $info->address,
                            $config['distribute_miner_fee'],
                            $config['gas_limit'],
                            $config['gas_price'],
                        );

                        Log::info('交易结果=>'.json_encode($deal,JSON_UNESCAPED_UNICODE));

                        if ($deal['status'] != 200) {
                            Log::info('交易参数=>' . json_encode([
                                    $config['miner_fee_private_key'],
                                    $info->address,
                                    $config['distribute_miner_fee'],
                                    $config['gas_limit'],
                                    $config['gas_price'],
                                ]));

                        }

                        // \Log::info("分发成功");
                        continue;
                    }
                    \Log::info(sprintf('[%s]矿工费充足 [%s]',$info->address,$balance));
                    // \Log::info("有燃料");
                }
                \Log::info(sprintf('[%s]未达分发矿工费阈值',$info->address));
            }
            $page ++;
        }
    }

    /**
     * 获取系统配置
     * @return mixed
     */
    public static function getMoneySet()
    {
        $value = DB::table('admin_config')
            ->where('name', 'like', 'money%')
            ->select('name', 'value')
            ->get();

        $arr = [];
        foreach ($value as $item) {
            $key = explode('.', $item->name);
            $arr[$key[1]] = $item->value;
        }
        return $arr;
    }
}
