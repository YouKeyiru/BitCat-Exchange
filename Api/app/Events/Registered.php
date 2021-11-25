<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Registered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    /**
     * Create a new event instance.
     * @param $user
     * @return void
     */
    public function __construct(User $user)
    {
        //
        $this->user = $user;
    }

    public function getData()
    {
        return $this->user;
    }
}
