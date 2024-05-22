<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatPrivateMessage implements ShouldBroadcast
{
	use Dispatchable;
	use InteractsWithSockets;
	use SerializesModels;

	public function __construct(public int $userId, public $message)
	{
		$this->dontBroadcastToCurrentUser();
	}

	public function broadcastOn()
	{
		return new PrivateChannel('user.' . $this->userId);
	}
}
