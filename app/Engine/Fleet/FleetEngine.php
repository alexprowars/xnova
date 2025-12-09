<?php

namespace App\Engine\Fleet;

use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Enums\PlanetType;
use App\Facades\Vars;
use App\Models;
use App\Models\Fleet;
use App\Models\Planet;

class FleetEngine
{
	public function __construct(public Fleet $fleet)
	{
	}

	public function killFleet($fleetId = null)
	{
		if (!$fleetId) {
			$fleetId = $this->fleet->id;
		}

		Fleet::find($fleetId)?->delete();
	}

	public function restoreFleetToPlanet($start = true, $fleet = true)
	{
		if (empty($this->fleet->id)) {
			return;
		}

		if ($fleet) {
			if ($start && $this->fleet->start_type == PlanetType::MOON) {
				$checkFleet = Models\Planet::findByCoordinates($this->fleet->getOriginCoordinates());

				if ($checkFleet && $checkFleet->destroyed_at) {
					$this->fleet->start_type = PlanetType::PLANET;
				}
			} elseif ($this->fleet->end_type == PlanetType::MOON) {
				$checkFleet = Models\Planet::findByCoordinates($this->fleet->getDestinationCoordinates());

				if ($checkFleet && $checkFleet->destroyed_at) {
					$this->fleet->end_type = PlanetType::PLANET;
				}
			}
		}

		if ($start) {
			$target = $this->fleet->getOriginCoordinates();
		} else {
			$target = $this->fleet->getDestinationCoordinates();
		}

		$targetPlanet = Planet::findByCoordinates($target);

		if ($targetPlanet && $targetPlanet->user) {
			$targetPlanet->getProduction()->update();

			if ($fleet) {
				foreach ($this->fleet->entities as $entity) {
					if ($entity->count > 0) {
						$targetPlanet->updateAmount($entity->id, $entity->count, true);
					}
				}
			}

			$targetPlanet->metal += $this->fleet->resource_metal;
			$targetPlanet->crystal += $this->fleet->resource_crystal;
			$targetPlanet->deuterium += $this->fleet->resource_deuterium;

			$targetPlanet->update();
		}
	}

	public function stayFleet(array $attributes = [])
	{
		$this->fleet->mess = 3;
		$this->fleet->updated_at = $this->fleet->end_stay;

		$this->fleet->update($attributes);
	}

	public function return(array $attributes = [])
	{
		$this->fleet->mess = 1;
		$this->fleet->updated_at = $this->fleet->end_date;
		$this->fleet->update($attributes);

		$this->fleet->assault?->delete();
	}

	public function convertFleetToDebris(FleetEntityCollection $fleets)
	{
		$debris = ['metal' => 0, 'crystal' => 0];

		foreach ($fleets as $entity) {
			$res = Vars::getItemPrice($entity->id);

			if (!empty($res['metal']) && $res['metal'] > 0) {
				$debris['metal'] += floor($entity->count * $res['metal'] * config('game.fleetDebrisRate', 0));
			}

			if (!empty($res['crystal']) && $res['crystal'] > 0) {
				$debris['crystal'] += floor($entity->count * $res['crystal'] * config('game.fleetDebrisRate', 0));
			}
		}

		return $debris;
	}
}
