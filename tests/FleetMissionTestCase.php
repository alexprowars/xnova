<?php

namespace Tests;

use App\Engine\Coordinates;
use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Fleet\MissionType;
use App\Facades\Galaxy;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

abstract class FleetMissionTestCase extends TestCase
{
	protected User $user;
	protected User $targetUser;
	protected Planet $planet;
	protected Planet $targetPlanet;

	protected function setUp(): void
	{
		parent::setUp();

		$this->freezeSecond();
		Notification::fake();
		config([
			'game.metal_basic_income' => 0,
			'game.crystal_basic_income' => 0,
			'game.deuterium_basic_income' => 0,
			'game.baseMetalProduction' => 5000,
			'game.baseCrystalProduction' => 5000,
			'game.baseDeuteriumProduction' => 5000,
			'game.maxPlanets' => 9,
		]);

		$this->user = User::factory()->createOne();
		$this->targetUser = User::factory()->createOne();
		$this->planet = Galaxy::createPlanet(new Coordinates(1, 1, 1), $this->user, null, true);
		$this->targetPlanet = Galaxy::createPlanet(new Coordinates(1, 1, 2), $this->targetUser);

		foreach ([$this->planet, $this->targetPlanet] as $planet) {
			$planet->update([
				'metal' => 10000,
				'crystal' => 10000,
				'deuterium' => 10000,
				'last_update' => now(),
			]);
		}
	}

	protected function createMissionFleet(MissionType $mission, array $ships = [202 => 2], array $attributes = []): Fleet
	{
		return Fleet::create([
			'user_id' => $this->user->id,
			'user_name' => $this->planet->name,
			'mission' => $mission,
			'entities' => FleetEntityCollection::createFromArray($ships),
			'start_galaxy' => $this->planet->galaxy,
			'start_system' => $this->planet->system,
			'start_planet' => $this->planet->planet,
			'start_type' => $this->planet->planet_type,
			'end_galaxy' => $this->targetPlanet->galaxy,
			'end_system' => $this->targetPlanet->system,
			'end_planet' => $this->targetPlanet->planet,
			'end_type' => $this->targetPlanet->planet_type,
			'target_user_id' => $this->targetUser->id,
			'target_user_name' => $this->targetPlanet->name,
			'start_date' => now()->addHour(),
			'end_date' => now()->addHours(2),
			'updated_at' => now()->addHour(),
			'resource_metal' => 1000,
			'resource_crystal' => 500,
			'resource_deuterium' => 200,
			'mess' => 0,
			...$attributes,
		]);
	}
}