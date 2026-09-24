<?php

namespace App\Engine\Ai;

use App\Engine\Coordinates;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\MissionType;
use App\Models\Ai;
use App\Models\Fleet;
use App\Models\Planet;

class ColonizationMap
{
	/** @var array<string, array{coordinates: Coordinates, population: int, owners: array<int, true>, index: int}> */
	private array $systems = [];
	/** @var array<int, list<string>> */
	private array $buckets = [];
	private array $planets = [];
	private array $fleets = [];

	public function __construct()
	{
		$maxGalaxies = (int) config('game.maxGalaxyInWorld');
		$maxSystems = (int) config('game.maxSystemInGalaxy');
		$this->buckets[0] = [];

		for ($galaxy = 1; $galaxy <= $maxGalaxies; $galaxy++) {
			for ($system = 1; $system <= $maxSystems; $system++) {
				$key = $galaxy . ':' . $system;
				$this->systems[$key] = ['coordinates' => new Coordinates($galaxy, $system), 'population' => 0, 'owners' => [], 'index' => count($this->buckets[0])];
				$this->buckets[0][] = $key;
			}
		}

		$planets = Planet::query()->whereIn('user_id', Ai::query()->select('user_id'))
			->where('planet_type', PlanetType::PLANET)->whereNull('destroyed_at')->get(['id', 'galaxy', 'system', 'user_id']);

		foreach ($planets as $planet) {
			$this->recordPlanet($planet);
		}

		$fleets = Fleet::query()->whereIn('user_id', Ai::query()->select('user_id'))
			->where('mission', MissionType::Colonization)->where('mess', 0)->get(['id', 'end_galaxy', 'end_system', 'user_id', 'mission', 'mess']);

		foreach ($fleets as $fleet) {
			$this->recordFleet($fleet);
		}
	}

	/** @return iterable<Coordinates> */
	public function candidates(int $userId): iterable
	{
		$populations = array_keys($this->buckets);
		sort($populations, SORT_NUMERIC);
		$maxBots = (int) config('ai.max_bots_per_system', 3);

		foreach ($populations as $population) {
			$keys = $this->buckets[$population];

			if (empty($keys)) {
				continue;
			}

			$count = count($keys);
			// Случайная стартовая система при равной заселённости, без сортировки всей вселенной.
			$start = random_int(0, $count - 1);

			for ($offset = 0; $offset < $count; $offset++) {
				$system = $this->systems[$keys[($start + $offset) % $count]];

				if (!isset($system['owners'][$userId]) && count($system['owners']) < $maxBots) {
					yield clone $system['coordinates'];
				}
			}
		}
	}

	public function recordPlanet(Planet $planet): void
	{
		if (isset($this->planets[$planet->id])) {
			return;
		}

		$this->planets[$planet->id] = true;
		$this->add($planet->galaxy, $planet->system, $planet->user_id);
	}

	public function recordFleet(Fleet $fleet): void
	{
		if ($fleet->mission !== MissionType::Colonization || $fleet->mess != 0 || isset($this->fleets[$fleet->id])) {
			return;
		}

		$this->fleets[$fleet->id] = true;
		$this->add($fleet->end_galaxy, $fleet->end_system, $fleet->user_id);
	}

	private function add(int $galaxy, int $system, int $userId): void
	{
		$key = $galaxy . ':' . $system;

		if (!isset($this->systems[$key])) {
			return;
		}

		$population = $this->systems[$key]['population'];
		$index = $this->systems[$key]['index'];
		$last = array_pop($this->buckets[$population]);

		// Удаляем систему из группы за O(1), перемещая последнюю на её место.
		if ($last !== $key) {
			$this->buckets[$population][$index] = $last;
			$this->systems[$last]['index'] = $index;
		}

		if (empty($this->buckets[$population])) {
			unset($this->buckets[$population]);
		}

		$population++;
		$this->buckets[$population] ??= [];
		$this->systems[$key]['population'] = $population;
		$this->systems[$key]['owners'][$userId] = true;
		$this->systems[$key]['index'] = count($this->buckets[$population]);
		$this->buckets[$population][] = $key;
	}
}
