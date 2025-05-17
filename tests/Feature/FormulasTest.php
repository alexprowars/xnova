<?php

use App\Engine\Coordinates;
use App\Engine\Enums\Resources;
use App\Engine\Fleet\FleetCollection;
use App\Engine\Vars;
use App\Facades\Galaxy;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
	Vars::init();

	$this->user = User::factory()->createOne();
	$this->planet = Galaxy::createPlanet(new Coordinates(1, 1, 1), $this->user, null, true);
});

test('construction cost', function () {
	$entity = $this->planet->getEntity('metal_mine')->unit();
	$entity->setLevel(9);

	expect($entity->getPrice())
		->toMatchArray(['metal' => 2306, 'crystal' => 576, 'deuterium' => 0]);

	$entity = $this->planet->getEntity('spy_tech')->unit();
	$entity->setLevel(9);

	expect($entity->getPrice())
		->toMatchArray(['metal' => 102400, 'crystal' => 512000, 'deuterium' => 102400]);

	$entity = $this->planet->getEntity('small_ship_cargo')->unit();

	expect($entity->getPrice())
		->toMatchArray(['metal' => 2000, 'crystal' => 2000, 'deuterium' => 0]);

	$entity = $this->planet->getEntity('gauss_canyon')->unit();

	expect($entity->getPrice())
		->toMatchArray(['metal' => 20000, 'crystal' => 15000, 'deuterium' => 2000]);
});

test('building time', function () {
	$entity = $this->planet->getEntity('metal_mine')->unit();
	$entity->setLevel(9);

	expect($entity->getTime())
		->toBeInt()->toEqual(4150);
});

test('building time with robot factory', function () {
	$this->planet->updateAmount('robot_factory', 5);

	$entity = $this->planet->getEntity('metal_mine')->unit();
	$entity->setLevel(9);

	expect($entity->getTime())
		->toBeInt()->toEqual(691);
});

test('building time with nano factory', function () {
	$this->planet->updateAmount('nano_factory', 5);

	$entity = $this->planet->getEntity('metal_mine')->unit();
	$entity->setLevel(9);

	expect($entity->getTime())
		->toBeInt()->toEqual(129);
});

test('research time', function () {
	$entity = $this->planet->getEntity('spy_tech')->unit();
	$entity->setLevel(9);

	expect($entity->getTime())
		->toBeInt()->toEqual(2211840);
});

test('research time with research lab', function () {
	$this->planet->updateAmount('laboratory', 5);

	$entity = $this->planet->getEntity('spy_tech')->unit();
	$entity->setLevel(9);

	expect($entity->getTime())
		->toBeInt()->toEqual(368640);
});

test('research time with intergalactic network', function () {
	$this->planet->updateAmount('laboratory', 5);

	$planet2 = Galaxy::createPlanet(new Coordinates(1, 1, 2), $this->user);
	$planet2->updateAmount('laboratory', 5);

	$planet3 = Galaxy::createPlanet(new Coordinates(1, 1, 3), $this->user);
	$planet3->updateAmount('laboratory', 5);

	$this->user->setTech('intergalactic', 3);

	$entity = $this->planet->getEntity('spy_tech')->unit();
	$entity->setLevel(9);

	expect($entity->getTime())
		->toBeInt()->toEqual(138240);
});

test('construction time', function () {
	$entity = $this->planet->getEntity('small_ship_cargo')->unit();

	expect($entity->getTime())
		->toBeInt()->toEqual(5760);

	$entity = $this->planet->getEntity('gauss_canyon')->unit();

	expect($entity->getTime())
		->toBeInt()->toEqual(50400);
});

test('construction time with shipyard', function () {
	$this->planet->updateAmount('hangar', 5);

	$entity = $this->planet->getEntity('small_ship_cargo')->unit();

	expect($entity->getTime())
		->toBeInt()->toEqual(960);

	$entity = $this->planet->getEntity('gauss_canyon')->unit();

	expect($entity->getTime())
		->toBeInt()->toEqual(8400);
});

test('resource production', function () {
	$coordinates = new Coordinates(1, 1, 15);

	$planet = Galaxy::createPlanet($coordinates, $this->user, null, true);
	$planet->temp_max = 20;
	$planet->updateAmount('metal_mine', 10);
	$planet->updateAmount('crystal_mine', 10);
	$planet->updateAmount('deuterium_mine', 10);
	$planet->updateAmount('solar_plant', 20);

	$production = $planet->getProduction(now()->addSecond());

	$rp = $production->getResourceProduction();
	$bp = $production->getBasicProduction();

	expect($rp->get(Resources::METAL) - $bp->get(Resources::METAL))
		->toEqual(778)
		->and($rp->get(Resources::CRYSTAL) - $bp->get(Resources::CRYSTAL))
		->toEqual(518)
		->and($rp->get(Resources::DEUTERIUM) - $bp->get(Resources::DEUTERIUM))
		->toEqual(352)
		->and($rp->get(Resources::ENERGY) - $bp->get(Resources::ENERGY))
		->toEqual(1654)
		->and($production->getProductionFactor())
		->toEqual(100);
});

test('resource production without enough energy', function () {
	$coordinates = new Coordinates(1, 1, 15);

	$planet = Galaxy::createPlanet($coordinates, $this->user, null, true);
	$planet->temp_max = 20;
	$planet->updateAmount('metal_mine', 10);
	$planet->updateAmount('crystal_mine', 10);
	$planet->updateAmount('deuterium_mine', 10);
	$planet->updateAmount('solar_plant', 10);

	$production = $planet->getProduction(now()->addSecond());

	$rp = $production->getResourceProduction();
	$bp = $production->getBasicProduction();

	expect($rp->get(Resources::METAL) - $bp->get(Resources::METAL))
		->toEqual(778 / 2)
		->and($rp->get(Resources::CRYSTAL) - $bp->get(Resources::CRYSTAL))
		->toEqual(518 / 2)
		->and($rp->get(Resources::DEUTERIUM) - $bp->get(Resources::DEUTERIUM))
		->toEqual(352 / 2)
		->and($rp->get(Resources::ENERGY) - $bp->get(Resources::ENERGY))
		->toEqual(-518)
		->and($production->getProductionFactor())
		->toEqual(50);
});

test('fusion plant production', function () {
	$this->planet->updateAmount('fusion_plant', 10);

	$production = $this->planet->getProduction(now()->addSecond());

	$rp = $production->getResourceProduction();
	$bp = $production->getBasicProduction();

	expect($rp->get(Resources::DEUTERIUM) - $bp->get(Resources::DEUTERIUM))
		->toEqual(-259)
		->and($rp->get(Resources::ENERGY) - $bp->get(Resources::ENERGY))
		->toEqual(488);
});

test('fusion plant production with energy level', function () {
	$this->planet->updateAmount('fusion_plant', 10);
	$this->user->setTech('energy', 10);

	$production = $this->planet->getProduction(now()->addSecond());

	$rp = $production->getResourceProduction();
	$bp = $production->getBasicProduction();

	expect($rp->get(Resources::DEUTERIUM) - $bp->get(Resources::DEUTERIUM))
		->toEqual(-259)
		->and($rp->get(Resources::ENERGY) - $bp->get(Resources::ENERGY))
		->toEqual(1213);
});

test('solar satelite production', function () {
	$this->planet->updateAmount('solar_satelit', 10);
	$this->planet->temp_max = 20;

	$production = $this->planet->getProduction(now()->addSecond());

	$rp = $production->getResourceProduction();

	expect($rp->get(Resources::ENERGY))
		->toEqual(260);
});

test('fleet duration', function () {
	$fleets = FleetCollection::createFromArray([202 => 1, 204 => 1], $this->planet);

	$target = new Coordinates(1, 1, 15);

	$distance = $fleets->getDistance($this->planet->coordinates, $target);
	$duration = $fleets->getDuration(10, $distance);

	expect($duration)
		->toEqual(5130);

	$target = new Coordinates(1, 50, 1);

	$distance = $fleets->getDistance($this->planet->coordinates, $target);
	$duration = $fleets->getDuration(10, $distance);

	expect($duration)
		->toEqual(13434);

	$target = new Coordinates(5, 1, 1);

	$distance = $fleets->getDistance($this->planet->coordinates, $target);
	$duration = $fleets->getDuration(10, $distance);

	expect($duration)
		->toEqual(44282);

	$distance = $fleets->getDistance($this->planet->coordinates, $target);
	$duration = $fleets->getDuration(5, $distance);

	expect($duration)
		->toEqual(88554);
});

test('fleet duration with combustion drive', function () {
	$fleets = FleetCollection::createFromArray([202 => 1], $this->planet);

	$this->user->setTech('combustion', 10);

	$target = new Coordinates(5, 1, 1);

	$distance = $fleets->getDistance($this->planet->coordinates, $target);
	$duration = $fleets->getDuration(10, $distance);

	expect($duration)
		->toEqual(31315);
});

test('fleet duration with impulse drive', function () {
	$fleets = FleetCollection::createFromArray([205 => 1], $this->planet);

	$this->user->setTech('impulse_motor', 10);

	$target = new Coordinates(5, 1, 1);

	$distance = $fleets->getDistance($this->planet->coordinates, $target);
	$duration = $fleets->getDuration(10, $distance);

	expect($duration)
		->toEqual(18084);
});

test('fleet duration with hyperspace drive', function () {
	$fleets = FleetCollection::createFromArray([207 => 1], $this->planet);

	$this->user->setTech('hyperspace_motor', 10);

	$target = new Coordinates(5, 1, 1);

	$distance = $fleets->getDistance($this->planet->coordinates, $target);
	$duration = $fleets->getDuration(10, $distance);

	expect($duration)
		->toEqual(15662);
});

test('fleet consumption', function () {
	$fleets = FleetCollection::createFromArray([202 => 1, 204 => 1], $this->planet);

	$target = new Coordinates(1, 1, 15);

	$distance = $fleets->getDistance($this->planet->coordinates, $target);
	$duration = $fleets->getDuration(10, $distance);
	$consumption = $fleets->getConsumption($duration, $distance);

	expect($consumption)
		->toEqual(3);

	$target = new Coordinates(1, 50, 1);

	$distance = $fleets->getDistance($this->planet->coordinates, $target);
	$duration = $fleets->getDuration(10, $distance);
	$consumption = $fleets->getConsumption($duration, $distance);

	expect($consumption)
		->toEqual(20);

	$target = new Coordinates(5, 1, 1);

	$distance = $fleets->getDistance($this->planet->coordinates, $target);
	$duration = $fleets->getDuration(10, $distance);
	$consumption = $fleets->getConsumption($duration, $distance);

	expect($consumption)
		->toEqual(213);

	$distance = $fleets->getDistance($this->planet->coordinates, $target);
	$duration = $fleets->getDuration(5, $distance);
	$consumption = $fleets->getConsumption($duration, $distance);

	expect($consumption)
		->toEqual(131);
});
