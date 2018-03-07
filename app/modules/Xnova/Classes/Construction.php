<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Di;
use Xnova\Models\Planet;
use Xnova\Models\User;

class Construction
{
	/**
	 * @var Models\User
	 */
	private $user;
	/**
	 * @var Models\Planet
	 */
	private $planet;
	public $mode = '';

	public function __construct (User $user, Planet $planet)
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

		if ($request->has('cmd'))
		{
			$Command 	= $request->getQuery('cmd', null, '');
			$Element 	= $request->getQuery('building', 'int', 0);
			$ListID 	= $request->getQuery('listid', 'int', 0);

			if (in_array($Element, Vars::getAllowedBuilds($this->planet->planet_type)) || ($ListID != 0 && ($Command == 'cancel' || $Command == 'remove')))
			{
				$queueManager = new Queue($this->planet->queue);
				$queueManager->setUserObject($this->user);
				$queueManager->setPlanetObject($this->planet);

				switch ($Command)
				{
					case 'cancel':
						$queueManager->delete(1, 0);
						break;
					case 'remove':
						$queueManager->delete(1, ($ListID - 1));
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

				$this->user->getDI()->getShared('response')->redirect("buildings/");
			}
		}

		$oldStyle = $this->user->getUserOption('only_available');

		$parse['items'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_BUILING) as $Element)
		{
			if (!in_array($Element, Vars::getAllowedBuilds($this->planet->planet_type)))
				continue;

			$isAccess = Building::isTechnologieAccessible($this->user, $this->planet, $Element);

			if (!$isAccess && $oldStyle)
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

		$NoResearchMessage = "";
		$bContinue = true;

		if (!Building::checkLabSettingsInQueue($this->planet))
		{
			$NoResearchMessage = _getText('labo_on_update');
			$bContinue = false;
		}

		$spaceLabs = [];

		if ($this->user->getTechLevel('intergalactic') > 0)
			$spaceLabs = $this->planet->getNetworkLevel();

		$this->planet->spaceLabs = $spaceLabs;

		$res_array = Vars::getItemsByType(Vars::ITEM_TYPE_TECH);

		$queueManager = new Queue();
		$queueManager->setUserObject($this->user);
		$queueManager->setPlanetObject($this->planet);

		$TechHandle = $queueManager->checkTechQueue();

		$queueManager->loadQueue((is_object($TechHandle['planet']) ? $TechHandle['planet']->queue : $this->planet->queue));

		if (isset($_GET['cmd']) AND $bContinue != false)
		{
			$Command 	= $request->getQuery('cmd', null, '');
			$Techno 	= $request->getQuery('tech', 'int', 0);

			if ($Techno > 0 && in_array($Techno, $res_array))
			{
				switch ($Command)
				{
					case 'cancel':

						if ($queueManager->getCount(Queue::QUEUE_TYPE_RESEARCH))
							$queueManager->delete($Techno);

						break;

					case 'search':

						if (!$queueManager->getCount(Queue::QUEUE_TYPE_RESEARCH))
							$queueManager->add($Techno);

						break;
				}

				$this->user->getDI()->getShared('response')->redirect("buildings/research/");
			}
		}

		$queueArray = $queueManager->get($queueManager::QUEUE_TYPE_RESEARCH);

		if (count($queueArray) && isset($queueArray[0]))
			$queueArray = $queueArray[0];

		$oldStyle = $this->user->getUserOption('only_available');

		$parse['items'] = [];

		foreach ($res_array AS $Tech)
		{
			$isAccess = Building::isTechnologieAccessible($this->user, $this->planet, $Tech);

			if (!$isAccess && $oldStyle)
				continue;

			if (!Building::checkTechnologyRace($this->user, $Tech))
				continue;

			$price = Vars::getItemPrice($Tech);

			$row = [];

			$row['allow'] 	= $isAccess;
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

				$row['time'] = Building::getBuildingTime($this->user, $this->planet, $Tech);

				if ($TechHandle['working'])
				{
					if (isset($queueArray['i']) && $queueArray['i'] == $Tech)
					{
						$row['build'] = [
							'id' => (int) $TechHandle['planet']->id,
							'name' => '',
							'time' => $queueArray['e']
						];

						if ($TechHandle['planet']->id != $this->planet->id)
							$row['build']['planet'] = $TechHandle['planet']->name;
					}
					else
						$row['build'] = true;
				}
			}
			else
				$row['need'] = Building::getTechTree($Tech, $this->user, $this->planet);

			$parse['items'][] = $row;
		}

		$parse['message'] = $NoResearchMessage;

		return $parse;
	}

	public function pageShipyard ($mode = 'fleet')
	{
		$queueManager = new Queue($this->planet->queue);

		if ($mode == 'defense')
			$elementIDs = Vars::getItemsByType(Vars::ITEM_TYPE_DEFENSE);
		else
			$elementIDs = Vars::getItemsByType(Vars::ITEM_TYPE_FLEET);

		if (isset($_POST['fmenge']))
		{
			$queueManager->setUserObject($this->user);
			$queueManager->setPlanetObject($this->planet);

			foreach ($_POST['fmenge'] as $Element => $Count)
			{
				$Element 	= intval($Element);
				$Count 		= abs(intval($Count));

				if (!in_array($Element, $elementIDs))
					continue;

				$queueManager->add($Element, $Count);
			}

			$this->planet->queue = $queueManager->get();
		}

		$queueArray = $queueManager->get($queueManager::QUEUE_TYPE_SHIPYARD);

		$BuildArray = $this->extractHangarQueue($queueArray);

		$oldStyle = $this->user->getUserOption('only_available');

		$parse = [];
		$parse['items'] = [];

		foreach ($elementIDs AS $Element)
		{
			$isAccess = Building::isTechnologieAccessible($this->user, $this->planet, $Element);

			if (!$isAccess && $oldStyle)
				continue;

			if (!Building::checkTechnologyRace($this->user, $Element))
				continue;

			$row = [];

			$row['allow']	= $isAccess;
			$row['i'] 		= $Element;
			$row['count'] 	= $this->planet->getUnitCount($Element);
			$row['price'] 	= Building::getBuildingPrice($this->user, $this->planet, $Element, false);
			$row['effects']	= '';

			if ($isAccess)
			{
				$row['time']	= Building::getBuildingTime($this->user, $this->planet, $Element);
				$row['is_max']	= false;

				$price = Vars::getItemPrice($Element);

				if (isset($price['max']))
				{
					$total = $this->planet->getUnitCount($Element);

					if (isset($BuildArray[$Element]))
						$total += $BuildArray[$Element];

					if ($total >= $price['max'])
						$row['is_max'] = true;
				}

				$row['max'] = isset($price['max']) ? (int) $price['max'] : 0;
				$row['effects'] = Building::getNextProduction($Element, 0, $this->planet);
			}
			else
				$row['need'] = Building::getTechTree($Element, $this->user, $this->planet);

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
				$result[$element['i']] = $element['l'];
			}
		}

		return $result;
	}

	private function ShowBuildingQueue ()
	{
		$queueManager = new Queue($this->planet->queue);

		$ActualCount = $queueManager->getCount($queueManager::QUEUE_TYPE_BUILDING);

		$ListIDRow = [];

		if ($ActualCount != 0)
		{
			$QueueArray = $queueManager->get($queueManager::QUEUE_TYPE_BUILDING);

			foreach ($QueueArray AS $item)
			{
				if ($item['e'] >= time())
				{
					$ListIDRow[] = Array
					(
						'name' 	=> _getText('tech', $item['i']),
						'level' => $item['d'] == 0 ? $item['l'] : $item['l'] + 1,
						'mode' 	=> $item['d'],
						'time' 	=> ($item['e'] - time()),
						'end' 	=> $item['e']
					);
				}
			}
		}

		$RetValue['lenght'] 	= $ActualCount;
		$RetValue['buildlist'] 	= $ListIDRow;

		return $RetValue;
	}

	public function ElementBuildListBox ()
	{
		$queueManager = new Queue($this->planet->queue);

		$ElementQueue = $queueManager->get($queueManager::QUEUE_TYPE_SHIPYARD);

		$data = [];

		if (count($ElementQueue))
		{
			$end = time();

			foreach ($ElementQueue as $queueArray)
			{
				$ElementTime = Building::getBuildingTime($this->user, $this->planet, $queueArray['i']);

				$end += ($ElementTime * $queueArray['l']) - $queueArray['s'];

				$row = [
					'i'		=> $queueArray['i'],
					'name'	=> _getText('tech', $queueArray['i']),
					'count'	=> $queueArray['l'],
					'time'	=> $ElementTime,
					'end'	=> $end
				];

				$data[] = $row;
			}
		}

		return $data;
	}
}