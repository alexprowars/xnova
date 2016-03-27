<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Building;
use App\Fleet;
use App\Helpers;
use App\Lang;

class InfoController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

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

		$res = array_merge($this->storage->reslist['fleet'], $this->storage->reslist['defense']);

		foreach ($res AS $Type)
		{
			if (isset($this->storage->CombatCaps[$BuildID]['sd'][$Type]) && $this->storage->CombatCaps[$BuildID]['sd'][$Type] > 1)
			{
				$ResultString .= _getText('nfo_rf_again') . " <font color=\"#00ff00\">" . $this->storage->CombatCaps[$BuildID]['sd'][$Type] . "</font> единиц " ._getText('tech', $Type) . "<br>";
			}
		}

		return $ResultString;
	}

	private function ShowRapidFireFrom ($BuildID)
	{
		$ResultString = "";

		$res = array_merge($this->storage->reslist['fleet'], $this->storage->reslist['defense']);

		foreach ($res AS $Type)
		{
			if (isset($this->storage->CombatCaps[$Type]['sd'][$BuildID]) && $this->storage->CombatCaps[$Type]['sd'][$BuildID] > 1)
			{
				$ResultString .= _getText('tech', $Type) . " " . _getText('nfo_rf_from') . " <font color=\"#ff0000\">" . $this->storage->CombatCaps[$Type]['sd'][$BuildID] . "</font> единиц<br>";
			}
		}

		return $ResultString;
	}

	private function BuildFleetCombo ()
	{
		$MoonList = \App\Models\Fleet::find(['end_galaxy = ?0 AND end_system = ?1 AND end_planet = ?2 AND end_type = ?3 AND mess = 3 AND owner = ?4', 'bind' => [$this->planet->galaxy, $this->planet->system, $this->planet->planet, $this->planet->planet_type, $this->user->id]]);

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
		$CurrentBuildtLvl = $this->planet->{$this->storage->resource[$BuildID]};

		$ActualNeed = $ActualProd = 0;

		if ($BuildID != 42 && !($BuildID >= 22 && $BuildID <= 24))
		{
			$BuildLevelFactor = $this->planet->{$this->storage->resource[$BuildID] . "_porcent"};
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
	 */
	private function ShowBuildingInfoPage ($BuildID)
	{
		Lang::includeLang('infos');

		$parse = [];

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

					/**
					 * @var $fleet \App\Models\Fleet
					 */
					$fleet = \App\Models\Fleet::find(['id = ?0 AND end_galaxy = ?1 AND end_system = ?2 AND end_planet = ?3 AND end_type = ?4 AND mess = 3', 'bind' => [$this->planet->galaxy, $this->planet->system, $this->planet->planet, $this->planet->planet_type]]);

					if (!$fleet)
						$parse['msg'] = "<font color=red>Флот отсутствует у планеты</font>";
					else
					{
						$tt = 0;

						foreach ($fleet->getShips() as $type => $ship)
						{
							if ($type > 100)
								$tt += $this->storage->pricelist[$type]['stay'] * $ship['cnt'];
						}

						$max = $this->planet->{$this->storage->resource[$BuildID]} * 10000;

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

				if ($this->planet->{$this->storage->resource[$BuildID]} > 0)
				{
					if (!$parse['msg'])
						$parse['msg'] = "Выберите флот для отправки дейтерия";

					$parse['fleet'] = $this->BuildFleetCombo();
					$parse['need'] = ($this->planet->{$this->storage->resource[$BuildID]} * 10000);

					$this->view->setVar('parse', $parse);
					$this->view->partial('info/buildings_ally');
				}
			}

			$this->view->setVar('parse', $parse);
			$this->view->partial('info/buildings');
		}
		elseif (in_array($BuildID, $this->storage->reslist['fleet']))
		{
			$parse['hull_pt']  = floor(($this->storage->pricelist[$BuildID]['metal'] + $this->storage->pricelist[$BuildID]['crystal']) / 10);
			$parse['~hull_pt'] = $parse['hull_pt'];
			$parse['hull_pt']  = Helpers::pretty_number($parse['hull_pt']) . ' (' . Helpers::pretty_number(round($parse['hull_pt'] * (1 + $this->user->defence_tech * 0.05 + (($this->storage->CombatCaps[$BuildID]['power_up'] * ((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0)) / 100)))) . ')';

			$attTech = 1 + (((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0) * ($this->storage->CombatCaps[$BuildID]['power_up'] / 100)) + $this->user->military_tech * 0.05;

			if ($this->storage->CombatCaps[$BuildID]['type_gun'] == 1)
				$attTech += $this->user->laser_tech * 0.05;
			elseif ($this->storage->CombatCaps[$BuildID]['type_gun'] == 2)
				$attTech += $this->user->ionic_tech * 0.05;
			elseif ($this->storage->CombatCaps[$BuildID]['type_gun'] == 3)
				$attTech += $this->user->buster_tech * 0.05;

			// Устанавливаем обновлённые двигателя кораблей
			Fleet::SetShipsEngine($this->user);

			$parse['rf_info_to']  = $this->ShowRapidFireTo($BuildID);
			$parse['rf_info_fr']  = $this->ShowRapidFireFrom($BuildID);

			$parse['attack_pt'] = Helpers::pretty_number($this->storage->CombatCaps[$BuildID]['attack']) . ' (' . Helpers::pretty_number(round($this->storage->CombatCaps[$BuildID]['attack'] * $attTech)) . ')';
			$parse['shield_pt'] = Helpers::pretty_number($this->storage->CombatCaps[$BuildID]['shield']);
			$parse['capacity_pt'] = Helpers::pretty_number($this->storage->CombatCaps[$BuildID]['capacity']);
			$parse['base_speed'] = Helpers::pretty_number($this->storage->CombatCaps[$BuildID]['speed']) . ' (' . Helpers::pretty_number(Fleet::GetFleetMaxSpeed('', $BuildID, $this->user)) . ')';
			$parse['base_conso'] = Helpers::pretty_number($this->storage->CombatCaps[$BuildID]['consumption']);
			$parse['block'] = $this->storage->CombatCaps[$BuildID]['power_armour'];
			$parse['upgrade'] = $this->storage->CombatCaps[$BuildID]['power_up'];
			$parse['met'] = Helpers::pretty_number($this->storage->pricelist[$BuildID]['metal']) . ' (' . Helpers::pretty_number($this->storage->pricelist[$BuildID]['metal'] * $this->user->bonusValue('res_fleet')) . ')';
			$parse['cry'] = Helpers::pretty_number($this->storage->pricelist[$BuildID]['crystal']) . ' (' . Helpers::pretty_number($this->storage->pricelist[$BuildID]['crystal'] * $this->user->bonusValue('res_fleet')) . ')';
			$parse['deu'] = Helpers::pretty_number($this->storage->pricelist[$BuildID]['deuterium']) . ' (' . Helpers::pretty_number($this->storage->pricelist[$BuildID]['deuterium'] * $this->user->bonusValue('res_fleet')) . ')';

			$engine = ['', 'Ракетный', 'Импульсный', 'Гиперпространственный'];
			$gun = ['', 'Лазерное', 'Ионное', 'Плазменное'];
			$armour = ['', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная'];

			$parse['base_engine'] = $engine[$this->storage->CombatCaps[$BuildID]['type_engine']];
			$parse['gun'] = $gun[$this->storage->CombatCaps[$BuildID]['type_gun']];
			$parse['armour'] = $armour[$this->storage->CombatCaps[$BuildID]['type_armour']];

			$this->view->setVar('parse', $parse);
			$this->view->partial('info/buildings_fleet');
		}
		elseif (in_array($BuildID, $this->storage->reslist['defense']))
		{
			$parse['element_typ'] = _getText('tech', 400);
			$parse['hull_pt']  = floor(($this->storage->pricelist[$BuildID]['metal'] + $this->storage->pricelist[$BuildID]['crystal']) / 10);
			$parse['~hull_pt'] = $parse['hull_pt'];
			$parse['hull_pt']  = Helpers::pretty_number($parse['hull_pt']) . ' (' . Helpers::pretty_number(round($parse['hull_pt'] * (1 + $this->user->defence_tech * 0.05 + (((isset($this->storage->CombatCaps[$BuildID]['power_up']) ? $this->storage->CombatCaps[$BuildID]['power_up'] : 0) * ((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0)) / 100)))) . ')';

			if (isset($this->storage->CombatCaps[$BuildID]['shield']))
				$parse['shield_pt'] = Helpers::pretty_number($this->storage->CombatCaps[$BuildID]['shield']);
			else
				$parse['shield_pt'] = '';

			$attTech = 1 + (((isset($this->user->{'fleet_' . $BuildID})) ? $this->user->{'fleet_' . $BuildID} : 0) * ((isset($this->storage->CombatCaps[$BuildID]['power_up']) ? $this->storage->CombatCaps[$BuildID]['power_up'] : 0) / 100)) + $this->user->military_tech * 0.05;

			$parse['attack_pt'] = Helpers::pretty_number($this->storage->CombatCaps[$BuildID]['attack']) . ' (' . Helpers::pretty_number(round($this->storage->CombatCaps[$BuildID]['attack'] * $attTech)) . ')';
			$parse['met'] = Helpers::pretty_number($this->storage->pricelist[$BuildID]['metal']);
			$parse['cry'] = Helpers::pretty_number($this->storage->pricelist[$BuildID]['crystal']);
			$parse['deu'] = Helpers::pretty_number($this->storage->pricelist[$BuildID]['deuterium']);

			if ($BuildID >= 400 && $BuildID < 500)
			{
				$gun = ['', 'Лазерное', 'Ионное', 'Плазменное'];
				$armour = ['', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная'];

				$parse['gun'] = $gun[$this->storage->CombatCaps[$BuildID]['type_gun']];
				$parse['armour'] = $armour[$this->storage->CombatCaps[$BuildID]['type_armour']];

				$parse['speedBattle'] = [];

				foreach ($this->storage->reslist['fleet'] AS $Type)
				{
					if (!isset($this->storage->CombatCaps[$Type]))
						continue;

					$enemy_durability = ($this->storage->pricelist[$Type]['metal'] + $this->storage->pricelist[$Type]['crystal']) / 10;

					$rapid = $this->storage->CombatCaps[$BuildID]['attack'] * (isset($this->storage->CombatCaps[$BuildID]['amplify'][$Type]) ? $this->storage->CombatCaps[$BuildID]['amplify'][$Type] : 1) / $enemy_durability;

					if ($rapid >= 1)
						$parse['speedBattle'][$Type]['TO'] = floor($rapid);

					$rapid = $this->storage->CombatCaps[$Type]['attack'] * (isset($this->storage->CombatCaps[$Type]['amplify'][$BuildID]) ? $this->storage->CombatCaps[$Type]['amplify'][$BuildID] : 1) / $parse['hull_pt'];

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

					if ($_POST['502'] > $this->planet->{$this->storage->resource[502]})
						$_POST['502'] = $this->planet->{$this->storage->resource[502]};
					if ($_POST['503'] > $this->planet->{$this->storage->resource[503]})
						$_POST['503'] = $this->planet->{$this->storage->resource[503]};

					$this->planet->{$this->storage->resource[502]} -= $_POST['502'];
					$this->planet->{$this->storage->resource[503]} -= $_POST['503'];
					$this->planet->update();
				}
				$pars = [];
				$pars['max_mis'] = $this->planet->{$this->storage->resource[44]} * 10;
				$pars['int_miss'] = _getText('tech', 502) . ': ' . $this->planet->{$this->storage->resource[502]};
				$pars['plant_miss'] = _getText('tech', 503) . ': ' . $this->planet->{$this->storage->resource[503]};

				$this->view->setVar('parse', $pars);
				$this->view->partial('info/missile');
			}
		}
		elseif (in_array($BuildID, $this->storage->reslist['officier']))
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
			if ($this->planet->{$this->storage->resource[$BuildID]} > 0)
			{
				$DestroyTime = Building::GetBuildingTime($this->user, $this->planet, $BuildID) / 2;

				if ($DestroyTime < 1)
					$DestroyTime = 1;

				$parse['levelvalue'] = $this->planet->{$this->storage->resource[$BuildID]};
				$parse['destroy'] = Building::GetElementPrice(Building::GetBuildingPrice($this->user, $this->planet, $BuildID, true, true), $this->planet);
				$parse['destroytime'] = Helpers::pretty_time($DestroyTime);

				$this->view->setVar('parse', $parse);
				$this->view->partial('info/buildings_destroy');
			}
		}

		return $parse['name'];
	}
}