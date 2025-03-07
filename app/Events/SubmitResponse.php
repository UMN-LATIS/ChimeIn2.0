<?php

namespace App\Events;

use App\Chime;
use App\Response;
use App\Session;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubmitResponse implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chime;

    public $session;

    public $response;

    public $user;

    public $isEdit;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Chime $chime, Session $session, Response $response, User $user, $isEdit = false)
    {
        $this->chime = $chime;
        $this->session = $session;
        $this->response = $response;
        $this->user = $user;
        $this->isEdit = $isEdit;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('session-response.'.$this->chime->id);
    }
}
