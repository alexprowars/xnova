<?php

namespace App\Engine\Ai;

use App\Engine\Ai\Combat\ThreatAssessment;
use App\Engine\Ai\Development\DevelopmentManager;
use App\Engine\Ai\Development\StrategyPlanner;
use App\Engine\Ai\Development\StrategyType;
use App\Engine\Ai\Fleet\FleetCommander;
use App\Engine\Ai\Runtime\RunContext;
use App\Engine\Ai\Runtime\TurnSchedule;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\QueueManager;
use App\Facades\Galaxy;
use App\Models\Ai;
use App\Models\Planet;
use App\Services\OfficierService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Collection;

class AiPlayer
{
	public function __construct(private Ai $ai, private RunContext $context = new RunContext())
	{
	}

	public function run(bool $force = true): void
	{
		$user = $this->ai->user;
		$schedule = new TurnSchedule($this->ai->id);
		$nextRun = $schedule->nextRun('fleet');

		if (!$user || $user->isVacation() || $user->blocked_at) {
			$this->ai->update(['next_run_at' => $nextRun]);

			return;
		}

		if (!$user->planet_current && !$user->planet_id && $user->race) {
			$planet = Galaxy::createPlanetByUser($user);

			if ($planet) {
				$this->context->recordPlanet($planet);
			}
		}

		$planets = $user->planets()
			->whereNull('destroyed_at')
			->where('planet_type', PlanetType::PLANET)
			->with('entities')
			->orderBy('id')
			->get();

		$hub = $planets->sortByDesc(fn(Planet $planet) => $planet->getLevel(31))->first();
		$state = $this->ai->state ?? [];
		$planetIds = array_fill_keys($planets->modelKeys(), true);

		foreach (['schedule', 'development', 'development_goals', 'shipyard_batch', 'resource_requests', 'fleet_requests'] as $key) {
			$state[$key] = array_intersect_key($state[$key] ?? [], $planetIds);
		}

		$this->ai->state = $state;

		foreach ($planets as $planet) {
			$timing = $this->ai->state['schedule'][$planet->id] ?? [];
			$fleetDue = $force || ($timing['fleet_at'] ?? 0) <= now()->timestamp;
			$developmentDue = $force || ($timing['development_at'] ?? 0) <= now()->timestamp;

			if (!$fleetDue && !$developmentDue) {
				$nextRun = $nextRun->min(now()->setTimestamp(min($timing['fleet_at'], $timing['development_at'])));
				continue;
			}

			$planet->setRelation('user', $this->ai->user);
			$planet->getProduction()->reset();

			if ($fleetDue) {
				$commander = new FleetCommander($planet, $this->ai, $this->context, $planets);
				$commander->run();
				$state = $commander->getState();
				$state['schedule'][$planet->id]['fleet_at'] = $schedule->nextRun('fleet')->timestamp;
				$this->ai->state = $state;
			}

			if ($developmentDue) {
				$this->develop($planet, $hub, $planets, $schedule);
			}

			$timing = $this->ai->state['schedule'][$planet->id] ?? [];
			$nextRun = $nextRun->min(now()->setTimestamp(min(
				$timing['fleet_at'] ?? $nextRun->timestamp,
				$timing['development_at'] ?? $nextRun->timestamp,
			)));
		}

		$this->ai->user->refresh();
		$this->ai->user->update(['onlinetime' => now()]);
		UserService::checkLevelXp($this->ai->user);

		if ($this->hireOfficer()) {
			$nextRun = $schedule->earliestRun();
			$state = $this->ai->state ?? [];

			foreach ($planets as $planet) {
				$state['schedule'][$planet->id]['development_at'] = $nextRun->timestamp;
			}

			$this->ai->state = $state;
		}

		$this->ai->update([
			'state' => $this->ai->state,
			'next_run_at' => $nextRun->max($schedule->earliestRun()),
		]);
	}

	public function defend(): void
	{
		$user = $this->ai->user;

		if (!$user || $user->isVacation() || $user->blocked_at) {
			return;
		}

		$planets = $user->planets()
			->whereNull('destroyed_at')
			->where('planet_type', PlanetType::PLANET)
			->whereExists(ThreatAssessment::incoming()
				->selectRaw('1')
				->whereNot('fleets.user_id', $user->id)
				->whereColumn('end_galaxy', 'planets.galaxy')
				->whereColumn('end_system', 'planets.system')
				->whereColumn('end_planet', 'planets.planet')
			->whereColumn('end_type', 'planets.planet_type'))
			->with('entities')->orderBy('id')->get();

		foreach ($planets as $planet) {
			$planet->setRelation('user', $user);
			$planet->getProduction()->reset();
			new FleetCommander($planet, $this->ai, $this->context)->defend();
		}

		$this->ai->user->refresh();
		$this->ai->user->update(['onlinetime' => now()]);
		UserService::checkLevelXp($this->ai->user);
	}

	/** @return array<array<int|string|bool>> */
	public function preview(): array
	{
		$user = $this->ai->user;

		if (!$user || $user->isVacation() || $user->blocked_at) {
			return [];
		}

		$planets = $user->planets()
			->whereNull('destroyed_at')
			->where('planet_type', PlanetType::PLANET)
			->with('entities')
			->orderBy('id')
			->get();
		$hub = $planets->sortByDesc(fn(Planet $planet) => $planet->getLevel(31))->first();
		$rows = [];

		foreach ($planets as $planet) {
			$planet->setRelation('user', $user);
			$planet->getProduction()->getResourceProduction();
			$state = $this->ai->state ?? [];
			$planner = $this->planner($planet, $hub, $planets);

			$goals = [
				'build' => $planner->getRecommendations(ItemType::BUILDING),
				'tech' => $planner->getRecommendations(ItemType::TECH),
				'fleet' => array_merge(
					$planner->getRecommendations(ItemType::FLEET),
					$planner->getRecommendations(ItemType::DEFENSE),
				),
			];

			foreach ($goals as $category => $items) {
				$item = new DevelopmentManager($this->ai, $this->context)->selectGoal($planet, $planner, $items, $category, $state);

				if ($item) {
					$entity = $planner->getEntity($item['id']);
					$rows[] = [
						$user->id,
						$planet->id,
						$entity->getObject()->getType()->value,
						$item['id'],
						$item['count'],
						$entity->canConstruct() ? 'yes' : 'saving',
						$item['reason'],
					];
				}
			}
		}

		return $rows;
	}

	/** @param Collection<int, Planet> $planets */
	private function develop(
		Planet $planet,
		?Planet $hub,
		Collection $planets,
		TurnSchedule $schedule,
	): void
	{
		$planner = $this->planner($planet, $hub, $planets);
		$planner->prepare(onlyAvailableQueues: true);

		$planet->getConnection()->transaction(function () use ($planet, $schedule, $planner) {
			$queue = $this->preparePlanet($planet);

			if (!$queue) {
				return;
			}

			$state = $this->ai->state ?? [];

			if (!$planner->isCurrent()) {
				// Перепланируем без блокировок в следующий ход развития.
				$state['schedule'][$planet->id]['development_at'] = $schedule->nextRun('development')->timestamp;
				$this->ai->state = $state;

				return;
			}

			$queue->loadQueue(true);

			$resourceHours = new DevelopmentManager($this->ai, $this->context)->develop($planet, $queue, $planner, $state);

			$state['schedule'][$planet->id]['development_at'] = $schedule->nextDevelopmentRun(
				$planet,
				$queue,
				$resourceHours,
			)->timestamp;
			$this->ai->update(['state' => $state]);
		});
	}

	/** @param Collection<int, Planet> $planets */
	private function planner(Planet $planet, ?Planet $hub, Collection $planets): StrategyPlanner
	{
		$state = $this->ai->state ?? [];
		$probes = max([7, ...array_column($state['targets'] ?? [], 'required_probes')]);
		$request = $state['fleet_requests'][$planet->id] ?? [];
		$units = ($request['expires_at'] ?? 0) > now()->timestamp ? $request['units'] : [];

		return new StrategyPlanner($planet, $this->ai->strategy, $planet->id === $hub?->id, $probes, $planets, $units);
	}

	private function hireOfficer(): bool
	{
		$user = $this->ai->user;

		$code = match ($this->ai->strategy) {
			StrategyType::ECONOMY, StrategyType::BALANCED => 'geologist',
			StrategyType::MILITARY => 'mercenary',
		};

		if ($user->created_at?->greaterThan(now()->subDays(3))) {
			$code = 'geologist';
		}

		if ($user->credits < OfficierService::getPrice(7) || $user->{'officier_' . $code}?->isFuture()) {
			return false;
		}

		return OfficierService::buy($user, $code, 7);
	}

	private function preparePlanet(Planet $planet): ?QueueManager
	{
		$user = $this->ai->user;
		$user->refreshForUpdate();

		$planet->unsetRelation('user')
			->unsetRelation('entities')
			->refreshForUpdate();

		if ($user->trashed() || $planet->trashed() || $planet->destroyed_at
			|| $planet->user_id !== $user->id
			|| $user->isVacation()
			|| $user->blocked_at) {
			return null;
		}

		$planet->setRelation('user', $user);
		$planet->setRelation('entities', $planet->entities()->lockForUpdate()->get());
		$planet->getProduction()->reset();
		$planet->checkUsedFields();
		$planet->getProduction()->update();

		$this->ai->setRelation('user', $planet->user);

		return new QueueManager($planet);
	}
}
