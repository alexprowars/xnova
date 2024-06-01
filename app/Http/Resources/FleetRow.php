<?php

namespace App\Http\Resources;

use App\Engine\Fleet;
use App\Helpers;
use App\Models\Fleet as FleetModel;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property FleetModel $resource
 */
class FleetRow extends JsonResource
{
	public function __construct(FleetModel $fleetRow, protected $status, protected $owner)
	{
		parent::__construct($fleetRow);
	}

	public function toArray($request)
	{
		$fleetStyle = [
			1 => 'attack',
			2 => 'federation',
			3 => 'transport',
			4 => 'deploy',
			5 => 'transport',
			6 => 'espionage',
			7 => 'colony',
			8 => 'harvest',
			9 => 'destroy',
			10 => 'missile',
			15 => 'transport',
			20 => 'attack'
		];

		$fleetStatus = [0 => 'flight', 1 => 'holding', 2 => 'return'];
		$fleetPrefix = $this->owner ? 'own' : '';
		$fleetClass = $fleetPrefix . $fleetStyle[$this->resource->mission];

		$fleetContent 	= Fleet::createFleetPopupedFleetLink($this->resource, __('overview.ov_fleet'), $fleetClass, auth()->user());
		$fleetCapacity 	= Fleet::createFleetPopupedMissionLink($this->resource, __('main.type_mission.' . $this->resource->mission), $fleetClass);

		$startId  = '';
		$targetId = '';

		if ($this->status != 2) {
			if (empty($this->resource->user_name)) {
				$startId = ' с координат ';
			} else {
				if ($this->resource->start_type == 1) {
					$startId = __('overview.ov_planet_to');
				} elseif ($this->resource->start_type == 3) {
					$startId = __('overview.ov_moon_to');
				} elseif ($this->resource->start_type == 5) {
					$startId = ' с военной базы ';
				}

				$startId .= $this->resource->user_name . ' ';
			}

			$startId .= $this->resource->getStartAdressLink($fleetClass);

			if (empty($this->resource->target_user_name)) {
				$targetId = ' координаты ';
			} else {
				if ($this->resource->mission != 15 && $this->resource->mission != 5) {
					if ($this->resource->end_type == 1) {
						$targetId = __('overview.ov_planet_to_target');
					} elseif ($this->resource->end_type == 2) {
						$targetId = __('overview.ov_debris_to_target');
					} elseif ($this->resource->end_type == 3) {
						$targetId = __('overview.ov_moon_to_target');
					} elseif ($this->resource->end_type == 5) {
						$targetId = ' военной базе ';
					}
				} else {
					$targetId = __('overview.ov_explo_to_target');
				}

				$targetId .= $this->resource->target_user_name . ' ';
			}

			$targetId .= $this->resource->getTargetAdressLink($fleetClass);
		} else {
			if (empty($this->resource->user_name)) {
				$startId = ' на координаты ';
			} else {
				if ($this->resource->start_type == 1) {
					$startId = __('overview.ov_back_planet');
				} elseif ($this->resource->start_type == 3) {
					$startId = __('overview.ov_back_moon');
				}

				$startId .= $this->resource->user_name . ' ';
			}

			$startId .= $this->resource->getStartAdressLink($fleetClass);

			if (empty($this->resource->target_user_name)) {
				$targetId = ' с координат ';
			} else {
				if ($this->resource->mission != 15) {
					if ($this->resource->end_type == 1) {
						$targetId = __('overview.ov_planet_from');
					} elseif ($this->resource->end_type == 2) {
						$targetId = __('overview.ov_debris_from');
					} elseif ($this->resource->end_type == 3) {
						$targetId = __('overview.ov_moon_from');
					} elseif ($this->resource->end_type == 5) {
						$targetId = ' с военной базы ';
					}
				} else {
					$targetId = __('overview.ov_explo_from');
				}

				$targetId .= $this->resource->target_user_name . ' ';
			}

			$targetId .= $this->resource->getTargetAdressLink($fleetClass);
		}

		if ($this->owner) {
			$rowString = __('overview.ov_une');
			$rowString .= $fleetContent;
		} else {
			$rowString = $this->resource->assault_id ? 'Союзный ' : __('overview.ov_une_hostile');
			$rowString .= $fleetContent;
			$rowString .= __('overview.ov_hostile');
			$rowString .= Helpers::buildHostileFleetPlayerLink($this->resource);
		}

		if ($this->status == 0) {
			$time = $this->resource->start_time;
			$rowString .= __('overview.ov_vennant');
			$rowString .= $startId;
			$rowString .= __('overview.ov_atteint');
			$rowString .= $targetId;
			$rowString .= __('overview.ov_mission');
		} elseif ($this->status == 1) {
			$time = $this->resource->end_stay;
			$rowString .= __('overview.ov_vennant');
			$rowString .= $startId;

			if ($this->resource->mission == 5) {
				$rowString .= ' защищает ';
			} else {
				$rowString .= __('overview.ov_explo_stay');
			}

			$rowString .= $targetId;
			$rowString .= __('overview.ov_explo_mission');
		} else {
			$time = $this->resource->end_time;
			$rowString .= __('overview.ov_rentrant');
			$rowString .= $targetId;
			$rowString .= $startId;
			$rowString .= __('overview.ov_mission');
		}

		$rowString .= $fleetCapacity;

		$bloc['id'] = $this->resource->id;
		$bloc['status'] = $fleetStatus[$this->status];
		$bloc['prefix'] = $fleetPrefix;
		$bloc['mission'] = $fleetStyle[$this->resource->mission];
		$bloc['time'] = $time->utc()->toAtomString();
		$bloc['text'] = $rowString;

		return $bloc;
	}
}
