<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Chime;
use App\Session;
use App\Response;

class SubmitResponse implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Chime $chime, Session $session, Response $response, $isEdit=false)
    {
        $this->chime = $chime;
        $this->session = $session;
        $this->response = $response;
        $this->isEdit = $isEdit;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('session-response.' .$this->chime->id);
    }

    public $chime;
    public $session;
    public $response;
    public $isEdit;
}
