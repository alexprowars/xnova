<?php

namespace App\Engine\Ai\Runtime;

use App\Engine\Ai\Combat\ThreatAssessment;
use App\Engine\Ai\World\ColonizationMap;
use App\Engine\Enums\MessageType;
use App\Models\Ai;
use App\Models\Fleet;
use App\Models\Message;
use App\Models\Planet;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class RunContext
{
	private ?array $bots = null;
	private ?int $activeHumans = null;
	private array $reports = [];
	private ?ColonizationMap $colonization = null;

	/** @var array<int, Collection<int, Fleet>> */
	private array $fleets = [];
	private array $simulations = [];
	private array $decisions = [];
	private array $escapeSlots = [];

	public function needsEscapeSlot(int $userId): bool
	{
		return $this->escapeSlots[$userId] ??= ThreatAssessment::incoming((int) config('ai.reserve_slot_minutes', 30))
			->where('target_user_id', $userId)
			->whereNot('user_id', $userId)
			->exists();
	}

	/** @return Collection<int, Fleet> */
	public function fleets(int $userId): Collection
	{
		return $this->fleets[$userId] ??= Fleet::query()
			->where('user_id', $userId)
			->get()
			->keyBy('id');
	}

	public function hasSimulationBudget(int $userId): bool
	{
		return ($this->simulations[$userId] ?? 0) < (int) config('ai.simulations_per_turn', 12);
	}

	public function useSimulation(int $userId): bool
	{
		$count = $this->simulations[$userId] ?? 0;

		if ($count >= (int) config('ai.simulations_per_turn', 12)) {
			$this->decision($userId, 'simulation_budget');

			return false;
		}

		$this->simulations[$userId] = $count + 1;

		return true;
	}

	public function decision(int $userId, string $reason): void
	{
		$this->decisions[$userId][$reason] = ($this->decisions[$userId][$reason] ?? 0) + 1;
	}

	public function metrics(int $userId): array
	{
		return ['simulations' => $this->simulations[$userId] ?? 0, 'decisions' => $this->decisions[$userId] ?? []];
	}

	/** @return array<int, true> */
	public function bots(): array
	{
		return $this->bots ??= Cache::remember(
			'ai:bots',
			now()->addSeconds((int) config('ai.world_cache_seconds', 300)),
			fn() => Ai::query()
				->pluck('user_id')
				->mapWithKeys(fn(int $id) => [$id => true])
				->all(),
		);
	}

	public function activeHumans(): int
	{
		return $this->activeHumans ??= Cache::remember(
			'ai:active-humans:' . config('ai.active_humans_hours'),
			now()->addMinute(),
			fn() => User::query()
				->whereNotIn('id', Ai::query()->select('user_id'))
				->whereNull('vacation')
				->whereNull('blocked_at')
				->whereDoesntHave('roles')
				->where('onlinetime', '>=', now()->subHours((int) config('ai.active_humans_hours', 24)))
				->count(),
		);
	}

	public function reports(int $userId): array
	{
		if (!isset($this->reports[$userId])) {
			$this->reports[$userId] = $this->loadReports($userId);
		}

		return $this->reports[$userId];
	}

	public function colonization(): ColonizationMap
	{
		return $this->colonization ??= new ColonizationMap();
	}

	public function recordFleet(Fleet $fleet): void
	{
		// Если карта ещё не загружена, будущая загрузка уже увидит флот в БД.
		$this->colonization?->recordFleet($fleet);

		if (isset($this->fleets[$fleet->user_id])) {
			$this->fleets[$fleet->user_id]->put($fleet->id, $fleet);
		}
	}

	public function recordPlanet(Planet $planet): void
	{
		$this->colonization?->recordPlanet($planet);
	}

	public function forgetPlayer(int $userId): void
	{
		unset(
			$this->reports[$userId],
			$this->fleets[$userId],
			$this->simulations[$userId],
			$this->decisions[$userId],
			$this->escapeSlots[$userId],
		);
	}

	private function loadReports(int $userId): array
	{
		$reports = [];
		$messages = Message::query()
			->where('user_id', $userId)
			->where('type', MessageType::Spy)
			->where('message->type', 'MissionEspionage')
			->where('date', '>=', now()->subMinutes((int) config('ai.report_lifetime_minutes', 120)))
			->orderByDesc('date')
			->orderByDesc('id')
			->limit(200)
			->get(['date', 'message']);

		foreach ($messages as $message) {
			$data = $message->message['data'] ?? [];
			$resourceRow = $data['rows'][0] ?? [];
			$position = $resourceRow['planet'] ?? [];

			if (empty($position)) {
				continue;
			}

			$key = ($position['galaxy'] ?? 0) . ':'
				. ($position['system'] ?? 0) . ':'
				. ($position['planet'] ?? 0) . ':'
				. ($position['type'] ?? 0);

			if (isset($reports[$key])) {
				continue;
			}

			$report = [
				'date' => $message->date->timestamp,
				'user_id' => $resourceRow['user']['id'] ?? null,
				'resources' => $resourceRow['resources'] ?? [],
				'units' => [],
				'technologies' => [],
				'technologies_known' => false,
				'fleet_known' => false,
				'defense_known' => false,
			];

			foreach ($data['rows'] ?? [] as $row) {
				$title = $row['title'] ?? '';
				$isFleet = $title === 'fleet_engine.espionage.fleet';
				$isDefense = $title === 'fleet_engine.espionage.defense';
				$isTech = $title === 'main.tech.100';
				$report['fleet_known'] = $report['fleet_known'] || $isFleet;
				$report['defense_known'] = $report['defense_known'] || $isDefense;
				$report['technologies_known'] = $report['technologies_known'] || $isTech;

				if (!$isFleet && !$isDefense && !$isTech) {
					continue;
				}

				foreach ($row['items'] ?? [] as $item) {
					$report[$isTech ? 'technologies' : 'units'][$item['id']] = (int) $item['lv'];
				}
			}

			$reports[$key] = $report;
		}

		return $reports;
	}
}
