<?php

use App\Engine\Building;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\ObjectsFactory;
use App\Engine\QueueManager;
use App\Models\Queue;
use Tests\QueueTestCase;

uses(QueueTestCase::class);

beforeEach(function () {
	$this->planet->updateAmount('laboratory', 3);
});

test('research charges resources and queues the next technology level', function () {
	$this->user->setTech(106, 1);
	$this->queue->add(ObjectsFactory::get(106));
	$item = Queue::sole();

	expect($item->type)->toBe(QueueType::RESEARCH)
		->and($item->user_id)->toBe($this->user->id)
		->and($item->planet_id)->toBe($this->planet->id)
		->and($item->object_id)->toBe(106)
		->and($item->level)->toBe(2)
		->and($item->date->equalTo(now()))->toBeTrue()
		->and($item->date_end->equalTo(now()->addSeconds(2160)))->toBeTrue()
		->and($this->user->fresh()->getTechLevel(106))->toBe(1)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 999600, 'crystal' => 998000, 'deuterium' => 999600]);
});

test('research cannot start without the required laboratory or resources', function (int $laboratory, int $deuterium) {
	$this->planet->updateAmount('laboratory', $laboratory);
	$this->planet->update(['deuterium' => $deuterium]);
	$this->queue->add(ObjectsFactory::get(106));

	expect(Queue::count())->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 1000000, 'crystal' => 1000000, 'deuterium' => $deuterium]);
})->with([
	'low laboratory level' => [2, 1000000],
	'insufficient deuterium' => [3, 199],
]);

test('only one research can run across all planets of a user', function () {
	$otherPlanet = $this->createQueuePlanet(2);
	$otherPlanet->updateAmount('laboratory', 3);
	$this->queue->add(ObjectsFactory::get(106));
	$this->queue->add(ObjectsFactory::get(108));
	(new QueueManager($otherPlanet))->add(ObjectsFactory::get(108));

	expect(Queue::count())->toBe(1)
		->and(Queue::sole()->object_id)->toBe(106)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 999800, 'crystal' => 999000, 'deuterium' => 999800])
		->and($otherPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 1000000, 'crystal' => 1000000, 'deuterium' => 1000000]);
});

test('research does not finish before its completion time', function () {
	$this->queue->add(ObjectsFactory::get(106));
	$this->queue->update();

	expect($this->user->fresh()->getTechLevel(106))->toBe(0)
		->and(Queue::count())->toBe(1);
});

test('research can finish from another planet and awards its level only once', function () {
	$otherPlanet = $this->createQueuePlanet(2);
	$this->queue->add(ObjectsFactory::get(106));
	Queue::sole()->update(['date' => now()->subHour()]);
	$otherQueue = new QueueManager($otherPlanet);

	$otherQueue->update();
	$otherQueue->update();

	expect($this->user->fresh()->getTechLevel(106))->toBe(1)
		->and(Queue::count())->toBe(0);
});

test('cancelling research from another planet refunds the original planet only once', function () {
	$otherPlanet = $this->createQueuePlanet(2);
	$object = ObjectsFactory::get(106);
	$this->queue->add($object);
	$otherQueue = new QueueManager($otherPlanet);

	$otherQueue->delete($object);
	$otherQueue->delete($object);

	expect(Queue::count())->toBe(0)
		->and($this->user->fresh()->getTechLevel(106))->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 1000000, 'crystal' => 1000000, 'deuterium' => 1000000])
		->and($otherPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 1000000, 'crystal' => 1000000, 'deuterium' => 1000000]);
});

test('cancelling a different technology leaves active research and resources unchanged', function () {
	$this->queue->add(ObjectsFactory::get(106));
	$this->queue->delete(ObjectsFactory::get(108));

	expect(Queue::sole()->object_id)->toBe(106)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 999800, 'crystal' => 999000, 'deuterium' => 999800]);
});

test('research respects the technology maximum level', function () {
	$this->planet->updateAmount('laboratory', 12);
	$this->planet->updateAmount('solar_satelit', 40000);
	$this->planet->update(['temp_max' => 20]);
	$this->user->setTech(199, 1);
	$this->queue->add(ObjectsFactory::get(199));

	expect($this->planet->energy)->toBeGreaterThanOrEqual(900000)
		->and(Queue::count())->toBe(0)
		->and($this->user->fresh()->getTechLevel(199))->toBe(1);
});

test('laboratory construction waits for research and resumes after cancellation', function () {
	$otherPlanet = $this->createQueuePlanet(2);
	$this->queue->add(ObjectsFactory::get(106));
	(new QueueManager($otherPlanet))->add(ObjectsFactory::get(31));
	$building = Queue::where('type', QueueType::BUILDING)->sole();

	expect($building->date)->toBeNull()
		->and($otherPlanet->fresh()->crystal)->toEqual(1000000);

	$this->queue->delete(ObjectsFactory::get(106));

	expect($building->fresh()->date->equalTo(now()))->toBeTrue()
		->and($otherPlanet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 999800, 'crystal' => 999600, 'deuterium' => 999800]);
});

test('laboratory construction resumes at the research completion time', function () {
	$otherPlanet = $this->createQueuePlanet(2);
	$this->queue->add(ObjectsFactory::get(106));
	$research = Queue::sole();
	$startedAt = now()->subSeconds(1200);
	$research->update(['date' => $startedAt]);
	(new QueueManager($otherPlanet))->add(ObjectsFactory::get(31));

	$this->queue->update();

	$building = Queue::sole();
	expect($this->user->fresh()->getTechLevel(106))->toBe(1)
		->and($building->type)->toBe(QueueType::BUILDING)
		->and($building->date->equalTo($startedAt->addSeconds(1080)))->toBeTrue();
});

test('laboratory construction may run alongside research when configured', function () {
	config(['game.BuildLabWhileRun' => 1]);
	$this->queue->add(ObjectsFactory::get(106));
	$this->queue->add(ObjectsFactory::get(31));

	expect(Queue::count())->toBe(2)
		->and(Queue::where('type', QueueType::BUILDING)->sole()->date)->not->toBeNull()
		->and(Building::checkLabInQueue($this->planet))->toBeFalse();
});

test('research detects an active laboratory upgrade', function () {
	$this->queue->add(ObjectsFactory::get(31));

	expect(Building::checkLabInQueue($this->planet))->toBeTrue();
});