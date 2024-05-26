<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class PlanetEntityUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
	use Dispatchable;

	public function __construct(public int $userId)
	{
	}

	public function broadcastOn()
	{
		return new PrivateChannel('user.' . $this->userId);
	}
}
