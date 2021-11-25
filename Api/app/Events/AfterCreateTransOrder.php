<?php

namespace App\Events;

use App\Models\FbTrans;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AfterCreateTransOrder
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     * @param $order
     * @return void
     */
    public function __construct(FbTrans $order)
    {
        //
        $this->order = $order;
    }

    public function getData()
    {
        return $this->order;
    }
}
