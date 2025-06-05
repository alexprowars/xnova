<?php

namespace App\Http\Controllers;

use App\Engine\Building;
use App\Engine\EntityFactory;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\QueueType;
use App\Engine\QueueManager;
use App\Facades\Vars;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ShipyardController extends Controller
{
	protected $mode = 'fleet';

	public function index()
	{
		$queueManager = new QueueManager($this->planet);

		if ($this->mode == 'defense') {
			$elementIds = Vars::getItemsByType(ItemType::DEFENSE);
		} else {
			$elementIds = Vars::getItemsByType(ItemType::FLEET);
		}

		$queueArray = $queueManager->get(QueueType::SHIPYARD);
		$buildArray = $queueArray->pluck('level', 'object_id')->all();

		$viewOnlyAvailable = $this->user->getOption('only_available');

		$items = [];

		foreach ($elementIds as $elementId) {
			$entity = EntityFactory::get($elementId);

			$available = $entity->isAvailable();

			if (!$available && $viewOnlyAvailable) {
				continue;
			}

			if (!Building::checkTechnologyRace($this->user, $elementId)) {
				continue;
			}

			$row = [
				'id' => $elementId,
				'code' => Vars::getName($elementId),
				'available' => $available,
				'price' => $entity->getPrice(),
				'effects' => null,
			];

			if ($available) {
				$row['time'] = $entity->getTime();
				$row['is_max'] = false;

				$price = Vars::getItemPrice($elementId);

				if (isset($price['max'])) {
					$total = $this->planet->getLevel($elementId);

					if (isset($buildArray[$elementId])) {
						$total += $buildArray[$elementId];
					}

					if ($total >= $price['max']) {
						$row['is_max'] = true;
					}
				}

				$row['max'] = isset($price['max']) ? (int) $price['max'] : 0;
				$row['effects'] = Building::getNextProduction($elementId, 0, $this->planet);
			} else {
				$row['requirements'] = Building::getTechTree($elementId, $this->user, $this->planet);
			}

			$items[] = $row;
		}

		return $items;
	}

	public function queue(Request $request)
	{
		if ($this->mode == 'defense') {
			$elementIds = Vars::getItemsByType(ItemType::DEFENSE);
		} else {
			$elementIds = Vars::getItemsByType(ItemType::FLEET);
		}

		$queueManager = new QueueManager($this->planet);

		$elements = Arr::wrap($request->post('element', []));

		foreach ($elements as $element => $count) {
			$element 	= (int) $element;
			$count 		= abs((int) $count);

			if (!in_array($element, $elementIds)) {
				continue;
			}

			$queueManager->add($element, $count);
		}
	}
}
