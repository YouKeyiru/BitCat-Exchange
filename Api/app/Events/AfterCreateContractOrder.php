<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AfterCreateContractOrder
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $order;

    /**
     * Create a new event instance.
     *
     * @param $order
     */
    public function __construct($order)
    {
        //
        $this->order = $order;

    }

    public function getData()
    {
        return $this->order;
    }
}
