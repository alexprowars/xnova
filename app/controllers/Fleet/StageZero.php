<?php
namespace App\Controllers\Fleet;

use App\Controllers\FleetController;
use App\Fleet;
use App\Lang;

class StageZero
{
	public function show (FleetController $controller)
	{
		if (!$controller->planet)
			$controller->message(_getText('fl_noplanetrow'), _getText('fl_error'));

		$MaxFlyingFleets = $controller->db->fetchColumn("SELECT COUNT(fleet_owner) AS `actcnt` FROM game_fleets WHERE `fleet_owner` = '" . $controller->user->id . "';");

		$MaxExpedition = $controller->user->{$controller->game->resource[124]};
		$ExpeditionEnCours = 0;
		$EnvoiMaxExpedition = 0;

		if ($MaxExpedition >= 1)
		{
			$ExpeditionEnCours = $controller->db->fetchColumn("SELECT COUNT(fleet_owner) AS `expedi` FROM game_fleets WHERE `fleet_owner` = '" . $controller->user->id . "' AND `fleet_mission` = '15';");
			$EnvoiMaxExpedition = 1 + floor($MaxExpedition / 3);
		}

		$MaxFlottes = 1 + $controller->user->{$controller->game->resource[108]};
		if ($controller->user->rpg_admiral > time())
			$MaxFlottes += 2;

		Lang::includeLang('fleet');

		$galaxy = $controller->request->getQuery('galaxy', 'int', 0);
		$system = $controller->request->getQuery('system', 'int', 0);
		$planet = $controller->request->getQuery('planet', 'int', 0);
		$planettype = $controller->request->getQuery('type', 'int', 0);
		$target_mission = $controller->request->getQuery('mission', 'int', 0);

		if (!$galaxy)
			$galaxy = $controller->planet->galaxy;

		if (!$system)
			$system = $controller->planet->system;

		if (!$planet)
			$planet = $controller->planet->planet;

		if (!$planettype)
			$planettype = 1;

		$parse = [];
		$parse['maxFlyingFleets'] = $MaxFlyingFleets;
		$parse['maxFlottes'] = $MaxFlottes;
		$parse['currentExpeditions'] = $ExpeditionEnCours;
		$parse['maxExpeditions'] = $EnvoiMaxExpedition;
		$parse['galaxy'] = $galaxy;
		$parse['system'] = $system;
		$parse['planet'] = $planet;
		$parse['planettype'] = $planettype;
		$parse['mission'] = $target_mission;

		$fq = $controller->db->query("SELECT * FROM game_fleets WHERE fleet_owner=" . $controller->user->id . "");

		$parse['fleets'] = [];

		while ($f = $fq->fetch())
		{
			$f['fleet_count'] = 0;

			$fleetArray = Fleet::unserializeFleet($f['fleet_array']);

			foreach ($fleetArray as $fleetId => $fleetData)
				$f['fleet_count'] += $fleetData['cnt'];

			$f['fleet_array'] = $fleetArray;

			$parse['fleets'][]= $f;
		}

		$parse['mission_text'] = '';

		if ($target_mission > 0)
			$parse['mission_text'] = ' для миссии "' . _getText('type_mission', $target_mission) . '"';
		if (($system > 0 && $galaxy > 0 && $planet > 0) && ($galaxy != $controller->planet->galaxy || $system != $controller->planet->system || $planet != $controller->planet->id))
			$parse['mission_text'] = ' на координаты [' . $galaxy . ':' . $system . ':' . $planet . ']';

		$parse['ships'] = [];

		foreach ($controller->game->reslist['fleet'] as $n => $i)
		{
			if ($controller->planet->{$controller->game->resource[$i]} > 0)
			{
				$ship = array
				(
					'id' => $i,
					'count' => $controller->planet->{$controller->game->resource[$i]},
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

		$controller->view->setVar('parse', $parse);
		$controller->tag->setTitle(_getText('fl_title_0'));
	}
}
?>