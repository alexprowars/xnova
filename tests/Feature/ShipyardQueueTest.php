<?php

use App\Engine\Enums\QueueType;
use App\Engine\Objects\ObjectsFactory;
use App\Models\Queue;
use Tests\QueueTestCase;

uses(QueueTestCase::class);

beforeEach(function () {
	$this->planet->updateAmount('hangar', 2);
	$this->user->setTech('combustion', 2);
});

test('shipyard charges for the whole batch before construction', function () {
	$this->queue->add(ObjectsFactory::get(202), 3);
	$item = Queue::sole();

	expect($item->type)->toBe(QueueType::SHIPYARD)
		->and($item->object_id)->toBe(202)
		->and($item->level)->toBe(3)
		->and($item->date->equalTo(now()))->toBeTrue()
		->and($item->date_end->equalTo(now()->addSeconds(1920)))->toBeTrue()
		->and($this->planet->fresh()->getLevel(202))->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 994000, 'crystal' => 994000, 'deuterium' => 1000000]);
});

test('shipyard limits a batch to the affordable amount', function (string $resource) {
	$this->planet->update([$resource => 4500]);
	$this->queue->add(ObjectsFactory::get(202), 10);

	expect(Queue::sole()->level)->toBe(2)
		->and($this->planet->fresh()->getAttribute($resource))->toEqual(500);
})->with(['metal', 'crystal']);

test('shipyard rejects nonpositive batch sizes', function (int $count) {
	$this->queue->add(ObjectsFactory::get(202), $count);

	expect(Queue::count())->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 1000000, 'crystal' => 1000000, 'deuterium' => 1000000]);
})->with([0, -1]);

test('shipyard requires enough resources for at least one ship', function () {
	$this->planet->update(['crystal' => 1999]);
	$this->queue->add(ObjectsFactory::get(202), 1);

	expect(Queue::count())->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal']))
		->toEqual(['metal' => 1000000, 'crystal' => 1999]);
});

test('shipyard requires both the building and technology prerequisites', function (int $hangar, int $combustion) {
	$this->planet->updateAmount('hangar', $hangar);
	$this->user->setTech('combustion', $combustion);
	$this->queue->add(ObjectsFactory::get(202), 1);

	expect(Queue::count())->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 1000000, 'crystal' => 1000000, 'deuterium' => 1000000]);
})->with([
	'low shipyard level' => [1, 2],
	'low combustion drive level' => [2, 1],
]);

test('shipyard does not deliver ships before they are ready', function () {
	$this->queue->add(ObjectsFactory::get(202), 3);
	$this->queue->update();

	expect($this->planet->fresh()->getLevel(202))->toBe(0)
		->and(Queue::sole()->level)->toBe(3);
});

test('shipyard delivers only completed ships and preserves progress toward the next ship', function () {
	$this->planet->updateAmount(202, 2);
	$this->queue->add(ObjectsFactory::get(202), 3);
	$startedAt = now()->subSeconds(1920 * 2 + 100);
	Queue::sole()->update(['date' => $startedAt]);

	$this->queue->update();
	$this->queue->update();

	$item = Queue::sole();
	expect($this->planet->fresh()->getLevel(202))->toBe(4)
		->and($item->level)->toBe(1)
		->and($item->date->equalTo($startedAt->addSeconds(1920 * 2)))->toBeTrue()
		->and($item->date_end->equalTo($startedAt->addSeconds(1920 * 3)))->toBeTrue()
		->and($this->planet->fresh()->only(['metal', 'crystal']))
		->toEqual(['metal' => 994000, 'crystal' => 994000]);
});

test('shipyard builds consecutive batches sequentially and carries elapsed time forward', function () {
	$this->queue->add(ObjectsFactory::get(202), 2);
	$this->queue->add(ObjectsFactory::get(204), 2);
	$startedAt = now()->subSeconds(1920 * 3 + 100);
	Queue::query()->update(['date' => $startedAt]);

	$this->queue->update();

	$item = Queue::sole();
	expect($this->planet->fresh()->getLevel(202))->toBe(2)
		->and($this->planet->fresh()->getLevel(204))->toBe(1)
		->and($item->object_id)->toBe(204)
		->and($item->level)->toBe(1)
		->and($item->date->equalTo($startedAt->addSeconds(1920 * 3)))->toBeTrue();
});

test('shipyard completes fleet and defense batches only once', function (int $objectId) {
	$this->queue->add(ObjectsFactory::get($objectId), 3);
	Queue::sole()->update(['date' => now()->subDay()]);

	$this->queue->update();
	$this->queue->update();

	expect($this->planet->fresh()->getLevel($objectId))->toBe(3)
		->and(Queue::count())->toBe(0);
})->with([
	'fleet' => [202],
	'defense' => [401],
]);

test('shield dome limit includes units already queued', function () {
	$this->user->setTech(110, 2);
	$this->queue->add(ObjectsFactory::get(407), 10);
	$this->queue->add(ObjectsFactory::get(407), 1);

	expect(Queue::sole()->level)->toBe(1)
		->and($this->planet->fresh()->only(['metal', 'crystal']))
		->toEqual(['metal' => 990000, 'crystal' => 990000]);
});

test('an existing shield dome prevents ordering another one', function () {
	$this->user->setTech(110, 2);
	$this->planet->updateAmount(407, 1);
	$this->queue->add(ObjectsFactory::get(407), 1);

	expect(Queue::count())->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal']))
		->toEqual(['metal' => 1000000, 'crystal' => 1000000]);
});

test('missile silo capacity includes existing and queued missiles of both types', function () {
	$this->planet->updateAmount('missile_facility', 4);
	$this->user->setTech('impulse_motor', 1);
	$this->planet->updateAmount(502, 5);
	$this->planet->updateAmount(503, 10);
	$this->queue->add(ObjectsFactory::get(502), 5);
	$this->queue->add(ObjectsFactory::get(503), 10);
	$this->queue->add(ObjectsFactory::get(502), 1);

	expect(Queue::orderBy('id')->pluck('level', 'object_id')->all())->toBe([502 => 5, 503 => 5])
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 897500, 'crystal' => 977500, 'deuterium' => 950000]);
});

test('interplanetary missiles cannot use a single remaining silo slot', function () {
	$this->planet->updateAmount('missile_facility', 4);
	$this->user->setTech('impulse_motor', 1);
	$this->planet->updateAmount(502, 39);
	$this->queue->add(ObjectsFactory::get(503), 1);

	expect(Queue::count())->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 1000000, 'crystal' => 1000000, 'deuterium' => 1000000]);
});