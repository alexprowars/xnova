<?php

namespace App\Events;

use App\Models\Planet;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class PlanetEntityUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
	use Dispatchable;

	public function __construct(public Planet $planet)
	{
	}


	public function broadcastWith(): array
	{
		return [
			'id' => $this->planet->id,
		];
	}

	public function broadcastOn()
	{
		return new PrivateChannel('user.' . $this->planet->user_id);
	}
}
