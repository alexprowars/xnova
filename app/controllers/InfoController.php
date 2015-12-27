<?php

namespace App\Controllers;

use App\Helpers;
use App\Lang;

class InfosController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		if ($this->request->getQuery('gid'))
			$html = $this->ShowBuildingInfoPage(user::get(), $this->planet->data, $this->request->getQuery('gid'));
		else
			$html = '';

		$this->tag->setTitle($html);
		$this->showTopPanel(false);
	}

	private function ShowRapidFireTo ($BuildID)
	{
		global $CombatCaps, $reslist;

		$ResultString = "";

		$res = array_merge($reslist['fleet'], $reslist['defense']);

		foreach ($res AS $Type)
		{
			if (isset($CombatCaps[$BuildID]['sd'][$Type]) && $CombatCaps[$BuildID]['sd'][$Type] > 1)
			{
				$ResultString .= _getText('nfo_rf_again') . " <font color=\"#00ff00\">" . $CombatCaps[$BuildID]['sd'][$Type] . "</font> единиц " ._getText('tech', $Type) . "<br>";
			}
		}

		return $ResultString;
	}

	private function ShowRapidFireFrom ($BuildID)
	{
		global $CombatCaps, $reslist;

		$ResultString = "";

		$res = array_merge($reslist['fleet'], $reslist['defense']);

		foreach ($res AS $Type)
		{
			if (isset($CombatCaps[$Type]['sd'][$BuildID]) && $CombatCaps[$Type]['sd'][$BuildID] > 1)
			{
				$ResultString .= _getText('tech', $Type) . " " . _getText('nfo_rf_from') . " <font color=\"#ff0000\">" . $CombatCaps[$Type]['sd'][$BuildID] . "</font> единиц<br>";
			}
		}

		return $ResultString;
	}

	private function BuildFleetListRows ($CurrentPlanet)
	{
		global $resource, $reslist;

		$CurrIdx = 1;
		$Result = array();
		foreach ($reslist['fleet'] AS $Ship)
		{
			if (isset($resource[$Ship]) && $CurrentPlanet[$resource[$Ship]] > 0)
			{
				$bloc = array();
				$bloc['idx'] = $CurrIdx;
				$bloc['fleet_id'] = $Ship;
				$bloc['fleet_name'] = _getText('tech', $Ship);
				$bloc['fleet_max'] = Helpers::pretty_number($CurrentPlanet[$resource[$Ship]]);
				$Result[] = $bloc;
				$CurrIdx++;
			}
		}
		return $Result;
	}

	private function BuildJumpableMoonCombo ($CurrentUser, $CurrentPlanet)
	{
		global $resource;

		$MoonList = $this->db->query("SELECT `id`, `name`, `system`, `galaxy`, `planet`, `sprungtor`, `last_jump_time` FROM game_planets WHERE (`planet_type` = '3' OR `planet_type` = '5') AND `id_owner` = '" . $CurrentUser['id'] . "';");

		$Combo = "";

		while ($CurMoon = $MoonList->fetch())
		{
			if ($CurMoon['id'] != $CurrentPlanet['id'])
			{
				$RestString = GetNextJumpWaitTime($CurMoon);

				if ($CurMoon[$resource[43]] >= 1)
				{
					$Combo .= "<option value=\"" . $CurMoon['id'] . "\">[" . $CurMoon['galaxy'] . ":" . $CurMoon['system'] . ":" . $CurMoon['planet'] . "] " . $CurMoon['name'] . $RestString['string'] . "</option>\n";
				}
			}
		}
		return $Combo;
	}

	private function BuildFleetCombo ($CurrentUser, $CurrentPlanet)
	{
		$MoonList = $this->db->query("SELECT * FROM game_fleets WHERE `fleet_end_galaxy` = " . $CurrentPlanet['galaxy'] . " AND `fleet_end_system` = " . $CurrentPlanet['system'] . " AND `fleet_end_planet` = " . $CurrentPlanet['planet'] . " AND `fleet_end_type` = " . $CurrentPlanet['planet_type'] . " AND `fleet_mess` = 3 AND `fleet_owner` = '" . $CurrentUser['id'] . "';");

		$Combo = "";

		while ($CurMoon = $MoonList->fetch())
		{
			$Combo .= "<option value=\"" . $CurMoon['fleet_id'] . "\">[" . $CurMoon['fleet_start_galaxy'] . ":" . $CurMoon['fleet_start_system'] . ":" . $CurMoon['fleet_start_planet'] . "] " . $CurMoon['fleet_owner_name'] . "</option>\n";
		}

		return $Combo;
	}

	/**
	 * @param  $CurrentUser user
	 * @param  $CurrentPlanet
	 * @param  $BuildID
	 * @return array
	 */
	private function ShowProductionTable ($CurrentUser, $CurrentPlanet, $BuildID)
	{
		global $resource;

		$CurrentBuildtLvl = $CurrentPlanet[$resource[$BuildID]];

		if ($BuildID != 42 && !($BuildID >= 22 && $BuildID <= 24))
		{
			$BuildLevelFactor = $CurrentPlanet[$resource[$BuildID] . "_porcent"];
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
				$bloc['build_range'] = floor((BASE_STORAGE_SIZE + floor(50000 * round(pow(1.6, $BuildLevel)))) * $CurrentUser->bonusValue('storage')) / 1000;
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
	 * @param $CurrentUser user
	 * @param $CurrentPlanet array
	 * @param $BuildID int
	 * @return array|string
	 */
	private function ShowBuildingInfoPage ($CurrentUser, $CurrentPlanet, $BuildID)
	{
		global $resource, $pricelist, $CombatCaps, $reslist;

		Lang::includeLang('infos');

		$parse = array();

		if (!_getText('tech', $BuildID))
			$this->message('Мы не сможем дать вам эту информацию', 'Ошибка', '?set=overview', 2);

		$parse['name'] = _getText('tech', $BuildID);
		$parse['image'] = $BuildID;
		$parse['description'] = _getText('info', $BuildID);

		if (($BuildID >= 1 && $BuildID <= 4) || $BuildID == 12 || $BuildID == 42 || ($BuildID >= 22 && $BuildID <= 24))
		{
			$this->view->pick('info/info_buildings_table');
			$parse['table_data'] = $this->ShowProductionTable($CurrentUser, $CurrentPlanet, $BuildID);
			$this->view->setVar('parse', $parse, 'info_buildings_table');
		}
		elseif (($BuildID >= 14 && $BuildID <= 34) || $BuildID == 6 || $BuildID == 43 || $BuildID == 44 || $BuildID == 41 || ($BuildID >= 106 && $BuildID <= 199))
		{
			$this->view->pick('info/info_buildings');

			if ($BuildID == 34)
			{
				$parse['msg'] = '';

				if (isset($_POST['send']) && isset($_POST['jmpto']))
				{
					$flid = intval($_POST['jmpto']);

					$query = $this->db->query("SELECT * FROM game_fleets WHERE fleet_id = '" . $flid . "' AND fleet_end_galaxy = " . $CurrentPlanet['galaxy'] . " AND fleet_end_system = " . $CurrentPlanet['system'] . " AND fleet_end_planet = " . $CurrentPlanet['planet'] . " AND fleet_end_type = " . $CurrentPlanet['planet_type'] . " AND `fleet_mess` = 3")->fetch();

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
								$tt += $pricelist[$temp2[0]]['stay'] * $temp2[1];
							}
						}
						$max = $CurrentPlanet[$resource[$BuildID]] * 10000;
						if ($max > $CurrentPlanet['deuterium'])
							$cur = $CurrentPlanet['deuterium'];
						else
							$cur = $max;

						$times = round(($cur / $tt) * 3600);
						$CurrentPlanet['deuterium'] -= $cur;
						$this->db->query("UPDATE game_fleets SET fleet_end_stay = fleet_end_stay + " . $times . ", fleet_end_time = fleet_end_time + " . $times . " WHERE fleet_id = '" . $flid . "'");

						$parse['msg'] = "<font color=red>Ракета с дейтерием отправлена на орбиту вашей планете</font>";
					}
				}

				if ($CurrentPlanet[$resource[$BuildID]] > 0)
				{
					if (!$parse['msg'])
						$parse['msg'] = "Выберите флот для отправки дейтерия";

					$parse['fleet'] = $this->BuildFleetCombo($CurrentUser->data, $CurrentPlanet);
					$parse['need'] = ($CurrentPlanet[$resource[$BuildID]] * 10000);

					$this->view->pick('info/info_buildings_ally');
					$this->view->setVar('parse', $parse);
				}
			}

			$this->setTemplateName('info/info_buildings');
			$this->view->setVar('parse', $parse);

		}
		elseif (in_array($BuildID, $reslist['fleet']))
		{
			$this->view->pick('info/info_buildings_fleet');

			$parse['hull_pt']  = floor(($pricelist[$BuildID]['metal'] + $pricelist[$BuildID]['crystal']) / 10);
			$parse['~hull_pt'] = $parse['hull_pt'];
			$parse['hull_pt']  = Helpers::pretty_number($parse['hull_pt']) . ' (' . Helpers::pretty_number(round($parse['hull_pt'] * (1 + $CurrentUser->data['defence_tech'] * 0.05 + (($CombatCaps[$BuildID]['power_up'] * ((isset($CurrentUser->data['fleet_' . $BuildID])) ? $CurrentUser->data['fleet_' . $BuildID] : 0)) / 100)))) . ')';

			$attTech = 1 + (((isset($CurrentUser->data['fleet_' . $BuildID])) ? $CurrentUser->data['fleet_' . $BuildID] : 0) * ($CombatCaps[$BuildID]['power_up'] / 100)) + $CurrentUser->data['military_tech'] * 0.05;

			if ($CombatCaps[$BuildID]['type_gun'] == 1)
				$attTech += $CurrentUser->data['laser_tech'] * 0.05;
			elseif ($CombatCaps[$BuildID]['type_gun'] == 2)
				$attTech += $CurrentUser->data['ionic_tech'] * 0.05;
			elseif ($CombatCaps[$BuildID]['type_gun'] == 3)
				$attTech += $CurrentUser->data['buster_tech'] * 0.05;

			include_once(APP_PATH.'functions/functions.php');
			// Устанавливаем обновлённые двигателя кораблей
			SetShipsEngine($CurrentUser->data);

			$parse['rf_info_to']  = $this->ShowRapidFireTo($BuildID);
			$parse['rf_info_fr']  = $this->ShowRapidFireFrom($BuildID);

			$parse['attack_pt'] = Helpers::pretty_number($CombatCaps[$BuildID]['attack']) . ' (' . Helpers::pretty_number(round($CombatCaps[$BuildID]['attack'] * $attTech)) . ')';
			$parse['shield_pt'] = Helpers::pretty_number($CombatCaps[$BuildID]['shield']);
			$parse['capacity_pt'] = Helpers::pretty_number($CombatCaps[$BuildID]['capacity']);
			$parse['base_speed'] = Helpers::pretty_number($CombatCaps[$BuildID]['speed']) . ' (' . Helpers::pretty_number(GetFleetMaxSpeed('', $BuildID, $CurrentUser)) . ')';
			$parse['base_conso'] = Helpers::pretty_number($CombatCaps[$BuildID]['consumption']);
			$parse['block'] = $CombatCaps[$BuildID]['power_armour'];
			$parse['upgrade'] = $CombatCaps[$BuildID]['power_up'];
			$parse['met'] = Helpers::pretty_number($pricelist[$BuildID]['metal']) . ' (' . Helpers::pretty_number($pricelist[$BuildID]['metal'] * $CurrentUser->bonusValue('res_fleet')) . ')';
			$parse['cry'] = Helpers::pretty_number($pricelist[$BuildID]['crystal']) . ' (' . Helpers::pretty_number($pricelist[$BuildID]['crystal'] * $CurrentUser->bonusValue('res_fleet')) . ')';
			$parse['deu'] = Helpers::pretty_number($pricelist[$BuildID]['deuterium']) . ' (' . Helpers::pretty_number($pricelist[$BuildID]['deuterium'] * $CurrentUser->bonusValue('res_fleet')) . ')';

			$engine = array('', 'Ракетный', 'Импульсный', 'Гиперпространственный');
			$gun = array('', 'Лазерное', 'Ионное', 'Плазменное');
			$armour = array('', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная');

			$parse['base_engine'] = $engine[$CombatCaps[$BuildID]['type_engine']];
			$parse['gun'] = $gun[$CombatCaps[$BuildID]['type_gun']];
			$parse['armour'] = $armour[$CombatCaps[$BuildID]['type_armour']];

			$this->view->setVar('parse', $parse);

		}
		elseif (in_array($BuildID, $reslist['defense']))
		{
			$this->view->pick('info/info_buildings_defence');

			$parse['element_typ'] = _getText('tech', 400);
			$parse['hull_pt']  = floor(($pricelist[$BuildID]['metal'] + $pricelist[$BuildID]['crystal']) / 10);
			$parse['~hull_pt'] = $parse['hull_pt'];
			$parse['hull_pt']  = Helpers::pretty_number($parse['hull_pt']) . ' (' . Helpers::pretty_number(round($parse['hull_pt'] * (1 + $CurrentUser->data['defence_tech'] * 0.05 + (((isset($CombatCaps[$BuildID]['power_up']) ? $CombatCaps[$BuildID]['power_up'] : 0) * ((isset($CurrentUser->data['fleet_' . $BuildID])) ? $CurrentUser->data['fleet_' . $BuildID] : 0)) / 100)))) . ')';

			if (isset($CombatCaps[$BuildID]['shield']))
				$parse['shield_pt'] = Helpers::pretty_number($CombatCaps[$BuildID]['shield']);
			else
				$parse['shield_pt'] = '';

			$attTech = 1 + (((isset($CurrentUser->data['fleet_' . $BuildID])) ? $CurrentUser->data['fleet_' . $BuildID] : 0) * ((isset($CombatCaps[$BuildID]['power_up']) ? $CombatCaps[$BuildID]['power_up'] : 0) / 100)) + $CurrentUser->data['military_tech'] * 0.05;

			$parse['attack_pt'] = Helpers::pretty_number($CombatCaps[$BuildID]['attack']) . ' (' . Helpers::pretty_number(round($CombatCaps[$BuildID]['attack'] * $attTech)) . ')';
			$parse['met'] = Helpers::pretty_number($pricelist[$BuildID]['metal']);
			$parse['cry'] = Helpers::pretty_number($pricelist[$BuildID]['crystal']);
			$parse['deu'] = Helpers::pretty_number($pricelist[$BuildID]['deuterium']);

			if ($BuildID >= 400 && $BuildID < 500)
			{
				$gun = array('', 'Лазерное', 'Ионное', 'Плазменное');
				$armour = array('', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная');

				$parse['gun'] = $gun[$CombatCaps[$BuildID]['type_gun']];
				$parse['armour'] = $armour[$CombatCaps[$BuildID]['type_armour']];

				$parse['speedBattle'] = array();

				foreach ($reslist['fleet'] AS $Type)
				{
					if (!isset($CombatCaps[$Type]))
						continue;

					$enemy_durability = ($pricelist[$Type]['metal'] + $pricelist[$Type]['crystal']) / 10;

					$rapid = $CombatCaps[$BuildID]['attack'] * (isset($CombatCaps[$BuildID]['amplify'][$Type]) ? $CombatCaps[$BuildID]['amplify'][$Type] : 1) / $enemy_durability;

					if ($rapid >= 1)
						$parse['speedBattle'][$Type]['TO'] = floor($rapid);

					$rapid = $CombatCaps[$Type]['attack'] * (isset($CombatCaps[$Type]['amplify'][$BuildID]) ? $CombatCaps[$Type]['amplify'][$BuildID] : 1) / $parse['hull_pt'];

					if ($rapid >= 1)
						$parse['speedBattle'][$Type]['FROM'] = floor($rapid);
				}
			}

			$this->view->setVar('parse', $parse);

			if ($BuildID >= 500 && $BuildID < 600)
			{
				if (isset($_POST['form']))
				{
					$_POST['502'] = abs(intval($_POST['502']));
					$_POST['503'] = abs(intval($_POST['503']));

					if ($_POST['502'] > $CurrentPlanet[$resource[502]])
					{
						$_POST['502'] = $CurrentPlanet[$resource[502]];
					}
					if ($_POST['503'] > $CurrentPlanet[$resource[503]])
					{
						$_POST['503'] = $CurrentPlanet[$resource[503]];
					}
					$this->db->query("UPDATE game_planets SET `" . $resource[502] . "` = `" . $resource[502] . "` - " . $_POST['502'] . " , `" . $resource[503] . "` = `" . $resource[503] . "` - " . $_POST['503'] . " WHERE `id` = " . $CurrentPlanet['id'] . ";");
					$CurrentPlanet[$resource[502]] -= $_POST['502'];
					$CurrentPlanet[$resource[503]] -= $_POST['503'];
				}
				$pars = array();
				$pars['max_mis'] = $CurrentPlanet[$resource[44]] * 10;
				$pars['int_miss'] = _getText('tech', 502) . ': ' . $CurrentPlanet[$resource[502]];
				$pars['plant_miss'] = _getText('tech', 503) . ': ' . $CurrentPlanet[$resource[503]];

				$this->view->pick('info/info_missile');
				$this->view->setVar('parse', $pars);
			}

		}
		elseif (in_array($BuildID, $reslist['officier']))
		{
			$this->view->pick('info/info_officier');
			$this->view->setVar('parse', $parse);
		}
		elseif ($BuildID >= 701 && $BuildID <= 704)
		{

			$parse['image'] = $BuildID - 700;

			$this->view->pick('info/info_race');
			$this->view->setVar('parse', $parse);
		}

		if ($BuildID <= 44 && $BuildID != 33 && $BuildID != 41 && !($BuildID >= 601 && $BuildID <= 615) && !($BuildID >= 502 && $BuildID <= 503))
		{
			if ($CurrentPlanet[$resource[$BuildID]] > 0)
			{
				$DestroyTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $BuildID) / 2;

				if ($DestroyTime < 1)
					$DestroyTime = 1;

				$parse['levelvalue'] = $CurrentPlanet[$resource[$BuildID]];
				$parse['destroy'] = GetElementPrice(GetBuildingPrice($CurrentUser, $CurrentPlanet, $BuildID, true, true), $CurrentPlanet);
				$parse['destroytime'] = Helpers::pretty_time($DestroyTime);

				$this->view->pick('info/info_buildings_destroy');
				$this->view->setVar('parse', $parse);
			}
		}

		return $parse['name'];
	}
}

?>