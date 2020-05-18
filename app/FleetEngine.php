<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova;

use Illuminate\Support\Facades\Config;
use Xnova\Models;

class FleetEngine
{
	public $fleet;

	function __construct(Models\Fleet $Fleet)
	{
		$this->fleet = $Fleet;
	}

	public function KillFleet($fleetId = false)
	{
		if (!$fleetId) {
			$fleetId = $this->fleet->id;
		}

		Models\Fleet::query()->where('id', $fleetId)->delete();
	}

	public function RestoreFleetToPlanet($Start = true, $fleet = true)
	{
		if (!isset($this->fleet->id)) {
			return;
		}

		if ($fleet) {
			if ($Start && $this->fleet->start_type == 3) {
				$CheckFleet = Models\Planet::query()
					->where('galaxy', $this->fleet->start_galaxy)
					->where('system', $this->fleet->start_system)
					->where('planet', $this->fleet->start_planet)
					->where('planet_type', $this->fleet->start_type)
					->first(['destruyed']);

				if ($CheckFleet && $CheckFleet->destruyed != 0) {
					$this->fleet->start_type = 1;
				}
			} elseif ($this->fleet->end_type == 3) {
				$CheckFleet = Models\Planet::query()
					->where('galaxy', $this->fleet->end_galaxy)
					->where('system', $this->fleet->end_system)
					->where('planet', $this->fleet->end_planet)
					->where('planet_type', $this->fleet->end_type)
					->first(['destruyed']);

				if ($CheckFleet && $CheckFleet->destruyed != 0) {
					$this->fleet->end_type = 1;
				}
			}
		}

		if ($Start) {
			$p = 'start';
		} else {
			$p = 'end';
		}

		$TargetPlanet = Planet::findByCoords($this->fleet->{$p . '_galaxy'}, $this->fleet->{$p . '_system'}, $this->fleet->{$p . '_planet'}, $this->fleet->{$p . '_type'});

		if ($TargetPlanet && $TargetPlanet->id_owner > 0) {
			$TargetUser = User::query()->find($TargetPlanet->id_owner);

			if ($TargetUser) {
				$TargetPlanet->assignUser($TargetUser);
				$TargetPlanet->resourceUpdate(time());
			}

			if ($fleet) {
				$fleetData = $this->fleet->getShips();

				foreach ($fleetData as $shipId => $shipArr) {
					if ($shipArr['count'] > 0) {
						$TargetPlanet->setUnit($shipId, $shipArr['count'], true);
					}
				}
			}

			$TargetPlanet->metal += $this->fleet->resource_metal;
			$TargetPlanet->crystal += $this->fleet->resource_crystal;
			$TargetPlanet->deuterium += $this->fleet->resource_deuterium;

			$TargetPlanet->update();
		}
	}

	public function StoreGoodsToPlanet($Start = true)
	{
		if (!isset($this->fleet->id)) {
			return;
		}

		$update =
		[
			'+metal' 		=> $this->fleet->resource_metal,
			'+crystal' 		=> $this->fleet->resource_crystal,
			'+deuterium' 	=> $this->fleet->resource_deuterium
		];

		if ($Start) {
			$p = 'start';
		} else {
			$p = 'end';
		}

		Models\Planet::query()
			->where('galaxy', $this->fleet->{$p . '_galaxy'})
			->where('system', $this->fleet->{$p . '_system'})
			->where('planet', $this->fleet->{$p . '_planet'})
			->where('planet_type', $this->fleet->{$p . '_type'})
			->update($update);
	}

	public function ReturnFleet($update = [], $fleetId = false)
	{
		$update['mess'] = 1;
		$update['update_time'] = $this->fleet->end_time;

		if (!$fleetId) {
			$fleetId = $this->fleet->id;
		}

		Models\Fleet::query()->where('id', $fleetId)->update($update);

		if ($this->fleet->group_id > 0) {
			Models\Assault::query()->where('id', $this->fleet->group_id)->delete();
			Models\AssaultUser::query()->where('aks_id', $this->fleet->group_id)->delete();
		}
	}

	public function StayFleet($update = [])
	{
		$update['mess'] = 3;
		$update['update_time'] = $this->fleet->end_stay;

		$this->fleet->update($update);
	}

	public function convertFleetToDebris($fleet)
	{
		$debris = ['metal' => 0, 'crystal' => 0];

		foreach ($fleet as $fleetId => $fleetData) {
			$res = Vars::getItemPrice($fleetId);

			if (isset($res['metal']) && $res['metal'] > 0) {
				$debris['metal'] += floor($fleetData['count'] * $res['metal'] * Config::get('settings.fleetDebrisRate', 0));
			}

			if (isset($res['crystal']) && $res['crystal'] > 0) {
				$debris['crystal'] += floor($fleetData['count'] * $res['crystal'] * Config::get('settings.fleetDebrisRate', 0));
			}
		}

		return $debris;
	}
}
