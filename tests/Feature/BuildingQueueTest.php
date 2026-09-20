<?php

use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\ObjectsFactory;
use App\Models\Queue;
use Illuminate\Support\Facades\Notification;
use Tests\QueueTestCase;

uses(QueueTestCase::class);

test('building starts immediately and charges resources only for the active level', function () {
	$this->queue->add(ObjectsFactory::get(1));
	$this->queue->add(ObjectsFactory::get(1));

	$items = $this->queue->get(QueueType::BUILDING)->values();

	expect($items)->toHaveCount(2)
		->and($items[0]->level)->toBe(1)
		->and($items[0]->operation)->toBe(QueueConstructionType::BUILDING)
		->and($items[0]->date->equalTo(now()))->toBeTrue()
		->and($items[0]->date_end->equalTo(now()->addSeconds(108)))->toBeTrue()
		->and($items[1]->level)->toBe(2)
		->and($items[1]->date)->toBeNull()
		->and($items[1]->date_end)->toBeNull()
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 999940, 'crystal' => 999985, 'deuterium' => 1000000]);
});

test('building does not finish before its construction time', function () {
	$this->queue->add(ObjectsFactory::get(1));
	$this->queue->update();

	expect($this->planet->fresh()->getLevel(1))->toBe(0)
		->and(Queue::count())->toBe(1);
});

test('completed building starts the next level at the previous completion time', function () {
	$this->queue->add(ObjectsFactory::get(1));
	$this->queue->add(ObjectsFactory::get(1));
	$first = $this->queue->get(QueueType::BUILDING)->first();
	$startedAt = now()->subSeconds(120);
	$first->update(['date' => $startedAt]);

	$this->queue->update();

	$next = Queue::sole();

	expect($this->planet->fresh()->getLevel(1))->toBe(1)
		->and($next->level)->toBe(2)
		->and($next->date->equalTo($startedAt->addSeconds(108)))->toBeTrue()
		->and($next->date_end->equalTo($next->date->addSeconds(161)))->toBeTrue()
		->and($this->planet->fresh()->only(['metal', 'crystal']))
		->toEqual(['metal' => 999850, 'crystal' => 999963]);
});

test('one update completes multiple overdue building levels without completing them twice', function () {
	$this->queue->add(ObjectsFactory::get(1));
	$this->queue->add(ObjectsFactory::get(1));
	$this->queue->get(QueueType::BUILDING)->first()->update(['date' => now()->subHour()]);

	$this->queue->update();
	$this->queue->update();

	expect($this->planet->fresh()->getLevel(1))->toBe(2)
		->and(Queue::count())->toBe(0);
});

test('cancelling an active building refunds it only once', function () {
	$object = ObjectsFactory::get(1);
	$this->queue->add($object);
	$id = Queue::sole()->id;

	$this->queue->delete($object, $id);
	$this->queue->delete($object, $id);

	expect(Queue::count())->toBe(0)
		->and($this->planet->fresh()->getLevel(1))->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 1000000, 'crystal' => 1000000, 'deuterium' => 1000000]);
});

test('cancelling a waiting building adjusts later levels without refunding unpaid resources', function () {
	$object = ObjectsFactory::get(1);
	$this->queue->add($object);
	$this->queue->add($object);
	$this->queue->add($object);
	$items = $this->queue->get(QueueType::BUILDING)->values();

	$this->queue->delete($object, $items[1]->id);

	expect(Queue::orderBy('id')->pluck('level')->all())->toBe([1, 2])
		->and($items[0]->fresh()->date)->not->toBeNull()
		->and($items[2]->fresh()->date)->toBeNull()
		->and($this->planet->fresh()->only(['metal', 'crystal']))
		->toEqual(['metal' => 999940, 'crystal' => 999985]);
});

test('cancelling the active building starts the next item with its corrected level', function () {
	$object = ObjectsFactory::get(1);
	$this->queue->add($object);
	$this->queue->add($object);
	$id = $this->queue->get(QueueType::BUILDING)->first()->id;

	$this->queue->delete($object, $id);

	expect(Queue::sole()->level)->toBe(1)
		->and(Queue::sole()->date)->not->toBeNull()
		->and($this->planet->fresh()->only(['metal', 'crystal']))
		->toEqual(['metal' => 999940, 'crystal' => 999985]);
});

test('building queue reserves the remaining planet fields', function () {
	$this->planet->update(['field_max' => 1]);
	$this->queue->add(ObjectsFactory::get(1));
	$this->queue->add(ObjectsFactory::get(2));

	expect(Queue::count())->toBe(1)
		->and(Queue::sole()->object_id)->toBe(1);
});

test('unavailable or unaffordable buildings do not consume resources', function (int $objectId, int $metal) {
	Notification::fake();
	$this->planet->update(['metal' => $metal]);

	$this->queue->add(ObjectsFactory::get($objectId));

	expect(Queue::count())->toBe(0)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => $metal, 'crystal' => 1000000, 'deuterium' => 1000000]);
})->with([
	'insufficient metal' => [1, 59],
	'missing robot factory' => [21, 1000000],
]);

test('demolition is allowed on a full planet and removes one level', function () {
	$this->planet->updateAmount(1, 1);
	$this->planet->update(['field_current' => 1, 'field_max' => 1]);
	$this->queue->add(ObjectsFactory::get(1), 1, true);
	$item = Queue::sole();

	expect($item->operation)->toBe(QueueConstructionType::DESTROY)
		->and($item->date_end->equalTo(now()->addSeconds(81)))->toBeTrue()
		->and($this->planet->fresh()->only(['metal', 'crystal']))
		->toEqual(['metal' => 999955, 'crystal' => 999989]);

	$item->update(['date' => now()->subMinutes(2)]);
	$this->queue->update();

	expect($this->planet->fresh()->getLevel(1))->toBe(0)
		->and(Queue::count())->toBe(0);
});

test('cancelling demolition refunds its demolition price', function () {
	$this->planet->updateAmount(1, 1);
	$object = ObjectsFactory::get(1);
	$this->queue->add($object, 1, true);
	$this->queue->delete($object, Queue::sole()->id);

	expect($this->planet->fresh()->getLevel(1))->toBe(1)
		->and($this->planet->fresh()->only(['metal', 'crystal', 'deuterium']))
		->toEqual(['metal' => 1000000, 'crystal' => 1000000, 'deuterium' => 1000000])
		->and(Queue::count())->toBe(0);
});

test('protected buildings cannot be demolished', function (int $objectId) {
	$this->planet->updateAmount($objectId, 1);
	$this->queue->add(ObjectsFactory::get($objectId), 1, true);

	expect(Queue::count())->toBe(0)
		->and($this->planet->fresh()->getLevel($objectId))->toBe(1);
})->with([33, 41]);