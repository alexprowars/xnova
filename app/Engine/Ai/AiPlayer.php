<?php

namespace App\Engine\Ai;

use App\Engine\Building;
use App\Engine\Entity\Defence;
use App\Engine\Entity\Ship;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\ObjectsFactory;
use App\Engine\QueueManager;
use App\Facades\Galaxy;
use App\Models\Ai;
use App\Models\Planet;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;

class AiPlayer
{
	public function __construct(private Ai $ai)
	{
	}

	public function run(): void
	{
		$user = $this->ai->user;

		if (!$user || $user->isVacation() || $user->blocked_at) {
			return;
		}

		if (!$user->planet_current && !$user->planet_id && $user->race) {
			Galaxy::createPlanetByUser($user);
		}

		$planets = $user->planets()
			->whereNull('destroyed_at')
			->where('planet_type', PlanetType::PLANET)
			->with('entities')
			->orderBy('id')
			->get();

		$hub = $planets->sortByDesc(fn(Planet $planet) => $planet->getLevel(31))->first();

		foreach ($planets as $planet) {
			$planet->getConnection()->transaction(function () use ($user, $planet, $hub) {
				$user->refreshForUpdate();
				$planet->refreshForUpdate();

				if ($planet->trashed() || $planet->destroyed_at || $planet->user_id !== $user->id || $user->isVacation() || $user->blocked_at) {
					return;
				}

				$planet->setRelation('user', $user);

				$queue = new QueueManager($planet);
				$queue->update();

				$planet->getProduction()->reset();
				$planet->getProduction()->update();
				$planet->checkUsedFields();

				$state = $this->ai->state ?? [];
				$commander = new FleetCommander($planet, $this->ai->strategy, $state);
				$commander->run();
				$state = $commander->getState();

				$probeTarget = max([7, ...array_column($state['targets'] ?? [], 'required_probes')]);
				$planner = new StrategyPlanner($planet, $this->ai->strategy, $planet->id === $hub?->id, $probeTarget);

				$this->develop($planet, new QueueManager($planet), $planner, $state);

				$this->ai->state = $state;
				$this->ai->save();
				UserService::checkLevelXp($planet->user);
				$planet->user->onlinetime = now();
				$planet->user->save();
			});
		}
	}

	/** @return array<array<int|string|bool>> */
	public function preview(): array
	{
		$user = $this->ai->user;

		if (!$user || $user->isVacation() || $user->blocked_at) {
			return [];
		}

		$planets = $user->planets()->whereNull('destroyed_at')->where('planet_type', PlanetType::PLANET)->with('entities')->orderBy('id')->get();
		$hub = $planets->sortByDesc(fn(Planet $planet) => $planet->getLevel(31))->first();
		$rows = [];

		foreach ($planets as $planet) {
			$planet->setRelation('user', $user);
			$planet->getProduction()->getResourceProduction();
			$state = $this->ai->state ?? [];
			$probeTarget = max([7, ...array_column($state['targets'] ?? [], 'required_probes')]);
			$planner = new StrategyPlanner($planet, $this->ai->strategy, $planet->id === $hub?->id, $probeTarget);

			foreach ([ItemType::BUILDING, ItemType::TECH, ItemType::FLEET, ItemType::DEFENSE] as $type) {
				$item = $planner->getRecommendations($type)[0] ?? null;

				if ($item) {
					$entity = $planner->getEntity($item['id']);
					$rows[] = [$user->id, $planet->id, $type->value, $item['id'], $item['count'], $entity->canConstruct() ? 'yes' : 'saving', $item['reason']];
				}
			}
		}

		return $rows;
	}

	private function develop(Planet $planet, QueueManager $queue, StrategyPlanner $planner, array &$state): void
	{
		$plans = [];

		if (!$queue->getCount(QueueType::BUILDING) && $planet->field_current < $planet->getMaxFields()) {
			foreach ($planner->getRecommendations(ItemType::BUILDING) as $item) {
				if ($item['id'] === 31 && config('game.BuildLabWhileRun', 0) != 1 && $planet->user->queue()->where('type', QueueType::RESEARCH)->exists()) {
					continue;
				}

				// Последнее поле оставляем для расширения; его требования планируем заранее.
				if ($planet->getMaxFields() - $planet->field_current <= 1 && $item['id'] !== 33) {
					continue;
				}

				$plans['build'] = $item;
				break;
			}
		}

		if (!$planet->user->queue()->where('type', QueueType::RESEARCH)->exists() && !Building::checkLabInQueue($planet)) {
			$research = $planner->getRecommendations(ItemType::TECH);

			if (!empty($research)) {
				$plans['tech'] = $research[0];
			}
		}

		if (!$queue->getCount(QueueType::SHIPYARD)) {
			$units = array_merge($planner->getRecommendations(ItemType::FLEET), $planner->getRecommendations(ItemType::DEFENSE));
			usort($units, fn(array $a, array $b) => $b['score'] <=> $a['score'] ?: $a['id'] <=> $b['id']);

			if (!empty($units)) {
				$plans['fleet'] = $units[0];
			}
		}

		if (empty($plans)) {
			return;
		}

		$rotation = match ($this->ai->strategy) {
			StrategyType::ECONOMY => ['build', 'build', 'tech', 'fleet'],
			StrategyType::MILITARY => ['build', 'tech', 'fleet', 'fleet'],
			StrategyType::BALANCED => ['build', 'tech', 'fleet'],
		};

		$cursor = ($state['development'][$planet->id] ?? 0) % count($rotation);

		$primary = array_key_first($plans);

		for ($i = 0; $i < count($rotation); $i++) {
			$position = ($cursor + $i) % count($rotation);

			if (isset($plans[$rotation[$position]])) {
				$primary = $rotation[$position];
				$cursor = $position;
				break;
			}
		}

		if (isset($plans['build']) && ($plans['build']['score'] >= 700 || $this->waitHours($planet, $planner, $plans[$primary]) > (float)config('ai.saving_horizon_hours', 6))) {
			$primary = 'build';
		}

		$order = [$primary => $plans[$primary]] + $plans;
		$reserve = [];

		foreach ($order as $category => $item) {
			if (($category === 'tech' && Building::checkLabInQueue($planet)) || ($item['id'] === 31 && config('game.BuildLabWhileRun', 0) != 1 && $planet->user->queue()->where('type', QueueType::RESEARCH)->exists())) {
				continue;
			}

			$entity = $planner->getEntity($item['id']);
			$price = $entity->getPrice();
			$count = $item['count'];

			foreach (['metal', 'crystal', 'deuterium'] as $resource) {
				if (($price[$resource] ?? 0) > 0) {
					$count = min($count, max(0, (int)floor(($planet->{$resource} - ($reserve[$resource] ?? 0)) / $price[$resource])));
				}
			}

			if ($entity instanceof Ship || $entity instanceof Defence) {
				$count = min($count, max(1, (int)floor((float)config('ai.shipyard_hours', 2) * 3600 / max(1, $entity->getTime()))));
			}

			if ($count > 0 && $entity->isAvailable() && $entity->canConstruct()) {
				$before = $queue->get()->pluck('id')->all();
				$queue->add(ObjectsFactory::get($item['id']), $count);
				$queue->loadQueue();
				$added = $queue->get()->whereNotIn('id', $before)->first();

				if ($added) {
					if ($category === $primary) {
						$state['development'][$planet->id] = ($cursor + 1) % count($rotation);
					}

					if (config('ai.log_decisions', true)) {
						Log::info('ai.development', ['user_id' => $planet->user_id, 'planet_id' => $planet->id, 'object_id' => $item['id'], 'count' => $count, 'reason' => $item['reason']]);
					}

					continue;
				}
			}

			if ($category === $primary) {
				// Копим на выбранную цель; остальные очереди используют только излишки.
				$reserve = $price;
			}
		}
	}

	private function waitHours(Planet $planet, StrategyPlanner $planner, array $item): float
	{
		$price = $planner->getEntity($item['id'])->getPrice();
		$production = $planet->getProduction()->getResourceProduction();
		$hours = 0.0;

		foreach (['metal', 'crystal', 'deuterium'] as $resource) {
			$missing = max(0, ($price[$resource] ?? 0) - $planet->{$resource});

			if ($missing > 0) {
				$hours = max($hours, $production->get($resource) > 0 ? $missing / $production->get($resource) : INF);
			}
		}

		return $hours;
	}
}
