<?php

namespace App\Engine;

use App\Models\Planet;
use App\Models\User;
use Backpack\Settings\app\Models\Setting;

class Galaxy
{
	public function createPlanetByUserId($userId)
	{
		$galaxy = (int) (Setting::get('lastSettedGalaxyPos') ?? 1);
		$system = (int) (Setting::get('lastSettedSystemPos') ?? 1);
		$planet = (int) (Setting::get('lastSettedPlanetPos') ?? 1);

		do {
			$isFree = $this->getFreePositions(
				new Coordinates($galaxy, $system),
				(int) round(config('settings.maxPlanetInSystem') * 0.2),
				(int) round(config('settings.maxPlanetInSystem') * 0.8)
			);

			if (!empty($isFree)) {
				$position = $isFree[array_rand($isFree)];
			} else {
				$position = 0;
			}

			if ($position > 0 && $planet < config('settings.maxRegPlanetsInSystem', 3)) {
				$planet++;
			} else {
				$planet = 1;

				if ($system >= config('settings.maxSystemInGalaxy')) {
					$system = 1;

					if ($galaxy >= config('settings.maxGalaxyInWorld')) {
						$galaxy = 1;
					} else {
						$galaxy++;
					}
				} else {
					$system++;
				}
			}
		} while ($this->isPositionFree(new Coordinates($galaxy, $system, $position)) === false);

		$planetId = $this->createPlanet(
			new Coordinates($galaxy, $system, $position),
			$userId,
			__('main.sys_plnt_defaultname'),
			true
		);

		if ($planetId !== false) {
			Setting::set('lastSettedGalaxyPos', $galaxy);
			Setting::set('lastSettedSystemPos', $system);
			Setting::set('lastSettedPlanetPos', $planet);

			User::find($userId)
				->update([
					'planet_id' => $planetId,
					'planet_current' => $planetId,
					'galaxy' => $galaxy,
					'system' => $system,
					'planet' => $position
				]);
		}

		return $planetId;
	}

	public function createPlanet(Coordinates $target, $userId, $title = '', $isMainPlanet = false)
	{
		if ($this->isPositionFree($target)) {
			$planet = $this->sizeRandomiser($target, $isMainPlanet);

			$planet->metal = config('settings.baseMetalProduction');
			$planet->crystal = config('settings.baseCrystalProduction');
			$planet->deuterium = config('settings.baseDeuteriumProduction');

			$planet->galaxy = $target->getGalaxy();
			$planet->system = $target->getSystem();
			$planet->planet = $target->getPlanet();

			$planet->planet_type = Coordinates::TYPE_PLANET;

			if ($target->getType() == Coordinates::TYPE_MILITARY_BASE) {
				$planet->planet_type = Coordinates::TYPE_MILITARY_BASE;
			}

			$planet->user_id = $userId;
			$planet->last_update = now();
			$planet->name = empty($title) ? __('main.sys_colo_defaultname') : $title;

			if ($planet->save()) {
				return $planet->id;
			}
		}

		return false;
	}

	public function createMoon(Coordinates $target, $userId, $chance): ?int
	{
		$planet = Planet::findByCoordinates(new Coordinates($target->getGalaxy(), $target->getSystem(), $target->getPlanet(), 1));

		if ($planet && $planet->parent_planet == 0) {
			$maxtemp = $planet->temp_max - random_int(10, 45);
			$mintemp = $planet->temp_min - random_int(10, 45);

			$chance = min($chance, 20);

			$size = floor(((random_int(10, 20) + 3 * $chance) ** 0.5) * 1000);

			$moon = (new Planet([
				'name' => __('main.sys_moon'),
				'user_id' => $userId,
				'galaxy' => $target->getGalaxy(),
				'system' => $target->getSystem(),
				'planet' => $target->getPlanet(),
				'planet_type' => Coordinates::TYPE_MOON,
				'last_update' => time(),
				'image' => 'mond',
				'diameter' => $size,
				'field_max' => 1,
				'temp_min' => $maxtemp,
				'temp_max' => $mintemp,
			]));

			$moon->save();

			if ($moon->id > 0) {
				$planet->parent_planet = $moon->id;
				$planet->update();

				return $moon->id;
			}
		}

		return null;
	}

	public function isPositionFree(Coordinates $target)
	{
		if ($target->isEmpty()) {
			return false;
		}

		$exist = Planet::query()->where('galaxy', $target->getGalaxy())
			->where('system', $target->getSystem())
			->where('planet', $target->getPlanet());

		if ($target->getType()) {
			$exist->where('planet_type', $target->getType());
		}

		return !$exist->count();
	}

	public function getFreePositions(Coordinates $target, $startPosition, $endPosition)
	{
		$planets = Planet::query()
			->where('galaxy', $target->getGalaxy())
			->where('system', $target->getSystem())
			->where('planet', '>=', $startPosition)
			->where('planet', '<=', $endPosition)
			->get()
			->keyBy('planet');

		$result = [];

		for ($i = $startPosition; $i <= $endPosition; $i++) {
			if (!isset($planets[$i])) {
				$result[] = $i;
			}
		}

		return $result;
	}

	public function sizeRandomiser(Coordinates $target, $isMainPlanet = false)
	{
		$planetData = [];
		require(resource_path('engine/planet.php'));

		$position = $target->getPlanet();

		$planet = new Planet();

		if ($isMainPlanet) {
			$planet->field_max = (int) config('settings.initial_fields', 163);
		} elseif ($target->getType() === Coordinates::TYPE_MILITARY_BASE) {
			$planet->field_max = (int) config('settings.initial_base_fields', 10);
		} else {
			$planet->field_max = (int) floor($planetData[$position]['fields'] * (int) config('settings.planetFactor', 1));
		}

		$planet->diameter = (int) floor(1000 * sqrt($planet->field_max));

		$planet->temp_max = $planetData[$position]['temp'];
		$planet->temp_min = $planet->temp_max - 40;

		if ($target->getType() === Coordinates::TYPE_MILITARY_BASE) {
			$planet->image = 'baseplanet01';
		} else {
			$imageNames = array_keys($planetData[$position]['image']);
			$imageNameType = $imageNames[array_rand($imageNames)];

			$planet->image  = $imageNameType;
			$planet->image .= 'planet';
			$planet->image .= $planetData[$position]['image'][$imageNameType] < 10 ? '0' : '';
			$planet->image .= $planetData[$position]['image'][$imageNameType];
		}

		return $planet;
	}
}
