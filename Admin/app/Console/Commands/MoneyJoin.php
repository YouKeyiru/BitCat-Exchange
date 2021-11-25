<?php

namespace App\Console\Commands;

use App\Entities\Wallet\EthInterface;
use App\Models\On;
use App\Models\UserAddress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MoneyJoin extends Command
{
    private  $type;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'money:join';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '一键归集';

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
            sleep(2);
            $on = On::where('name','money_join')->first();
            //Log::info('查询归集是否是进行中==='.json_encode($on,JSON_UNESCAPED_UNICODE));
            if($on && $on->value) {
                $d = On::where('name','distribute_fee')->first();
                if(!$d) {
                    continue;
                }


                $this->business();//业务
                $on->value = 0;
                $on->save();
            }
        }
    }

    protected function business()
    {

//        $config = ConfigService::get([
//            'charge_enter_address',
//            'enter_value_limit',
//            'distribute_miner_fee',
//            'distribute_miner_limit',
//            'miner_fee_private_key',
//            'usdt_contract_address',
//        ]);
        $config = $this->getMoneySet();
//        \Log::info("后台配置项归集".json_encode($config));
        $type = [
            'usdt',
        ];
        foreach($type as $t) {
            $this->type = $t;
            Log::info('开始归集 '.$t);
            $page = 1;
            $pageSize   = 1000;
            while(true) {
                $offset = ($page - 1)*$pageSize;
                //TODO 这里区分下 只查ERC20的USDT
                $lists  = UserAddress::where('type',2)->offset($offset)->limit($pageSize)->get();
                if($lists->isEmpty()) {
                    break;
                }

                $eth = new EthInterface();
                foreach($lists as $info) {

                    $res = $eth->tokenBalance($info->address,$config[$this->type.'_contract_address']);

                    // Log::info($info->address);
                    if(!$res['status']) {
                        continue;
                    }
                    //归拢阈值判断
                    $amount = $res['data']['balance'];
                    Log::info(sprintf('[%s]地址TOKEN余额 %s',$info->address,$amount));

                    if($amount < $config['enter_value_limit']) {
                        continue;
                    }

                    $result = $eth->ethBalance($info->address);
                    if(!$result['status']) {
                        continue;
                    }
                    //燃料余额
                    $balance = $result['data']['balance'];
                    Log::info(sprintf('[%s]地址ETH余额 %s',$info->address,$balance));
                    //交易燃料不足
                    if($balance <= 0) {
                        continue;
                    }
                    // $privateKey, $contractAddress, $address, $amount
                    // Log::info('contract money data is'.json_encode($amount));
                    $deal = $eth->tokenTrans(
                        $info->private_key,
                        $config[$this->type.'_contract_address'],
                        $config['charge_enter_address'],
                        $amount,
                        $config['gas_limit'],
                        $config['gas_price']
                    );
                    //归拢
                    Log::info('交易结果=>'.json_encode($deal,JSON_UNESCAPED_UNICODE));

                    if($deal['status'] != 200){
                        Log::info('交易参数=>'.json_encode([
                                $info->private_key,
                                $config[$this->type . '_contract_address'],
                                $config['charge_enter_address'],
                                $amount,
                                $config['gas_limit'],
                                $config['gas_price']
                            ]));

                    }

                }
                $page ++;
            }
            Log::info("over");
            sleep(60);
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
