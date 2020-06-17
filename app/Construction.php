<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova;

use Illuminate\Support\Facades\Request;
use Xnova\Exceptions\RedirectException;
use Xnova\Models;
use Xnova\Planet\EntityFactory;

class Construction
{
	/** @var User */
	private $user;
	/** @var Planet */
	private $planet;

	public function __construct(User $user, Planet $planet)
	{
		$this->user = $user;
		$this->planet = $planet;
	}

	public function pageBuilding()
	{
		$parse = [];

		$Queue = $this->ShowBuildingQueue();

		$MaxBuidSize = config('settings.maxBuildingQueue') + $this->user->bonusValue('queue', 0);

		$CanBuildElement = ($Queue['lenght'] < $MaxBuidSize);

		if (Request::instance()->isMethod('post')) {
			$Command = Request::post('cmd', '');
			$elementId = (int) Request::post('building', 0);
			$ListID = (int) Request::post('listid', 0);

			if (in_array($elementId, Vars::getAllowedBuilds($this->planet->planet_type)) || ($ListID != 0 && ($Command == 'cancel' || $Command == 'remove'))) {
				$queueManager = new Queue($this->user, $this->planet);

				switch ($Command) {
					case 'cancel':
						$queueManager->delete(1, 0);
						break;
					case 'remove':
						$queueManager->delete(1, $ListID);
						break;
					case 'insert':
						if ($CanBuildElement) {
							$queueManager->add($elementId);
						}

						break;
					case 'destroy':
						if ($CanBuildElement) {
							$queueManager->add($elementId, 1, true);
						}

						break;
				}

				throw new RedirectException('', 'buildings/');
			}
		}

		$viewOnlyAvailable = $this->user->getUserOption('only_available');

		$parse['items'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_BUILING) as $elementId) {
			if (
				!in_array($elementId, Vars::getAllowedBuilds($this->planet->planet_type))
				|| !Building::checkTechnologyRace($this->user, $elementId)
			) {
				continue;
			}

			$entity = $this->planet->getEntity($elementId);

			$isAvailable = $entity->isAvailable();

			if (!$isAvailable && $viewOnlyAvailable) {
				continue;
			}

			$price = $entity->getPrice();

			$row = [];

			$row['allow'] = $isAvailable;
			$row['i'] = $elementId;
			$row['level'] = $entity->getLevel();
			$row['price'] = $price;

			if ($isAvailable) {
				if (in_array($elementId, Vars::getItemsByType('build_exp'))) {
					$row['exp'] = floor(($price['metal'] + $price['crystal'] + $price['deuterium']) / config('settings.buildings_exp_mult', 1000));
				}

				$row['time'] = $entity->getTime();
				$row['effects'] = Building::getNextProduction($elementId, $entity->getLevel(), $this->planet);
			} else {
				$row['requirements'] = Building::getTechTree($elementId, $this->user, $this->planet);
			}

			$parse['items'][] = $row;
		}

		$parse['queue'] 			= $Queue['buildlist'];
		$parse['queue_max'] 		= $MaxBuidSize;
		$parse['planet'] 			= 'normaltemp';

		preg_match('/(.*?)planet/', $this->planet->image, $match);

		if (isset($match[1])) {
			$parse['planet'] = trim($match[1]);
		}

		return $parse;
	}

	public function pageResearch()
	{
		$bContinue = true;

		if (!Building::checkLabSettingsInQueue($this->planet)) {
			session()->flash('error-static', __('buildings.labo_on_update'));

			$bContinue = false;
		}

		$spaceLabs = [];

		if ($this->user->getTechLevel('intergalactic') > 0) {
			$spaceLabs = $this->planet->getNetworkLevel();
		}

		$this->planet->spaceLabs = $spaceLabs;

		$res_array = Vars::getItemsByType(Vars::ITEM_TYPE_TECH);

		$techHandle = Models\Queue::query()
			->where('user_id', $this->user->id)
			->where('type', Models\Queue::TYPE_TECH)
			->first();

		if (Request::post('cmd') && $bContinue != false) {
			$queueManager = new Queue($this->user, $this->planet);

			$command = Request::post('cmd', '');
			$techId = (int) Request::post('tech', 0);

			if ($techId > 0 && in_array($techId, $res_array)) {
				switch ($command) {
					case 'cancel':
						if ($queueManager->getCount(Queue::TYPE_RESEARCH)) {
							$queueManager->delete($techId);
						}

						break;

					case 'search':
						if (!$queueManager->getCount(Queue::TYPE_RESEARCH)) {
							$queueManager->add($techId);
						}

						break;
				}

				throw new RedirectException('', '/research/');
			}
		}

		$viewOnlyAvailable = $this->user->getUserOption('only_available');

		$parse['items'] = [];

		foreach ($res_array as $elementId) {
			$entity = EntityFactory::create($elementId, $this->user->getTechLevel($elementId), $this->planet);

			$isAvailable = $entity->isAvailable();

			if (!$isAvailable && $viewOnlyAvailable) {
				continue;
			}

			if (!Building::checkTechnologyRace($this->user, $elementId)) {
				continue;
			}

			$price = Vars::getItemPrice($elementId);

			$row = [
				'i' => $elementId,
				'allow' => $isAvailable && $bContinue,
				'level' => $this->user->getTechLevel($elementId),
				'max' => $price['max'] ?? 0,
				'price' => $entity->getPrice(),
				'build' => false,
				'effects' => '',
			];

			if ($isAvailable) {
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
							'id' => (int) $techHandle->planet_id,
							'name' => '',
							'time' => $techHandle->time + $row['time']
						];

						if ($techHandle->planet_id != $this->planet->id) {
							$planet = Models\Planet::query()
								->select(['id', 'name'])
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

		return $parse;
	}

	public function pageShipyard($mode = 'fleet')
	{
		$queueManager = new Queue($this->user, $this->planet);

		if ($mode == 'defense') {
			$elementIDs = Vars::getItemsByType(Vars::ITEM_TYPE_DEFENSE);
		} else {
			$elementIDs = Vars::getItemsByType(Vars::ITEM_TYPE_FLEET);
		}

		if (Request::post('fmenge')) {
			foreach (Request::post('fmenge', []) as $element => $count) {
				$element 	= (int) $element;
				$count 		= abs((int) $count);

				if (!in_array($element, $elementIDs)) {
					continue;
				}

				$queueManager->add($element, $count);
			}

			$this->planet->queue = $queueManager->get();
		}

		$queueArray = $queueManager->get($queueManager::TYPE_SHIPYARD);

		$BuildArray = $this->extractHangarQueue($queueArray);

		$viewOnlyAvailable = $this->user->getUserOption('only_available');

		$parse = [];
		$parse['items'] = [];

		foreach ($elementIDs as $element) {
			$entity = EntityFactory::create($element);

			$isAccess = $entity->isAvailable();

			if (!$isAccess && $viewOnlyAvailable) {
				continue;
			}

			if (!Building::checkTechnologyRace($this->user, $element)) {
				continue;
			}

			$row = [];

			$row['allow']	= $isAccess;
			$row['i'] 		= $element;
			$row['count'] 	= $this->planet->getLevel($element);
			$row['price'] 	= $entity->getPrice();
			$row['effects']	= null;

			if ($isAccess) {
				$row['time'] = $entity->getTime();
				$row['is_max'] = false;

				$price = Vars::getItemPrice($element);

				if (isset($price['max'])) {
					$total = $this->planet->getLevel($element);

					if (isset($BuildArray[$element])) {
						$total += $BuildArray[$element];
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

		return $parse;
	}

	private function extractHangarQueue($queue = '')
	{
		$result = [];

		if (is_array($queue) && count($queue)) {
			foreach ($queue as $element) {
				$result[$element->object_id] = $element->level;
			}
		}

		return $result;
	}

	private function ShowBuildingQueue()
	{
		$queueManager = new Queue($this->user, $this->planet);

		$queueItems = $queueManager->get($queueManager::TYPE_BUILDING);

		$listRow = [];

		if (count($queueItems)) {
			$end = 0;

			foreach ($queueItems as $item) {
				if (!$end) {
					$end = $item->time;
				}

				$entity = EntityFactory::create(
					$item->object_id,
					$item->level - ($item->operation == $item::OPERATION_BUILD ? 1 : 0),
					$this->planet
				);

				$elementTime = $entity->getTime();

				if ($item->operation == $item::OPERATION_DESTROY) {
					$elementTime = ceil($elementTime / 2);
				}

				if ($item->time > 0 && $item->time_end - $item->time != $elementTime) {
					$item->update([
						'time_end' => $item->time + $elementTime
					]);
				}

				$end += $elementTime;

				$listRow[] = [
					'name' 	=> __('main.tech.' . $item->object_id),
					'level' => $item->level,
					'mode' 	=> $item->operation == $item::OPERATION_DESTROY,
					'time' 	=> $end - time(),
					'end' 	=> $end
				];
			}
		}

		$RetValue['lenght'] 	= count($listRow);
		$RetValue['buildlist'] 	= $listRow;

		return $RetValue;
	}

	public function ElementBuildListBox()
	{
		$queueManager = new Queue($this->user, $this->planet);

		$queueItems = $queueManager->get($queueManager::TYPE_SHIPYARD);

		$data = [];

		if (count($queueItems)) {
			$end = 0;

			foreach ($queueItems as $item) {
				if (!$end) {
					$end = $item->time;
				}

				$entity = EntityFactory::create($item->object_id);

				$time = $entity->getTime();

				$end += $time * $item->level;

				$row = [
					'i'		=> (int) $item->object_id,
					'name'	=> __('main.tech.' . $item->object_id),
					'count'	=> (int) $item->level,
					'time'	=> $time,
					'end'	=> $end
				];

				$data[] = $row;
			}
		}

		return $data;
	}
}
