<?php

namespace App\Http\Controllers;

use App\Engine\Building;
use App\Engine\Entity;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\QueueType;
use App\Engine\QueueManager;
use App\Facades\Vars;
use Illuminate\Http\Request;

class BuildingsController extends Controller
{
	public function index()
	{
		$viewOnlyAvailable = $this->user->getOption('only_available');

		$items = [];

		foreach (Vars::getItemsByType(ItemType::BUILDING) as $elementId) {
			if (
				!in_array($elementId, Vars::getAllowedBuilds($this->planet->planet_type))
				|| !Building::checkTechnologyRace($this->user, $elementId)
			) {
				continue;
			}

			$entity = $this->planet->getEntity($elementId)->unit();

			if (!($entity instanceof Entity\Building)) {
				continue;
			}

			$available = $entity->isAvailable();

			if (!$available && $viewOnlyAvailable) {
				continue;
			}

			$price = $entity->getPrice();

			$row = [
				'id' => $elementId,
				'code' => Vars::getName($elementId),
				'available' => $available,
				'price' => $price,
			];

			if ($available) {
				if (in_array($elementId, Vars::getItemsByType(ItemType::BUILING_EXP))) {
					$row['exp'] = $entity->getExp();
				}

				$row['time'] = $entity->getTime();
				$row['effects'] = Building::getNextProduction($elementId, $entity->getLevel(), $this->planet)?->toArray();
			} else {
				$row['requirements'] = Building::getTechTree($elementId, $this->user, $this->planet);
			}

			$items[] = $row;
		}

		return $items;
	}

	public function build(Request $request, string $action)
	{
		$elementId = (int) $request->post('element', 0);

		if (!in_array($elementId, Vars::getAllowedBuilds($this->planet->planet_type))) {
			return;
		}

		$queueManager = new QueueManager($this->planet);

		$maxQueueSize = config('game.maxBuildingQueue') + $this->user->bonus('queue', 0);

		if ($queueManager->getCount(QueueType::BUILDING) >= $maxQueueSize) {
			return;
		}

		switch ($action) {
			case 'insert':
				$queueManager->add($elementId);
				break;
			case 'destroy':
				$queueManager->add($elementId, 1, true);
				break;
		}
	}

	public function queue(Request $request, string $action)
	{
		$index = (int) $request->post('index', 0);

		if (!$index) {
			return;
		}

		$queueManager = new QueueManager($this->planet);

		switch ($action) {
			case 'cancel':
				$queueManager->delete(1);
				break;
			case 'remove':
				$queueManager->delete(1, $index);
				break;
		}
	}
}
