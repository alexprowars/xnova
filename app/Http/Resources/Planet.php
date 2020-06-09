<?php

namespace Xnova\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Xnova\Vars;

/**
 * @mixin \Xnova\Planet
 */
class Planet extends JsonResource
{
	public function toArray($request)
	{
		$user = $this->getUser();

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
					'current' => $this->energy_max + $this->energy_used,
					'max' => $this->energy_max
				],
			],
			'coordinates' => [
				'galaxy' => $this->galaxy,
				'system' => $this->system,
				'position' => $this->planet,
			],
		];

		foreach (Vars::getResources() as $res) {
			$entity = $this->entities->getEntity($res . '_mine');

			$data['resources'][$res] = [
				'current' => floor(floatval($this->{$res})),
				'max' => $this->{$res . '_max'},
				'production' => 0,
				'factor' => $entity ? $entity->factor / 10 : 0,
			];

			if (!$user->isVacation()) {
				$data['resources'][$res]['production'] = $this->{$res . '_perhour'} + floor(config('settings.' . $res . '_basic_income', 0) * config('settings.resource_multiplier', 1));
			}
		}

		return $data;
	}
}
