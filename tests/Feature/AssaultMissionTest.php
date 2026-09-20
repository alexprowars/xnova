<?php

use App\Engine\Coordinates;
use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Fleet\FleetCollection;
use App\Engine\Fleet\FleetSend;
use App\Engine\Fleet\MissionType;
use App\Exceptions\Exception;
use App\Facades\Galaxy;
use App\Jobs\FleetMissionJob;
use App\Models\Assault;
use App\Models\AssaultUser;
use App\Models\Fleet;
use App\Models\Report;
use App\Models\User;
use Tests\FleetMissionTestCase;
use Tests\Support\RequiresBattleEngine;

uses(FleetMissionTestCase::class, RequiresBattleEngine::class);

beforeEach(function () {
	config(['game.noobprotection' => 0, 'game.disableAttacks' => 0, 'game.fleet_speed' => 1]);
	$this->ally = User::factory()->createOne();
	$this->ally->setTech('computer', 5);
	$this->allyPlanet = Galaxy::createPlanet(new Coordinates(1, 1, 13), $this->ally);
	$this->allyPlanet->update([
		'metal' => 10000,
		'crystal' => 10000,
		'deuterium' => 10000,
		'last_update' => now(),
	]);
	$this->allyPlanet->updateAmount(202, 10);
	$this->allyPlanet->updateAmount(204, 10);

	$this->leader = $this->createMissionFleet(MissionType::Attack, [202 => 2], [
		'resource_metal' => 0,
		'resource_crystal' => 0,
		'resource_deuterium' => 0,
	]);
	$this->assault = Assault::create([
		'user_id' => $this->user->id,
		'fleet_id' => $this->leader->id,
		'galaxy' => $this->targetPlanet->galaxy,
		'system' => $this->targetPlanet->system,
		'planet' => $this->targetPlanet->planet,
		'planet_type' => $this->targetPlanet->planet_type,
	]);
	$this->assault->users()->createMany([
		['user_id' => $this->user->id],
		['user_id' => $this->ally->id],
	]);
	$this->leader->update(['assault_id' => $this->assault->id]);
	$this->follower = $this->createMissionFleet(MissionType::Assault, [202 => 6], [
		'user_id' => $this->ally->id,
		'user_name' => $this->allyPlanet->name,
		'start_planet' => $this->allyPlanet->planet,
		'assault_id' => $this->assault->id,
		'end_date' => now()->addMinutes(150),
		'resource_metal' => 0,
		'resource_crystal' => 0,
		'resource_deuterium' => 0,
	]);
});

test('joining fleets synchronize arrival while preserving each members return duration', function (int $shipId, int $speed, bool $delaysGroup) {
	$originalArrival = $this->leader->start_date;
	$unrelated = $this->createMissionFleet(MissionType::Attack);
	$collection = FleetCollection::createFromArray([$shipId => 1], $this->allyPlanet);
	$distance = $collection->getDistance($this->allyPlanet->coordinates, $this->targetPlanet->coordinates);
	$duration = $collection->getDuration($speed, $distance);
	$ownArrival = now()->addSeconds($duration);
	$expectedArrival = $delaysGroup ? $ownArrival : $originalArrival;
	$sender = new FleetSend($this->allyPlanet, $this->targetPlanet->coordinates, MissionType::Assault);
	$sender->setFleets([$shipId => 1]);
	$sender->setFleetSpeed($speed);
	$sender->setAssault($this->assault);

	$joined = $sender->send()->fresh();

	expect($ownArrival->greaterThan($originalArrival))->toBe($delaysGroup)
		->and($joined->assault_id)->toBe($this->assault->id)
		->and($joined->start_date->equalTo($expectedArrival))->toBeTrue()
		->and($joined->end_date->equalTo($expectedArrival->addSeconds($duration)))->toBeTrue()
		->and($joined->updated_at->equalTo($expectedArrival))->toBeTrue()
		->and($this->leader->fresh()->start_date->equalTo($expectedArrival))->toBeTrue()
		->and($this->leader->fresh()->end_date->equalTo($expectedArrival->addHour()))->toBeTrue()
		->and($this->follower->fresh()->start_date->equalTo($expectedArrival))->toBeTrue()
		->and($this->follower->fresh()->end_date->equalTo($expectedArrival->addMinutes(90)))->toBeTrue()
		->and($this->leader->fresh()->updated_at->equalTo($expectedArrival))->toBeTrue()
		->and($this->follower->fresh()->updated_at->equalTo($expectedArrival))->toBeTrue()
		->and($unrelated->fresh()->start_date->equalTo($unrelated->start_date))->toBeTrue()
		->and($unrelated->fresh()->end_date->equalTo($unrelated->end_date))->toBeTrue()
		->and($this->allyPlanet->fresh()->getLevel($shipId))->toBe(9);
})->with([
	'slower cargo ship postpones the group' => [202, 10, true],
	'faster fighter waits for the group' => [204, 10, false],
	'reduced speed postpones the group' => [204, 5, true],
]);

test('a rejected group join leaves existing schedules and ships unchanged', function () {
	$this->allyPlanet->update(['deuterium' => 0]);
	$sender = new FleetSend($this->allyPlanet, $this->targetPlanet->coordinates, MissionType::Assault);
	$sender->setFleets([202 => 1]);
	$sender->setAssault($this->assault);

	expect(fn() => $sender->send())->toThrow(Exception::class, 'Не хватает топлива на полёт!')
		->and(Fleet::count())->toBe(2)
		->and($this->allyPlanet->fresh()->getLevel(202))->toBe(10)
		->and($this->assault->fresh())->not->toBeNull()
		->and($this->leader->fresh()->start_date->equalTo($this->leader->start_date))->toBeTrue()
		->and($this->leader->fresh()->end_date->equalTo($this->leader->end_date))->toBeTrue()
		->and($this->follower->fresh()->start_date->equalTo($this->follower->start_date))->toBeTrue()
		->and($this->follower->fresh()->end_date->equalTo($this->follower->end_date))->toBeTrue();
});

test('a group participant waits for the leading attack without starting a separate battle', function () {
	$this->travelTo($this->follower->start_date);

	(new FleetMissionJob($this->follower->fresh()))->handle();
	(new FleetMissionJob($this->follower->fresh()))->handle();

	expect(Report::count())->toBe(0)
		->and($this->follower->fresh()->mess)->toBe(0)
		->and($this->follower->fresh()->assault_id)->toBe($this->assault->id)
		->and($this->leader->fresh()->mess)->toBe(0)
		->and(AssaultUser::where('assault_id', $this->assault->id)->count())->toBe(2)
		->and($this->targetPlanet->fresh()->metal)->toEqual(10000);
});

test('a missing target disbands the group and all participants return without a battle', function () {
	$this->targetPlanet->delete();
	$this->travelTo($this->leader->start_date);

	(new FleetMissionJob($this->leader->fresh()))->handle();
	(new FleetMissionJob($this->follower->fresh()))->handle();

	expect(Assault::find($this->assault->id))->toBeNull()
		->and(AssaultUser::where('assault_id', $this->assault->id)->count())->toBe(0)
		->and($this->leader->fresh()->mess)->toBe(1)
		->and($this->follower->fresh()->mess)->toBe(1)
		->and($this->leader->fresh()->assault_id)->toBeNull()
		->and($this->follower->fresh()->assault_id)->toBeNull()
		->and(Report::count())->toBe(0);

	$this->travelTo($this->leader->end_date);
	(new FleetMissionJob($this->leader->fresh()))->handle();
	expect(Fleet::find($this->leader->id))->toBeNull()
		->and($this->planet->fresh()->getLevel(202))->toBe(2)
		->and(Fleet::find($this->follower->id))->not->toBeNull();

	$this->travelTo($this->follower->end_date);
	(new FleetMissionJob($this->follower->fresh()))->handle();
	expect(Fleet::find($this->follower->id))->toBeNull()
		->and($this->allyPlanet->fresh()->getLevel(202))->toBe(16);
});

test('joint attack shares loot by free cargo space and returns survivors to their owners', function (int $cargo, int $followerCount, int $leaderShare, int $followerShare) {
	$this->requireBattleEngine();
	$this->targetPlanet->update(['metal' => 6000, 'crystal' => 6000, 'deuterium' => 6000]);
	$this->leader->update(['resource_metal' => $cargo]);
	$this->follower->update(['entities' => FleetEntityCollection::createFromArray([202 => $followerCount])]);
	$this->travelTo($this->leader->start_date);

	(new FleetMissionJob($this->follower->fresh()))->handle();
	(new FleetMissionJob($this->leader->fresh()))->handle();
	(new FleetMissionJob($this->follower->fresh()))->handle();

	$leader = $this->leader->fresh();
	$follower = $this->follower->fresh();
	expect($leader->mess)->toBe(1)
		->and($follower->mess)->toBe(1)
		->and($leader->won)->toBe(1)
		->and($follower->won)->toBe(1)
		->and($leader->entities->pluck('count', 'id')->all())->toBe([202 => 2])
		->and($follower->entities->pluck('count', 'id')->all())->toBe([202 => $followerCount])
		->and($leader->only(['resource_metal', 'resource_crystal', 'resource_deuterium']))
		->toBe(['resource_metal' => $cargo + $leaderShare, 'resource_crystal' => $leaderShare, 'resource_deuterium' => $leaderShare])
		->and($follower->only(['resource_metal', 'resource_crystal', 'resource_deuterium']))
		->toBe(['resource_metal' => $followerShare, 'resource_crystal' => $followerShare, 'resource_deuterium' => $followerShare])
		->and($leader->getCargo())->toBeLessThanOrEqual($leader->entities->getCapacity())
		->and($follower->getCargo())->toBeLessThanOrEqual($follower->entities->getCapacity())
		->and($this->targetPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 3000, 'crystal' => 3000, 'deuterium' => 3000])
		->and(Report::count())->toBe(1)
		->and(Assault::find($this->assault->id))->toBeNull()
		->and(AssaultUser::where('assault_id', $this->assault->id)->count())->toBe(0)
		->and($leader->assault_id)->toBeNull()
		->and($follower->assault_id)->toBeNull()
		->and($leader->updated_at->equalTo($leader->end_date))->toBeTrue()
		->and($follower->updated_at->equalTo($follower->end_date))->toBeTrue();

	$this->travelTo($leader->end_date);
	(new FleetMissionJob($leader))->handle();
	expect(Fleet::find($leader->id))->toBeNull()
		->and(Fleet::find($follower->id))->not->toBeNull()
		->and($this->planet->fresh()->getLevel(202))->toBe(2)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 10000 + $cargo + $leaderShare, 'crystal' => 10000 + $leaderShare, 'deuterium' => 10000 + $leaderShare]);

	$this->travelTo($follower->end_date);
	(new FleetMissionJob($follower->fresh()))->handle();
	expect(Fleet::count())->toBe(0)
		->and(Report::count())->toBe(1)
		->and($this->allyPlanet->fresh()->getLevel(202))->toBe(10 + $followerCount)
		->and($this->allyPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 10000 + $followerShare, 'crystal' => 10000 + $followerShare, 'deuterium' => 10000 + $followerShare]);
})->with([
	'different fleet capacities' => [0, 6, 750, 2250],
	'cargo already occupies half the leading hold' => [5000, 2, 1000, 2000],
	'full leading hold gets no additional loot' => [10000, 2, 0, 3000],
]);

test('defeated joint attack removes destroyed fleets and the group without restoring ships', function () {
	$this->requireBattleEngine();
	$this->targetPlanet->updateAmount(214, 1000);
	$this->travelTo($this->leader->start_date);

	(new FleetMissionJob($this->leader->fresh()))->handle();

	expect(Fleet::find($this->leader->id))->toBeNull()
		->and(Fleet::find($this->follower->id))->toBeNull()
		->and(Assault::find($this->assault->id))->toBeNull()
		->and(AssaultUser::where('assault_id', $this->assault->id)->count())->toBe(0)
		->and(Report::sole()->data['won'])->toBe(2)
		->and($this->planet->fresh()->getLevel(202))->toBe(0)
		->and($this->allyPlanet->fresh()->getLevel(202))->toBe(10)
		->and($this->planet->fresh()->metal)->toEqual(10000)
		->and($this->allyPlanet->fresh()->metal)->toEqual(10000)
		->and($this->targetPlanet->fresh()->metal)->toEqual(10000);
});