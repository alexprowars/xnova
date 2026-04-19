<?php

namespace App\Http\Controllers;

use App\Engine\Building;
use App\Engine\Entity;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\BuildingObject;
use App\Engine\Objects\ObjectsFactory;
use App\Engine\QueueManager;
use App\Exceptions\Exception;
use App\Facades\Vars;
use Illuminate\Http\Request;

class BuildingsController extends Controller
{
	public function index(): array
	{
		$viewOnlyAvailable = $this->user->getOption('only_available');

		$items = [];

		$elements = Vars::getObjectsByType(ItemType::BUILDING);

		/** @var \App\Engine\Objects\BuildingObject $element */
		foreach ($elements as $element) {
			if (!$element->hasAllowedBuild($this->planet->planet_type) || !Building::checkTechnologyRace($this->user, $element->getId())) {
				continue;
			}

			$entity = $this->planet->getEntityUnit($element->getId());

			if (!($entity instanceof Entity\Building)) {
				continue;
			}

			$available = $entity->isAvailable();

			if (!$available && $viewOnlyAvailable) {
				continue;
			}

			$price = $entity->getPrice();

			$row = [
				'id' => $element->getId(),
				'name' => $element->getName(),
				'code' => $element->getCode(),
				'available' => $available,
				'price' => $price,
			];

			if ($available) {
				if ($element->hasExperience()) {
					$row['exp'] = $entity->getExp();
				}

				$row['time'] = $entity->getTime();
				$row['effects'] = Building::getNextProduction($element, $entity->getLevel(), $this->planet)?->toArray();
			} else {
				$row['requirements'] = Building::getTechTree($element, $this->user, $this->planet);
			}

			$items[] = $row;
		}

		return $items;
	}

	public function build(Request $request, string $action): void
	{
		$elementId = (int) $request->post('element', 0);

		$object = ObjectsFactory::get($elementId);

		if (!($object instanceof BuildingObject)) {
			throw new Exception('Invalid building object');
		}

		if (!$object->hasAllowedBuild($this->planet->planet_type)) {
			throw new Exception('Not allowed');
		}

		$queueManager = new QueueManager($this->planet);

		$maxQueueSize = config('game.maxBuildingQueue') + $this->user->bonus('queue', 0);

		if ($queueManager->getCount(QueueType::BUILDING) >= $maxQueueSize) {
			return;
		}

		switch ($action) {
			case 'insert':
				$queueManager->add($object);
				break;
			case 'destroy':
				$queueManager->add($object, 1, true);
				break;
		}
	}

	public function queue(Request $request, string $action): void
	{
		$index = (int) $request->post('index', 0);

		if (!$index) {
			return;
		}

		$queueManager = new QueueManager($this->planet);

		switch ($action) {
			case 'cancel':
				$queueManager->delete(ObjectsFactory::get(1));
				break;
			case 'remove':
				$queueManager->delete(ObjectsFactory::get(1), $index);
				break;
		}
	}
}
