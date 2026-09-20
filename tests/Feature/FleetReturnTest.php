<?php

use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\MissionType;
use App\Facades\Galaxy;
use App\Jobs\FleetMissionJob;
use App\Models\Fleet;
use Tests\FleetMissionTestCase;

uses(FleetMissionTestCase::class);

test('returning fleet restores ships and cargo only when its return time is reached', function (MissionType $mission, int $shipId) {
	$this->planet->updateAmount($shipId, 3);
	$fleet = $this->createMissionFleet($mission, [$shipId => 2], ['mess' => 1]);
	$this->travelTo($fleet->end_date->subSecond());

	(new FleetMissionJob($fleet->fresh()))->handle();

	expect(Fleet::find($fleet->id))->not->toBeNull()
		->and($this->planet->fresh()->getLevel($shipId))->toBe(3)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 10000, 'crystal' => 10000, 'deuterium' => 10000]);

	$this->travelTo($fleet->end_date);
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel($shipId))->toBe(5)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 11000, 'crystal' => 10500, 'deuterium' => 10200])
		->and($this->targetPlanet->fresh()->getLevel($shipId))->toBe(0)
		->and($this->targetPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 10000, 'crystal' => 10000, 'deuterium' => 10000]);
})->with([
	'attack' => [MissionType::Attack, 204],
	'joint attack' => [MissionType::Assault, 204],
	'transport' => [MissionType::Transport, 202],
	'cancelled deployment' => [MissionType::Stay, 202],
	'allied holding' => [MissionType::StayAlly, 204],
	'espionage' => [MissionType::Spy, 210],
	'failed colonization' => [MissionType::Colonization, 208],
	'recycling' => [MissionType::Recycling, 209],
	'moon destruction' => [MissionType::Destruction, 214],
	'failed base creation' => [MissionType::CreateBase, 216],
	'expedition' => [MissionType::Expedition, 202],
]);

test('returning fleet restores every surviving ship type and ignores destroyed ships', function () {
	$this->planet->updateAmount(202, 3);
	$this->planet->updateAmount(204, 1);
	$this->planet->updateAmount(209, 4);
	$fleet = $this->createMissionFleet(MissionType::Attack, [202 => 2, 204 => 5, 209 => 1], ['mess' => 1]);
	$fleet->entities->getByEntityId(209)->count = 0;
	$fleet->save();
	$this->travelTo($fleet->end_date);

	(new FleetMissionJob($fleet->fresh()))->handle();

	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel(202))->toBe(5)
		->and($this->planet->fresh()->getLevel(204))->toBe(6)
		->and($this->planet->fresh()->getLevel(209))->toBe(4);
});

test('return to a moon restores the fleet to the moon or its planet if destroyed', function (bool $destroyed) {
	$moon = Galaxy::createMoon($this->planet->coordinates, $this->user, 20);
	$moon->update([
		'metal' => 100,
		'crystal' => 200,
		'deuterium' => 300,
		'last_update' => now(),
		'destroyed_at' => $destroyed ? now() : null,
	]);
	$fleet = $this->createMissionFleet(MissionType::Transport, [202 => 2], [
		'mess' => 1,
		'start_type' => PlanetType::MOON,
	]);
	$this->travelTo($fleet->end_date);

	(new FleetMissionJob($fleet->fresh()))->handle();

	expect(Fleet::find($fleet->id))->toBeNull();
	if ($destroyed) {
		expect($this->planet->fresh()->getLevel(202))->toBe(2)
			->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
			->toEqual(['metal' => 11000, 'crystal' => 10500, 'deuterium' => 10200])
			->and($moon->fresh()->getLevel(202))->toBe(0)
			->and($moon->fresh()->only(['metal', 'crystal', 'deuterium']))
			->toEqual(['metal' => 100, 'crystal' => 200, 'deuterium' => 300]);
	} else {
		expect($moon->fresh()->getLevel(202))->toBe(2)
			->and($moon->fresh()->only(['metal', 'crystal', 'deuterium']))
			->toEqual(['metal' => 1100, 'crystal' => 700, 'deuterium' => 500])
			->and($this->planet->fresh()->getLevel(202))->toBe(0)
			->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
			->toEqual(['metal' => 10000, 'crystal' => 10000, 'deuterium' => 10000]);
	}
})->with([
	'intact moon' => [false],
	'destroyed moon' => [true],
]);

test('cancelled deployment does not transfer ships or cargo to a new owner of the origin', function () {
	$fleet = $this->createMissionFleet(MissionType::Stay, [202 => 2], ['mess' => 1]);
	$this->planet->update(['user_id' => $this->targetUser->id]);
	$this->travelTo($fleet->end_date);

	(new FleetMissionJob($fleet->fresh()))->handle();

	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel(202))->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 10000, 'crystal' => 10000, 'deuterium' => 10000]);
});

test('returning fleet is removed when its origin no longer exists', function (MissionType $mission) {
	$fleet = $this->createMissionFleet($mission, [202 => 2], ['mess' => 1]);
	$this->planet->delete();
	$this->travelTo($fleet->end_date);

	(new FleetMissionJob($fleet->fresh()))->handle();

	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->targetPlanet->fresh()->getLevel(202))->toBe(0)
		->and($this->targetPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 10000, 'crystal' => 10000, 'deuterium' => 10000]);
})->with([MissionType::Transport, MissionType::Stay]);