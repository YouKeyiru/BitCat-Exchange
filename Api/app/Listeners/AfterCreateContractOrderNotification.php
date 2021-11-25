<?php

namespace App\Listeners;

use App\Events\AfterCreateContractOrder;
use App\Services\ContractTransService;

class AfterCreateContractOrderNotification
{
    /**
     * 合约下单后
     *
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
     * @param AfterCreateContractOrder $event
     * @return void
     */
    public function handle(AfterCreateContractOrder $event)
    {
        //
        $order = $event->getData();

        ContractTransService::setCacheOrder($order);
    }



}
