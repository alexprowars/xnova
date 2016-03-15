<?php
namespace App;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Models\Fleet;
use App\Models\Planet;
use App\Models\User;
use Phalcon\Di\Injectable;

/**
 * Class UpdateStatistics
 * @package App
 * @property \App\Database db
 * @property \Phalcon\Config|\stdClass config
 * @property \Phalcon\Cache\Backend\Memcache cache
 * @property \Phalcon\Registry|\stdClass storage
 * @property \App\Game game
 */
class FleetEngine extends Injectable
{
	public $_fleet;

	function __construct(Fleet $Fleet)
	{
		$this->_fleet = $Fleet;
	}

	public function KillFleet ($fleetId = false)
	{
		if (!$fleetId)
			$fleetId = $this->_fleet->id;

		$this->db->delete($this->_fleet->getSource(), 'id = ?', [$fleetId]);
	}

	public function RestoreFleetToPlanet ($Start = true, $fleet = true)
	{
		if (!isset($this->_fleet->id))
			return;

		if ($fleet)
		{
			if ($Start && $this->_fleet->start_type == 3)
			{
				$CheckFleet = Planet::findFirst(['columns' => 'destruyed', 'conditions' => 'galaxy = ?0 AND system = ?1 AND planet = ?2 AND planet_type = ?3', 'bind' => [$this->_fleet->start_galaxy, $this->_fleet->start_system, $this->_fleet->start_planet, $this->_fleet->start_type]]);

				if ($CheckFleet && $CheckFleet->destruyed != 0)
					$this->_fleet->start_type = 1;
			}
			elseif ($this->_fleet->end_type == 3)
			{
				$CheckFleet = Planet::findFirst(['columns' => 'destruyed', 'conditions' => 'galaxy = ?0 AND system = ?1 AND planet = ?2 AND planet_type = ?3', 'bind' => [$this->_fleet->end_galaxy, $this->_fleet->end_system, $this->_fleet->end_planet, $this->_fleet->end_type]]);

				if ($CheckFleet && $CheckFleet->destruyed != 0)
					$this->_fleet->end_type = 1;
			}
		}

		if ($Start)
			$p = 'start';
		else
			$p = 'end';

		$TargetPlanet = Planet::findByCoords($this->_fleet->{$p.'_galaxy'}, $this->_fleet->{$p.'_system'}, $this->_fleet->{$p.'_planet'}, $this->_fleet->{$p.'_type'});

		if ($TargetPlanet && $TargetPlanet->id_owner > 0)
		{
			/**
			 * @var \App\Models\User $TargetUser
			 */
			$TargetUser = User::findFirst($TargetPlanet->id_owner);

			if ($TargetUser)
			{
				$TargetPlanet->assignUser($TargetUser);
				$TargetPlanet->PlanetResourceUpdate(time());
			}

			if ($fleet)
			{
				$fleetData = $this->_fleet->getShips();

				foreach ($fleetData as $shipId => $shipArr)
				{
					if ($shipArr['cnt'] > 0)
						$TargetPlanet->{$this->storage->resource[$shipId]} += $shipArr['cnt'];
				}
			}

			$TargetPlanet->metal += $this->_fleet->resource_metal;
			$TargetPlanet->crystal += $this->_fleet->resource_crystal;
			$TargetPlanet->deuterium += $this->_fleet->resource_deuterium;

			$TargetPlanet->update();
		}
	}

	public function StoreGoodsToPlanet ($Start = true)
	{
		if (!isset($this->_fleet->id))
			return;

		$update =
		[
			'+metal' 		=> $this->_fleet->resource_metal,
			'+crystal' 		=> $this->_fleet->resource_crystal,
			'+deuterium' 	=> $this->_fleet->resource_deuterium
		];

		if ($Start)
			$p = 'start';
		else
			$p = 'end';

		$this->db->updateAsDict('game_planets', $update, [
			'conditions' => 'galaxy = ? AND system = ? AND planet = ? AND planet_type = ?',
			'bind' => [
				$this->_fleet->{$p.'_galaxy'},
				$this->_fleet->{$p.'_system'},
				$this->_fleet->{$p.'_planet'},
				$this->_fleet->{$p.'_type'}
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
					if (isset($this->storage->resource[$Item]) && (($TargetPlanet->{$this->storage->resource[$Item]} > 0 && $Item < 600) || ($TargetPlanet->{$this->storage->resource[$Item]} > time() && $Item > 600)))
					{
						if ($row == 0)
							$String .= "<tr>";

						$String .= "<th width=40%>" . _getText('tech', $Item) . "</th><th width=10%>" . (($Item < 600) ? $TargetPlanet->{$this->storage->resource[$Item]} : '+') . "</th>";

						$Count += $TargetPlanet->{$this->storage->resource[$Item]};
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
		$update['update_time'] = $this->_fleet->end_time;

		if (!$fleetId)
			$fleetId = $this->_fleet->id;

		$this->db->updateAsDict($this->_fleet->getSource(), $update, 'id = '.$fleetId);

		if ($this->_fleet->group_id != 0)
		{
			$this->db->delete('game_aks', 'id = ?', [$this->_fleet->group_id]);
			$this->db->delete('game_aks_user', 'aks_id = ?', [$this->_fleet->group_id]);
		}
	}

	public function StayFleet ($update = [])
	{
		$update['mess'] = 3;
		$update['update_time'] = $this->_fleet->end_stay;

		$this->_fleet->update($update);
	}

	public function convertFleetToDebris ($fleet)
	{
		$debris = ['metal' => 0, 'crystal' => 0];

		foreach ($fleet AS $fleetId => $fleetData)
		{
			$res = $this->storage->pricelist[$fleetId];

			$debris['metal'] 	+= floor($fleetData['cnt'] * $res['metal'] * $this->config->game->get('fleetDebrisRate', 0));
			$debris['crystal'] 	+= floor($fleetData['cnt'] * $res['crystal'] * $this->config->game->get('fleetDebrisRate', 0));
		}

		return $debris;
	}
}