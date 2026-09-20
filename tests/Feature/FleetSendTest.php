<?php

use App\Engine\Coordinates;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\FleetCollection;
use App\Engine\Fleet\FleetSend;
use App\Engine\Fleet\MissionType;
use App\Exceptions\Exception;
use App\Facades\Galaxy;
use App\Models\Assault;
use App\Models\Fleet;
use App\Models\Friend;
use App\Models\LogsFleet;
use App\Models\LogsTransfer;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
	$this->freezeSecond();
	config([
		'game.fleet_speed' => 1,
		'game.maxGalaxyInWorld' => 9,
		'game.maxSystemInGalaxy' => 499,
		'game.maxPlanetInSystem' => 15,
		'game.noobprotection' => 0,
		'game.disableAttacks' => 0,
	]);

	$this->user = User::factory()->createOne();
	$this->user->setTech('computer', 5);
	$this->user->setTech('expedition', 2);
	$this->planet = Galaxy::createPlanet(new Coordinates(1, 1, 1), $this->user, null, true);
	$this->planet->update([
		'metal' => 100000,
		'crystal' => 100000,
		'deuterium' => 100000,
		'last_update' => now(),
	]);
	foreach ([202, 204, 208, 209, 210, 214, 216] as $shipId) {
		$this->planet->updateAmount($shipId, 10);
	}

	$this->otherUser = User::factory()->createOne(['onlinetime' => now()]);
	$this->targetPlanet = Galaxy::createPlanet(new Coordinates(1, 1, 2), $this->otherUser);
	$this->targetPlanet->update(['debris_metal' => 10000, 'debris_crystal' => 5000]);
	$this->ownPlanet = Galaxy::createPlanet(new Coordinates(1, 1, 13), $this->user);
	$this->moon = Galaxy::createMoon($this->targetPlanet->coordinates, $this->otherUser, 20);
	$this->targets = [
		'enemy' => $this->targetPlanet->coordinates,
		'own' => $this->ownPlanet->coordinates,
		'moon' => $this->moon->coordinates,
		'debris' => new Coordinates(1, 1, 2, PlanetType::DEBRIS),
		'empty' => new Coordinates(1, 1, 14, PlanetType::PLANET),
		'expedition' => new Coordinates(1, 1, 16, PlanetType::PLANET),
	];
});

test('sending a fleet persists the mission and deducts ships cargo and fuel', function (MissionType $mission, string $destination, array $ships) {
	$target = $this->targets[$destination];
	$sender = new FleetSend($this->planet, $target, $mission);
	$sender->setFleets($ships);
	$cargo = $mission == MissionType::Transport ? ['metal' => 1000, 'crystal' => 500, 'deuterium' => 200] : [];
	$sender->setResources($cargo);
	$stayDuration = 0;
	$assault = null;

	if ($mission == MissionType::Expedition) {
		$sender->setExpeditionTime(1);
		$stayDuration = 3600;
	}
	if ($mission == MissionType::StayAlly) {
		Friend::create([
			'user_id' => $this->user->id,
			'friend_id' => $this->otherUser->id,
			'active' => true,
			'message' => '',
		]);
		$this->targetPlanet->updateAmount('ally_deposit', 1);
		$sender->setStayTime(2);
		$stayDuration = 7200;
	}
	if ($mission == MissionType::Assault) {
		$assault = Assault::create([
			'user_id' => $this->user->id,
			'galaxy' => 1,
			'system' => 1,
			'planet' => 2,
			'planet_type' => PlanetType::PLANET,
		]);
		$sender->setAssault($assault);
	}

	$collection = FleetCollection::createFromArray($ships, $this->planet);
	$distance = $collection->getDistance($this->planet->coordinates, $target);
	$duration = $collection->getDuration(10, $distance);
	$fuel = $collection->getConsumption($duration, $distance);
	$stayFuel = $mission == MissionType::StayAlly ? $collection->getStayConsumption() * 2 : 0;
	$targetResources = $this->targetPlanet->only(['metal', 'crystal', 'deuterium']);

	$fleet = $sender->send()->fresh();

	expect(Fleet::sole()->id)->toBe($fleet->id)
		->and($fleet->mission)->toBe($mission)
		->and($fleet->user_id)->toBe($this->user->id)
		->and($fleet->entities->pluck('count', 'id')->all())->toBe($ships)
		->and($fleet->getOriginCoordinates()->isSame($this->planet->coordinates))->toBeTrue()
		->and($fleet->getDestinationCoordinates()->isSame($target))->toBeTrue()
		->and($fleet->start_date->equalTo(now()->addSeconds($duration)))->toBeTrue()
		->and($fleet->end_date->equalTo(now()->addSeconds(2 * $duration + $stayDuration)))->toBeTrue()
		->and($fleet->assault_id)->toBe($assault?->id)
		->and($fleet->rounds)->toBe($mission == MissionType::Attack ? 6 : 0)
		->and($fleet->only(['resource_metal', 'resource_crystal', 'resource_deuterium']))
		->toEqual([
			'resource_metal' => $cargo['metal'] ?? 0,
			'resource_crystal' => $cargo['crystal'] ?? 0,
			'resource_deuterium' => $cargo['deuterium'] ?? 0,
		])
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual([
			'metal' => 100000 - ($cargo['metal'] ?? 0),
			'crystal' => 100000 - ($cargo['crystal'] ?? 0),
			'deuterium' => 100000 - ($cargo['deuterium'] ?? 0) - $fuel - $stayFuel,
		])
		->and($this->targetPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))->toEqual($targetResources);

	foreach ($ships as $shipId => $count) {
		expect($this->planet->fresh()->getLevel($shipId))->toBe(10 - $count);
	}
	if ($stayDuration > 0) {
		expect($fleet->end_stay->equalTo($fleet->start_date->addSeconds($stayDuration)))->toBeTrue();
	} else {
		expect($fleet->end_stay)->toBeNull();
	}
	$targetUserId = match ($destination) {
		'empty', 'expedition' => null,
		'own' => $this->user->id,
		default => $this->otherUser->id,
	};
	expect($fleet->target_user_id)->toBe($targetUserId);

	if ($mission == MissionType::Transport) {
		expect(LogsTransfer::sole()->data['resources'])->toBe($cargo)
			->and(LogsTransfer::sole()->target_id)->toBe($this->otherUser->id);
	}
	if ($mission == MissionType::Attack) {
		expect(LogsFleet::sole()->amount)->toBe(1);
	}
})->with([
	'attack' => [MissionType::Attack, 'enemy', [202 => 1, 204 => 2]],
	'joint attack' => [MissionType::Assault, 'enemy', [204 => 2]],
	'transport' => [MissionType::Transport, 'enemy', [202 => 2]],
	'deployment' => [MissionType::Stay, 'own', [204 => 2]],
	'hold at a friend planet' => [MissionType::StayAlly, 'enemy', [204 => 2]],
	'espionage' => [MissionType::Spy, 'enemy', [210 => 2]],
	'colonization' => [MissionType::Colonization, 'empty', [208 => 1]],
	'recycling' => [MissionType::Recycling, 'debris', [209 => 2]],
	'moon destruction' => [MissionType::Destruction, 'moon', [214 => 1]],
	'base creation' => [MissionType::CreateBase, 'empty', [216 => 1]],
	'expedition' => [MissionType::Expedition, 'expedition', [202 => 2]],
]);

test('incompatible missions are rejected without deducting ships or resources', function (MissionType $mission, string $destination, array $ships) {
	$sender = new FleetSend($this->planet, $this->targets[$destination], $mission);
	$sender->setFleets($ships);
	$sender->setResources(['metal' => 100]);
	$sender->setExpeditionTime(1);

	expect(fn() => $sender->send())->toThrow(Exception::class, 'Выполнение данной миссии невозможно!');

	expect(Fleet::count())->toBe(0)
		->and(LogsFleet::count())->toBe(0)
		->and(LogsTransfer::count())->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 100000, 'crystal' => 100000, 'deuterium' => 100000]);
	foreach ($ships as $shipId => $count) {
		expect($this->planet->fresh()->getLevel($shipId))->toBe(10);
	}
})->with([
	'attack on own colony' => [MissionType::Attack, 'own', [204 => 1]],
	'attack with recycler' => [MissionType::Attack, 'enemy', [209 => 1]],
	'joint attack without a group' => [MissionType::Assault, 'enemy', [204 => 1]],
	'transport without cargo ship' => [MissionType::Transport, 'enemy', [204 => 1]],
	'deployment to enemy' => [MissionType::Stay, 'enemy', [204 => 1]],
	'espionage without probes' => [MissionType::Spy, 'enemy', [204 => 1]],
	'espionage on own colony' => [MissionType::Spy, 'own', [210 => 1]],
	'colonization without colony ship' => [MissionType::Colonization, 'empty', [202 => 1]],
	'colonization of occupied position' => [MissionType::Colonization, 'enemy', [208 => 1]],
	'recycling without recycler' => [MissionType::Recycling, 'debris', [202 => 1]],
	'destruction without death star' => [MissionType::Destruction, 'moon', [204 => 1]],
	'destruction of a planet' => [MissionType::Destruction, 'enemy', [214 => 1]],
	'base creation without base ship' => [MissionType::CreateBase, 'empty', [202 => 1]],
	'expedition with probes only' => [MissionType::Expedition, 'expedition', [210 => 1]],
	'expedition to a regular position' => [MissionType::Expedition, 'empty', [202 => 1]],
]);

test('transport failures roll back ships and do not create transfer logs', function (array $resources, int $deuterium, string $error) {
	$this->planet->update(['deuterium' => $deuterium]);
	$sender = new FleetSend($this->planet, $this->targetPlanet->coordinates, MissionType::Transport);
	$sender->setFleets([202 => 1]);
	$sender->setResources($resources);

	expect(fn() => $sender->send())->toThrow(Exception::class, $error);

	expect(Fleet::count())->toBe(0)
		->and(LogsTransfer::count())->toBe(0)
		->and($this->planet->fresh()->getLevel(202))->toBe(10)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 100000, 'crystal' => 100000, 'deuterium' => $deuterium]);
})->with([
	'empty cargo' => [[], 100000, 'Нет сырья для транспорта!'],
	'no fuel' => [['metal' => 100], 0, 'Не хватает топлива на полёт!'],
	'not enough metal' => [['metal' => 100001], 100000, fn() => __('fleet.fl_noressources')],
	'cargo leaves no room for fuel' => [['metal' => 5000], 100000, fn() => __('fleet.fl_nostoragespa')],
]);

test('recycling requires a nonempty debris field', function () {
	$this->targetPlanet->update(['debris_metal' => 0, 'debris_crystal' => 0]);
	$sender = new FleetSend($this->planet, $this->targets['debris'], MissionType::Recycling);
	$sender->setFleets([209 => 1]);

	expect(fn() => $sender->send())->toThrow(Exception::class, 'Нет обломков для сбора.')
		->and(Fleet::count())->toBe(0)
		->and($this->planet->fresh()->getLevel(209))->toBe(10);
});

test('expedition requires technology and a permitted holding time', function (int $technology, int $hours, string $error) {
	$this->user->setTech('expedition', $technology);
	$sender = new FleetSend($this->planet, $this->targets['expedition'], MissionType::Expedition);
	$sender->setFleets([202 => 1]);
	$sender->setExpeditionTime($hours);

	expect(fn() => $sender->send())->toThrow(Exception::class, $error)
		->and(Fleet::count())->toBe(0)
		->and($this->planet->fresh()->getLevel(202))->toBe(10);
})->with([
	'no expedition technology' => [0, 1, 'Вами не изучена "Экспедиционная технология"!'],
	'zero holding time' => [2, 0, 'Вы не можете столько времени летать в экспедиции!'],
	'excessive holding time' => [2, 3, 'Вы не можете столько времени летать в экспедиции!'],
]);

test('holding at a foreign planet requires a depot and friendship', function (bool $hasDepot, string $error) {
	if ($hasDepot) {
		$this->targetPlanet->updateAmount('ally_deposit', 1);
	}
	$sender = new FleetSend($this->planet, $this->targetPlanet->coordinates, MissionType::StayAlly);
	$sender->setFleets([204 => 1]);
	$sender->setStayTime(1);

	expect(fn() => $sender->send())->toThrow(Exception::class, $error)
		->and(Fleet::count())->toBe(0)
		->and($this->planet->fresh()->getLevel(204))->toBe(10);
})->with([
	'no alliance depot' => [false, 'На планете нет склада альянса!'],
	'no diplomatic relationship' => [true, 'Нельзя охранять вражеские планеты!'],
]);