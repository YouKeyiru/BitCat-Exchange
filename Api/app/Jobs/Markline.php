<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
// use DB;
// use Log;
use App\Models\ProductsExchange;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Markline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $matchArr = $this->data;
            if($matchArr){
            	//var_dump($matchArr);
                foreach ($matchArr as $item => $value) {
                    //只处理自发币 排除主流币
                    if(!in_array($value['market'], ['btc/usdt','eth/usdt','xrp/usdt','ltc/usdt','eos/usdt','bch/usdt','etc/usdt'])){
                        $row = ProductsExchange::where(['code'=>$value['market']])->first()->toArray();
                        if($row){
                            $insert = [
                                    'pid' => $row['id'],
                                    'code' => $row['code'],
                                    'pname' => $row['pname'],
                                    'price' => $value['price'],//实时价格
                                    'volume' => $value['quantity'],//交易量
                                    'addtime' => time(),
                                    // 'ctime' => now(),
                            ];
                            DB::table('xy_second_info_token')->insert($insert);
                        }
                    }
                }
            }
        } catch (Exception $exception){
            Log::info('Markline '.$exception->getMessage().$exception->getLine());
        }

    }

    /**
     * 要处理的失败任务。
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Log::info('Markline Failed'.$exception->getMessage());
    }

}
