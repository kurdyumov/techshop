<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Guards\UserGuard;
use Illuminate\Support\Facades\Auth;
use App\Services\SqlService;

class ReceiveOrder
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected SqlService $sqlService;

    public function __construct(array $data = [])
    {
        $this->sqlService = new SqlService();
        

        if (!Auth::guard()->guest()) {
            // dd($this->sqlService->getCurrentOrderIfExists());
            $order = $this->sqlService->retrieveCurrentOrder();
            // dd($order);
            Auth::user()->setOrder($order);
        }
    }
}
