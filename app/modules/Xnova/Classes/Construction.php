<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Di;
use Xnova\Models;
use Xnova\Models\Planet;
use Xnova\Models\User as UserModel;

class Construction
{
	/** @var UserModel */
	private $user;
	/** @var Models\Planet */
	private $planet;

	public function __construct (UserModel $user, Planet $planet)
	{
		$this->user = $user;
		$this->planet = $planet;
	}

	public function pageBuilding ()
	{
		$parse = [];

		$request 	= Di::getDefault()->getShared('request');
		$storage 	= Di::getDefault()->getShared('registry');
		$config 	= Di::getDefault()->getShared('config');

		if ($this->planet->id_ally > 0 && $this->planet->id_ally == $this->user->ally_id)
			$storage->reslist['allowed'][5] = [14, 21, 34, 44];

		$Queue = $this->ShowBuildingQueue();

		$MaxBuidSize = $config->game->maxBuildingQueue + $this->user->bonusValue('queue', 0);

		$CanBuildElement = ($Queue['lenght'] < $MaxBuidSize);

		if ($request->hasPost('cmd'))
		{
			$Command 	= $request->getPost('cmd', null, '');
			$Element 	= $request->getPost('building', 'int', 0);
			$ListID 	= (int) $request->getPost('listid', 'int', 0);

			if (in_array($Element, Vars::getAllowedBuilds($this->planet->planet_type)) || ($ListID != 0 && ($Command == 'cancel' || $Command == 'remove')))
			{
				$queueManager = new Queue($this->user, $this->planet);

				switch ($Command)
				{
					case 'cancel':
						$queueManager->delete(1, 0);
						break;
					case 'remove':
						$queueManager->delete(1, $ListID);
						break;
					case 'insert':

						if ($CanBuildElement)
							$queueManager->add($Element);

						break;
					case 'destroy':

						if ($CanBuildElement)
							$queueManager->add($Element, 1, true);

						break;
				}

				return Di::getDefault()->getShared('response')->redirect('buildings/');
			}
		}

		$viewOnlyAvailable = $this->user->getUserOption('only_available');

		$parse['items'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_BUILING) as $Element)
		{
			if (!in_array($Element, Vars::getAllowedBuilds($this->planet->planet_type)))
				continue;

			$isAccess = Building::isTechnologieAccessible($this->user, $this->planet, $Element);

			if (!$isAccess && $viewOnlyAvailable)
				continue;

			if (!Building::checkTechnologyRace($this->user, $Element))
				continue;

			$build = $this->planet->getBuild($Element);

			if (!$build)
				continue;

			$BuildingLevel 		= $build['level'];
			$BuildingPrice 		= Building::getBuildingPrice($this->user, $this->planet, $Element);

			$row = [];

			$row['allow']	= $isAccess;
			$row['i'] 		= $Element;
			$row['level'] 	= $BuildingLevel;
			$row['price'] 	= $BuildingPrice;

			if ($isAccess)
			{
				if (in_array($Element, $storage->reslist['build_exp']))
					$row['exp'] = floor(($BuildingPrice['metal'] + $BuildingPrice['crystal'] + $BuildingPrice['deuterium']) / $config->game->get('buildings_exp_mult', 1000));

				$row['time'] 	= Building::getBuildingTime($this->user, $this->planet, $Element);
				$row['effects'] = Building::getNextProduction($Element, $BuildingLevel, $this->planet);
			}
			else
				$row['need'] = Building::getTechTree($Element, $this->user, $this->planet);

			$parse['items'][] = $row;
		}

		$parse['queue'] 			= $Queue['buildlist'];
		$parse['queue_max'] 		= $MaxBuidSize;
		$parse['fields_current'] 	= (int) $this->planet->field_current;
		$parse['fields_max'] 		= (int) $this->planet->getMaxFields();
		$parse['planet'] 			= 'normaltemp';

		preg_match('/(.*?)planet/', $this->planet->image, $match);

		if (isset($match[1]))
			$parse['planet'] = trim($match[1]);

		return $parse;
	}

	public function pageResearch ()
	{
		$request = $this->user->getDI()->getShared('request');

		$bContinue = true;

		if (!Building::checkLabSettingsInQueue($this->planet))
		{
			$this->user->getDI()->getShared('flashSession')->message('error-static', _getText('labo_on_update'));

			$bContinue = false;
		}

		$spaceLabs = [];

		if ($this->user->getTechLevel('intergalactic') > 0)
			$spaceLabs = $this->planet->getNetworkLevel();

		$this->planet->spaceLabs = $spaceLabs;

		$res_array = Vars::getItemsByType(Vars::ITEM_TYPE_TECH);

		$techHandle = Models\Queue::findFirst([
			'conditions' => 'user_id = :user: AND type = :type:',
			'bind' => [
				'user' => $this->user->id,
				'type' => Models\Queue::TYPE_TECH
			]
		]);

		if ($request->hasPost('cmd') && $bContinue != false)
		{
			$queueManager = new Queue($this->user, $this->planet);

			$command 	= $request->getPost('cmd', 'string', '');
			$techId 	= (int) $request->getPost('tech', 'int', 0);

			if ($techId > 0 && in_array($techId, $res_array))
			{
				switch ($command)
				{
					case 'cancel':

						if ($queueManager->getCount(Queue::TYPE_RESEARCH))
							$queueManager->delete($techId);

						break;

					case 'search':

						if (!$queueManager->getCount(Queue::TYPE_RESEARCH))
							$queueManager->add($techId);

						break;
				}

				Di::getDefault()->getShared('response')->redirect('buildings/research/');
			}
		}

		$viewOnlyAvailable = $this->user->getUserOption('only_available');

		$parse['items'] = [];

		foreach ($res_array AS $Tech)
		{
			$isAccess = Building::isTechnologieAccessible($this->user, $this->planet, $Tech);

			if (!$isAccess && $viewOnlyAvailable)
				continue;

			if (!Building::checkTechnologyRace($this->user, $Tech))
				continue;

			$price = Vars::getItemPrice($Tech);

			$row = [];

			$row['allow'] 	= $isAccess && $bContinue;
			$row['i'] 		= $Tech;
			$row['level']	= $this->user->getTechLevel($Tech);
			$row['max']		= isset($price['max']) ? $price['max'] : 0;
			$row['price'] 	= Building::getBuildingPrice($this->user, $this->planet, $Tech);
			$row['build']	= false;
			$row['effects']	= '';

			if ($isAccess)
			{
				if ($Tech >= 120 && $Tech <= 122)
					$row['effects'] = '<div class="tech-effects-row"><span class="icon damage" title="Атака"></span><span class="positive">'.(5 * $row['level']).'%</span></div>';
				elseif ($Tech == 115)
					$row['effects'] = '<div class="tech-effects-row"><span class="icon speed" title="Скорость"></span><span class="positive">'.(10 * $row['level']).'%</span></div>';
				elseif ($Tech == 117)
					$row['effects'] = '<div class="tech-effects-row"><span class="icon speed" title="Скорость"></span><span class="positive">'.(20 * $row['level']).'%</span></div>';
				elseif ($Tech == 118)
					$row['effects'] = '<div class="tech-effects-row"><span class="icon speed" title="Скорость"></span><span class="positive">'.(30 * $row['level']).'%</span></div>';
				elseif ($Tech == 108)
					$row['effects'] = '<div class="tech-effects-row">+'.($row['level'] + 1).' слотов флота</div>';
				elseif ($Tech == 109)
					$row['effects'] = '<div class="tech-effects-row"><span class="icon damage" title="Атака"></span><span class="positive">'.(5 * $row['level']).'%</span></div>';
				elseif ($Tech == 110)
					$row['effects'] = '<div class="tech-effects-row"><span class="icon shield" title="Щиты"></span><span class="positive">'.(3 * $row['level']).'%</span></div>';
				elseif ($Tech == 111)
					$row['effects'] = '<div class="tech-effects-row"><span class="icon armor" title="Броня"></span><span class="positive">'.(5 * $row['level']).'%</span></div>';
				elseif ($Tech == 123)
					$row['effects'] = '<div class="tech-effects-row">+'.$row['level'].'% лабораторий</div>';
				elseif ($Tech == 113)
					$row['effects'] = '<div class="tech-effects-row"><span class="sprite skin_s_energy" title="Энергия"></span><span class="positive">'.($row['level'] * 2).'%</span></div>';

				$row['time'] = Building::getBuildingTime($this->user, $this->planet, $Tech);

				if ($techHandle)
				{
					if ($techHandle->object_id == $Tech)
					{
						$row['build'] = [
							'id' => (int) $techHandle->planet_id,
							'name' => '',
							'time' => $techHandle->time + $row['time']
						];

						if ($techHandle->planet_id != $this->planet->id)
						{
							$planet = Planet::findFirst([
								'columns' => 'id, name',
								'conditions' => 'id = :id:',
								'bind' => ['id' => $techHandle->planet_id]
							]);

							if ($planet)
								$row['build']['planet'] = $planet->name;
						}
					}
					else
						$row['build'] = true;
				}
			}
			else
				$row['need'] = Building::getTechTree($Tech, $this->user, $this->planet);

			$parse['items'][] = $row;
		}

		return $parse;
	}

	public function pageShipyard ($mode = 'fleet')
	{
		$queueManager = new Queue($this->user, $this->planet);

		if ($mode == 'defense')
			$elementIDs = Vars::getItemsByType(Vars::ITEM_TYPE_DEFENSE);
		else
			$elementIDs = Vars::getItemsByType(Vars::ITEM_TYPE_FLEET);

		$request = Di::getDefault()->getShared('request');

		if ($request->hasPost('fmenge'))
		{
			foreach ($request->getPost('fmenge') as $element => $count)
			{
				$element 	= (int) $element;
				$count 		= abs((int) $count);

				if (!in_array($element, $elementIDs))
					continue;

				$queueManager->add($element, $count);
			}

			$this->planet->queue = $queueManager->get();
		}

		$queueArray = $queueManager->get($queueManager::TYPE_SHIPYARD);

		$BuildArray = $this->extractHangarQueue($queueArray);

		$viewOnlyAvailable = $this->user->getUserOption('only_available');

		$parse = [];
		$parse['items'] = [];

		foreach ($elementIDs AS $element)
		{
			$isAccess = Building::isTechnologieAccessible($this->user, $this->planet, $element);

			if (!$isAccess && $viewOnlyAvailable)
				continue;

			if (!Building::checkTechnologyRace($this->user, $element))
				continue;

			$row = [];

			$row['allow']	= $isAccess;
			$row['i'] 		= $element;
			$row['count'] 	= $this->planet->getUnitCount($element);
			$row['price'] 	= Building::getBuildingPrice($this->user, $this->planet, $element, false);
			$row['effects']	= '';

			if ($isAccess)
			{
				$row['time']	= Building::getBuildingTime($this->user, $this->planet, $element);
				$row['is_max']	= false;

				$price = Vars::getItemPrice($element);

				if (isset($price['max']))
				{
					$total = $this->planet->getUnitCount($element);

					if (isset($BuildArray[$element]))
						$total += $BuildArray[$element];

					if ($total >= $price['max'])
						$row['is_max'] = true;
				}

				$row['max'] = isset($price['max']) ? (int) $price['max'] : 0;
				$row['effects'] = Building::getNextProduction($element, 0, $this->planet);
			}
			else
				$row['need'] = Building::getTechTree($element, $this->user, $this->planet);

			$parse['items'][] = $row;
		}

		return $parse;
	}

	private function extractHangarQueue ($queue = '')
	{
		$result = [];

		if (is_array($queue) && count($queue))
		{
			foreach ($queue AS $element)
			{
				$result[$element->object_id] = $element->level;
			}
		}

		return $result;
	}

	private function ShowBuildingQueue ()
	{
		$queueManager = new Queue($this->user, $this->planet);

		$queueItems = $queueManager->get($queueManager::TYPE_BUILDING);

		$listRow = [];

		if (count($queueItems))
		{
			$end = 0;

			foreach ($queueItems as $item)
			{
				if (!$end)
					$end = $item->time;

				$elementTime = Building::getBuildingTime($this->user, $this->planet, $item->object_id, $item->level - ($item->operation == $item::OPERATION_BUILD ? 1 : 0));

				if ($item->operation == $item::OPERATION_DESTROY)
					$elementTime = ceil($elementTime / 2);

				if ($item->time > 0 && $item->time_end - $item->time != $elementTime)
				{
					$item->update([
						'time_end' => $item->time + $elementTime
					]);
				}

				$end += $elementTime;

				$listRow[] = [
					'name' 	=> _getText('tech', $item->object_id),
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

	public function ElementBuildListBox ()
	{
		$queueManager = new Queue($this->user, $this->planet);

		$queueItems = $queueManager->get($queueManager::TYPE_SHIPYARD);

		$data = [];

		if (count($queueItems))
		{
			$end = 0;

			foreach ($queueItems as $item)
			{
				if (!$end)
					$end = $item->time;

				$time = Building::getBuildingTime($this->user, $this->planet, $item->object_id);

				$end += $time * $item->level;

				$row = [
					'i'		=> (int) $item->object_id,
					'name'	=> _getText('tech', $item->object_id),
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