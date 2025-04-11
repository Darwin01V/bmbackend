<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagoRecibido
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $email;
    public $dni;
    public $nombre_plan;
    public $cantidad;

    public function __construct($email, $dni, $nombre_plan, $cantidad)
    {
        $this->email = $email;
        $this->dni = $dni;
        $this->nombre_plan = $nombre_plan;
        $this->cantidad = $cantidad;
    }
    // /**
    //  * Get the channels the event should broadcast on.
    //  *
    //  * @return array<int, \Illuminate\Broadcasting\Channel>
    //  */
    // public function broadcastOn(): array
    // {
    //     return [
    //         new PrivateChannel('channel-name'),
    //     ];
    // }
}
