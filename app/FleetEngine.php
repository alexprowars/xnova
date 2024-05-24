<?php

namespace App;

use App\Entity\Coordinates;
use App\Models\Planet;

class FleetEngine
{
	public $fleet;

	public function __construct(Models\Fleet $Fleet)
	{
		$this->fleet = $Fleet;
	}

	public function killFleet($fleetId = false)
	{
		if (!$fleetId) {
			$fleetId = $this->fleet->id;
		}

		Models\Fleet::query()->where('id', $fleetId)->delete();
	}

	public function restoreFleetToPlanet($Start = true, $fleet = true)
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

		$targetPlanet = Planet::findByCoordinates(
			new Coordinates($this->fleet->{$p . '_galaxy'}, $this->fleet->{$p . '_system'}, $this->fleet->{$p . '_planet'}, $this->fleet->{$p . '_type'})
		);

		if ($targetPlanet && $targetPlanet->user) {
			$targetPlanet->getProduction()->update();

			if ($fleet) {
				$fleetData = $this->fleet->getShips();

				foreach ($fleetData as $shipId => $shipArr) {
					if ($shipArr['count'] > 0) {
						$targetPlanet->updateAmount($shipId, $shipArr['count'], true);
					}
				}
			}

			$targetPlanet->metal += $this->fleet->resource_metal;
			$targetPlanet->crystal += $this->fleet->resource_crystal;
			$targetPlanet->deuterium += $this->fleet->resource_deuterium;

			$targetPlanet->update();
		}
	}

	public function storeGoodsToPlanet($isOrigin = true)
	{
		if (!isset($this->fleet->id)) {
			return;
		}

		$update = [
			'+metal' => $this->fleet->resource_metal,
			'+crystal' => $this->fleet->resource_crystal,
			'+deuterium' => $this->fleet->resource_deuterium
		];

		if ($isOrigin) {
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

	public function returnFleet($update = [], $fleetId = false)
	{
		$update['mess'] = 1;
		$update['updated_at'] = $this->fleet->end_time;

		if (!$fleetId) {
			$fleetId = $this->fleet->id;
		}

		Models\Fleet::find($fleetId)->update($update);

		if ($this->fleet->assault) {
			$this->fleet->assault->delete();
		}
	}

	public function stayFleet($update = [])
	{
		$update['mess'] = 3;
		$update['updated_at'] = $this->fleet->end_stay;

		$this->fleet->update($update);
	}

	public function convertFleetToDebris($fleet)
	{
		$debris = ['metal' => 0, 'crystal' => 0];

		foreach ($fleet as $fleetId => $fleetData) {
			$res = Vars::getItemPrice($fleetId);

			if (isset($res['metal']) && $res['metal'] > 0) {
				$debris['metal'] += floor($fleetData['count'] * $res['metal'] * config('settings.fleetDebrisRate', 0));
			}

			if (isset($res['crystal']) && $res['crystal'] > 0) {
				$debris['crystal'] += floor($fleetData['count'] * $res['crystal'] * config('settings.fleetDebrisRate', 0));
			}
		}

		return $debris;
	}
}
