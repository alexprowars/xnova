<?php

namespace App\Http\Controllers;

use App\Building;
use App\Construction;
use App\Controller;
use App\Exceptions\PageException;
use App\Queue;
use App\Vars;
use Illuminate\Http\Request;

class BuildingsController extends Controller
{
	public function index()
	{
		if ($this->user->isVacation()) {
			throw new PageException('Нет доступа!');
		}

		$viewOnlyAvailable = $this->user->getOption('only_available');

		$parse = [];
		$parse['items'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_BUILING) as $elementId) {
			if (
				!in_array($elementId, Vars::getAllowedBuilds($this->planet->planet_type))
				|| !Building::checkTechnologyRace($this->user, $elementId)
			) {
				continue;
			}

			$entity = $this->planet->getEntity($elementId);

			$available = $entity->isAvailable();

			if (!$available && $viewOnlyAvailable) {
				continue;
			}

			$price = $entity->getPrice();

			$row = [];
			$row['id'] = $elementId;
			$row['available'] = $available;
			$row['level'] = $entity->getLevel();
			$row['price'] = $price;

			if ($available) {
				if (in_array($elementId, Vars::getItemsByType('build_exp'))) {
					$row['exp'] = floor(($price['metal'] + $price['crystal'] + $price['deuterium']) / config('settings.buildings_exp_mult', 1000));
				}

				$row['time'] = $entity->getTime();
				$row['effects'] = Building::getNextProduction($elementId, $entity->getLevel(), $this->planet)?->toArray();
			} else {
				$row['requirements'] = Building::getTechTree($elementId, $this->user, $this->planet);
			}

			$parse['items'][] = $row;
		}

		$parse['queue'] 	= Construction::showBuildingQueue($this->user, $this->planet);
		$parse['queue_max'] = config('settings.maxBuildingQueue') + $this->user->bonusValue('queue', 0);
		$parse['planet'] 	= 'normaltemp';

		preg_match('/(.*?)planet/', $this->planet->image, $match);

		if (isset($match[1])) {
			$parse['planet'] = trim($match[1]);
		}

		return response()->state($parse);
	}

	public function build(Request $request, string $action)
	{
		$elementId = (int) $request->post('element', 0);

		if (!in_array($elementId, Vars::getAllowedBuilds($this->planet->planet_type))) {
			return;
		}

		$queueManager = new Queue($this->user, $this->planet);

		$maxQueueSize = config('settings.maxBuildingQueue') + $this->user->bonusValue('queue', 0);

		if ($queueManager->getCount($queueManager::TYPE_BUILDING) >= $maxQueueSize) {
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

		$queueManager = new Queue($this->user, $this->planet);

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
