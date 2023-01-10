<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Vars;

/**
 * @mixin \App\Planet
 */
class Planet extends JsonResource
{
	public function toArray($request)
	{
		$data = [
			'id' => $this->id,
			'name' => $this->name,
			'type' => $this->planet_type,
			'image' => $this->image,
			'diameter' => $this->diameter,
			'field_used' => $this->field_current,
			'field_max' => $this->getMaxFields(),
			'temp_min' => $this->temp_min,
			'temp_max' => $this->temp_max,
			'resources' => [
				'energy' => [
					'value' => $this->energy_max - $this->energy_used,
					'capacity' => $this->energy_max,
				],
			],
			'coordinates' => $this->getCoordinates()->toArray(),
		];

		$planetProduction = $this->getProduction();

		$storage = $planetProduction->getStorageCapacity();
		$production = $planetProduction->getResourceProduction();

		foreach (Vars::getResources() as $res) {
			$entity = $this->entities->getEntity($res . '_mine');

			$data['resources'][$res] = [
				'value' => floor(floatval($this->{$res})),
				'capacity' => $storage->get($res),
				'production' => $production->get($res),
				'factor' => $entity ? $entity->factor / 10 : 0,
			];
		}

		return $data;
	}
}
