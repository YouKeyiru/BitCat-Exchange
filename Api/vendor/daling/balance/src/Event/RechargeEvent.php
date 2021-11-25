<?php

namespace Daling\Balance\Event;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RechargeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $eventData;

    /**
     * Create a new event instance.
     * @param $eventData
     * @return void
     */
    public function __construct($eventData)
    {
        //
        $this->eventData = $eventData;
    }
}
