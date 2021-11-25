<?php

namespace App\Jobs;

use App\Models\ContractEntrust;
use App\Models\ContractPosition;
use App\Models\ProductsContract;
use App\Services\ContractTransService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EntrustsToPositions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $new_price;

    private $entrust;

    public function __construct($queue_data)
    {
        //
        $this->new_price = $queue_data['new_price'];
        $this->entrust = $queue_data['entrust'];
    }

    public function handle()
    {

        $order = ContractEntrust::find($this->entrust->id);
        if (!$order) {
            \Log::error(sprintf('订单[%s]不存在', $this->entrust->id));
            return;
        }

        $codeInfo = ProductsContract::find($this->entrust->pid);
        if (!$codeInfo) {
            \Log::error(sprintf('币种[%s]不存在', $this->entrust->id));
            return;
        }

        \DB::beginTransaction();
        try {
            $update = ContractEntrust::query()->where(['id' => $order->id, 'version' => $order->version])
                ->update([
                    'status' => ContractEntrust::STATE_OVER,
                    'version' => $order->version + 1,
                ]);
            if ($update === false) {
                throw new Exception('订单更新失败');
            }

            $key = 'contract:order:entrusts:' . $order->code;
            ContractTransService::delCacheOrder($order->order_no,$key);

            //处理点差
            if ($order->otype == 1) {
                //1 买涨 2买跌
                $entrust['buy_price'] = bcMath($order->buy_price, $order->spread, '+');
            } else {
                $entrust['buy_price'] = bcMath($order->buy_price, $order->spread, '-');
            }

            $create = ContractPosition::query()->create([
                'uid' => $order->uid,
                'pid' => $order->pid,
                'name' => $order->name,
                'code' => $order->code,
                'sheets' => $order->sheets,
                'buy_price' => $entrust['buy_price'],
                'buy_num' => $order->buy_num,
                'price' => $order->price,
                'market_price' => $this->new_price,
                'total_price' => $order->total_price,
                'otype' => $order->otype,
                'stop_win' => $order->stop_win,
                'stop_loss' => $order->stop_loss,
                'fee' => $order->fee,
                'deposit' => $order->deposit,
                'leverage' => $order->leverage,
                'spread' => $order->spread,
                'source' => 2,
            ]);
            if (!$create) {
                throw new Exception('订单创建失败');
            }

            // 添加缓存
            $create->type = 1;
            ContractTransService::setCacheOrder($create);

            \DB::commit();
        }catch (\Exception $exception){
            \DB::rollBack();
            \Log::error(sprintf('委托转持仓异常：%', json_encode(['err' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' =>$exception->getFile()])));
            //回滚。数据回填
            ContractTransService::setCacheOrder($order);
        }
    }
}
