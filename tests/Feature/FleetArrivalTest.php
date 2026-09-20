<?php

use App\Engine\Coordinates;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\MissionType;
use App\Facades\Galaxy;
use App\Jobs\FleetMissionJob;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\Report;
use App\Notifications\SystemMessage;
use Illuminate\Support\Facades\Notification;
use Tests\FleetMissionTestCase;
use Tests\Support\RequiresBattleEngine;

uses(FleetMissionTestCase::class, RequiresBattleEngine::class);

test('transport unloads at arrival and returns ships without returning delivered cargo', function () {
	$this->planet->updateAmount(202, 3);
	$this->targetPlanet->updateAmount(202, 1);
	$fleet = $this->createMissionFleet(MissionType::Transport);

	$this->travelTo($fleet->start_date->subSecond());
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect($fleet->fresh()->mess)->toBe(0)
		->and($this->targetPlanet->fresh()->metal)->toEqual(10000);
	Notification::assertNothingSent();

	$this->travelTo($fleet->start_date);
	(new FleetMissionJob($fleet->fresh()))->handle();
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect($fleet->fresh()->mess)->toBe(1)
		->and($fleet->fresh()->updated_at->equalTo($fleet->end_date))->toBeTrue()
		->and($fleet->fresh()->getCargo())->toBe(0)
		->and($this->targetPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 11000, 'crystal' => 10500, 'deuterium' => 10200])
		->and($this->targetPlanet->fresh()->getLevel(202))->toBe(1)
		->and($this->planet->fresh()->getLevel(202))->toBe(3);
	Notification::assertSentToTimes($this->user, SystemMessage::class, 1);
	Notification::assertSentToTimes($this->targetUser, SystemMessage::class, 1);

	$this->travelTo($fleet->end_date);
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel(202))->toBe(5)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 10000, 'crystal' => 10000, 'deuterium' => 10000]);
});

test('an overdue transport delivers cargo and returns ships in one job', function () {
	$fleet = $this->createMissionFleet(MissionType::Transport);
	$this->travelTo($fleet->end_date->addHour());

	(new FleetMissionJob($fleet->fresh()))->handle();

	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->targetPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 11000, 'crystal' => 10500, 'deuterium' => 10200])
		->and($this->planet->fresh()->getLevel(202))->toBe(2)
		->and($this->planet->fresh()->metal)->toEqual(10000);
});

test('deployment transfers ships and cargo to the destination and removes the flight', function () {
	$this->targetPlanet->update(['user_id' => $this->user->id]);
	$this->targetPlanet->updateAmount(202, 3);
	$fleet = $this->createMissionFleet(MissionType::Stay, [202 => 2], ['target_user_id' => $this->user->id]);
	$this->travelTo($fleet->start_date);

	(new FleetMissionJob($fleet->fresh()))->handle();

	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->targetPlanet->fresh()->getLevel(202))->toBe(5)
		->and($this->targetPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 11000, 'crystal' => 10500, 'deuterium' => 10200])
		->and($this->planet->fresh()->getLevel(202))->toBe(0)
		->and($this->planet->fresh()->metal)->toEqual(10000);
	Notification::assertSentTo($this->user, SystemMessage::class);
});

test('deployment returns with cargo if the destination changes owner', function () {
	$fleet = $this->createMissionFleet(MissionType::Stay, [202 => 2], ['target_user_id' => $this->user->id]);
	$this->travelTo($fleet->start_date);

	(new FleetMissionJob($fleet->fresh()))->handle();

	expect($fleet->fresh()->mess)->toBe(1)
		->and($fleet->fresh()->getCargo())->toBe(1700)
		->and($this->targetPlanet->fresh()->getLevel(202))->toBe(0)
		->and($this->targetPlanet->fresh()->metal)->toEqual(10000);

	$this->travelTo($fleet->end_date);
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel(202))->toBe(2)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 11000, 'crystal' => 10500, 'deuterium' => 10200]);
});

test('allied fleet holds until the deadline and then returns ships and cargo', function () {
	$fleet = $this->createMissionFleet(MissionType::StayAlly, [202 => 2], [
		'end_stay' => now()->addHours(3),
		'end_date' => now()->addHours(4),
	]);
	$this->travelTo($fleet->start_date);
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect($fleet->fresh()->mess)->toBe(3)
		->and($fleet->fresh()->updated_at->equalTo($fleet->end_stay))->toBeTrue()
		->and($this->targetPlanet->fresh()->getLevel(202))->toBe(0)
		->and($this->targetPlanet->fresh()->metal)->toEqual(10000);

	$this->travelTo($fleet->end_stay->subSecond());
	(new FleetMissionJob($fleet->fresh()))->handle();
	expect($fleet->fresh()->mess)->toBe(3);

	$this->travelTo($fleet->end_stay);
	(new FleetMissionJob($fleet->fresh()))->handle();
	expect($fleet->fresh()->mess)->toBe(1)
		->and($fleet->fresh()->updated_at->equalTo($fleet->end_date))->toBeTrue()
		->and($this->planet->fresh()->getLevel(202))->toBe(0);

	$this->travelTo($fleet->end_date);
	(new FleetMissionJob($fleet->fresh()))->handle();
	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel(202))->toBe(2)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 11000, 'crystal' => 10500, 'deuterium' => 10200]);
});

test('expedition enters its holding phase upon arrival', function () {
	$fleet = $this->createMissionFleet(MissionType::Expedition, [202 => 2], [
		'end_planet' => 16,
		'target_user_id' => null,
		'end_stay' => now()->addHours(3),
		'end_date' => now()->addHours(4),
	]);
	$this->travelTo($fleet->start_date);
	(new FleetMissionJob($fleet->fresh()))->handle();
	$this->travelTo($fleet->end_stay->subSecond());
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect($fleet->fresh()->mess)->toBe(3)
		->and($fleet->fresh()->updated_at->equalTo($fleet->end_stay))->toBeTrue()
		->and($fleet->fresh()->getCargo())->toBe(1700)
		->and($this->planet->fresh()->getLevel(202))->toBe(0);
	Notification::assertNothingSent();
});

test('recyclers collect only available debris within their remaining capacity and return it', function (int $metal, int $crystal, int $cargo, int $collectedMetal, int $collectedCrystal) {
	$this->targetPlanet->update(['debris_metal' => $metal, 'debris_crystal' => $crystal]);
	$fleet = $this->createMissionFleet(MissionType::Recycling, [209 => 1], [
		'end_type' => PlanetType::DEBRIS,
		'resource_metal' => $cargo,
		'resource_crystal' => 0,
		'resource_deuterium' => 0,
	]);
	$this->travelTo($fleet->start_date);
	(new FleetMissionJob($fleet->fresh()))->handle();
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect($fleet->fresh()->mess)->toBe(1)
		->and($fleet->fresh()->resource_metal)->toBe($cargo + $collectedMetal)
		->and($fleet->fresh()->resource_crystal)->toBe($collectedCrystal)
		->and($this->targetPlanet->fresh()->debris_metal)->toEqual($metal - $collectedMetal)
		->and($this->targetPlanet->fresh()->debris_crystal)->toEqual($crystal - $collectedCrystal);

	$this->travelTo($fleet->end_date);
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel(209))->toBe(1)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 10000 + $cargo + $collectedMetal, 'crystal' => 10000 + $collectedCrystal, 'deuterium' => 10000]);
})->with([
	'whole debris field' => [6000, 4000, 0, 6000, 4000],
	'balanced collection at capacity' => [30000, 30000, 0, 10000, 10000],
	'remaining capacity used for metal' => [30000, 2000, 0, 18000, 2000],
	'existing cargo occupies recycler capacity' => [30000, 30000, 2000, 9000, 9000],
	'debris already collected' => [0, 0, 0, 0, 0],
]);

test('espionage of an undefended planet sends reports and returns probes', function () {
	$fleet = $this->createMissionFleet(MissionType::Spy, [210 => 2], [
		'resource_metal' => 0,
		'resource_crystal' => 0,
		'resource_deuterium' => 0,
	]);
	$this->travelTo($fleet->start_date);
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect($fleet->fresh()->mess)->toBe(1)
		->and($fleet->fresh()->entities->getByEntityId(210)->count)->toBe(2)
		->and(Report::count())->toBe(0);
	Notification::assertSentTo($this->user, SystemMessage::class);
	Notification::assertSentTo($this->targetUser, SystemMessage::class);

	$this->travelTo($fleet->end_date);
	(new FleetMissionJob($fleet->fresh()))->handle();
	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel(210))->toBe(2);
});

test('attack on an undefended planet loads loot and returns the surviving fleet', function () {
	$this->requireBattleEngine();

	$fleet = $this->createMissionFleet(MissionType::Attack, [202 => 4], [
		'resource_metal' => 0,
		'resource_crystal' => 0,
		'resource_deuterium' => 0,
	]);
	$this->travelTo($fleet->start_date);
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect($fleet->fresh()->mess)->toBe(1)
		->and($fleet->fresh()->won)->toBe(1)
		->and($fleet->fresh()->getCargo())->toBe(15000)
		->and($this->targetPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 5000, 'crystal' => 5000, 'deuterium' => 5000])
		->and(Report::count())->toBe(1);

	$this->travelTo($fleet->end_date);
	(new FleetMissionJob($fleet->fresh()))->handle();
	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel(202))->toBe(4)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 15000, 'crystal' => 15000, 'deuterium' => 15000]);
});

test('colonization and base creation consume one founding ship and unload the remaining fleet', function (MissionType $mission, int $shipId, PlanetType $type) {
	$this->user->setTech('colonization', 1);
	$this->user->setTech('fleet_base', 1);
	$fleet = $this->createMissionFleet($mission, [$shipId => 2, 202 => 1], [
		'end_planet' => 15,
		'target_user_id' => null,
	]);
	$this->travelTo($fleet->start_date);
	(new FleetMissionJob($fleet->fresh()))->handle();

	$colony = Planet::findByCoordinates(new Coordinates(1, 1, 15, $type));
	expect($colony)->not->toBeNull()
		->and($colony->user_id)->toBe($this->user->id)
		->and($colony->planet_type)->toBe($type)
		->and($colony->getLevel($shipId))->toBe(1)
		->and($colony->getLevel(202))->toBe(1)
		->and($colony->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 6000, 'crystal' => 5500, 'deuterium' => 5200])
		->and(Fleet::find($fleet->id))->toBeNull();
	Notification::assertSentTo($this->user, SystemMessage::class);
})->with([
	'colony' => [MissionType::Colonization, 208, PlanetType::PLANET],
	'military base' => [MissionType::CreateBase, 216, PlanetType::MILITARY_BASE],
]);

test('founding fleet returns intact when the colony or base limit is reached', function (MissionType $mission, int $shipId) {
	$fleet = $this->createMissionFleet($mission, [$shipId => 1], ['end_planet' => 15, 'target_user_id' => null]);
	$this->travelTo($fleet->start_date);
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect($fleet->fresh()->mess)->toBe(1)
		->and($fleet->fresh()->entities->getByEntityId($shipId)->count)->toBe(1)
		->and(Galaxy::isPositionFree(new Coordinates(1, 1, 15)))->toBeTrue();

	$this->travelTo($fleet->end_date);
	(new FleetMissionJob($fleet->fresh()))->handle();
	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel($shipId))->toBe(1)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 11000, 'crystal' => 10500, 'deuterium' => 10200]);
})->with([
	'colony' => [MissionType::Colonization, 208],
	'military base' => [MissionType::CreateBase, 216],
]);

test('founding fleet returns without unloading when another player occupies its destination', function (MissionType $mission, int $shipId) {
	$this->user->setTech('colonization', 1);
	$this->user->setTech('fleet_base', 1);
	$fleet = $this->createMissionFleet($mission, [$shipId => 1], ['target_user_id' => null]);
	$this->travelTo($fleet->start_date);
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect($fleet->fresh()->mess)->toBe(1)
		->and($this->targetPlanet->fresh()->getLevel($shipId))->toBe(0)
		->and($this->targetPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 10000, 'crystal' => 10000, 'deuterium' => 10000]);

	$this->travelTo($fleet->end_date);
	(new FleetMissionJob($fleet->fresh()))->handle();
	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel($shipId))->toBe(1)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 11000, 'crystal' => 10500, 'deuterium' => 10200]);
})->with([
	'colony' => [MissionType::Colonization, 208],
	'military base' => [MissionType::CreateBase, 216],
]);

test('a fleet returns intact when its destination has disappeared', function (MissionType $mission, int $shipId) {
	$fleet = $this->createMissionFleet($mission, [$shipId => 1]);
	$this->targetPlanet->delete();
	$this->travelTo($fleet->start_date);
	(new FleetMissionJob($fleet->fresh()))->handle();

	expect($fleet->fresh()->mess)->toBe(1)
		->and($fleet->fresh()->getCargo())->toBe(1700);

	$this->travelTo($fleet->end_date);
	(new FleetMissionJob($fleet->fresh()))->handle();
	expect(Fleet::find($fleet->id))->toBeNull()
		->and($this->planet->fresh()->getLevel($shipId))->toBe(1)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 11000, 'crystal' => 10500, 'deuterium' => 10200]);
})->with([
	'attack' => [MissionType::Attack, 202],
	'espionage' => [MissionType::Spy, 210],
	'deployment' => [MissionType::Stay, 202],
	'recycling' => [MissionType::Recycling, 209],
]);