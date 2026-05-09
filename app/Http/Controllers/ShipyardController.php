<?php

namespace App\Http\Controllers;

use App\Engine\Building;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\ObjectsFactory;
use App\Engine\QueueManager;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Facades\Vars;
use App\Support\ToastType;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class ShipyardController extends Controller
{
	protected string $mode = 'fleet';

	public function index()
	{
		$elements = Vars::getObjectsByType(ItemType::FLEET);

		return Inertia::render('Shipyard', [
			'items' => $this->getItems($elements),
		]);
	}

	public function queue(Request $request): void
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

			$object = ObjectsFactory::get($element);

			$queueManager->add($object, $count);
		}
	}

	protected function getItems(array $elements): array
	{
		$queueManager = new QueueManager($this->planet);

		$queueArray = $queueManager->get(QueueType::SHIPYARD);
		$buildArray = $queueArray->pluck('level', 'object_id')->all();

		$viewOnlyAvailable = $this->user->getOption('only_available');

		$items = [];

		foreach ($elements as $element) {
			$entity = $this->planet->getEntityUnit($element->getId());

			$available = $entity->isAvailable();

			if (!$available && $viewOnlyAvailable) {
				continue;
			}

			if (!Building::checkTechnologyRace($this->user, $element->getId())) {
				continue;
			}

			$row = [
				'id' => $element->getId(),
				'name' => $element->getName(),
				'code' => $element->getCode(),
				'available' => $available,
				'price' => $entity->getPrice(),
				'effects' => null,
			];

			if ($available) {
				$row['time'] = $entity->getTime();
				$row['is_max'] = false;

				$maxConstructable = $element->getMaxConstructable();

				if ($maxConstructable) {
					$total = $this->planet->getLevel($element->getId());

					if (isset($buildArray[$element->getId()])) {
						$total += $buildArray[$element->getId()];
					}

					if ($total >= $maxConstructable) {
						$row['is_max'] = true;
					}
				}

				$row['max'] = $maxConstructable ?: 0;
				$row['effects'] = Building::getNextProduction($element, 0, $this->planet)?->toArray() ?? [];
			} else {
				$row['requirements'] = Building::getTechTree($element, $this->user, $this->planet);
			}

			$items[] = $row;
		}

		return $items;
	}
}
