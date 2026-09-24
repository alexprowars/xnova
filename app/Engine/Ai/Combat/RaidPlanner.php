<?php

namespace App\Engine\Ai\Combat;

use App\Engine\Ai\Development\StrategyType;
use App\Engine\Ai\Economy\ResourceValue;
use App\Engine\Ai\Fleet\FleetComposition;
use App\Engine\Ai\Fleet\FlightPlan;
use App\Engine\Ai\Runtime\RunContext;
use App\Engine\Entity\Ship;
use App\Engine\Fleet\MissionType;
use App\Engine\Objects\ObjectsFactory;
use App\Models\Planet;

class RaidPlanner
{
	private array $requests = [];

	public function __construct(
		private Planet $planet,
		private StrategyType $strategy,
		private RunContext $context,
	)
	{
	}

	public function requests(): array
	{
		return $this->requests;
	}

	/** @param list<array{target: Planet, report: array, weight: float}> $targets */
	public function choose(array $targets): ?array
	{
		$composition = new FleetComposition($this->planet);
		$candidates = [];

		foreach ($targets as $target) {
			$variants = [];

			foreach ($composition->raids($target['report']) as $ships) {
				$flight = new FlightPlan($this->planet, $target['target']->coordinates, $ships);

				if (!$flight->isAffordable($this->planet, MissionType::Attack, 0.5)) {
					$this->context->decision($this->planet->user_id, 'raid_fuel_or_distance');
					continue;
				}

				$profit = $flight->profit($target['report']);

				if ($profit >= (float) config('ai.min_raid_profit', 1000)) {
					$variants[] = $target + [
						'flight' => $flight,
						'score' => $profit * $target['weight'] / max(60, $flight->duration * 2),
					];
				}
			}

			if (!empty($variants)) {
				usort($variants, fn(array $a, array $b) => $b['score'] <=> $a['score']);
				$candidates[] = $variants;
			}
		}

		usort($candidates, fn(array $a, array $b) => $b[0]['score'] <=> $a[0]['score']);
		$candidates = array_slice($candidates, 0, (int) config('ai.raid_target_limit', 3));
		$forecast = new BattleForecast($this->context);
		$best = null;
		$lossRatio = (float) config('ai.max_loss_ratio', 0.15) * ($this->strategy === StrategyType::ECONOMY ? 0.5 : 1);

		// Сначала лучший состав каждой цели, затем альтернативы в пределах общего бюджета.
		for ($variant = 0; $variant < 4; $variant++) {
			foreach ($candidates as $variants) {
				$candidate = $variants[$variant] ?? null;

				if (!$candidate) {
					continue;
				}

				/** @var FlightPlan $flight */
				$flight = $candidate['flight'];
				$result = $forecast->evaluate(
					$this->planet,
					$candidate['target'],
					$flight,
					$candidate['report'],
					$flight->cost * $lossRatio,
				);

				if (!$result) {
					$this->requestReinforcements($candidate['report']);
					continue;
				}

				$profit = $flight->profit($candidate['report'], $result['loss'], $result['capacity']);
				$score = $profit * $candidate['weight'] / max(60, $flight->duration * 2);

				if ($profit >= (float) config('ai.min_raid_profit', 1000)
					&& ($best === null || $score > $best['score'])) {
					$best = array_replace($candidate, ['forecast' => $result, 'score' => $score]);
				}
			}
		}

		if (!$best) {
			$this->context->decision($this->planet->user_id, 'no_profitable_raid');
		}

		return $best;
	}

	private function requestReinforcements(array $report): void
	{
		if (!$this->context->hasSimulationBudget($this->planet->user_id)) {
			return;
		}

		$id = ($report['units'][204] ?? 0) + ($report['units'][401] ?? 0) > 0 ? 206 : 207;
		$cost = 0.0;

		foreach ($report['units'] as $unitId => $count) {
			$cost += ResourceValue::sum(ObjectsFactory::get($unitId)->getPrice()) * $count;
		}

		$unitCost = ResourceValue::sum(Ship::createEntity($id, 1, $this->planet)->getPrice());
		$limit = max(3, (int) ceil(max(1, $this->planet->getLevel(1) - 4) ** 2 / 4));
		$count = min($limit, (int) ceil($cost * (float) config('ai.combat_margin', 1.15) / max(1, $unitCost)));

		if ($count > $this->planet->getLevel($id)) {
			$this->requests[$id] = max($this->requests[$id] ?? 0, $count);
		}
	}
}
