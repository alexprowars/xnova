<?php

namespace App\Services;

use App\Engine\Coordinates;
use App\Engine\Enums\PlanetType;
use App\Models\Planet;
use App\Models\User;
use App\Settings;

class GalaxyService
{
	public function createPlanetByUser(User $user): ?Planet
	{
		$settings = app(Settings::class);

		$galaxyPos = (int) ($settings->lastSettedGalaxyPos ?? 1);
		$systemPos = (int) ($settings->lastSettedSystemPos ?? 1);
		$planetPos = (int) ($settings->lastSettedPlanetPos ?? 1);

		do {
			$isFree = $this->getFreePositions(
				new Coordinates($galaxyPos, $systemPos),
				(int) round(config('game.maxPlanetInSystem') * 0.2),
				(int) round(config('game.maxPlanetInSystem') * 0.8)
			);

			if (!empty($isFree)) {
				$position = $isFree[array_rand($isFree)];
			} else {
				$position = 0;
			}

			if ($position > 0 && $planetPos < config('game.maxRegPlanetsInSystem', 3)) {
				$planetPos++;
			} else {
				$planetPos = 1;

				if ($systemPos >= config('game.maxSystemInGalaxy')) {
					$systemPos = 1;

					if ($galaxyPos >= config('game.maxGalaxyInWorld')) {
						$galaxyPos = 1;
					} else {
						$galaxyPos++;
					}
				} else {
					$systemPos++;
				}
			}
		} while (!$this->isPositionFree(new Coordinates($galaxyPos, $systemPos, $position)));

		$planet = $this->createPlanet(
			new Coordinates($galaxyPos, $systemPos, $position),
			$user,
			__('main.sys_plnt_defaultname'),
			true
		);

		if ($planet) {
			$settings->lastSettedGalaxyPos = $galaxyPos;
			$settings->lastSettedSystemPos = $systemPos;
			$settings->lastSettedPlanetPos = $planetPos;
			$settings->save();

			$user->setMainPlanet($planet);
		}

		return $planet;
	}

	public function createPlanet(Coordinates $target, User $user, ?string $title = null, bool $mainPlanet = false): ?Planet
	{
		if (!$this->isPositionFree($target)) {
			return null;
		}

		$planet = new Planet();
		$planet->galaxy = $target->getGalaxy();
		$planet->system = $target->getSystem();
		$planet->planet = $target->getPlanet();
		$planet->planet_type = PlanetType::PLANET;

		if ($target->getType() == PlanetType::MILITARY_BASE) {
			$planet->planet_type = PlanetType::MILITARY_BASE;
		}

		$this->sizeRandomiser($planet, $mainPlanet);

		$planet->metal = (int) config('game.baseMetalProduction');
		$planet->crystal = (int) config('game.baseCrystalProduction');
		$planet->deuterium = (int) config('game.baseDeuteriumProduction');

		$planet->user()->associate($user);
		$planet->name = empty($title) ? __('main.sys_colo_defaultname') : $title;

		if ($planet->save()) {
			$planet->refresh();
			$planet->setRelation('user', $user);

			return $planet;
		}

		return null;
	}

	public function createMoon(Coordinates $target, User $user, $chance): ?Planet
	{
		$planet = Planet::findByCoordinates(new Coordinates($target->getGalaxy(), $target->getSystem(), $target->getPlanet(), PlanetType::PLANET));

		if ($planet && !$planet->moon_id) {
			$maxtemp = $planet->temp_max - random_int(10, 45);
			$mintemp = $planet->temp_min - random_int(10, 45);

			$chance = min($chance, 20);

			$size = floor(((random_int(10, 20) + 3 * $chance) ** 0.5) * 1000);

			$moon = new Planet([
				'name' => __('main.sys_moon'),
				'galaxy' => $target->getGalaxy(),
				'system' => $target->getSystem(),
				'planet' => $target->getPlanet(),
				'planet_type' => PlanetType::MOON,
				'image' => 'mond',
				'diameter' => $size,
				'field_max' => 1,
				'temp_min' => $maxtemp,
				'temp_max' => $mintemp,
			]);

			$moon->user()->associate($user);

			if ($moon->save()) {
				$planet->moon()->associate($moon);
				$planet->save();

				return $moon;
			}
		}

		return null;
	}

	public function isPositionFree(Coordinates $target): bool
	{
		if ($target->isEmpty()) {
			return false;
		}

		return !Planet::query()->coordinates($target)->exists();
	}

	public function getFreePositions(Coordinates $target, $startPosition, $endPosition): array
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

	public function sizeRandomiser(Planet $planet, $mainPlanet = false): Planet
	{
		/** @var array<int, array> $planetData */
		$planetData = [];
		require(resource_path('engine/planet.php'));

		$position = $planet->planet;

		if ($mainPlanet) {
			$planet->field_max = (int) config('game.initial_fields', 163);
		} elseif ($planet->planet_type === PlanetType::MILITARY_BASE) {
			$planet->field_max = (int) config('game.initial_base_fields', 10);
		} else {
			$planet->field_max = (int) floor($planetData[$position]['fields'] * (int) config('game.planetFactor', 1));
		}

		$planet->diameter = (int) floor(1000 * sqrt($planet->field_max));

		$planet->temp_max = $planetData[$position]['temp'];
		$planet->temp_min = $planet->temp_max - 40;

		if ($planet->planet_type === PlanetType::MILITARY_BASE) {
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
