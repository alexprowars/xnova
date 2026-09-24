<?php

namespace App\Engine\Ai\Development;

use App\Engine\Ai\Runtime\RunContext;
use App\Engine\Building;
use App\Engine\Entity\Defence;
use App\Engine\Entity\Ship;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\ObjectsFactory;
use App\Engine\QueueManager;
use App\Models\Ai;
use App\Models\Planet;
use Illuminate\Support\Facades\Log;

class DevelopmentManager
{
	public function __construct(private Ai $ai, private RunContext $context = new RunContext())
	{
	}

	public function develop(
		Planet $planet,
		QueueManager $queue,
		StrategyPlanner $planner,
		array &$state,
	): ?float
	{
		$plans = $this->collectPlans($planet, $queue, $planner, $state);

		if (empty($plans)) {
			$this->context->decision($planet->user_id, 'development_busy_or_unavailable');

			return null;
		}

		$rotation = match ($this->ai->strategy) {
			StrategyType::ECONOMY => ['build', 'build', 'tech', 'fleet'],
			StrategyType::MILITARY => ['build', 'tech', 'fleet', 'fleet'],
			StrategyType::BALANCED => ['build', 'tech', 'fleet'],
		};

		$cursor = ($state['development'][$planet->id] ?? 0) % count($rotation);
		$horizon = max(1 / 60, (float) config('ai.saving_horizon_hours', 3));
		$nearby = array_filter($plans, fn(array $item) => $this->waitHours($planet, $planner, $item) <= $horizon);
		$eligible = !empty($nearby) ? $nearby : $plans;

		// Срочная стройка не блокирует остальные очереди накоплением на недели.
		if (isset($nearby['build']) && $nearby['build']['score'] >= 700) {
			$eligible = ['build' => $nearby['build']];
		}

		$primary = array_key_first($eligible);

		for ($i = 0; $i < count($rotation); $i++) {
			$position = ($cursor + $i) % count($rotation);

			if (isset($eligible[$rotation[$position]])) {
				$primary = $rotation[$position];
				$cursor = $position;
				break;
			}
		}

		$order = [$primary => $plans[$primary]] + $plans;
		$reserve = [];
		$waiting = [];

		foreach ($order as $category => $item) {
			if (($category === 'tech' && Building::checkLabInQueue($planet, $queue))
				|| ($item['id'] === 31 && config('game.BuildLabWhileRun', 0) != 1
					&& $queue->getResearch())) {
				continue;
			}

			$entity = $planner->getEntity($item['id']);
			$price = $entity->getPrice();
			$count = $item['count'];

			foreach (['metal', 'crystal', 'deuterium'] as $resource) {
				if (($price[$resource] ?? 0) > 0) {
					$count = min(
						$count,
						max(
							0,
							(int) floor(($planet->{$resource} - ($reserve[$resource] ?? 0)) / $price[$resource]),
						),
					);
				}
			}

			if ($entity instanceof Ship || $entity instanceof Defence) {
				$count = min(
					$count,
					max(1, (int) floor((float) config('ai.shipyard_hours', 2) * 3600 / max(1, $entity->getTime()))),
				);
			}

			if ($count >= ($item['minimum_count'] ?? 1) && $entity->isAvailable()
				&& $entity->canConstruct()) {
				$added = $queue->add(ObjectsFactory::get($item['id']), $count);

				if ($added) {
					$this->context->decision($planet->user_id, 'development_started');
					unset($state['development_goals'][$planet->id][$category][$item['id']]);

					if ($category === 'fleet') {
						unset($state['shipyard_batch'][$planet->id]);
					}

					if ($category === $primary) {
						$state['development'][$planet->id] = ($cursor + 1) % count($rotation);
					}

					if (config('ai.log_decisions', true)) {
						Log::info('ai.development', [
							'user_id' => $planet->user_id,
							'planet_id' => $planet->id,
							'object_id' => $item['id'],
							'count' => $count,
							'reason' => $item['reason'],
						]);
					}

					continue;
				}
			}

			if ($category === $primary) {
				// Копим на выбранную цель; остальные очереди используют только излишки.
				$reserve = array_map(fn(int $amount) => $amount * ($item['minimum_count'] ?? 1), $price);
			}

			if ($category !== $primary) {
				$item['reserve'] = $reserve;
			}

			$item['category'] = $category;
			$waiting[] = $item;
		}

		if (!empty($waiting)) {
			$item = $waiting[0];
			$entity = $planner->getEntity($item['id']);
			$state['resource_requests'][$planet->id] = [
				'id' => $item['id'],
				'level' => $entity->getLevel(),
				'category' => $item['category'],
				'resources' => array_map(fn(int $amount) => $amount * ($item['minimum_count'] ?? 1), $entity->getPrice()),
				'expires_at' => now()->addHours(max(1, (float) config('ai.saving_horizon_hours', 3)) * 2)->timestamp,
			];
			$this->context->decision($planet->user_id, 'saving_resources');
		}

		$resourceHours = null;

		foreach ($waiting as $item) {
			$hours = $this->waitHours($planet, $planner, $item);

			if ($hours > 0 && is_finite($hours)) {
				$resourceHours = $resourceHours === null ? $hours : min($resourceHours, $hours);
			}
		}

		return $resourceHours;
	}

	public function selectGoal(
		Planet $planet,
		StrategyPlanner $planner,
		array $items,
		string $category,
		array &$state,
	): ?array
	{
		$horizon = max(1 / 60, (float) config('ai.saving_horizon_hours', 3));
		$previous = $state['development_goals'][$planet->id][$category] ?? [];
		$pending = [];
		$candidates = [];

		foreach ($items as $item) {
			$entity = $planner->getEntity($item['id']);
			$level = $category === 'fleet' ? 0 : $entity->getLevel();
			$goal = $previous[$item['id']] ?? null;
			$since = ($goal['level'] ?? null) === $level ? $goal['since'] : now()->timestamp;
			$pending[$item['id']] = ['level' => $level, 'since' => $since];
			$wait = $this->waitHours($planet, $planner, $item);

			// Без добычи недостающего ресурса накопление само по себе не завершится.
			if (!is_finite($wait)) {
				continue;
			}

			$duration = $entity->getTime() / 3600;
			$age = max(0, now()->timestamp - $since) / 3600;

			// За горизонт ожидания цель получает прибавку, равную базовому приоритету шахты.
			$item['priority'] = $item['score'] / (1 + ($wait + $duration) / $horizon) + 100 * $age / $horizon;
			$item['nearby'] = $wait <= $horizon;
			$candidates[] = $item;
		}

		// Удаляем завершённые и больше не нужные цели; новый уровень начинает ждать заново.
		$state['development_goals'][$planet->id][$category] = $pending;
		usort(
			$candidates,
			fn(array $a, array $b) => $b['nearby'] <=> $a['nearby']
				?: $b['priority'] <=> $a['priority'] ?: $a['id'] <=> $b['id'],
		);

		return $candidates[0] ?? null;
	}

	private function collectPlans(
		Planet $planet,
		QueueManager $queue,
		StrategyPlanner $planner,
		array &$state,
	): array
	{
		$plans = [];
		unset($state['resource_requests'][$planet->id]);

		if (!$queue->getCount(QueueType::BUILDING) && $planet->field_current < $planet->getMaxFields()) {
			$buildings = [];

			foreach ($planner->getRecommendations(ItemType::BUILDING) as $item) {
				if ($item['id'] === 31 && config('game.BuildLabWhileRun', 0) != 1
					&& $queue->getResearch()) {
					continue;
				}

				// Последнее поле оставляем для расширения; его требования планируем заранее.
				if ($planet->getMaxFields() - $planet->field_current <= 1 && $item['id'] !== 33) {
					continue;
				}

				$buildings[] = $item;
			}

			$building = $this->selectGoal($planet, $planner, $buildings, 'build', $state);

			if ($building) {
				$plans['build'] = $building;
			}
		}

		if (!$queue->getResearch() && !Building::checkLabInQueue($planet, $queue)) {
			$research = $this->selectGoal($planet, $planner, $planner->getRecommendations(ItemType::TECH), 'tech', $state);

			if ($research) {
				$plans['tech'] = $research;
			}
		}

		if (!$queue->getCount(QueueType::SHIPYARD)) {
			$units = array_merge($planner->getRecommendations(ItemType::FLEET), $planner->getRecommendations(ItemType::DEFENSE));
			$unit = $this->selectGoal($planet, $planner, $units, 'fleet', $state);

			if ($unit) {
				$plans['fleet'] = $this->prepareShipyardBatch($planet, $planner, $unit, $state);
			} else {
				unset($state['shipyard_batch'][$planet->id]);
			}
		} else {
			unset($state['shipyard_batch'][$planet->id]);
		}


		return $plans;
	}

	private function prepareShipyardBatch(
		Planet $planet,
		StrategyPlanner $planner,
		array $item,
		array &$state,
	): array
	{
		$entity = $planner->getEntity($item['id']);
		$interval = max(60, (int) config('ai.min_interval_seconds', 300));
		$batch = $state['shipyard_batch'][$planet->id] ?? null;

		if (($batch['id'] ?? null) !== $item['id']) {
			$batch = null;
			unset($state['shipyard_batch'][$planet->id]);
		}

		// Дорогие и долгие корабли можно заказывать по одному; колонизатор всегда одиночный.
		if (!$entity instanceof Ship || $item['id'] === 208 || $entity->getTime() >= $interval) {
			unset($state['shipyard_batch'][$planet->id]);

			return $item;
		}

		// После предельного срока строим доступное количество, не начиная ожидание заново.
		if (isset($batch['deadline']) && $batch['deadline'] <= now()->timestamp) {
			return $item;
		}

		$maximum = min(
			$item['count'],
			max(1, (int) floor((float) config('ai.shipyard_hours', 2) * 3600 / max(1, $entity->getTime()))),
		);
		$production = $planet->getProduction()->getResourceProduction();
		$capacity = $planet->getProduction()->getStorageCapacity();
		$horizon = max(0, (float) config('ai.saving_horizon_hours', 3));
		$prices = $entity->getPrice();

		foreach (['metal', 'crystal', 'deuterium'] as $resource) {
			$price = $prices[$resource] ?? 0;

			if ($price > 0) {
				$available = min(
					max($planet->{$resource}, $capacity->get($resource)),
					$planet->{$resource} + max(0, $production->get($resource)) * $horizon,
				);
				$maximum = min($maximum, max(1, (int) floor($available / $price)));
			}
		}

		if ($maximum < 2) {
			return $item;
		}

		$count = $batch !== null
			? min($maximum, max(2, $batch['count']))
			: random_int(max(2, (int) ceil($maximum / 2)), $maximum);
		$deadline = $batch['deadline'] ?? now()->addSeconds(max($interval, (int) ceil($horizon * 3600)))->timestamp;

		$state['shipyard_batch'][$planet->id] = [
			'id' => $item['id'],
			'count' => $count,
			'deadline' => $deadline,
		];
		$item['count'] = $count;
		$item['minimum_count'] = $count;
		$item['batch_deadline'] = $deadline;

		return $item;
	}

	private function waitHours(Planet $planet, StrategyPlanner $planner, array $item): float
	{
		$price = $planner->getEntity($item['id'])->getPrice();
		$production = $planet->getProduction()->getResourceProduction();
		$hours = 0.0;

		foreach (['metal', 'crystal', 'deuterium'] as $resource) {
			$missing = max(
				0,
				($price[$resource] ?? 0) * ($item['minimum_count'] ?? 1) + ($item['reserve'][$resource] ?? 0) - $planet->{$resource},
			);

			if ($missing > 0) {
				$hours = max($hours, $production->get($resource) > 0 ? $missing / $production->get($resource) : INF);
			}
		}

		return isset($item['batch_deadline'])
			? min($hours, max(0, ($item['batch_deadline'] - now()->timestamp) / 3600))
			: $hours;
	}
}
