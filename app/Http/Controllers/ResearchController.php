<?php

namespace App\Http\Controllers;

use App\Engine\Building;
use App\Engine\EntityFactory;
use App\Engine\Queue;
use App\Engine\Vars;
use App\Exceptions\PageException;
use App\Models\Planet;
use App\Models\Queue as QueueModel;
use Illuminate\Http\Request;

class ResearchController extends Controller
{
	public function index()
	{
		if ($this->user->isVacation()) {
			throw new PageException('Нет доступа!');
		}

		$labInQueue = true;

		if (!Building::checkLabSettingsInQueue($this->planet)) {
			session()->flash('error-static', __('buildings.labo_on_update'));

			$labInQueue = false;
		}

		if ($this->user->getTechLevel('intergalactic') > 0) {
			$this->planet->spaceLabs = $this->planet->getNetworkLevel();
		}

		$techHandle = QueueModel::query()
			->where('user_id', $this->user->id)
			->where('type', QueueModel::TYPE_TECH)
			->first();

		$viewOnlyAvailable = $this->user->getOption('only_available');

		$parse['items'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_TECH) as $elementId) {
			$entity = EntityFactory::get($elementId, $this->user->getTechLevel($elementId), $this->planet);

			$available = $entity->isAvailable();

			if (!$available && $viewOnlyAvailable) {
				continue;
			}

			if (!Building::checkTechnologyRace($this->user, $elementId)) {
				continue;
			}

			$price = Vars::getItemPrice($elementId);

			$row = [
				'id' => $elementId,
				'available' => $available && $labInQueue,
				'level' => $this->user->getTechLevel($elementId),
				'max' => $price['max'] ?? 0,
				'price' => $entity->getPrice(),
				'build' => false,
				'effects' => '',
			];

			if ($available) {
				if ($elementId >= 120 && $elementId <= 122) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon damage" title="Атака"></span><span class="positive">' . (5 * $row['level']) . '%</span></div>';
				} elseif ($elementId == 115) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon speed" title="Скорость"></span><span class="positive">' . (10 * $row['level']) . '%</span></div>';
				} elseif ($elementId == 117) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon speed" title="Скорость"></span><span class="positive">' . (20 * $row['level']) . '%</span></div>';
				} elseif ($elementId == 118) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon speed" title="Скорость"></span><span class="positive">' . (30 * $row['level']) . '%</span></div>';
				} elseif ($elementId == 108) {
					$row['effects'] = '<div class="tech-effects-row">+' . ($row['level'] + 1) . ' слотов флота</div>';
				} elseif ($elementId == 109) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon damage" title="Атака"></span><span class="positive">' . (5 * $row['level']) . '%</span></div>';
				} elseif ($elementId == 110) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon shield" title="Щиты"></span><span class="positive">' . (3 * $row['level']) . '%</span></div>';
				} elseif ($elementId == 111) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon armor" title="Броня"></span><span class="positive">' . (5 * $row['level']) . '%</span></div>';
				} elseif ($elementId == 123) {
					$row['effects'] = '<div class="tech-effects-row">+' . $row['level'] . '% лабораторий</div>';
				} elseif ($elementId == 113) {
					$row['effects'] = '<div class="tech-effects-row"><span class="sprite skin_s_energy" title="Энергия"></span><span class="positive">' . ($row['level'] * 2) . '%</span></div>';
				}

				$row['time'] = $entity->getTime();

				if ($techHandle) {
					if ($techHandle->object_id == $elementId) {
						$row['build'] = [
							'id' => $techHandle->planet_id,
							'name' => null,
							'time' => $techHandle->time->addSeconds($row['time'])->utc()->toAtomString(),
						];

						if ($techHandle->planet_id != $this->planet->id) {
							$planet = Planet::select(['name'])
								->where('id', $techHandle->planet_id)
								->first();

							if ($planet) {
								$row['build']['planet'] = $planet->name;
							}
						}
					} else {
						$row['build'] = true;
					}
				}
			} else {
				$row['requirements'] = Building::getTechTree($elementId, $this->user, $this->planet);
			}

			$parse['items'][] = $row;
		}

		return response()->state($parse);
	}

	public function action(Request $request, string $action)
	{
		if (!Building::checkLabSettingsInQueue($this->planet)) {
			return;
		}

		$elementId = (int) $request->post('element', 0);

		if (!$elementId || !in_array($elementId, Vars::getItemsByType(Vars::ITEM_TYPE_TECH))) {
			return;
		}

		$queueManager = new Queue($this->user, $this->planet);

		switch ($action) {
			case 'cancel':
				if ($queueManager->getCount(Queue::TYPE_RESEARCH)) {
					$queueManager->delete($elementId);
				}
				break;
			case 'search':
				if (!$queueManager->getCount(Queue::TYPE_RESEARCH)) {
					$queueManager->add($elementId);
				}
				break;
		}
	}
}
