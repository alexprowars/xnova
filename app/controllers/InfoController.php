<?php

namespace App\Controllers;

use App\Building;
use App\Fleet;
use App\Helpers;
use App\Lang;

class InfoController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		if ($this->request->getQuery('gid'))
			$html = $this->ShowBuildingInfoPage($this->request->getQuery('gid'));
		else
			$html = '';

		$this->tag->setTitle($html);
		$this->showTopPanel(false);
	}

	private function ShowRapidFireTo ($BuildID)
	{
		$ResultString = "";

		$res = array_merge($this->game->reslist['fleet'], $this->game->reslist['defense']);

		foreach ($res AS $Type)
		{
			if (isset($this->game->CombatCaps[$BuildID]['sd'][$Type]) && $this->game->CombatCaps[$BuildID]['sd'][$Type] > 1)
			{
				$ResultString .= _getText('nfo_rf_again') . " <font color=\"#00ff00\">" . $this->game->CombatCaps[$BuildID]['sd'][$Type] . "</font> единиц " ._getText('tech', $Type) . "<br>";
			}
		}

		return $ResultString;
	}

	private function ShowRapidFireFrom ($BuildID)
	{
		$ResultString = "";

		$res = array_merge($this->game->reslist['fleet'], $this->game->reslist['defense']);

		foreach ($res AS $Type)
		{
			if (isset($this->game->CombatCaps[$Type]['sd'][$BuildID]) && $this->game->CombatCaps[$Type]['sd'][$BuildID] > 1)
			{
				$ResultString .= _getText('tech', $Type) . " " . _getText('nfo_rf_from') . " <font color=\"#ff0000\">" . $this->game->CombatCaps[$Type]['sd'][$BuildID] . "</font> единиц<br>";
			}
		}

		return $ResultString;
	}

	private function BuildFleetCombo ()
	{
		$MoonList = $this->db->query("SELECT * FROM game_fleets WHERE `fleet_end_galaxy` = " . $this->planet->galaxy . " AND `fleet_end_system` = " . $this->planet->system . " AND `fleet_end_planet` = " . $this->planet->planet . " AND `fleet_end_type` = " . $this->planet->planet_type . " AND `fleet_mess` = 3 AND `fleet_owner` = '" . $this->user->id . "';");

		$Combo = "";

		while ($CurMoon = $MoonList->fetch())
		{
			$Combo .= "<option value=\"" . $CurMoon['fleet_id'] . "\">[" . $CurMoon['fleet_start_galaxy'] . ":" . $CurMoon['fleet_start_system'] . ":" . $CurMoon['fleet_start_planet'] . "] " . $CurMoon['fleet_owner_name'] . "</option>\n";
		}

		return $Combo;
	}

	/**
	 * @param  $BuildID
	 * @return array
	 */
	private function ShowProductionTable ($BuildID)
	{
		$CurrentBuildtLvl = $this->planet->{$this->game->resource[$BuildID]};

		$ActualNeed = $ActualProd = 0;

		if ($BuildID != 42 && !($BuildID >= 22 && $BuildID <= 24))
		{
			$BuildLevelFactor = $this->planet->{$this->game->resource[$BuildID] . "_porcent"};
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

		$Table = array();

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
	 */
	private function ShowBuildingInfoPage ($BuildID)
	{
		Lang::includeLang('infos');

		$parse = array();

		if (!_getText('tech', $BuildID))
			$this->message('Мы не сможем дать вам эту информацию', 'Ошибка', '/overview/', 2);

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

					$query = $this->db->query("SELECT * FROM game_fleets WHERE fleet_id = '" . $flid . "' AND fleet_end_galaxy = " . $this->planet->galaxy . " AND fleet_end_system = " . $this->planet->system . " AND fleet_end_planet = " . $this->planet->planet . " AND fleet_end_type = " . $this->planet->planet_type . " AND `fleet_mess` = 3")->fetch();

					if (!$query['fleet_id'])
						$parse['msg'] = "<font color=red>Флот отсутствует у планеты</font>";
					else
					{
						$tt = 0;
						$temp = explode(';', $query['fleet_array']);
						foreach ($temp as $temp2)
						{
							$temp2 = explode(',', $temp2);
							if ($temp2[0] > 100)
							{
								$tt += $this->game->pricelist[$temp2[0]]['stay'] * $temp2[1];
							}
						}
						$max = $this->planet->{$this->game->resource[$BuildID]} * 10000;
						if ($max > $this->planet->deuterium)
							$cur = $this->planet->deuterium;
						else
							$cur = $max;

						$times = round(($cur / $tt) * 3600);
						$this->planet->deuterium -= $cur;
						$this->db->query("UPDATE game_fleets SET fleet_end_stay = fleet_end_stay + " . $times . ", fleet_end_time = fleet_end_time + " . $times . " WHERE fleet_id = '" . $flid . "'");

						$parse['msg'] = "<font color=red>Ракета с дейтерием отправлена на орбиту вашей планете</font>";
					}
				}

				if ($this->planet->{$this->game->resource[$BuildID]} > 0)
				{
					if (!$parse['msg'])
						$parse['msg'] = "Выберите флот для отправки дейтерия";

					$parse['fleet'] = $this->BuildFleetCombo();
					$parse['need'] = ($this->planet->{$this->game->resource[$BuildID]} * 10000);

					$this->view->setVar('parse', $parse);
					$this->view->partial('info/buildings_ally');
				}
			}

			$this->view->setVar('parse', $parse);
			$this->view->partial('info/buildings');
		}
		elseif (in_array($BuildID, $this->game->reslist['fleet']))
		{
			$parse['hull_pt']  = floor(($this->game->pricelist[$BuildID]['metal'] + $this->game->pricelist[$BuildID]['crystal']) / 10);
			$parse['~hull_pt'] = $parse['hull_pt'];
			$parse['hull_pt']  = Helpers::pretty_number($parse['hull_pt']) . ' (' . Helpers::pretty_number(round($parse['hull_pt'] * (1 + $this->user->defence_tech * 0.05 + (($this->game->CombatCaps[$BuildID]['power_up'] * ((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0)) / 100)))) . ')';

			$attTech = 1 + (((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0) * ($this->game->CombatCaps[$BuildID]['power_up'] / 100)) + $this->user->military_tech * 0.05;

			if ($this->game->CombatCaps[$BuildID]['type_gun'] == 1)
				$attTech += $this->user->laser_tech * 0.05;
			elseif ($this->game->CombatCaps[$BuildID]['type_gun'] == 2)
				$attTech += $this->user->ionic_tech * 0.05;
			elseif ($this->game->CombatCaps[$BuildID]['type_gun'] == 3)
				$attTech += $this->user->buster_tech * 0.05;

			// Устанавливаем обновлённые двигателя кораблей
			Fleet::SetShipsEngine($this->user);

			$parse['rf_info_to']  = $this->ShowRapidFireTo($BuildID);
			$parse['rf_info_fr']  = $this->ShowRapidFireFrom($BuildID);

			$parse['attack_pt'] = Helpers::pretty_number($this->game->CombatCaps[$BuildID]['attack']) . ' (' . Helpers::pretty_number(round($this->game->CombatCaps[$BuildID]['attack'] * $attTech)) . ')';
			$parse['shield_pt'] = Helpers::pretty_number($this->game->CombatCaps[$BuildID]['shield']);
			$parse['capacity_pt'] = Helpers::pretty_number($this->game->CombatCaps[$BuildID]['capacity']);
			$parse['base_speed'] = Helpers::pretty_number($this->game->CombatCaps[$BuildID]['speed']) . ' (' . Helpers::pretty_number(Fleet::GetFleetMaxSpeed('', $BuildID, $this->user)) . ')';
			$parse['base_conso'] = Helpers::pretty_number($this->game->CombatCaps[$BuildID]['consumption']);
			$parse['block'] = $this->game->CombatCaps[$BuildID]['power_armour'];
			$parse['upgrade'] = $this->game->CombatCaps[$BuildID]['power_up'];
			$parse['met'] = Helpers::pretty_number($this->game->pricelist[$BuildID]['metal']) . ' (' . Helpers::pretty_number($this->game->pricelist[$BuildID]['metal'] * $this->user->bonusValue('res_fleet')) . ')';
			$parse['cry'] = Helpers::pretty_number($this->game->pricelist[$BuildID]['crystal']) . ' (' . Helpers::pretty_number($this->game->pricelist[$BuildID]['crystal'] * $this->user->bonusValue('res_fleet')) . ')';
			$parse['deu'] = Helpers::pretty_number($this->game->pricelist[$BuildID]['deuterium']) . ' (' . Helpers::pretty_number($this->game->pricelist[$BuildID]['deuterium'] * $this->user->bonusValue('res_fleet')) . ')';

			$engine = array('', 'Ракетный', 'Импульсный', 'Гиперпространственный');
			$gun = array('', 'Лазерное', 'Ионное', 'Плазменное');
			$armour = array('', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная');

			$parse['base_engine'] = $engine[$this->game->CombatCaps[$BuildID]['type_engine']];
			$parse['gun'] = $gun[$this->game->CombatCaps[$BuildID]['type_gun']];
			$parse['armour'] = $armour[$this->game->CombatCaps[$BuildID]['type_armour']];

			$this->view->setVar('parse', $parse);
			$this->view->partial('info/buildings_fleet');
		}
		elseif (in_array($BuildID, $this->game->reslist['defense']))
		{
			$parse['element_typ'] = _getText('tech', 400);
			$parse['hull_pt']  = floor(($this->game->pricelist[$BuildID]['metal'] + $this->game->pricelist[$BuildID]['crystal']) / 10);
			$parse['~hull_pt'] = $parse['hull_pt'];
			$parse['hull_pt']  = Helpers::pretty_number($parse['hull_pt']) . ' (' . Helpers::pretty_number(round($parse['hull_pt'] * (1 + $this->user->defence_tech * 0.05 + (((isset($this->game->CombatCaps[$BuildID]['power_up']) ? $this->game->CombatCaps[$BuildID]['power_up'] : 0) * ((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0)) / 100)))) . ')';

			if (isset($this->game->CombatCaps[$BuildID]['shield']))
				$parse['shield_pt'] = Helpers::pretty_number($this->game->CombatCaps[$BuildID]['shield']);
			else
				$parse['shield_pt'] = '';

			$attTech = 1 + (((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0) * ((isset($this->game->CombatCaps[$BuildID]['power_up']) ? $this->game->CombatCaps[$BuildID]['power_up'] : 0) / 100)) + $this->user->military_tech * 0.05;

			$parse['attack_pt'] = Helpers::pretty_number($this->game->CombatCaps[$BuildID]['attack']) . ' (' . Helpers::pretty_number(round($this->game->CombatCaps[$BuildID]['attack'] * $attTech)) . ')';
			$parse['met'] = Helpers::pretty_number($this->game->pricelist[$BuildID]['metal']);
			$parse['cry'] = Helpers::pretty_number($this->game->pricelist[$BuildID]['crystal']);
			$parse['deu'] = Helpers::pretty_number($this->game->pricelist[$BuildID]['deuterium']);

			if ($BuildID >= 400 && $BuildID < 500)
			{
				$gun = array('', 'Лазерное', 'Ионное', 'Плазменное');
				$armour = array('', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная');

				$parse['gun'] = $gun[$this->game->CombatCaps[$BuildID]['type_gun']];
				$parse['armour'] = $armour[$this->game->CombatCaps[$BuildID]['type_armour']];

				$parse['speedBattle'] = array();

				foreach ($this->game->reslist['fleet'] AS $Type)
				{
					if (!isset($this->game->CombatCaps[$Type]))
						continue;

					$enemy_durability = ($this->game->pricelist[$Type]['metal'] + $this->game->pricelist[$Type]['crystal']) / 10;

					$rapid = $this->game->CombatCaps[$BuildID]['attack'] * (isset($this->game->CombatCaps[$BuildID]['amplify'][$Type]) ? $this->game->CombatCaps[$BuildID]['amplify'][$Type] : 1) / $enemy_durability;

					if ($rapid >= 1)
						$parse['speedBattle'][$Type]['TO'] = floor($rapid);

					$rapid = $this->game->CombatCaps[$Type]['attack'] * (isset($this->game->CombatCaps[$Type]['amplify'][$BuildID]) ? $this->game->CombatCaps[$Type]['amplify'][$BuildID] : 1) / $parse['hull_pt'];

					if ($rapid >= 1)
						$parse['speedBattle'][$Type]['FROM'] = floor($rapid);
				}
			}

			$this->view->setVar('parse', $parse);
			$this->view->partial('info/buildings_defence');

			if ($BuildID >= 500 && $BuildID < 600)
			{
				if (isset($_POST['form']))
				{
					$_POST['502'] = abs(intval($_POST['502']));
					$_POST['503'] = abs(intval($_POST['503']));

					if ($_POST['502'] > $this->planet->{$this->game->resource[502]})
						$_POST['502'] = $this->planet->{$this->game->resource[502]};
					if ($_POST['503'] > $this->planet->{$this->game->resource[503]})
						$_POST['503'] = $this->planet->{$this->game->resource[503]};

					$this->db->query("UPDATE game_planets SET `" . $this->game->resource[502] . "` = `" . $this->game->resource[502] . "` - " . $_POST['502'] . " , `" . $this->game->resource[503] . "` = `" . $this->game->resource[503] . "` - " . $_POST['503'] . " WHERE `id` = " . $this->planet->id . ";");

					$this->planet->{$this->game->resource[502]} -= $_POST['502'];
					$this->planet->{$this->game->resource[503]} -= $_POST['503'];
				}
				$pars = array();
				$pars['max_mis'] = $this->planet->{$this->game->resource[44]} * 10;
				$pars['int_miss'] = _getText('tech', 502) . ': ' . $this->planet->{$this->game->resource[502]};
				$pars['plant_miss'] = _getText('tech', 503) . ': ' . $this->planet->{$this->game->resource[503]};

				$this->view->setVar('parse', $pars);
				$this->view->partial('info/missile');
			}
		}
		elseif (in_array($BuildID, $this->game->reslist['officier']))
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
			if ($this->planet->{$this->game->resource[$BuildID]} > 0)
			{
				$DestroyTime = Building::GetBuildingTime($this->user, $this->planet, $BuildID) / 2;

				if ($DestroyTime < 1)
					$DestroyTime = 1;

				$parse['levelvalue'] = $this->planet->{$this->game->resource[$BuildID]};
				$parse['destroy'] = Building::GetElementPrice(Building::GetBuildingPrice($this->user, $this->planet, $BuildID, true, true), $this->planet);
				$parse['destroytime'] = Helpers::pretty_time($DestroyTime);

				$this->view->setVar('parse', $parse);
				$this->view->partial('info/buildings_destroy');
			}
		}

		return $parse['name'];
	}
}

?>