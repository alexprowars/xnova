<?php

use App\Engine\Battle\Battle;
use App\Engine\Battle\Result\Result;
use App\Engine\Coordinates;
use App\Engine\Entity\Model\FleetEntityCollection;
use App\Facades\Galaxy;
use App\Models\Fleet;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class);

test('battle combat', function () {
	$battle = new Battle();

	/** @var User $user */
	$user = User::factory()->createOne(['username' => 'Attacker']);

	$fleets = [
		204 => 100,
		205 => 50,
		206 => 25,
		207 => 15,
		213 => 5,
	];

	$techs = [
		109 => 5,
		110 => 5,
		111 => 10,
	];

	foreach ($techs as $id => $lvl) {
		$user->technologies->getByEntityId($id)->setLevel($lvl);
	}

	$fleet = new Fleet();
	$fleet->id = 1;
	$fleet->entities = FleetEntityCollection::createFromArray($fleets);
	$fleet->user()->associate($user);
	$fleet->end_galaxy = 1;
	$fleet->end_system = 1;
	$fleet->end_planet = 1;

	$battle->addAttackerFleet($fleet);

	/** @var User $user */
	$user = User::factory()->createOne(['username' => 'Defender']);

	$fleets = [
		204 => 110,
		205 => 40,
		206 => 35,
		207 => 10,
		213 => 10,
	];

	$techs = [
		109 => 10,
		110 => 10,
		111 => 5,
	];

	foreach ($techs as $id => $lvl) {
		$user->technologies->getByEntityId($id)->setLevel($lvl);
	}

	$planet = Galaxy::createPlanet(new Coordinates(1, 1, 1), $user, null, true);

	foreach ($fleets as $id => $lvl) {
		$planet->entities->getByEntityId($id)->setLevel($lvl);
	}

	$battle->addPlanet($planet);

	$report = $battle->run();

	expect($report)->toBeInstanceOf(Result::class);
});
