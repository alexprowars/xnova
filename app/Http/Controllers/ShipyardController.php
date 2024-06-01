<?php

namespace App\Http\Controllers;

use App\Building;
use App\Engine\EntityFactory;
use App\Queue;
use App\Vars;
use App\Construction;
use App\Controller;
use App\Exceptions\PageException;
use Illuminate\Http\Request;

class ShipyardController extends Controller
{
	protected $mode = 'fleet';

	public function index()
	{
		if ($this->user->isVacation()) {
			throw new PageException('Нет доступа!');
		}

		$queueManager = new Queue($this->user, $this->planet);

		if ($this->mode == 'defense') {
			$elementIds = Vars::getItemsByType(Vars::ITEM_TYPE_DEFENSE);
		} else {
			$elementIds = Vars::getItemsByType(Vars::ITEM_TYPE_FLEET);
		}

		$queueArray = $queueManager->get($queueManager::TYPE_SHIPYARD);

		$buildArray = [];

		if (is_array($queueArray) && count($queueArray)) {
			foreach ($queueArray as $element) {
				$buildArray[$element->object_id] = $element->level;
			}
		}

		$viewOnlyAvailable = $this->user->getOption('only_available');

		$parse = [];
		$parse['items'] = [];

		foreach ($elementIds as $element) {
			$entity = EntityFactory::get($element);

			$available = $entity->isAvailable();

			if (!$available && $viewOnlyAvailable) {
				continue;
			}

			if (!Building::checkTechnologyRace($this->user, $element)) {
				continue;
			}

			$row = [];

			$row['available']	= $available;
			$row['id'] 		= $element;
			$row['count'] 	= $this->planet->getLevel($element);
			$row['price'] 	= $entity->getPrice();
			$row['effects']	= null;

			if ($available) {
				$row['time'] = $entity->getTime();
				$row['is_max'] = false;

				$price = Vars::getItemPrice($element);

				if (isset($price['max'])) {
					$total = $this->planet->getLevel($element);

					if (isset($buildArray[$element])) {
						$total += $buildArray[$element];
					}

					if ($total >= $price['max']) {
						$row['is_max'] = true;
					}
				}

				$row['max'] = isset($price['max']) ? (int) $price['max'] : 0;
				$row['effects'] = Building::getNextProduction($element, 0, $this->planet);
			} else {
				$row['requirements'] = Building::getTechTree($element, $this->user, $this->planet);
			}

			$parse['items'][] = $row;
		}

		$parse['queue'] = Construction::queueList($this->user, $this->planet);

		return response()->state($parse);
	}

	public function queue(Request $request)
	{
		if ($this->mode == 'defense') {
			$elementIds = Vars::getItemsByType(Vars::ITEM_TYPE_DEFENSE);
		} else {
			$elementIds = Vars::getItemsByType(Vars::ITEM_TYPE_FLEET);
		}

		$queueManager = new Queue($this->user, $this->planet);

		foreach ($request->post('element', []) as $element => $count) {
			$element 	= (int) $element;
			$count 		= abs((int) $count);

			if (!in_array($element, $elementIds)) {
				continue;
			}

			$queueManager->add($element, $count);
		}
	}
}
