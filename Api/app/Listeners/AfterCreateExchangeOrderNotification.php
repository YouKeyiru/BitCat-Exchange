<?php

namespace App\Listeners;

use App\Events\AfterCreateExchangeOrder;
use App\Http\Traits\Job;

class AfterCreateExchangeOrderNotification
{
    use Job;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param AfterCreateExchangeOrder $event
     * @return void
     */
    public function handle(AfterCreateExchangeOrder $event)
    {
        //
        $order = $event->getData();

//        $this->match($order);
    }

    protected function match($order)
    {
//        \Log::debug($order->toArray());
        //进入撮合
//        $params = [
//            'type' => 'order',
//            'data' => [
//                'type'     => $order->otype == 1 ? 'limit' : 'market', //限价单limit,市价单market
//                'side'     => $order->type == 1 ? 'bid' : 'ask', //买单bid,卖单ask
//                'quantity' => $order->wtnum, //订单数量
//                'price'    => $order->otype == 1 ? $order->wtprice1 : $order->total_price, //价格  如果是市价这里是总金额
//                'market'   => $order->code, //交易市场
//                'user_id'  => $order->uid,
//                'order_id' => $order->id,
//            ]
//        ];
//        $this->MatchOrder(json_encode($params));
//
//        \Log::debug($params);
    }
}
