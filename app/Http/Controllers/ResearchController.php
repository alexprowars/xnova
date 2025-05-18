<?php

namespace App\Http\Controllers;

use App\Engine\Building;
use App\Engine\EntityFactory;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\QueueType;
use App\Engine\QueueManager;
use App\Facades\Vars;
use App\Models\Planet;
use App\Models\Queue as QueueModel;
use Illuminate\Http\Request;

class ResearchController extends Controller
{
	public function index()
	{
		$labInQueue = true;

		if (!Building::checkLabSettingsInQueue($this->planet)) {
			session()->flash('error-static', __('buildings.labo_on_update'));

			$labInQueue = false;
		}

		$techHandle = QueueModel::query()
			->whereBelongsTo($this->user)
			->where('type', QueueType::RESEARCH)
			->first();

		$viewOnlyAvailable = $this->user->getOption('only_available');

		$items = [];

		foreach (Vars::getItemsByType(ItemType::TECH) as $elementId) {
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
				'code' => Vars::getName($elementId),
				'available' => $available && $labInQueue,
				'max' => $price['max'] ?? 0,
				'price' => $entity->getPrice(),
				'build' => false,
				'effects' => '',
			];

			if ($available) {
				if ($elementId >= 120 && $elementId <= 122) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon damage" title="Атака"></span><span class="positive">' . (5 * $entity->getLevel()) . '%</span></div>';
				} elseif ($elementId == 115) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon speed" title="Скорость"></span><span class="positive">' . (10 * $entity->getLevel()) . '%</span></div>';
				} elseif ($elementId == 117) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon speed" title="Скорость"></span><span class="positive">' . (20 * $entity->getLevel()) . '%</span></div>';
				} elseif ($elementId == 118) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon speed" title="Скорость"></span><span class="positive">' . (30 * $entity->getLevel()) . '%</span></div>';
				} elseif ($elementId == 108) {
					$row['effects'] = '<div class="tech-effects-row">+' . ($entity->getLevel() + 1) . ' слотов флота</div>';
				} elseif ($elementId == 109) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon damage" title="Атака"></span><span class="positive">' . (5 * $entity->getLevel()) . '%</span></div>';
				} elseif ($elementId == 110) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon shield" title="Щиты"></span><span class="positive">' . (3 * $entity->getLevel()) . '%</span></div>';
				} elseif ($elementId == 111) {
					$row['effects'] = '<div class="tech-effects-row"><span class="icon armor" title="Броня"></span><span class="positive">' . (5 * $entity->getLevel()) . '%</span></div>';
				} elseif ($elementId == 123) {
					$row['effects'] = '<div class="tech-effects-row">+' . $entity->getLevel() . '% лабораторий</div>';
				} elseif ($elementId == 113) {
					$row['effects'] = '<div class="tech-effects-row"><span class="sprite skin_s_energy" title="Энергия"></span><span class="positive">' . ($entity->getLevel() * 2) . '%</span></div>';
				}

				$row['time'] = $entity->getTime();

				if ($techHandle) {
					if ($techHandle->object_id == $elementId) {
						$row['build'] = [
							'planet_id' => $techHandle->planet_id,
							'item' => $techHandle->object_id,
							'name' => null,
							'level' => $techHandle->level,
							'date' => $techHandle->date->addSeconds($row['time'])->utc()->toAtomString(),
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

			$items[] = $row;
		}

		return $items;
	}

	public function action(Request $request, string $action)
	{
		if (!Building::checkLabSettingsInQueue($this->planet)) {
			return;
		}

		$elementId = (int) $request->post('element', 0);

		if (!$elementId || !in_array($elementId, Vars::getItemsByType(ItemType::TECH))) {
			return;
		}

		$queueManager = new QueueManager($this->user, $this->planet);

		switch ($action) {
			case 'cancel':
				$queueManager->delete($elementId);
				break;
			case 'search':
				$queueManager->add($elementId);
				break;
		}
	}
}
