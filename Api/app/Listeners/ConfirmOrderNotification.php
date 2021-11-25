<?php

namespace App\Listeners;

use App\Events\afterConfirmOrder;

class ConfirmOrderNotification
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
     * @param afterConfirmOrder $event
     * @return void
     */
    public function handle(afterConfirmOrder $event)
    {
        //
        $data = $event->getData();

    }
}
