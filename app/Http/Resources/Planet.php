<?php

namespace App\Http\Resources;

use App\Engine\Enums\ItemType;
use App\Engine\Vars;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Planet
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
					'basic' => (int) config('game.energy_basic_income', 0),
				],
			],
			'coordinates' => $this->getCoordinates()->toArray(),
			'debris' => [
				'metal' => $this->debris_metal,
				'crystal' => $this->debris_crystal,
			],
			'buildings' => [],
			'units' => [],
		];

		$planetProduction = $this->getProduction();

		$storage = $planetProduction->getStorageCapacity();
		$production = $planetProduction->getResourceProduction();
		$basicProduction = $planetProduction->getBasicProduction();

		foreach (Vars::getResources() as $res) {
			$entity = $this->entities->where('entity_id', Vars::getIdByName($res . '_mine'))
				->first();

			$data['resources'][$res] = [
				'value' => floor((float) $this->{$res}),
				'capacity' => $storage->get($res),
				'basic' => $basicProduction->get($res),
				'production' => $production->get($res),
				'factor' => $entity ? $entity->factor / 10 : 0,
			];
		}

		foreach (Vars::getItemsByType(ItemType::BUILDING) as $elementId) {
			$data['buildings'][Vars::getName($elementId)] = $this->getLevel($elementId);
		}

		foreach (Vars::getItemsByType([ItemType::FLEET, ItemType::DEFENSE]) as $elementId) {
			$data['units'][Vars::getName($elementId)] = $this->getLevel($elementId);
		}

		return $data;
	}
}
