<?php

namespace App\Listeners;

use App\Events\afterCreateTransOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateTransOrderNotification
{
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
     * @param  afterCreateTransOrder  $event
     * @return void
     */
    public function handle(afterCreateTransOrder $event)
    {
        //
        $data = $event->getData();

    }
}
