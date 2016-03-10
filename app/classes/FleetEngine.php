<?php

namespace App;

use App\Models\Planet;
use App\Models\User;
use Phalcon\Di\Injectable;

/**
 * Class UpdateStatistics
 * @package App
 * @property \App\Database db
 * @property \Phalcon\Config|\stdClass config
 * @property \Phalcon\Cache\Backend\Memcache cache
 * @property \App\Game game
 */
class FleetEngine extends Injectable
{
	public $_fleet = [];

	public function KillFleet ($fleetId = false)
	{
		if (!$fleetId)
			$fleetId = $this->_fleet['id'];

		$this->db->delete('game_fleets', 'id = ?', [$fleetId]);
	}

	public function RestoreFleetToPlanet ($Start = true, $fleet = true)
	{
		if (!isset($this->_fleet["id"]))
			return;

		if ($fleet)
		{
			if ($Start && $this->_fleet['start_type'] == 3)
			{
				$CheckFleet = $this->db->query("SELECT destruyed FROM game_planets WHERE `galaxy` = '" . $this->_fleet['start_galaxy'] . "' AND `system` = '" . $this->_fleet['start_system'] . "' AND `planet` = '" . $this->_fleet['start_planet'] . "' AND `planet_type` = '" . $this->_fleet['start_type'] . "'")->fetch();

				if ($CheckFleet['destruyed'] != 0)
					$this->_fleet['start_type'] = 1;
			}
			elseif ($this->_fleet['end_type'] == 3)
			{
				$CheckFleet = $this->db->query("SELECT destruyed FROM game_planets WHERE `galaxy` = '" . $this->_fleet['end_galaxy'] . "' AND `system` = '" . $this->_fleet['end_system'] . "' AND `planet` = '" . $this->_fleet['end_planet'] . "' AND `planet_type` = '" . $this->_fleet['end_type'] . "'")->fetch();

				if ($CheckFleet['destruyed'] != 0)
					$this->_fleet['end_type'] = 1;
			}
		}

		if ($Start)
			$p = 'start';
		else
			$p = 'end';

		$TargetPlanet = Planet::findByCoords($this->_fleet[$p.'_galaxy'], $this->_fleet[$p.'_system'], $this->_fleet[$p.'_planet'], $this->_fleet[$p.'_type']);

		if (isset($TargetPlanet->id) && $TargetPlanet->id_owner > 0)
		{
			/**
			 * @var \App\Models\User $TargetUser
			 */
			$TargetUser = User::findFirst($TargetPlanet->id_owner);

			if (isset($TargetUser->id))
			{
				$TargetPlanet->assignUser($TargetUser);
				$TargetPlanet->PlanetResourceUpdate(time());
			}
		}

		$update = [];

		if ($fleet)
		{
			$fleetData = Fleet::unserializeFleet($this->_fleet['fleet_array']);

			foreach ($fleetData as $shipId => $shipArr)
			{
				if ($shipArr['cnt'] > 0)
					$update['+'.$this->game->resource[$shipId]] = $shipArr['cnt'];
			}
		}

		$update['+metal'] 		= $this->_fleet['resource_metal'];
		$update['+crystal'] 	= $this->_fleet['resource_crystal'];
		$update['+deuterium'] 	= $this->_fleet['resource_deuterium'];

		$this->db->updateAsDict('game_planets', $update, [
			'conditions' => 'galaxy = ? AND system = ? AND planet = ? AND planet_type = ?',
			'bind' => [
				$this->_fleet[$p.'_galaxy'],
				$this->_fleet[$p.'_system'],
				$this->_fleet[$p.'_planet'],
				$this->_fleet[$p.'_type']
			]
		]);
	}

	public function StoreGoodsToPlanet ($Start = true)
	{
		if (!isset($this->_fleet["id"]))
			return;

		$update =
		[
			'+metal' 		=> $this->_fleet['resource_metal'],
			'+crystal' 		=> $this->_fleet['resource_crystal'],
			'+deuterium' 	=> $this->_fleet['resource_deuterium']
		];

		if ($Start)
			$p = 'start';
		else
			$p = 'end';

		$this->db->updateAsDict('game_planets', $update, [
			'conditions' => 'galaxy = ? AND system = ? AND planet = ? AND planet_type = ?',
			'bind' => [
				$this->_fleet[$p.'_galaxy'],
				$this->_fleet[$p.'_system'],
				$this->_fleet[$p.'_planet'],
				$this->_fleet[$p.'_type']
			]
		]);
	}

	public function SpyTarget ($TargetPlanet, $Mode, $TitleString)
	{
		$LookAtLoop = true;
		$String = '';
		$Loops = 0;
		$ResFrom = [];
		$ResTo = [];

		if ($Mode == 0)
		{
			$t = time().''.mt_rand(1, 100);

			$String .= "<table width=\"100%\"><tr><td class=\"c\" colspan=\"4\">";
			$String .= $TitleString . " " . $TargetPlanet->name;
			$String .= " <a href=\"/galaxy/" . $TargetPlanet->galaxy . "/" . $TargetPlanet->system . "/\">";
			$String .= "[" . $TargetPlanet->galaxy . ":" . $TargetPlanet->system . ":" . $TargetPlanet->planet . "]</a>";
			$String .= "<br>на <span id='d".$t."'></span><script>$('#d".$t."').html(print_date(" . time() . ", 1));</script></td>";
			$String .= "</tr><tr>";
			$String .= "<th width=220>металла:</th><th width=220 align=right>" . Helpers::pretty_number($TargetPlanet->metal) . "</th>";
			$String .= "<th width=220>кристалла:</th><th width=220 align=right>" . Helpers::pretty_number($TargetPlanet->crystal) . "</th>";
			$String .= "</tr><tr>";
			$String .= "<th width=220>дейтерия:</th><th width=220 align=right>" . Helpers::pretty_number($TargetPlanet->deuterium) . "</th>";
			$String .= "<th width=220>энергии:</th><th width=220 align=right>" . Helpers::pretty_number($TargetPlanet->energy_max) . "</th>";
			$String .= "</tr>";
			$LookAtLoop = false;
		}
		elseif ($Mode == 1)
		{
			$ResFrom[0] = 200;
			$ResTo[0] = 299;
			$Loops = 1;
		}
		elseif ($Mode == 2)
		{
			$ResFrom[0] = 400;
			$ResTo[0] = 499;
			$ResFrom[1] = 500;
			$ResTo[1] = 599;
			$Loops = 2;
		}
		elseif ($Mode == 3)
		{
			$ResFrom[0] = 1;
			$ResTo[0] = 99;
			$Loops = 1;
		}
		elseif ($Mode == 4)
		{
			$ResFrom[0] = 100;
			$ResTo[0] = 199;
			$Loops = 1;
		}
		elseif ($Mode == 5)
		{
			$ResFrom[0] = 300;
			$ResTo[0] = 375;
			$Loops = 1;
		}
		elseif ($Mode == 6)
		{
			$ResFrom[0] = 600;
			$ResTo[0] = 607;
			$Loops = 1;
		}

		if ($LookAtLoop == true)
		{
			$String = "<table width=\"100%\" cellspacing=\"1\"><tr><td class=\"c\" colspan=\"" . ((2 * $this->config->game->get('spyReportRow', 1)) + ($this->config->game->get('spyReportRow', 1) - 2)) . "\">" . $TitleString . "</td></tr>";
			$Count = 0;
			$CurrentLook = 0;

			while ($CurrentLook < $Loops)
			{
				$row = 0;

				for ($Item = $ResFrom[$CurrentLook]; $Item <= $ResTo[$CurrentLook]; $Item++)
				{
					if (isset($this->game->resource[$Item]) && (($TargetPlanet->{$this->game->resource[$Item]} > 0 && $Item < 600) || ($TargetPlanet->{$this->game->resource[$Item]} > time() && $Item > 600)))
					{
						if ($row == 0)
							$String .= "<tr>";

						$String .= "<th width=40%>" . _getText('tech', $Item) . "</th><th width=10%>" . (($Item < 600) ? $TargetPlanet->{$this->game->resource[$Item]} : '+') . "</th>";

						$Count += $TargetPlanet->{$this->game->resource[$Item]};
						$row++;

						if ($row == $this->config->game->get('spyReportRow', 1))
						{
							$String .= "</tr>";
							$row = 0;
						}
					}
				}

				while ($row != 0)
				{
					$String .= "<th width=40%>&nbsp;</th><th width=10%>&nbsp;</th>";
					$row++;

					if ($row == $this->config->game->get('spyReportRow', 1))
					{
						$String .= "</tr>";
						$row = 0;
					}
				}

				$CurrentLook++;
			}

			if ($Count == 0)
				$String .= "<tr><th>нет данных</th></tr>";
		}
		else
			$Count = 0;

		$String .= "</table>";

		$return['String'] = $String;
		$return['Count'] = $Count;

		return $return;
	}

	public function ReturnFleet ($update = [], $fleetId = false)
	{
		$update['mess'] = 1;
		$update['update_time'] = $this->_fleet['end_time'];

		if (!$fleetId)
			$fleetId = $this->_fleet['id'];

		$this->db->updateAsDict('game_fleets', $update, 'id = '.$fleetId);

		if ($this->_fleet['group_id'] != 0)
		{
			$this->db->delete('game_aks', 'id = ?', [$this->_fleet['group_id']]);
			$this->db->delete('game_aks_user', 'aks_id = ?', [$this->_fleet['group_id']]);
		}
	}

	public function StayFleet ($update = [])
	{
		$update['mess'] = 3;
		$update['update_time'] = $this->_fleet['end_stay'];

		$this->db->updateAsDict('game_fleets', $update, 'id = '.$this->_fleet['id']);
	}

	public function convertFleetToDebris ($fleet)
	{
		$debris = ['metal' => 0, 'crystal' => 0];

		foreach ($fleet AS $fleetId => $fleetData)
		{
			$res = $this->game->pricelist[$fleetId];

			$debris['metal'] 	+= floor($fleetData['cnt'] * $res['metal'] * $this->config->game->get('fleetDebrisRate', 0));
			$debris['crystal'] 	+= floor($fleetData['cnt'] * $res['crystal'] * $this->config->game->get('fleetDebrisRate', 0));
		}

		return $debris;
	}
}

?>