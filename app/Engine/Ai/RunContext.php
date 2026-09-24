<?php

namespace App\Engine\Ai;

use App\Engine\Enums\MessageType;
use App\Models\Ai;
use App\Models\Fleet;
use App\Models\Message;
use App\Models\Planet;
use App\Models\User;

class RunContext
{
	private ?array $bots = null;
	private ?int $activeHumans = null;
	private array $reports = [];
	private ?ColonizationMap $colonization = null;

	/** @return array<int, true> */
	public function bots(): array
	{
		return $this->bots ??= Ai::query()->pluck('user_id')->mapWithKeys(fn(int $id) => [$id => true])->all();
	}

	public function activeHumans(): int
	{
		return $this->activeHumans ??= User::query()->whereNotIn('id', Ai::query()->select('user_id'))
			->whereNull('vacation')->whereNull('blocked_at')->whereDoesntHave('roles')
			->where('onlinetime', '>=', now()->subHours((int) config('ai.active_humans_hours', 24)))->count();
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
	}

	public function recordPlanet(Planet $planet): void
	{
		$this->colonization?->recordPlanet($planet);
	}

	public function forgetPlayer(int $userId): void
	{
		unset($this->reports[$userId]);
	}

	private function loadReports(int $userId): array
	{
		$reports = [];
		$messages = Message::query()->where('user_id', $userId)->where('type', MessageType::Spy)
			->where('message->type', 'MissionEspionage')
			->where('date', '>=', now()->subMinutes((int) config('ai.report_lifetime_minutes', 120)))
			->orderByDesc('date')->orderByDesc('id')->limit(200)->get();

		foreach ($messages as $message) {
			$data = $message->message['data'] ?? [];
			$resourceRow = $data['rows'][0] ?? [];
			$position = $resourceRow['planet'] ?? [];

			if (empty($position)) {
				continue;
			}

			$key = ($position['galaxy'] ?? 0) . ':' . ($position['system'] ?? 0) . ':' . ($position['planet'] ?? 0) . ':' . ($position['type'] ?? 0);

			if (isset($reports[$key])) {
				continue;
			}

			$report = ['date' => $message->date->timestamp, 'user_id' => $resourceRow['user']['id'] ?? null, 'resources' => $resourceRow['resources'] ?? [], 'units' => [], 'technologies' => [], 'technologies_known' => false, 'fleet_known' => false, 'defense_known' => false];

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
