<?php

namespace Tests;

use App\Engine\Coordinates;
use App\Engine\QueueManager;
use App\Facades\Galaxy;
use App\Models\Planet;
use App\Models\User;

abstract class QueueTestCase extends TestCase
{
	protected Planet $planet;
	protected User $user;
	protected QueueManager $queue;

	protected function setUp(): void
	{
		parent::setUp();

		$this->freezeSecond();
		config([
			'game.game_speed' => 1,
			'game.maxBuildingQueue' => 5,
			'game.BuildLabWhileRun' => 0,
		]);

		$this->user = User::factory()->createOne();
		$this->planet = $this->createQueuePlanet(1);
		$this->queue = new QueueManager($this->planet);
	}

	protected function createQueuePlanet(int $position): Planet
	{
		$planet = Galaxy::createPlanet(new Coordinates(1, 1, $position), $this->user, null, true);
		$planet->update([
			'metal' => 1000000,
			'crystal' => 1000000,
			'deuterium' => 1000000,
			'field_max' => 100,
			'last_update' => now(),
		]);

		return $planet;
	}
}