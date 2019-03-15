<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Building;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\SuccessException;
use Xnova\Fleet;
use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Request;
use Xnova\Vars;

/**
 * @RoutePrefix("/info")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class InfoController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		$this->user->loadPlanet();
	}

	/**
	 * @Route("/{element:[0-9]+}{params:(/.*)*}")
	 * @param null $element
	 * @throws \Exception
	 */
	public function indexAction ($element = null)
	{
		$this->ShowBuildingInfoPage((int) $element);

		$this->tag->setTitle(_getText('tech', $element));
		$this->showTopPanel(false);
	}

	private function ShowRapidFireTo ($BuildID)
	{
		$ResultString = "";

		$res = Vars::getItemsByType([Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]);

		foreach ($res AS $Type)
		{
			if (isset($this->registry->CombatCaps[$BuildID]['sd'][$Type]) && $this->registry->CombatCaps[$BuildID]['sd'][$Type] > 1)
			{
				$ResultString .= _getText('nfo_rf_again') . " <font color=\"#00ff00\">" . $this->registry->CombatCaps[$BuildID]['sd'][$Type] . "</font> единиц " ._getText('tech', $Type) . "<br>";
			}
		}

		return $ResultString;
	}

	private function ShowRapidFireFrom ($BuildID)
	{
		$ResultString = "";

		$res = Vars::getItemsByType([Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]);

		foreach ($res AS $Type)
		{
			if (isset($this->registry->CombatCaps[$Type]['sd'][$BuildID]) && $this->registry->CombatCaps[$Type]['sd'][$BuildID] > 1)
			{
				$ResultString .= _getText('tech', $Type) . " " . _getText('nfo_rf_from') . " <font color=\"#ff0000\">" . $this->registry->CombatCaps[$Type]['sd'][$BuildID] . "</font> единиц<br>";
			}
		}

		return $ResultString;
	}

	/**
	 * @param  $BuildID
	 * @return array
	 * @throws \Phalcon\Exception
	 */
	private function ShowProductionTable ($BuildID)
	{
		$CurrentBuildtLvl = $this->planet->getBuildLevel($BuildID);

		$ActualNeed = $ActualProd = 0;

		if ($BuildID != 42 && !($BuildID >= 22 && $BuildID <= 24))
		{
			$BuildLevelFactor = $this->planet->getBuild($BuildID)['power'];
			$BuildLevel = ($CurrentBuildtLvl > 0) ? $CurrentBuildtLvl : 1;

			$res = $this->planet->getResourceProductionLevel($BuildID, $BuildLevel, $BuildLevelFactor);

			$Prod[1] = $res['metal'];
			$Prod[2] = $res['crystal'];
			$Prod[3] = $res['deuterium'];
			$Prod[4] = $res['energy'];

			if ($BuildID != 12)
			{
				$ActualNeed = floor($Prod[4]);
				$ActualProd = floor($Prod[$BuildID]);
			}
			else
			{
				$ActualNeed = floor($Prod[3]);
				$ActualProd = floor($Prod[4]);
			}
		}

		$BuildStartLvl = $CurrentBuildtLvl - 2;

		if ($BuildStartLvl < 1)
			$BuildStartLvl = 1;

		$items = [];

		$ProdFirst = 0;

		for ($BuildLevel = $BuildStartLvl; $BuildLevel < $BuildStartLvl + 10; $BuildLevel++)
		{
			$row = [];

			if ($BuildID != 42 && !($BuildID >= 22 && $BuildID <= 24))
			{
				$res = $this->planet->getResourceProductionLevel($BuildID, $BuildLevel);

				$Prod[1] = $res['metal'];
				$Prod[2] = $res['crystal'];
				$Prod[3] = $res['deuterium'];
				$Prod[4] = $res['energy'];

				if ($BuildID != 12)
				{
					$row['prod'] = floor($Prod[$BuildID]);
					$row['prod_diff'] = floor($Prod[$BuildID] - $ActualProd);
					$row['need'] = floor($Prod[4]);
					$row['need_diff'] = floor($Prod[4] - $ActualNeed);
				}
				else
				{
					$row['prod'] = floor($Prod[4]);
					$row['prod_diff'] = floor($Prod[4] - $ActualProd);
					$row['need'] = floor($Prod[3]);
					$row['need_diff'] = floor($Prod[3] - $ActualNeed);
				}

				if ($ProdFirst == 0)
				{
					if ($BuildID != 12)
						$ProdFirst = floor($Prod[$BuildID]);
					else
						$ProdFirst = floor($Prod[4]);
				}
			}
			elseif ($BuildID >= 22 && $BuildID <= 24)
			{
				$row['range'] = floor(($this->config->game->baseStorageSize + floor(50000 * round(pow(1.6, $BuildLevel)))) * $this->user->bonusValue('storage')) / 1000;
			}
			else
			{
				$row['range'] = ($BuildLevel * $BuildLevel) - 1;
			}

			$row['current'] = $CurrentBuildtLvl == $BuildLevel;
			$row['level'] = $BuildLevel;

			$items[] = $row;
		}

		return $items;
	}

	/**
	 * @param $itemId int
	 * @throws \Exception
	 */
	private function ShowBuildingInfoPage ($itemId)
	{
		Lang::includeLang('infos', 'xnova');

		$parse = [];

		if (!_getText('info', $itemId, true))
			throw new ErrorException('Мы не сможем дать вам эту информацию');

		$price = Vars::getItemPrice($itemId);

		$parse['i'] = (int) $itemId;
		$parse['description'] = _getText('info', $itemId);

		$parse['production'] = false;
		$parse['destroy'] = false;
		$parse['fleet'] = false;
		$parse['defence'] = false;
		$parse['missile'] = false;

		if (($itemId >= 1 && $itemId <= 4) || $itemId == 12 || $itemId == 42 || ($itemId >= 22 && $itemId <= 24))
		{
			$parse['production'] = $this->ShowProductionTable($itemId);
		}
		elseif ($itemId == 34)
		{
			$parse['msg'] = '';

			if ($this->request->hasPost('send') && $this->request->hasPost('fleet'))
			{
				$fleetId = (int) $this->request->getPost('fleet', 'int', 0);

				$fleet = \Xnova\Models\Fleet::findFirst([
					'id = :fleet: AND end_galaxy = :galaxy: AND end_system = :system: AND end_planet = :planet: AND end_type = :type: AND mess = 3',
					'bind' => [
						'galaxy' => $this->planet->galaxy,
						'system' => $this->planet->system,
						'planet' => $this->planet->planet,
						'type' => $this->planet->planet_type,
						'fleet' => $fleetId,
					]
				]);

				if (!$fleet)
					throw new ErrorException('Флот отсутствует у планеты');
				else
				{
					$tt = 0;

					foreach ($fleet->getShips() as $type => $ship)
					{
						if ($type > 100)
							$tt += $this->registry->CombatCaps[$type]['stay'] * $ship['count'];
					}

					$max = $this->planet->getBuildLevel($itemId) * 10000;

					if ($max > $this->planet->deuterium)
						$cur = $this->planet->deuterium;
					else
						$cur = $max;

					$times = round(($cur / $tt) * 3600);

					$this->planet->deuterium -= $cur;
					$this->planet->update();

					$fleet->end_stay += $times;
					$fleet->end_time += $times;
					$fleet->update();

					throw new SuccessException('Ракета с дейтерием отправлена на орбиту вашей планете');
				}
			}

			if ($this->planet->getBuildLevel($itemId) > 0)
			{
				$list = [];

				$fleets = \Xnova\Models\Fleet::find([
					'end_galaxy = ?0 AND end_system = ?1 AND end_planet = ?2 AND end_type = ?3 AND mess = 3 AND owner = ?4',
					'bind' => [
						$this->planet->galaxy,
						$this->planet->system,
						$this->planet->planet,
						$this->planet->planet_type,
						$this->user->id
					]
				]);

				foreach ($fleets as $item)
				{
					$list[] = [
						'id' => $item->id,
						'start_galaxy' => $item->start_galaxy,
						'system' => $item->start_system,
						'planet' => $item->start_planet,
						'name' => $item->owner_name,
					];
				}

				$parse['alliance'] = [
					'fleets' => $list,
					'cost' => $this->planet->getBuildLevel($itemId) * 10000,
				];
			}
		}
		elseif (Vars::getItemType($itemId) == Vars::ITEM_TYPE_FLEET)
		{
			$fleet = [];

			$fleet['armor'] = floor(($price['metal'] + $price['crystal']) / 10);
			$fleet['armor_full'] = round($fleet['armor'] * (1 + $this->user->getTechLevel('defence') * 0.05));

			$attTech = 1 + $this->user->getTechLevel('military') * 0.05;

			if ($this->registry->CombatCaps[$itemId]['type_gun'] == 1)
				$attTech += $this->user->getTechLevel('laser') * 0.05;
			elseif ($this->registry->CombatCaps[$itemId]['type_gun'] == 2)
				$attTech += $this->user->getTechLevel('ionic') * 0.05;
			elseif ($this->registry->CombatCaps[$itemId]['type_gun'] == 3)
				$attTech += $this->user->getTechLevel('buster') * 0.05;

			// Устанавливаем обновлённые двигателя кораблей
			Fleet::SetShipsEngine($this->user);

			$fleet['rapidfire'] = [
				'to' => $this->ShowRapidFireTo($itemId),
				'from' => $this->ShowRapidFireFrom($itemId)
			];

			$fleet['attack'] = $this->registry->CombatCaps[$itemId]['attack'];
			$fleet['attack_full'] = round($this->registry->CombatCaps[$itemId]['attack'] * $attTech);
			$fleet['shield'] = $this->registry->CombatCaps[$itemId]['shield'];
			$fleet['capacity'] = $this->registry->CombatCaps[$itemId]['capacity'];
			$fleet['speed'] = $this->registry->CombatCaps[$itemId]['speed'];
			$fleet['speed_full'] = Fleet::GetFleetMaxSpeed('', $itemId, $this->user);
			$fleet['consumption'] = $this->registry->CombatCaps[$itemId]['consumption'];

			$fleet['resources'] = [];

			foreach ($price as $res => $value)
			{
				$fleet['resources'][$res] = [
					'base' => $value,
					'full' => $value * $this->user->bonusValue('res_fleet')
				];
			}

			$engine = ['', 'Ракетный', 'Импульсный', 'Гиперпространственный'];
			$gun = ['', 'Лазерное', 'Ионное', 'Плазменное'];
			$armour = ['', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная'];

			$fleet['type_engine'] = $engine[$this->registry->CombatCaps[$itemId]['type_engine']];
			$fleet['type_gun'] = $gun[$this->registry->CombatCaps[$itemId]['type_gun']];
			$fleet['type_armour'] = $armour[$this->registry->CombatCaps[$itemId]['type_armour']];

			$parse['fleet'] = $fleet;
		}
		elseif (Vars::getItemType($itemId) == Vars::ITEM_TYPE_DEFENSE)
		{
			$fleet = [];

			$fleet['armor'] = floor(($price['metal'] + $price['crystal']) / 10);
			$fleet['armor_full'] = round($fleet['armor'] * (1 + $this->user->getTechLevel('defence') * 0.05));

			if (isset($this->registry->CombatCaps[$itemId]['shield']))
				$fleet['shield'] = $this->registry->CombatCaps[$itemId]['shield'];
			else
				$fleet['shield'] = 0;

			$attTech = 1 + $this->user->getTechLevel('military') * 0.05;

			if ($this->registry->CombatCaps[$itemId]['type_gun'] == 1)
				$attTech += $this->user->getTechLevel('laser') * 0.05;
			elseif ($this->registry->CombatCaps[$itemId]['type_gun'] == 2)
				$attTech += $this->user->getTechLevel('ionic') * 0.05;
			elseif ($this->registry->CombatCaps[$itemId]['type_gun'] == 3)
				$attTech += $this->user->getTechLevel('buster') * 0.05;

			$fleet['attack'] = $this->registry->CombatCaps[$itemId]['attack'];
			$fleet['attack_full'] = round($this->registry->CombatCaps[$itemId]['attack'] * $attTech);

			$fleet['resources'] = [];

			foreach ($price as $res => $value)
				$fleet['resources'][$res] = $value;

			$fleet['type_gun'] = false;
			$fleet['type_armour'] = false;
			$fleet['rapidfire'] = false;

			if ($itemId >= 400 && $itemId < 500)
			{
				$gun = ['', 'Лазерное', 'Ионное', 'Плазменное'];
				$armour = ['', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная'];

				$fleet['type_gun'] = $gun[$this->registry->CombatCaps[$itemId]['type_gun']];
				$fleet['type_armour'] = $armour[$this->registry->CombatCaps[$itemId]['type_armour']];
				$fleet['rapidfire'] = [];

				foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) AS $Type)
				{
					if (!isset($this->registry->CombatCaps[$Type]))
						continue;

					$enemyPrice = Vars::getItemPrice($Type);

					$enemy_durability = ($enemyPrice['metal'] + $enemyPrice['crystal']) / 10;

					$rapid = $this->registry->CombatCaps[$itemId]['attack'] * (isset($this->registry->CombatCaps[$itemId]['amplify'][$Type]) ? $this->registry->CombatCaps[$itemId]['amplify'][$Type] : 1) / $enemy_durability;

					if ($rapid >= 1)
						$fleet['rapidfire'][$Type]['TO'] = floor($rapid);

					$rapid = $this->registry->CombatCaps[$Type]['attack'] * (isset($this->registry->CombatCaps[$Type]['amplify'][$itemId]) ? $this->registry->CombatCaps[$Type]['amplify'][$itemId] : 1) / $fleet['armor'];

					if ($rapid >= 1)
						$fleet['rapidfire'][$Type]['FROM'] = floor($rapid);
				}
			}

			$parse['defence'] = $fleet;

			if ($itemId >= 500 && $itemId < 600)
			{
				if ($this->request->hasPost('missiles'))
				{
					$icm = abs((int) $this->request->getPost('interceptor', 'int', 0));
					$ipm = abs((int) $this->request->getPost('interplanetary', 'int', 0));

					if ($icm > $this->planet->getUnitCount('interceptor_misil'))
						$icm = $this->planet->getUnitCount('interceptor_misil');
					if ($ipm > $this->planet->getUnitCount('interplanetary_misil'))
						$ipm = $this->planet->getUnitCount('interplanetary_misil');

					$this->planet->setUnit('interceptor_misil', -$icm, true);
					$this->planet->setUnit('interplanetary_misil', -$ipm, true);
					$this->planet->update();

					throw new SuccessException('Ракеты уничтожены');
				}

				$parse['missile'] = [
					'interceptor' => $this->planet->getUnitCount('interceptor_misil'),
					'interplanetary' => $this->planet->getUnitCount('interplanetary_misil')
				];
			}
		}

		if ($itemId <= 44 && $itemId != 33 && $itemId != 41 && !($itemId >= 601 && $itemId <= 615) && !($itemId >= 502 && $itemId <= 503))
		{
			$build = $this->planet->getBuild($itemId);

			if ($build && $build['level'] > 0)
			{
				$time = ceil(Building::getBuildingTime($this->user, $this->planet, $itemId) / 2);

				if ($time < 1)
					$time = 1;

				$parse['destroy'] = [
					'level' => $build['level'],
					'resources' => Building::getBuildingPrice($this->user, $this->planet, $itemId, true, true),
					'time' => $time
				];
			}
		}

		Request::addData('page', $parse);
	}
}