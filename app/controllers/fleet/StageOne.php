<?php
namespace App\Controllers\Fleet;

use App\Controllers\FleetController;
use App\Fleet;
use App\Lang;

class StageOne
{
	public function show (FleetController $controller)
	{
		if ($controller->user->vacation > 0)
			$controller->message("Нет доступа!");

		if (!isset($_POST['crc']) || ($_POST['crc'] != md5($controller->user->id . '-CHeAT_CoNTROL_Stage_01-' . date("dmY", time()))))
			$controller->message('Ошибка контрольной суммы!');

		Lang::includeLang('fleet');

		$parse = array();

		$speed = array(
			10 => 100,
			9 => 90,
			8 => 80,
			7 => 70,
			6 => 60,
			5 => 50,
			4 => 40,
			3 => 30,
			2 => 20,
			1 => 10,
		);

		$g = $controller->request->getPost('galaxy', 'int', 0);
		$s = $controller->request->getPost('system', 'int', 0);
		$p = $controller->request->getPost('planet', 'int', 0);
		$t = $controller->request->getPost('planet_type', 'int', 0);

		if (!$g)
			$g = $controller->planet->galaxy;

		if (!$s)
			$s = $controller->planet->system;

		if (!$p)
			$p = $controller->planet->planet;

		if (!$t)
			$t = 1;

		$FleetHiddenBlock = "";
		$fleet['fleetlist'] = "";
		$fleet['amount'] = 0;

		foreach ($controller->game->reslist['fleet'] as $n => $i)
		{
			if (isset($_POST["ship" . $i]) && in_array($i, $controller->game->reslist['fleet']) && intval($_POST["ship" . $i]) > 0)
			{
				if (intval($_POST["ship" . $i]) > $controller->planet->{$controller->game->resource[$i]})
					continue;

				$fleet['fleetarray'][$i] = intval($_POST["ship" . $i]);
				$fleet['fleetlist'] .= $i . "," . intval($_POST["ship" . $i]) . ";";
				$fleet['amount'] += intval($_POST["ship" . $i]);

				$ship = array
				(
					'id' => $i,
					'count' => intval($_POST["ship" . $i]),
					'consumption' => Fleet::GetShipConsumption($i, $controller->user),
					'speed' => Fleet::GetFleetMaxSpeed("", $i, $controller->user)
				);

				if (isset($controller->user->{'fleet_' . $i}) && isset($controller->game->CombatCaps[$i]['power_consumption']) && $controller->game->CombatCaps[$i]['power_consumption'] > 0)
					$ship['capacity'] = round($controller->game->CombatCaps[$i]['capacity'] * (1 + $controller->user->{'fleet_' . $i} * ($controller->game->CombatCaps[$i]['power_consumption'] / 100)));
				else
					$ship['capacity'] = $controller->game->CombatCaps[$i]['capacity'];

				$parse['ships'][] = $ship;
			}
		}

		if (!$fleet['fleetlist'])
			$controller->message(_getText('fl_unselectall'), _getText('fl_error'), "?set=fleet", 1);

		$parse['usedfleet'] = str_rot13(base64_encode(json_encode($fleet['fleetarray'])));
		$parse['thisgalaxy'] = $controller->planet->galaxy;
		$parse['thissystem'] = $controller->planet->system;
		$parse['thisplanet'] = $controller->planet->planet;
		$parse['thistype'] = $controller->planet->planet_type;
		$parse['galaxyend'] = $g;
		$parse['systemend'] = $s;
		$parse['planetend'] = $p;
		$parse['typeend'] = $t;
		$parse['thisresource1'] = floor($controller->planet->metal);
		$parse['thisresource2'] = floor($controller->planet->crystal);
		$parse['thisresource3'] = floor($controller->planet->deuterium);
		$parse['speed'] = $speed;

		$parse['shortcut'] = array();

		$inf = $controller->db->query("SELECT fleet_shortcut FROM game_users_info WHERE id = " . $controller->user->id . ";")->fetch();

		if ($inf['fleet_shortcut'])
		{
			$scarray = explode("\r\n", $inf['fleet_shortcut']);

			foreach ($scarray as $a => $b)
			{
				if ($b != '')
				{
					$c = explode(',', $b);

					$parse['shortcut'][] = $c;
				}
			}
		}

		$parse['planets'] = array();

		$kolonien = $controller->user->getUserPlanets($controller->user->getId(), true, $controller->user->ally_id);

		if (count($kolonien) > 1)
		{
			foreach ($kolonien AS $row)
			{
				if ($row['id'] == $controller->planet->id)
					continue;

				if ($row['planet_type'] == 3)
					$row['name'] .= " " . _getText('fl_shrtcup3');

				$parse['planets'][] = $row;
			}
		}

		$parse['moon_timer'] = '';
		$parse['moons'] = array();

		if ($controller->planet->planet_type == 3 || $controller->planet->planet_type == 5)
		{
			$moons = $controller->db->query("SELECT `id`, `name`, `system`, `galaxy`, `planet`, `sprungtor`, `last_jump_time` FROM game_planets WHERE (`planet_type` = '3' OR `planet_type` = '5') AND " . $controller->game->resource[43] . " > 0 AND id != ".$controller->planet->id." AND `id_owner` = '" . $controller->user->id . "';");

			if ($moons->numRows())
			{
				$timer = GetNextJumpWaitTime($controller->planet->toArray());

				if ($timer['value'] != 0)
					$parse['moon_timer'] = InsertJavaScriptChronoApplet("Gate", "1", $timer['value'], true);;

				while ($moon = $moons->fetch())
				{
					$moon['timer'] = GetNextJumpWaitTime($moon);

					$parse['moons'][] = $moon;
				}
			}
		}

		$parse['aks'] = array();

		$aks_madnessred = $controller->db->query("SELECT a.* FROM game_aks a, game_aks_user au WHERE au.aks_id = a.id AND au.user_id = " . $controller->user->id . " ;");

		if ($aks_madnessred->numRows())
		{
			while ($row = $aks_madnessred->fetch())
			{
				$parse['aks'][] = $row;
			}
		}

		$parse['maxepedition'] = intval($_POST['maxepedition']);
		$parse['curepedition'] = intval($_POST['curepedition']);
		$parse['target_mission'] = intval($_POST['target_mission']);
		$parse['crc'] =  md5($controller->user->id . '-CHeAT_CoNTROL_Stage_02-' . date("dmY", time()) . '-' . str_rot13(base64_encode(json_encode($fleet['fleetarray']))));

		$controller->view->setVar('parse', $parse);
		$controller->tag->setTitle(_getText('fl_title_1'));
	}
}

?>