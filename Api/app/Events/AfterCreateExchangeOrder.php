<?php

namespace App\Events;

use App\Models\ExchangeOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AfterCreateExchangeOrder
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     *
     * @param $order
     */
    public function __construct(ExchangeOrder $order)
    {
        //
        $this->order = $order;

    }

    public function getData()
    {
        return $this->order;
    }
}
