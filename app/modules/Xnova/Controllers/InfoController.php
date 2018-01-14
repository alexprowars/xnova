<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Building;
use Xnova\Exceptions\ErrorException;
use Xnova\Fleet;
use Xnova\Format;
use Xnova\Helpers;
use Friday\Core\Lang;
use Xnova\Controller;
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
	 * @throws ErrorException
	 */
	public function indexAction ($element = null)
	{
		if (is_numeric($element))
			$html = $this->ShowBuildingInfoPage($element);
		else
			$html = '';

		$this->tag->setTitle($html);
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

	private function BuildFleetCombo ()
	{
		$MoonList = \Xnova\Models\Fleet::find(['end_galaxy = ?0 AND end_system = ?1 AND end_planet = ?2 AND end_type = ?3 AND mess = 3 AND owner = ?4', 'bind' => [$this->planet->galaxy, $this->planet->system, $this->planet->planet, $this->planet->planet_type, $this->user->id]]);

		$Combo = "";

		foreach ($MoonList as $CurMoon)
		{
			$Combo .= "<option value=\"" . $CurMoon->id . "\">[" . $CurMoon->start_galaxy . ":" . $CurMoon->start_system . ":" . $CurMoon->start_planet . "] " . $CurMoon->owner_name . "</option>\n";
		}

		return $Combo;
	}

	/**
	 * @param  $BuildID
	 * @return array
	 */
	private function ShowProductionTable ($BuildID)
	{
		$CurrentBuildtLvl = $this->planet->getBuildLevel($BuildID);

		$ActualNeed = $ActualProd = 0;

		if ($BuildID != 42 && !($BuildID >= 22 && $BuildID <= 24))
		{
			$BuildLevelFactor = $this->planet->getBuild($BuildID)['power'];
			$BuildLevel = ($CurrentBuildtLvl > 0) ? $CurrentBuildtLvl : 1;

			$res = $this->planet->getProductionLevel($BuildID, $BuildLevel, $BuildLevelFactor);

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

		$Table = [];

		$ProdFirst = 0;

		for ($BuildLevel = $BuildStartLvl; $BuildLevel < $BuildStartLvl + 10; $BuildLevel++)
		{
			if ($BuildID != 42 && !($BuildID >= 22 && $BuildID <= 24))
			{
				$res = $this->planet->getProductionLevel($BuildID, $BuildLevel);

				$Prod[1] = $res['metal'];
				$Prod[2] = $res['crystal'];
				$Prod[3] = $res['deuterium'];
				$Prod[4] = $res['energy'];

				$bloc['build_lvl'] = ($CurrentBuildtLvl == $BuildLevel) ? "<font color=\"#ff0000\">" . $BuildLevel . "</font>" : $BuildLevel;

				if ($BuildID != 12)
				{
					$bloc['build_prod'] = Format::number(floor($Prod[$BuildID]));
					$bloc['build_prod_diff'] = Helpers::colorNumber(Format::number(floor($Prod[$BuildID] - $ActualProd)));
					$bloc['build_need'] = Helpers::colorNumber(Format::number(floor($Prod[4])));
					$bloc['build_need_diff'] = Helpers::colorNumber(Format::number(floor($Prod[4] - $ActualNeed)));
				}
				else
				{
					$bloc['build_prod'] = Format::number(floor($Prod[4]));
					$bloc['build_prod_diff'] = Helpers::colorNumber(Format::number(floor($Prod[4] - $ActualProd)));
					$bloc['build_need'] = Helpers::colorNumber(Format::number(floor($Prod[3])));
					$bloc['build_need_diff'] = Helpers::colorNumber(Format::number(floor($Prod[3] - $ActualNeed)));
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
				$bloc['build_lvl'] = ($CurrentBuildtLvl == $BuildLevel) ? "<font color=\"#ff0000\">" . $BuildLevel . "</font>" : $BuildLevel;
				$bloc['build_range'] = floor(($this->config->game->baseStorageSize + floor(50000 * round(pow(1.6, $BuildLevel)))) * $this->user->bonusValue('storage')) / 1000;
			}
			else
			{
				$bloc['build_lvl'] = ($CurrentBuildtLvl == $BuildLevel) ? "<font color=\"#ff0000\">" . $BuildLevel . "</font>" : $BuildLevel;
				$bloc['build_range'] = ($BuildLevel * $BuildLevel) - 1;
			}

			$Table[] = $bloc;
		}

		return $Table;
	}

	/**
	 * @param $itemId int
	 * @return array|string
	 * @throws ErrorException
	 */
	private function ShowBuildingInfoPage ($itemId)
	{
		Lang::includeLang('infos', 'xnova');

		$parse = [];

		if (!_getText('info', $itemId, true))
			throw new ErrorException('Мы не сможем дать вам эту информацию');

		$price = Vars::getItemPrice($itemId);

		$parse['name'] = _getText('tech', $itemId);
		$parse['image'] = $itemId;
		$parse['description'] = _getText('info', $itemId);

		if (($itemId >= 1 && $itemId <= 4) || $itemId == 12 || $itemId == 42 || ($itemId >= 22 && $itemId <= 24))
		{
			$parse['table_data'] = $this->ShowProductionTable($itemId);
			$this->view->setVar('parse', $parse);
			$this->view->partial('info/buildings_table');
		}
		elseif (($itemId >= 14 && $itemId <= 34) || $itemId == 6 || $itemId == 43 || $itemId == 44 || $itemId == 41 || ($itemId >= 106 && $itemId <= 199))
		{
			if ($itemId == 34)
			{
				$parse['msg'] = '';

				if (isset($_POST['send']) && isset($_POST['jmpto']))
				{
					$flid = intval($_POST['jmpto']);

					$fleet = \Xnova\Models\Fleet::findFirst(['id = ?0 AND end_galaxy = ?1 AND end_system = ?2 AND end_planet = ?3 AND end_type = ?4 AND mess = 3', 'bind' => [$this->planet->galaxy, $this->planet->system, $this->planet->planet, $this->planet->planet_type]]);

					if (!$fleet)
						$parse['msg'] = "<font color=red>Флот отсутствует у планеты</font>";
					else
					{
						$tt = 0;

						foreach ($fleet->getShips() as $type => $ship)
						{
							if ($type > 100)
								$tt += $this->registry->CombatCaps[$type]['stay'] * $ship['cnt'];
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

						$parse['msg'] = "<font color=red>Ракета с дейтерием отправлена на орбиту вашей планете</font>";
					}
				}

				if ($this->planet->getBuildLevel($itemId) > 0)
				{
					if (!$parse['msg'])
						$parse['msg'] = "Выберите флот для отправки дейтерия";

					$parse['fleet'] = $this->BuildFleetCombo();
					$parse['need'] = ($this->planet->getBuildLevel($itemId) * 10000);

					$this->view->setVar('parse', $parse);
					$this->view->partial('info/buildings_ally');
				}
			}

			$this->view->setVar('parse', $parse);
			$this->view->partial('info/buildings');
		}
		elseif (Vars::getItemType($itemId) == Vars::ITEM_TYPE_FLEET)
		{
			$parse['hull_pt']  = floor(($price['metal'] + $price['crystal']) / 10);
			$parse['~hull_pt'] = $parse['hull_pt'];
			$parse['hull_pt']  = Format::number($parse['hull_pt']) . ' (' . Format::number(round($parse['hull_pt'] * (1 + $this->user->getTechLevel('defence') * 0.05 + (($this->registry->CombatCaps[$itemId]['power_up'] * ((isset($this->user->{'fleet_' . $itemId})) ? $this->user->{'fleet_' . $itemId} : 0)) / 100)))) . ')';

			$attTech = 1 + (((isset($this->user->{'fleet_' . $itemId})) ? $this->user->{'fleet_' . $itemId} : 0) * ($this->registry->CombatCaps[$itemId]['power_up'] / 100)) + $this->user->getTechLevel('military') * 0.05;

			if ($this->registry->CombatCaps[$itemId]['type_gun'] == 1)
				$attTech += $this->user->getTechLevel('laser') * 0.05;
			elseif ($this->registry->CombatCaps[$itemId]['type_gun'] == 2)
				$attTech += $this->user->getTechLevel('ionic') * 0.05;
			elseif ($this->registry->CombatCaps[$itemId]['type_gun'] == 3)
				$attTech += $this->user->getTechLevel('buster') * 0.05;

			// Устанавливаем обновлённые двигателя кораблей
			Fleet::SetShipsEngine($this->user);

			$parse['rf_info_to']  = $this->ShowRapidFireTo($itemId);
			$parse['rf_info_fr']  = $this->ShowRapidFireFrom($itemId);

			$parse['attack_pt'] = Format::number($this->registry->CombatCaps[$itemId]['attack']) . ' (' . Format::number(round($this->registry->CombatCaps[$itemId]['attack'] * $attTech)) . ')';
			$parse['shield_pt'] = Format::number($this->registry->CombatCaps[$itemId]['shield']);
			$parse['capacity_pt'] = Format::number($this->registry->CombatCaps[$itemId]['capacity']);
			$parse['base_speed'] = Format::number($this->registry->CombatCaps[$itemId]['speed']) . ' (' . Format::number(Fleet::GetFleetMaxSpeed('', $itemId, $this->user)) . ')';
			$parse['base_conso'] = Format::number($this->registry->CombatCaps[$itemId]['consumption']);
			$parse['block'] = $this->registry->CombatCaps[$itemId]['power_armour'];
			$parse['upgrade'] = $this->registry->CombatCaps[$itemId]['power_up'];
			$parse['met'] = Format::number($price['metal']) . ' (' . Format::number($price['metal'] * $this->user->bonusValue('res_fleet')) . ')';
			$parse['cry'] = Format::number($price['crystal']) . ' (' . Format::number($price['crystal'] * $this->user->bonusValue('res_fleet')) . ')';
			$parse['deu'] = Format::number($price['deuterium']) . ' (' . Format::number($price['deuterium'] * $this->user->bonusValue('res_fleet')) . ')';

			$engine = ['', 'Ракетный', 'Импульсный', 'Гиперпространственный'];
			$gun = ['', 'Лазерное', 'Ионное', 'Плазменное'];
			$armour = ['', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная'];

			$parse['base_engine'] = $engine[$this->registry->CombatCaps[$itemId]['type_engine']];
			$parse['gun'] = $gun[$this->registry->CombatCaps[$itemId]['type_gun']];
			$parse['armour'] = $armour[$this->registry->CombatCaps[$itemId]['type_armour']];

			$this->view->setVar('parse', $parse);
			$this->view->partial('info/buildings_fleet');
		}
		elseif (Vars::getItemType($itemId) == Vars::ITEM_TYPE_DEFENSE)
		{
			$parse['element_typ'] = _getText('tech', 400);
			$parse['hull_pt']  = floor(($price['metal'] + $price['crystal']) / 10);
			$parse['~hull_pt'] = $parse['hull_pt'];
			$parse['hull_pt']  = Format::number($parse['hull_pt']) . ' (' . Format::number(round($parse['hull_pt'] * (1 + $this->user->getTechLevel('defence') * 0.05 + (((isset($this->registry->CombatCaps[$itemId]['power_up']) ? $this->registry->CombatCaps[$itemId]['power_up'] : 0) * ((isset($this->user->{'fleet_' . $itemId})) ? $this->user->{'fleet_' . $itemId} : 0)) / 100)))) . ')';

			if (isset($this->registry->CombatCaps[$itemId]['shield']))
				$parse['shield_pt'] = Format::number($this->registry->CombatCaps[$itemId]['shield']);
			else
				$parse['shield_pt'] = '';

			$attTech = 1 + (((isset($this->user->{'fleet_' . $itemId})) ? $this->user->{'fleet_' . $itemId} : 0) * ((isset($this->registry->CombatCaps[$itemId]['power_up']) ? $this->registry->CombatCaps[$itemId]['power_up'] : 0) / 100)) + $this->user->getTechLevel('military') * 0.05;

			$parse['attack_pt'] = Format::number($this->registry->CombatCaps[$itemId]['attack']) . ' (' . Format::number(round($this->registry->CombatCaps[$itemId]['attack'] * $attTech)) . ')';
			$parse['met'] = Format::number($price['metal']);
			$parse['cry'] = Format::number($price['crystal']);
			$parse['deu'] = Format::number($price['deuterium']);

			if ($itemId >= 400 && $itemId < 500)
			{
				$gun = ['', 'Лазерное', 'Ионное', 'Плазменное'];
				$armour = ['', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная'];

				$parse['gun'] = $gun[$this->registry->CombatCaps[$itemId]['type_gun']];
				$parse['armour'] = $armour[$this->registry->CombatCaps[$itemId]['type_armour']];

				$parse['speedBattle'] = [];

				foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) AS $Type)
				{
					if (!isset($this->registry->CombatCaps[$Type]))
						continue;

					$enemyPrice = Vars::getItemPrice($Type);

					$enemy_durability = ($enemyPrice['metal'] + $enemyPrice['crystal']) / 10;

					$rapid = $this->registry->CombatCaps[$itemId]['attack'] * (isset($this->registry->CombatCaps[$itemId]['amplify'][$Type]) ? $this->registry->CombatCaps[$itemId]['amplify'][$Type] : 1) / $enemy_durability;

					if ($rapid >= 1)
						$parse['speedBattle'][$Type]['TO'] = floor($rapid);

					$rapid = $this->registry->CombatCaps[$Type]['attack'] * (isset($this->registry->CombatCaps[$Type]['amplify'][$itemId]) ? $this->registry->CombatCaps[$Type]['amplify'][$itemId] : 1) / $parse['hull_pt'];

					if ($rapid >= 1)
						$parse['speedBattle'][$Type]['FROM'] = floor($rapid);
				}
			}

			$this->view->partial('info/buildings_defence');

			if ($itemId >= 500 && $itemId < 600)
			{
				if (isset($_POST['form']))
				{
					$_POST['502'] = abs(intval($_POST['502']));
					$_POST['503'] = abs(intval($_POST['503']));

					if ($_POST['502'] > $this->planet->getUnitCount('interceptor_misil'))
						$_POST['502'] = $this->planet->getUnitCount('interceptor_misil');
					if ($_POST['503'] > $this->planet->getUnitCount('interplanetary_misil'))
						$_POST['503'] = $this->planet->getUnitCount('interplanetary_misil');

					$this->planet->setUnit('interceptor_misil', -$_POST['502'], true);
					$this->planet->setUnit('interplanetary_misil', -$_POST['502'], true);
					$this->planet->update();
				}

				$parse['max_mis'] = $this->planet->getBuildLevel('missile_facility') * 10;
				$parse['int_miss'] = _getText('tech', 502) . ': ' . $this->planet->getUnitCount('interceptor_misil');
				$parse['plant_miss'] = _getText('tech', 503) . ': ' . $this->planet->getUnitCount('interplanetary_misil');

				$this->view->partial('info/missile');
			}

			$this->view->setVar('parse', $parse);
		}
		elseif (Vars::getItemType($itemId) == Vars::ITEM_TYPE_OFFICIER)
		{
			$this->view->setVar('parse', $parse);
			$this->view->partial('info/officier');
		}
		elseif ($itemId >= 701 && $itemId <= 704)
		{
			$parse['image'] = $itemId - 700;

			$this->view->setVar('parse', $parse);
			$this->view->partial('info/race');
		}

		if ($itemId <= 44 && $itemId != 33 && $itemId != 41 && !($itemId >= 601 && $itemId <= 615) && !($itemId >= 502 && $itemId <= 503))
		{
			$build = $this->planet->getBuild($itemId);

			if ($build && $build['level'] > 0)
			{
				$DestroyTime = Building::getBuildingTime($this->user, $this->planet, $itemId) / 2;

				if ($DestroyTime < 1)
					$DestroyTime = 1;

				$parse['levelvalue'] = $build['level'];
				$parse['destroy'] = Building::getElementPrice(Building::getBuildingPrice($this->user, $this->planet, $itemId, true, true), $this->planet);
				$parse['destroytime'] = Format::time($DestroyTime);

				$this->view->setVar('parse', $parse);
				$this->view->partial('info/buildings_destroy');
			}
		}

		return $parse['name'];
	}
}