<?php

namespace App\Http\Resources;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Facades\Vars;
use App\Models\Planet;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

/**
 * @mixin Planet
 * @property Planet $resource
 */
class PlanetResource extends JsonResource
{
	public function toArray($request)
	{
		$data = [
			'id' => $this->resource->id,
			'name' => $this->resource->name,
			'type' => $this->resource->planet_type,
			'image' => $this->resource->image,
			'diameter' => $this->resource->diameter,
			'field_used' => $this->resource->field_current,
			'field_max' => $this->resource->getMaxFields(),
			'temp_min' => $this->resource->temp_min,
			'temp_max' => $this->resource->temp_max,
			'resources' => [
				'energy' => [
					'value' => $this->resource->energy - $this->resource->energy_used,
					'capacity' => $this->resource->energy,
					'basic' => (int) config('game.energy_basic_income', 0),
				],
			],
			'coordinates' => $this->resource->coordinates->toArray(),
			'moon' => null,
			'debris' => [
				'metal' => $this->resource->debris_metal,
				'crystal' => $this->resource->debris_crystal,
			],
			'buildings' => [],
			'units' => [],
		];

		$planetProduction = $this->resource->getProduction();

		$storage = $planetProduction->getStorageCapacity();
		$production = $planetProduction->getResourceProduction();
		$basicProduction = $planetProduction->getBasicProduction();

		foreach (Vars::getResources() as $res) {
			$entity = $this->resource->entities->getByEntityId(Vars::getIdByName($res . '_mine'));

			$data['resources'][$res] = [
				'value' => floor((float) $this->resource->{$res}),
				'capacity' => $storage->get($res),
				'basic' => $basicProduction->get($res),
				'production' => $production->get($res),
				'factor' => $entity->factor / 10,
			];
		}

		foreach (Vars::getItemsByType(ItemType::BUILDING) as $elementId) {
			$data['buildings'][Vars::getName($elementId)] = $this->resource->getLevel($elementId);
		}

		foreach (Vars::getItemsByType([ItemType::FLEET, ItemType::DEFENSE]) as $elementId) {
			$data['units'][Vars::getName($elementId)] = $this->resource->getLevel($elementId);
		}

		if ($this->resource->moon_id && $this->resource->planet_type != PlanetType::MOON && $this->resource->id) {
			/** @var ?Planet $moon */
			$moon = Cache::remember('app::moon_' . $this->resource->moon_id, 300, function () {
				return PlanetResource::query()
					->select(['id', 'name', 'image'])
					->whereKey($this->resource->moon_id)
					->where('planet_type', PlanetType::MOON)
					->whereNull('destroyed_at')
					->first();
			});

			if ($moon) {
				$data['moon'] = [
					'id' => $moon->id,
					'name' => $moon->name,
					'image' => $moon->image,
				];
			}
		}

		return $data;
	}
}
