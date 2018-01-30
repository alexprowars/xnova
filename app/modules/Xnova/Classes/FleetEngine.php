<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Models\Fleet;
use Xnova\Models\Planet;
use Xnova\Models\User;
use Phalcon\Di\Injectable;

/**
 * Class UpdateStatistics
 * @package App
 * @property \Xnova\Database db
 * @property \Phalcon\Config|\stdClass config
 * @property \Phalcon\Cache\Backend\Memcache cache
 * @property \Phalcon\Registry|\stdClass registry
 * @property \Xnova\Game game
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
			$TargetUser = User::findFirst($TargetPlanet->id_owner);

			if ($TargetUser)
			{
				$TargetPlanet->assignUser($TargetUser);
				$TargetPlanet->resourceUpdate(time());
			}

			if ($fleet)
			{
				$fleetData = $this->_fleet->getShips();

				foreach ($fleetData as $shipId => $shipArr)
				{
					if ($shipArr['cnt'] > 0)
						$TargetPlanet->setUnit($shipId, $shipArr['cnt'], true);
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
			$res = Vars::getItemPrice($fleetId);

			if (isset($res['metal']) && $res['metal'] > 0)
				$debris['metal'] += floor($fleetData['cnt'] * $res['metal'] * $this->config->game->get('fleetDebrisRate', 0));

			if (isset($res['crystal']) && $res['crystal'] > 0)
				$debris['crystal'] += floor($fleetData['cnt'] * $res['crystal'] * $this->config->game->get('fleetDebrisRate', 0));
		}

		return $debris;
	}
}