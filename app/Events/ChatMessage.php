<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatMessage implements ShouldBroadcast
{
	use Dispatchable;
	use InteractsWithSockets;
	use SerializesModels;

	public $message;

	public function __construct($message)
	{
		$this->message = $message;
		$this->dontBroadcastToCurrentUser();
	}

	public function broadcastOn()
	{
		return new Channel('chat');
	}
}
