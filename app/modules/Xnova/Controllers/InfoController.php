<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Building;
use Xnova\Exceptions\ErrorException;
use Xnova\Fleet;
use Xnova\Helpers;
use Friday\Core\Lang;
use Xnova\Controller;

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

		$res = array_merge($this->registry->reslist['fleet'], $this->registry->reslist['defense']);

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

		$res = array_merge($this->registry->reslist['fleet'], $this->registry->reslist['defense']);

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
		$CurrentBuildtLvl = $this->planet->{$this->registry->resource[$BuildID]};

		$ActualNeed = $ActualProd = 0;

		if ($BuildID != 42 && !($BuildID >= 22 && $BuildID <= 24))
		{
			$BuildLevelFactor = $this->planet->{$this->registry->resource[$BuildID] . "_porcent"};
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
					$bloc['build_prod'] = Helpers::pretty_number(floor($Prod[$BuildID]));
					$bloc['build_prod_diff'] = Helpers::colorNumber(Helpers::pretty_number(floor($Prod[$BuildID] - $ActualProd)));
					$bloc['build_need'] = Helpers::colorNumber(Helpers::pretty_number(floor($Prod[4])));
					$bloc['build_need_diff'] = Helpers::colorNumber(Helpers::pretty_number(floor($Prod[4] - $ActualNeed)));
				}
				else
				{
					$bloc['build_prod'] = Helpers::pretty_number(floor($Prod[4]));
					$bloc['build_prod_diff'] = Helpers::colorNumber(Helpers::pretty_number(floor($Prod[4] - $ActualProd)));
					$bloc['build_need'] = Helpers::colorNumber(Helpers::pretty_number(floor($Prod[3])));
					$bloc['build_need_diff'] = Helpers::colorNumber(Helpers::pretty_number(floor($Prod[3] - $ActualNeed)));
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
	 * @param $BuildID int
	 * @return array|string
	 * @throws ErrorException
	 */
	private function ShowBuildingInfoPage ($BuildID)
	{
		Lang::includeLang('infos', 'xnova');

		$parse = [];

		if (!_getText('info', $BuildID, true))
			throw new ErrorException('Мы не сможем дать вам эту информацию');

		$parse['name'] = _getText('tech', $BuildID);
		$parse['image'] = $BuildID;
		$parse['description'] = _getText('info', $BuildID);

		if (($BuildID >= 1 && $BuildID <= 4) || $BuildID == 12 || $BuildID == 42 || ($BuildID >= 22 && $BuildID <= 24))
		{
			$parse['table_data'] = $this->ShowProductionTable($BuildID);
			$this->view->setVar('parse', $parse);
			$this->view->partial('info/buildings_table');
		}
		elseif (($BuildID >= 14 && $BuildID <= 34) || $BuildID == 6 || $BuildID == 43 || $BuildID == 44 || $BuildID == 41 || ($BuildID >= 106 && $BuildID <= 199))
		{
			if ($BuildID == 34)
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
								$tt += $this->registry->pricelist[$type]['stay'] * $ship['cnt'];
						}

						$max = $this->planet->{$this->registry->resource[$BuildID]} * 10000;

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

				if ($this->planet->{$this->registry->resource[$BuildID]} > 0)
				{
					if (!$parse['msg'])
						$parse['msg'] = "Выберите флот для отправки дейтерия";

					$parse['fleet'] = $this->BuildFleetCombo();
					$parse['need'] = ($this->planet->{$this->registry->resource[$BuildID]} * 10000);

					$this->view->setVar('parse', $parse);
					$this->view->partial('info/buildings_ally');
				}
			}

			$this->view->setVar('parse', $parse);
			$this->view->partial('info/buildings');
		}
		elseif (in_array($BuildID, $this->registry->reslist['fleet']))
		{
			$parse['hull_pt']  = floor(($this->registry->pricelist[$BuildID]['metal'] + $this->registry->pricelist[$BuildID]['crystal']) / 10);
			$parse['~hull_pt'] = $parse['hull_pt'];
			$parse['hull_pt']  = Helpers::pretty_number($parse['hull_pt']) . ' (' . Helpers::pretty_number(round($parse['hull_pt'] * (1 + $this->user->defence_tech * 0.05 + (($this->registry->CombatCaps[$BuildID]['power_up'] * ((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0)) / 100)))) . ')';

			$attTech = 1 + (((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0) * ($this->registry->CombatCaps[$BuildID]['power_up'] / 100)) + $this->user->military_tech * 0.05;

			if ($this->registry->CombatCaps[$BuildID]['type_gun'] == 1)
				$attTech += $this->user->laser_tech * 0.05;
			elseif ($this->registry->CombatCaps[$BuildID]['type_gun'] == 2)
				$attTech += $this->user->ionic_tech * 0.05;
			elseif ($this->registry->CombatCaps[$BuildID]['type_gun'] == 3)
				$attTech += $this->user->buster_tech * 0.05;

			// Устанавливаем обновлённые двигателя кораблей
			Fleet::SetShipsEngine($this->user);

			$parse['rf_info_to']  = $this->ShowRapidFireTo($BuildID);
			$parse['rf_info_fr']  = $this->ShowRapidFireFrom($BuildID);

			$parse['attack_pt'] = Helpers::pretty_number($this->registry->CombatCaps[$BuildID]['attack']) . ' (' . Helpers::pretty_number(round($this->registry->CombatCaps[$BuildID]['attack'] * $attTech)) . ')';
			$parse['shield_pt'] = Helpers::pretty_number($this->registry->CombatCaps[$BuildID]['shield']);
			$parse['capacity_pt'] = Helpers::pretty_number($this->registry->CombatCaps[$BuildID]['capacity']);
			$parse['base_speed'] = Helpers::pretty_number($this->registry->CombatCaps[$BuildID]['speed']) . ' (' . Helpers::pretty_number(Fleet::GetFleetMaxSpeed('', $BuildID, $this->user)) . ')';
			$parse['base_conso'] = Helpers::pretty_number($this->registry->CombatCaps[$BuildID]['consumption']);
			$parse['block'] = $this->registry->CombatCaps[$BuildID]['power_armour'];
			$parse['upgrade'] = $this->registry->CombatCaps[$BuildID]['power_up'];
			$parse['met'] = Helpers::pretty_number($this->registry->pricelist[$BuildID]['metal']) . ' (' . Helpers::pretty_number($this->registry->pricelist[$BuildID]['metal'] * $this->user->bonusValue('res_fleet')) . ')';
			$parse['cry'] = Helpers::pretty_number($this->registry->pricelist[$BuildID]['crystal']) . ' (' . Helpers::pretty_number($this->registry->pricelist[$BuildID]['crystal'] * $this->user->bonusValue('res_fleet')) . ')';
			$parse['deu'] = Helpers::pretty_number($this->registry->pricelist[$BuildID]['deuterium']) . ' (' . Helpers::pretty_number($this->registry->pricelist[$BuildID]['deuterium'] * $this->user->bonusValue('res_fleet')) . ')';

			$engine = ['', 'Ракетный', 'Импульсный', 'Гиперпространственный'];
			$gun = ['', 'Лазерное', 'Ионное', 'Плазменное'];
			$armour = ['', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная'];

			$parse['base_engine'] = $engine[$this->registry->CombatCaps[$BuildID]['type_engine']];
			$parse['gun'] = $gun[$this->registry->CombatCaps[$BuildID]['type_gun']];
			$parse['armour'] = $armour[$this->registry->CombatCaps[$BuildID]['type_armour']];

			$this->view->setVar('parse', $parse);
			$this->view->partial('info/buildings_fleet');
		}
		elseif (in_array($BuildID, $this->registry->reslist['defense']))
		{
			$parse['element_typ'] = _getText('tech', 400);
			$parse['hull_pt']  = floor(($this->registry->pricelist[$BuildID]['metal'] + $this->registry->pricelist[$BuildID]['crystal']) / 10);
			$parse['~hull_pt'] = $parse['hull_pt'];
			$parse['hull_pt']  = Helpers::pretty_number($parse['hull_pt']) . ' (' . Helpers::pretty_number(round($parse['hull_pt'] * (1 + $this->user->defence_tech * 0.05 + (((isset($this->registry->CombatCaps[$BuildID]['power_up']) ? $this->registry->CombatCaps[$BuildID]['power_up'] : 0) * ((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0)) / 100)))) . ')';

			if (isset($this->registry->CombatCaps[$BuildID]['shield']))
				$parse['shield_pt'] = Helpers::pretty_number($this->registry->CombatCaps[$BuildID]['shield']);
			else
				$parse['shield_pt'] = '';

			$attTech = 1 + (((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0) * ((isset($this->registry->CombatCaps[$BuildID]['power_up']) ? $this->registry->CombatCaps[$BuildID]['power_up'] : 0) / 100)) + $this->user->military_tech * 0.05;

			$parse['attack_pt'] = Helpers::pretty_number($this->registry->CombatCaps[$BuildID]['attack']) . ' (' . Helpers::pretty_number(round($this->registry->CombatCaps[$BuildID]['attack'] * $attTech)) . ')';
			$parse['met'] = Helpers::pretty_number($this->registry->pricelist[$BuildID]['metal']);
			$parse['cry'] = Helpers::pretty_number($this->registry->pricelist[$BuildID]['crystal']);
			$parse['deu'] = Helpers::pretty_number($this->registry->pricelist[$BuildID]['deuterium']);

			if ($BuildID >= 400 && $BuildID < 500)
			{
				$gun = ['', 'Лазерное', 'Ионное', 'Плазменное'];
				$armour = ['', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная'];

				$parse['gun'] = $gun[$this->registry->CombatCaps[$BuildID]['type_gun']];
				$parse['armour'] = $armour[$this->registry->CombatCaps[$BuildID]['type_armour']];

				$parse['speedBattle'] = [];

				foreach ($this->registry->reslist['fleet'] AS $Type)
				{
					if (!isset($this->registry->CombatCaps[$Type]))
						continue;

					$enemy_durability = ($this->registry->pricelist[$Type]['metal'] + $this->registry->pricelist[$Type]['crystal']) / 10;

					$rapid = $this->registry->CombatCaps[$BuildID]['attack'] * (isset($this->registry->CombatCaps[$BuildID]['amplify'][$Type]) ? $this->registry->CombatCaps[$BuildID]['amplify'][$Type] : 1) / $enemy_durability;

					if ($rapid >= 1)
						$parse['speedBattle'][$Type]['TO'] = floor($rapid);

					$rapid = $this->registry->CombatCaps[$Type]['attack'] * (isset($this->registry->CombatCaps[$Type]['amplify'][$BuildID]) ? $this->registry->CombatCaps[$Type]['amplify'][$BuildID] : 1) / $parse['hull_pt'];

					if ($rapid >= 1)
						$parse['speedBattle'][$Type]['FROM'] = floor($rapid);
				}
			}

			$this->view->partial('info/buildings_defence');

			if ($BuildID >= 500 && $BuildID < 600)
			{
				if (isset($_POST['form']))
				{
					$_POST['502'] = abs(intval($_POST['502']));
					$_POST['503'] = abs(intval($_POST['503']));

					if ($_POST['502'] > $this->planet->{$this->registry->resource[502]})
						$_POST['502'] = $this->planet->{$this->registry->resource[502]};
					if ($_POST['503'] > $this->planet->{$this->registry->resource[503]})
						$_POST['503'] = $this->planet->{$this->registry->resource[503]};

					$this->planet->{$this->registry->resource[502]} -= $_POST['502'];
					$this->planet->{$this->registry->resource[503]} -= $_POST['503'];
					$this->planet->update();
				}

				$parse['max_mis'] = $this->planet->{$this->registry->resource[44]} * 10;
				$parse['int_miss'] = _getText('tech', 502) . ': ' . $this->planet->{$this->registry->resource[502]};
				$parse['plant_miss'] = _getText('tech', 503) . ': ' . $this->planet->{$this->registry->resource[503]};

				$this->view->partial('info/missile');
			}

			$this->view->setVar('parse', $parse);
		}
		elseif (in_array($BuildID, $this->registry->reslist['officier']))
		{
			$this->view->setVar('parse', $parse);
			$this->view->partial('info/officier');
		}
		elseif ($BuildID >= 701 && $BuildID <= 704)
		{
			$parse['image'] = $BuildID - 700;

			$this->view->setVar('parse', $parse);
			$this->view->partial('info/race');
		}

		if ($BuildID <= 44 && $BuildID != 33 && $BuildID != 41 && !($BuildID >= 601 && $BuildID <= 615) && !($BuildID >= 502 && $BuildID <= 503))
		{
			if ($this->planet->{$this->registry->resource[$BuildID]} > 0)
			{
				$DestroyTime = Building::GetBuildingTime($this->user, $this->planet, $BuildID) / 2;

				if ($DestroyTime < 1)
					$DestroyTime = 1;

				$parse['levelvalue'] = $this->planet->{$this->registry->resource[$BuildID]};
				$parse['destroy'] = Building::GetElementPrice(Building::GetBuildingPrice($this->user, $this->planet, $BuildID, true, true), $this->planet);
				$parse['destroytime'] = Helpers::pretty_time($DestroyTime);

				$this->view->setVar('parse', $parse);
				$this->view->partial('info/buildings_destroy');
			}
		}

		return $parse['name'];
	}
}