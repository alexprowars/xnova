<?php
namespace App;

use App\Models\Planet;
use App\Models\User;

class Construction
{
	/**
	 * @var \App\Models\User user
	 */
	private $user;
	/**
	 * @var \App\Models\Planet planet
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
		$parse = array();

		$request 	= $this->user->getDI()->getShared('request');
		$game 		= $this->user->getDI()->getShared('game');
		$config 	= $this->user->getDI()->getShared('config');

		if ($this->planet->id_ally > 0 && $this->planet->id_ally == $this->user->ally_id)
			$game->reslist['allowed']['5'] = array(14, 21, 34, 44);

		$this->planet->SetNextQueueElementOnTop();

		$Queue = $this->ShowBuildingQueue();

		$MaxBuidSize = $config->game->maxBuildingQueue + $this->user->bonusValue('queue', 0);

		$CanBuildElement = ($Queue['lenght'] < $MaxBuidSize);

		if ($request->has('cmd'))
		{
			$Command 	= $request->getQuery('cmd', null, '');
			$Element 	= $request->getQuery('building', 'int', 0);
			$ListID 	= $request->getQuery('listid', 'int', 0);

			if (in_array($Element, $game->reslist['allowed'][$this->planet->planet_type]) || ($ListID != 0 && ($Command == 'cancel' || $Command == 'remove')))
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

		$CurrentMaxFields = $this->planet->getMaxFields();
		$RoomIsOk = ($this->planet->field_current < ($CurrentMaxFields - $Queue['lenght']));

		$oldStyle = $this->user->getUserOption('only_available');

		$parse['BuildingsList'] = array();

		foreach ($game->reslist['build'] as $Element)
		{
			if (!in_array($Element, $game->reslist['allowed'][$this->planet->planet_type]))
				continue;

			$isAccess = Building::IsTechnologieAccessible($this->user, $this->planet, $Element);

			if (!$isAccess && $oldStyle)
				continue;

			if (!Building::checkTechnologyRace($this->user, $Element))
				continue;

			$HaveRessources 	= Building::IsElementBuyable($this->user, $this->planet, $Element, true, false);
			$BuildingLevel 		= (int) $this->planet->{$game->resource[$Element]};

			$row = array();

			$row['access']= $isAccess;
			$row['i'] 	= $Element;
			$row['count'] = $BuildingLevel;
			$row['price'] = Building::GetElementPrice(Building::GetBuildingPrice($this->user, $this->planet, $Element), $this->planet);

			if ($isAccess)
			{
				$row['time'] 	= Building::GetBuildingTime($this->user, $this->planet, $Element);
				$row['add'] 	= Building::GetNextProduction($Element, $BuildingLevel, $this->planet);
				$row['click'] 	= '';

				if ($Element == 31)
				{
					if ($this->user->b_tech_planet != 0)
						$row['click'] = "<span class=\"resNo\">" . _getText('in_working') . "</span>";
				}

				if (!$row['click'])
				{
					if ($RoomIsOk && $CanBuildElement)
					{
						if ($Queue['lenght'] == 0)
						{
							if ($HaveRessources == true)
								$row['click'] = "<a href=\"/buildings/index/cmd/insert/building/" . $Element . "/\"><span class=\"resYes\">".((!$this->planet->{$game->resource[$Element]}) ? 'Построить' : 'Улучшить')."</span></a>";
							else
								$row['click'] = "<span class=\"resNo\">нет ресурсов</span>";
						}
						else
							$row['click'] = "<a href=\"/buildings/index/cmd/insert/building/" . $Element . "/\"><span class=\"resYes\">В очередь</span></a>";
					}
					elseif ($RoomIsOk && !$CanBuildElement)
						$row['click'] = "<span class=\"resNo\">".((!$this->planet->{$game->resource[$Element]}) ? 'Построить' : 'Улучшить')."</span>";
					else
						$row['click'] = "<span class=\"resNo\">нет места</span>";
				}
			}

			$parse['BuildingsList'][] = $row;
		}

		$parse['BuildList'] 			= $Queue['buildlist'];
		$parse['planet_field_current'] 	= $this->planet->field_current;
		$parse['planet_field_max'] 		= $CurrentMaxFields;
		$parse['field_libre'] 			= $parse['planet_field_max'] - $this->planet->field_current;

		return $parse;
	}

	public function pageResearch ($mode = '')
	{
		$request 	= $this->user->getDI()->getShared('request');
		$game 		= $this->user->getDI()->getShared('game');

		$TechHandle = $this->planet->HandleTechnologieBuild();

		$NoResearchMessage = "";
		$bContinue = true;

		if (!Building::CheckLabSettingsInQueue($this->planet))
		{
			$NoResearchMessage = _getText('labo_on_update');
			$bContinue = false;
		}

		$spaceLabs = array();

		if ($this->user->{$game->resource[123]} > 0)
			$spaceLabs = $this->planet->getNetworkLevel();

		$this->planet->spaceLabs = $spaceLabs;

		if ($mode == 'fleet')
			$res_array = $game->reslist['tech_f'];
		else
			$res_array = $game->reslist['tech'];

		$PageParse['mode'] = $this->mode;

		$queueManager = new Queue((isset($TechHandle['WorkOn']['queue']) ? $TechHandle['WorkOn']['queue'] : $this->planet->queue));

		if (isset($_GET['cmd']) AND $bContinue != false)
		{
			$Command 	= $request->getQuery('cmd', null, '');
			$Techno 	= $request->getQuery('tech', 'int', 0);

			$queueManager->setUserObject($this->user);
			$queueManager->setPlanetObject($this->planet);

			if ($Techno > 0 && in_array($Techno, $res_array))
			{
				switch ($Command)
				{
					case 'cancel':
						$queueManager->delete($Techno);
						break;

					case 'search':
						$queueManager->add($Techno);
						break;
				}

				$this->user->getDI()->getShared('response')->redirect("buildings/research".($mode != '' ? '_'.$mode : '')."/");
			}
		}

		$queueArray = $queueManager->get($queueManager::QUEUE_TYPE_RESEARCH);

		if (count($queueArray) && isset($queueArray[0]))
			$queueArray = $queueArray[0];

		$oldStyle = $this->user->getUserOption('only_available');

		$PageParse['technolist'] = array();

		foreach ($res_array AS $Tech)
		{
			$isAccess = Building::IsTechnologieAccessible($this->user, $this->planet, $Tech);

			if (!$isAccess && $oldStyle)
				continue;

			if (!Building::checkTechnologyRace($this->user, $Tech))
				continue;

			$row = array();
			$row['access'] = $isAccess;
			$row['i'] = $Tech;

			$building_level = $this->user->{$game->resource[$Tech]};

			$row['tech_level'] = ($building_level == 0) ? "<font color=#FF0000>" . $building_level . "</font>" : "<font color=#00FF00>" . $building_level . "</font>";

			if (isset($game->pricelist[$Tech]['max']))
				$row['tech_level'] .= ' из <font color=yellow>' . $game->pricelist[$Tech]['max'] . '</font>';

			$row['tech_price'] = Building::GetElementPrice(Building::GetBuildingPrice($this->user, $this->planet, $Tech), $this->planet);

			if ($isAccess)
			{
				if ($Tech > 300 && $Tech < 400)
				{
					$l = ($Tech < 350 ? ($Tech - 100) : ($Tech + 50));

					if (isset($CombatCaps[$l]['power_up']) && $CombatCaps[$l]['power_up'] > 0)
					{
						$row['add'] = '+' . ($CombatCaps[$l]['power_up'] * $building_level) . '% атака<br>';
						$row['add'] .= '+' . ($CombatCaps[$l]['power_armour'] * $building_level) . '% прочность<br>';
					}
					if (isset($CombatCaps[$l]['power_consumption']) && $CombatCaps[($Tech < 350 ? ($Tech - 100) : ($Tech + 50))]['power_consumption'] > 0)
						$row['add'] = '+' . ($CombatCaps[$l]['power_consumption'] * $building_level) . '% вместимость<br>';
				}
				elseif ($Tech >= 120 && $Tech <= 122)
					$row['add'] = '+' . (5 * $building_level) . '% атака<br>';
				elseif ($Tech == 115)
					$row['add'] = '+' . (10 * $building_level) . '% скорости РД<br>';
				elseif ($Tech == 117)
					$row['add'] = '+' . (20 * $building_level) . '% скорости ИД<br>';
				elseif ($Tech == 118)
					$row['add'] = '+' . (30 * $building_level) . '% скорости ГД<br>';
				elseif ($Tech == 108)
					$row['add'] = '+' . ($building_level + 1) . ' слотов флота<br>';
				elseif ($Tech == 109)
					$row['add'] = '+' . (5 * $building_level) . '% атаки<br>';
				elseif ($Tech == 110)
					$row['add'] = '+' . (3 * $building_level) . '% защиты<br>';
				elseif ($Tech == 111)
					$row['add'] = '+' . (5 * $building_level) . '% прочности<br>';
				elseif ($Tech == 123)
					$row['add'] = '+' . ($building_level) . '% лабораторий<br>';

				$SearchTime = Building::GetBuildingTime($this->user, $this->planet, $Tech);
				$row['search_time'] = $SearchTime;
				$CanBeDone = Building::IsElementBuyable($this->user, $this->planet, $Tech);

				if (!$TechHandle['OnWork'])
				{
					$LevelToDo = 1 + $this->user->{$game->resource[$Tech]};

					if (isset($game->pricelist[$Tech]['max']) && $this->user->{$game->resource[$Tech]} >= $game->pricelist[$Tech]['max'])
						$TechnoLink = '<font color=#FF0000>максимальный уровень</font>';
					elseif ($CanBeDone)
					{
						if (!Building::CheckLabSettingsInQueue($this->planet))
						{
							if ($LevelToDo == 1)
								$TechnoLink = "<font color=#FF0000>Исследовать</font>";
							else
								$TechnoLink = "<font color=#FF0000>Улучшить</font>";
						}
						else
						{
							$TechnoLink = "<a href=\"/buildings/" . $this->mode. "/cmd/search/tech/" . $Tech . "/\">";

							if ($LevelToDo == 1)
								$TechnoLink .= "<font color=#00FF00>Исследовать</font>";
							else
								$TechnoLink .= "<font color=#00FF00>Улучшить</font>";

							$TechnoLink .= "</a>";
						}
					}
					else
						$TechnoLink = '<span class="resNo">нет ресурсов</span>';
				}
				else
				{
					if (isset($queueArray['i']) && $queueArray['i'] == $Tech)
					{
						$bloc = array();

						if ($TechHandle['WorkOn']['id'] != $this->planet->id)
							$bloc['tech_name'] 	= ' на ' . $TechHandle['WorkOn']["name"];
						else
							$bloc['tech_name'] 	= "";

						$bloc['tech_time'] 	= $queueArray['e'] - time();
						$bloc['tech_home'] 	= $TechHandle['WorkOn']["id"];
						$bloc['tech_id'] 	= $queueArray['i'];

						$TechnoLink = $bloc;
					}
					else
						$TechnoLink = "<center>-</center>";
				}
				$row['tech_link'] = $TechnoLink;
			}

			$PageParse['technolist'][] = $row;
		}

		$PageParse['noresearch'] = $NoResearchMessage;

		return $PageParse;
	}

	public function pageShipyard ($mode = 'fleet')
	{
		$game = $this->user->getDI()->getShared('game');

		$queueManager = new Queue($this->planet->queue);

		if ($mode == 'defense')
			$elementIDs = $game->reslist['defense'];
		else
			$elementIDs = $game->reslist['fleet'];

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

		$parse = array();
		$parse['buildlist'] = array();

		foreach ($elementIDs AS $Element)
		{
			$isAccess = Building::IsTechnologieAccessible($this->user, $this->planet, $Element);

			if (!$isAccess && $oldStyle)
				continue;

			if (!Building::checkTechnologyRace($this->user, $Element))
				continue;

			$row = array();

			$row['access']	= $isAccess;
			$row['i'] 		= $Element;
			$row['count'] 	= $this->planet->{$game->resource[$Element]};
			$row['price'] 	= Building::GetElementPrice(Building::GetBuildingPrice($this->user, $this->planet, $Element, false), $this->planet);

			if ($isAccess)
			{
				$row['time'] 	 	= Building::GetBuildingTime($this->user, $this->planet, $Element);
				$row['can_build'] 	= Building::IsElementBuyable($this->user, $this->planet, $Element, false);

				if ($row['can_build'])
				{
					$row['maximum'] = false;

					if (isset($game->pricelist[$Element]['max']))
					{
						$total = $this->planet->{$game->resource[$Element]};

						if (isset($BuildArray[$Element]))
							$total += $BuildArray[$Element];

						if ($total >= $game->pricelist[$Element]['max'])
							$row['maximum'] = true;
					}

					$row['max'] = Building::GetMaxConstructibleElements($Element, $this->planet, $this->user);
				}

				$row['add'] = Building::GetNextProduction($Element, 0, $this->planet);
			}

			$parse['buildlist'][] = $row;
		}

		return $parse;
	}

	private function extractHangarQueue ($queue = '')
	{
		$result = array();

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

		$ListIDRow = array();

		if ($ActualCount != 0)
		{
			$PlanetID = $this->planet->id;

			$QueueArray = $queueManager->get($queueManager::QUEUE_TYPE_BUILDING);

			foreach ($QueueArray AS $i => $item)
			{
				if ($item['e'] >= time())
				{
					$ListIDRow[] = Array
					(
						'ListID' 		=> ($i + 1),
						'ElementTitle' 	=> _getText('tech', $item['i']),
						'BuildLevel' 	=> $item['l'],
						'BuildMode' 	=> $item['d'],
						'BuildTime' 	=> ($item['e'] - time()),
						'PlanetID' 		=> $PlanetID,
						'BuildEndTime' 	=> $item['e']
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
		$NbrePerType = "";
		$NamePerType = "";
		$TimePerType = "";
		$QueueTime = 0;

		$parse = array();

		if (count($ElementQueue))
		{
			foreach ($ElementQueue as $queueArray)
			{
				$ElementTime = Building::GetBuildingTime($this->user, $this->planet, $queueArray['i']);

				$QueueTime += $ElementTime * $queueArray['l'];

				$TimePerType .= "" . $ElementTime . ",";
				$NamePerType .= "'" . html_entity_decode(_getText('tech', $queueArray['i'])) . "',";
				$NbrePerType .= "" . $queueArray['l'] . ",";
			}


			$parse['a'] = $NbrePerType;
			$parse['b'] = $NamePerType;
			$parse['c'] = $TimePerType;
			$parse['b_hangar_id_plus'] = $ElementQueue[0]['s'];

			$parse['time'] = Helpers::pretty_time($QueueTime - $ElementQueue[0]['s']);
		}

		$parse['count'] = count($ElementQueue);

		return $parse;
	}
}

?>