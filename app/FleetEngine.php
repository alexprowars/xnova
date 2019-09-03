<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Config;
use Xnova\Models;

class FleetEngine
{
	public $_fleet;

	function __construct(Models\Fleet $Fleet)
	{
		$this->_fleet = $Fleet;
	}

	public function KillFleet ($fleetId = false)
	{
		if (!$fleetId)
			$fleetId = $this->_fleet->id;

		Models\Fleet::query()->where('id', $fleetId)->delete();
	}

	public function RestoreFleetToPlanet ($Start = true, $fleet = true)
	{
		if (!isset($this->_fleet->id))
			return;

		if ($fleet)
		{
			if ($Start && $this->_fleet->start_type == 3)
			{
				$CheckFleet = Models\Planets::query()
					->where('galaxy', $this->_fleet->start_galaxy)
					->where('system', $this->_fleet->start_system)
					->where('planet', $this->_fleet->start_planet)
					->where('planet_type', $this->_fleet->start_type)
					->first(['destruyed']);

				if ($CheckFleet && $CheckFleet->destruyed != 0)
					$this->_fleet->start_type = 1;
			}
			elseif ($this->_fleet->end_type == 3)
			{
				$CheckFleet = Models\Planets::query()
					->where('galaxy', $this->_fleet->end_galaxy)
					->where('system', $this->_fleet->end_system)
					->where('planet', $this->_fleet->end_planet)
					->where('planet_type', $this->_fleet->end_type)
					->first(['destruyed']);

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
			/** @var User $TargetUser */
			$TargetUser = User::query()->find($TargetPlanet->id_owner);

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
					if ($shipArr['count'] > 0)
						$TargetPlanet->setUnit($shipId, $shipArr['count'], true);
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

		Models\Planets::query()
			->where('galaxy', $this->_fleet->{$p.'_galaxy'})
			->where('system', $this->_fleet->{$p.'_system'})
			->where('planet', $this->_fleet->{$p.'_planet'})
			->where('planet_type', $this->_fleet->{$p.'_type'})
			->update($update);
	}

	public function ReturnFleet ($update = [], $fleetId = false)
	{
		$update['mess'] = 1;
		$update['update_time'] = $this->_fleet->end_time;

		if (!$fleetId)
			$fleetId = $this->_fleet->id;

		Models\Fleet::query()->where('id', $fleetId)->update($update);

		if ($this->_fleet->group_id > 0)
		{
			Models\Aks::query()->where('id', $this->_fleet->group_id)->delete();
			Models\AksUser::query()->where('aks_id', $this->_fleet->group_id)->delete();
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
				$debris['metal'] += floor($fleetData['count'] * $res['metal'] * Config::get('game.fleetDebrisRate', 0));

			if (isset($res['crystal']) && $res['crystal'] > 0)
				$debris['crystal'] += floor($fleetData['count'] * $res['crystal'] * Config::get('game.fleetDebrisRate', 0));
		}

		return $debris;
	}
}