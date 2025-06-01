<?php

namespace App\Http\Resources;

use App\Engine\Fleet;
use App\Models;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Models\Fleet $resource
 */
class FleetRow extends JsonResource
{
	public function __construct(Models\Fleet $fleetRow, protected $status, protected $owner)
	{
		parent::__construct($fleetRow);
	}

	public function toArray($request)
	{
		if ($this->status == 0) {
			$date = $this->resource->start_date;
		} elseif ($this->status == 1) {
			$date = $this->resource->end_stay;
		} else {
			$date = $this->resource->end_date;
		}

		return [
			'id' => $this->resource->id,
			'units' => Fleet::createFleetPopupedFleetLink($this->resource, auth()->user()),
			'status' => $this->status,
			'owner' => $this->owner,
			'user' => $this->resource->user ? [
				'id' => $this->resource->user->id,
				'name' => $this->resource->user->username,
			] : null,
			'assault' => $this->resource->assault_id,
			'mission' => $this->resource->mission->value,
			'date' => $date->utc()->toAtomString(),
			'start' => $this->resource->getOriginCoordinates()->toArray(),
			'start_name' => $this->resource->user_name,
			'target' => $this->resource->getDestinationCoordinates()->toArray(),
			'target_name' => $this->resource->target_user_name,
			'resources' => [
				'metal' => $this->resource->resource_metal,
				'crystal' => $this->resource->resource_crystal,
				'deuterium' => $this->resource->resource_deuterium,
			]
		];
	}
}
