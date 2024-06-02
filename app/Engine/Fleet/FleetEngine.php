<?php

namespace App\Engine\Fleet;

use App\Engine\Coordinates;
use App\Engine\Enums\PlanetType;
use App\Engine\Vars;
use App\Models;
use App\Models\Planet;

class FleetEngine
{
	public function __construct(public Models\Fleet $fleet)
	{
	}

	public function killFleet($fleetId = null)
	{
		if (!$fleetId) {
			$fleetId = $this->fleet->id;
		}

		Models\Fleet::find($fleetId)?->delete();
	}

	public function restoreFleetToPlanet($start = true, $fleet = true)
	{
		if (!isset($this->fleet->id)) {
			return;
		}

		if ($fleet) {
			if ($start && $this->fleet->start_type == PlanetType::MOON) {
				$checkFleet = Models\Planet::findByCoordinates($this->fleet->getOriginCoordinates());

				if ($checkFleet && $checkFleet->destruyed) {
					$this->fleet->start_type = PlanetType::PLANET;
				}
			} elseif ($this->fleet->end_type == PlanetType::MOON) {
				$checkFleet = Models\Planet::findByCoordinates($this->fleet->getDestinationCoordinates());

				if ($checkFleet && $checkFleet->destruyed) {
					$this->fleet->end_type = PlanetType::PLANET;
				}
			}
		}

		if ($start) {
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
