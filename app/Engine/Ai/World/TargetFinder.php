<?php

namespace App\Engine\Ai\World;

use App\Engine\Ai\Runtime\RunContext;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\FleetCollection;
use App\Engine\Fleet\MissionType;
use App\Models\LogsFleet;
use App\Models\Planet;
use App\Models\Statistic;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class TargetFinder
{
	private array $targetWeights = [];

	public function __construct(private Planet $planet, private RunContext $context, private array $state)
	{
	}

	public function weight(int $planetId): float
	{
		return $this->targetWeights[$planetId] ?? 1.0;
	}

	/** @return list<Planet> */
	public function find(): array
	{
		$radius = (int) config('ai.search_radius', 50);
		$galaxyRadius = (int) config('ai.search_galaxy_radius', 1);
		$targetLimit = (int) config('ai.target_limit', 30);
		$cacheKey = 'ai:targets:v2:' . implode(':', [
			$this->planet->id,
			$this->planet->user_id,
			$this->planet->galaxy,
			$this->planet->system,
			$this->planet->planet,
			$radius,
			$galaxyRadius,
			(int) config('game.maxGalaxyInWorld'),
			$targetLimit,
		]);

		// Сохраняем итог отбора вместе с данными целей и весами, без повторных запросов при чтении.
		$cached = Cache::remember(
			$cacheKey,
			now()->addMinutes((int) config('ai.target_cache_minutes', 30)),
			function () use ($radius, $galaxyRadius, $targetLimit): array {
				return array_map(
					fn(Planet $target) => [
						'planet' => Arr::only(
							$target->getAttributes(),
							['id', 'user_id', 'galaxy', 'system', 'planet', 'planet_type'],
						),
						'user' => ['id' => $target->user_id, 'username' => $target->user->username],
						'weight' => $this->targetWeights[$target->id],
					],
					$this->selectTargets($radius, $galaxyRadius, $targetLimit),
				);
			},
		);
		$targets = [];
		$this->targetWeights = [];

		foreach ($cached as $item) {
			$target = new Planet()->newFromBuilder($item['planet']);
			$target->setRelation('user', new User()->newFromBuilder($item['user']));
			$this->targetWeights[$target->id] = $item['weight'];
			$targets[] = $target;
		}

		return $targets;
	}

	/** @return array<Planet> */
	private function selectTargets(int $radius, int $galaxyRadius, int $targetLimit): array
	{
		$query = Planet::query()
			->select(['id', 'user_id', 'galaxy', 'system', 'planet', 'planet_type'])
			->with('user:id,username,onlinetime')
			->whereNot('user_id', $this->planet->user_id)
			->where('planet_type', PlanetType::PLANET)
			->whereNull('destroyed_at')
			->whereHas(
				'user',
				function (Builder $query) {
					$query->whereNull('vacation')->whereNull('blocked_at')->whereDoesntHave('roles');
				},
			);
		/** @var Collection<int, Planet> $targets */
		$targets = new Collection();
		$firstGalaxy = max(1, $this->planet->galaxy - $galaxyRadius);
		$lastGalaxy = min((int) config('game.maxGalaxyInWorld'), $this->planet->galaxy + $galaxyRadius);

		for ($galaxy = $firstGalaxy; $galaxy <= $lastGalaxy; $galaxy++) {
			$galaxyQuery = (clone $query)->where('galaxy', $galaxy);

			if ($galaxy === $this->planet->galaxy) {
				$galaxyQuery->whereBetween('system', [max(1, $this->planet->system - $radius), $this->planet->system + $radius])
					->orderByRaw('ABS(`system` - ?)', [$this->planet->system])
					->orderBy('id');
				$targets = $targets->merge($galaxyQuery->limit($targetLimit * 5)->get());
			} else {
				// Выборка по индексу id вместо сортировки всей галактики через RAND().
				$maximum = Cache::remember(
					'ai:planet-id:' . $galaxy,
					now()->addSeconds((int) config('ai.world_cache_seconds', 300)),
					fn() => Planet::query()->where('galaxy', $galaxy)->max('id') ?? 1,
				);
				$pivot = random_int(1, max(1, $maximum));
				$sample = (clone $galaxyQuery)->where('id', '>=', $pivot)
					->orderBy('id')
					->limit($targetLimit * 5)
					->get();

				if ($sample->count() < $targetLimit * 5) {
					$sample = $sample->merge($galaxyQuery->where('id', '<', $pivot)
						->orderBy('id')
						->limit($targetLimit * 5 - $sample->count())
						->get());
				}

				$targets = $targets->merge($sample);
			}
		}

		$bots = $this->context->bots();
		$activeHumansThreshold = (int) config('ai.active_humans_threshold', 10);
		$preferBots = $activeHumansThreshold > 0 && $this->context->activeHumans() < $activeHumansThreshold;
		$points = Statistic::query()
			->where('stat_type', 1)
			->where('stat_code', 1)
			->whereIn('user_id', [...$targets->pluck('user_id')->all(), $this->planet->user_id])
			->pluck('total_points', 'user_id');
		$cappedTargets = LogsFleet::query()
			->where('s_id', $this->planet->user_id)
			->where('mission', MissionType::Attack)
			->where('amount', '>=', 3)
			->where('created_at', '>=', now()->startOfDay())
			->get(['e_galaxy', 'e_system', 'e_planet'])
			->mapWithKeys(fn(LogsFleet $log) => [$log->e_galaxy . ':' . $log->e_system . ':' . $log->e_planet => true])
			->all();
		$eligible = [];
		$distances = [];
		$distanceCalculator = new FleetCollection();

		foreach ($targets as $target) {
			$user = $target->user;

			$key = $target->galaxy . ':' . $target->system . ':' . $target->planet;
			if (isset($cappedTargets[$key])) {
				continue;
			}

			if (config('game.noobprotection') && (int) config('game.noobprotectionPoints') > 0
				&& $user->onlinetime?->greaterThan(now()->subDays(7))) {
				$theirPoints = $points[$user->id] ?? 0;
				$factor = (int) config('game.noobprotectionFactor');

				if ($theirPoints < (int) config('game.noobprotectionPoints')
					|| ($factor > 0 && ($points[$this->planet->user_id] ?? 0) > $theirPoints * $factor)) {
					continue;
				}
			}

			$memory = $this->state['targets'][$target->id] ?? [];

			if (($memory['attacked_at'] ?? 0) > now()->subMinutes((int) config('ai.attack_cooldown_minutes', 180))->timestamp) {
				continue;
			}

			$this->targetWeights[$target->id] = $preferBots && isset($bots[$user->id])
				? (float) config('ai.bot_target_bonus', 3)
				: 1.0;
			$distances[$target->id] = $distanceCalculator->getDistance($this->planet->coordinates, $target->coordinates);
			$eligible[] = $target;
		}

		usort(
			$eligible,
			function (Planet $a, Planet $b) use ($distances) {
				$scoreA = $this->targetWeights[$a->id] / (1 + $distances[$a->id] / 950);
				$scoreB = $this->targetWeights[$b->id] / (1 + $distances[$b->id] / 950);

				return $scoreB <=> $scoreA
					?: ($this->state['targets'][$a->id]['scouted_at'] ?? 0)
						<=> ($this->state['targets'][$b->id]['scouted_at'] ?? 0);
			},
		);

		$byGalaxy = [];

		foreach ($eligible as $target) {
			$byGalaxy[$target->galaxy][] = $target;
		}

		// Ближайшие цели не должны вытеснять соседние галактики из ограниченной выборки.
		$selected = [];

		while (!empty($byGalaxy) && count($selected) < $targetLimit) {
			foreach (array_keys($byGalaxy) as $galaxy) {
				$selected[] = array_shift($byGalaxy[$galaxy]);

				if (empty($byGalaxy[$galaxy])) {
					unset($byGalaxy[$galaxy]);
				}

				if (count($selected) === $targetLimit) {
					break;
				}
			}
		}

		return $selected;
	}
}
