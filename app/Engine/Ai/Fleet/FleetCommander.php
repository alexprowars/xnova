<?php

namespace App\Engine\Ai\Fleet;

use App\Engine\Ai\Combat\RaidPlanner;
use App\Engine\Ai\Combat\ThreatAssessment;
use App\Engine\Ai\Development\StrategyType;
use App\Engine\Ai\Economy\ColonySupply;
use App\Engine\Ai\Runtime\RunContext;
use App\Engine\Ai\World\TargetFinder;
use App\Engine\Coordinates;
use App\Engine\Entity\Ship;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\MissionType;
use App\Facades\Galaxy;
use App\Models\Ai;
use App\Models\Fleet;
use App\Models\Planet;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class FleetCommander
{
	private array $reports = [];
	private StrategyType $strategy;
	private array $state;

	/** @param Collection<int, Planet>|null $colonies */
	public function __construct(
		private Planet $planet,
		private Ai $ai,
		private RunContext $context = new RunContext(),
		private ?Collection $colonies = null,
	)
	{
		$this->strategy = $ai->strategy;
		$this->state = $ai->state ?? [];
	}

	public function getState(): array
	{
		return $this->state;
	}

	public function defend(): bool
	{
		return $this->hasSlot(emergency: true) && $this->evacuate();
	}

	public function run(): void
	{
		foreach ($this->state['targets'] ?? [] as $id => $target) {
			if (max($target['scouted_at'] ?? 0, $target['attacked_at'] ?? 0) < now()->subDays(2)->timestamp) {
				unset($this->state['targets'][$id]);
			}
		}

		if (empty(new FleetComposition($this->planet)->available())) {
			return;
		}

		if ($this->defend() || !$this->hasSlot()) {
			return;
		}

		$this->colonize();

		if (!$this->hasSlot()) {
			return;
		}

		if ($this->strategy === StrategyType::ECONOMY) {
			$this->supplyColony();

			if (!$this->hasSlot()) {
				return;
			}
		}

		$this->reports = $this->context->reports($this->planet->user_id);
		$finder = new TargetFinder($this->planet, $this->context, $this->state);
		$targets = $finder->find();
		$this->attack($targets, $finder);

		if ($this->hasSlot()) {
			$this->scout($targets);
		}

		if ($this->hasSlot()) {
			$this->recycle();
		}

		if ($this->strategy !== StrategyType::ECONOMY && $this->hasSlot()) {
			$this->supplyColony();
		}
	}

	/** @return Collection<int, Planet> */
	private function colonies(): Collection
	{
		$this->colonies ??= $this->planet->user->planets()
			->whereNull('destroyed_at')
			->where('planet_type', PlanetType::PLANET)
			->orderBy('id')
			->get();

		return $this->colonies->filter(fn(Planet $colony) => $colony->user_id === $this->ai->user_id && !$colony->trashed()
			&& !$colony->destroyed_at
			&& $colony->planet_type === PlanetType::PLANET);
	}

	private function hasSlot(bool $fresh = false, bool $emergency = false): bool
	{
		$max = 1 + $this->planet->user->getTechLevel('computer');

		if ($this->planet->user->officier_admiral?->isFuture()) {
			$max += 2;
		}

		$count = $fresh ? Fleet::query()->where('user_id', $this->planet->user_id)->count()
			: $this->context->fleets($this->planet->user_id)->count();
		$reserve = !$emergency && $this->context->needsEscapeSlot($this->planet->user_id) ? 1 : 0;

		if ($count >= $max - $reserve) {
			$this->context->decision($this->planet->user_id, 'no_fleet_slot');

			return false;
		}

		return true;
	}

	private function flightExists(Coordinates $target, array $missions, bool $fresh = false): bool
	{
		if ($fresh) {
			return Fleet::query()
				->where('user_id', $this->planet->user_id)
				->coordinates(FleetDirection::END, $target)
				->whereIn('mission', $missions)
				->where('mess', 0)
				->exists();
		}

		return $this->context->fleets($this->planet->user_id)->contains(
			fn(Fleet $fleet) => $fleet->mess == 0
				&& in_array($fleet->mission, $missions, true)
				&& $fleet->getDestinationCoordinates()->isSame($target),
		);
	}

	private function flight(array $ships, Coordinates $target): FlightPlan
	{
		return new FlightPlan($this->planet, $target, $ships);
	}

	private function send(
		Coordinates $target,
		MissionType $mission,
		array $ships,
		array $resources = [],
		?Closure $validate = null,
		?Closure $onSent = null,
		int $speed = 10,
		bool $emergency = false,
	): bool
	{
		return new FleetDispatcher($this->planet, $this->ai, $this->context)->send(
			$target,
			$mission,
			$ships,
			$resources,
			fn() => $this->hasSlot(fresh: true, emergency: $emergency)
				&& $this->missionIsCurrent($target, $mission)
				&& (!$validate || $validate()),
			function () use ($onSent) {
				if ($onSent) {
					$onSent();
				}

				$this->ai->update(['state' => $this->state]);
			},
			$speed,
		);
	}

	private function missionIsCurrent(Coordinates $target, MissionType $mission): bool
	{
		$missions = match ($mission) {
			MissionType::Attack => [MissionType::Attack, MissionType::Assault],
			MissionType::Spy => [MissionType::Spy, MissionType::Attack],
			MissionType::Transport => [MissionType::Transport, MissionType::Stay],
			default => [$mission],
		};

		if ($mission !== MissionType::Stay && $this->flightExists($target, $missions, fresh: true)) {
			return false;
		}

		if ($mission === MissionType::Colonization) {
			$user = $this->planet->user;
			$max = min((int) config('game.maxPlanets', 9), $user->getTechLevel('colonization') + 1);

			return $user->planets()
				->where('planet_type', PlanetType::PLANET)
				->whereNull('destroyed_at')
				->count() < $max
				&& !Fleet::query()
					->whereBelongsTo($user)
					->where('mission', MissionType::Colonization)
					->where('mess', 0)
					->exists()
				&& !Fleet::query()
					->coordinates(FleetDirection::END, $target)
					->where('mission', MissionType::Colonization)
					->where('mess', 0)
					->exists()
				&& $this->colonizationSystemIsCurrent($target);
		}

		if (in_array($mission, [MissionType::Stay, MissionType::Transport], true)) {
			if (!Planet::query()
				->coordinates($target)
				->where('user_id', $this->planet->user_id)
				->whereNull('destroyed_at')
				->exists()) {
				return false;
			}
		}

		if ($mission === MissionType::Stay) {
			return ThreatAssessment::incoming()
				->coordinates(FleetDirection::END, $this->planet->coordinates)
				->whereNot('user_id', $this->planet->user_id)
				->exists()
				&& !Fleet::query()
					->coordinates(FleetDirection::END, $target)
					->where('mess', 0)
					->whereIn('mission', [MissionType::Attack, MissionType::Assault, MissionType::Destruction])
					->exists();
		}

		return true;
	}

	private function colonizationSystemIsCurrent(Coordinates $target): bool
	{
		// Общая карта — снимок прохода; перед отправкой проверяем только выбранную систему.
		$owners = Planet::query()
			->whereIn('user_id', Ai::query()->select('user_id'))
			->where('galaxy', $target->getGalaxy())
			->where('system', $target->getSystem())
			->where('planet_type', PlanetType::PLANET)
			->whereNull('destroyed_at')
			->distinct()
			->pluck('user_id');
		$pendingOwners = Fleet::query()
			->whereIn('user_id', Ai::query()->select('user_id'))
			->where('end_galaxy', $target->getGalaxy())
			->where('end_system', $target->getSystem())
			->where('mission', MissionType::Colonization)
			->where('mess', 0)
			->distinct()
			->pluck('user_id');
		$owners = $owners->merge($pendingOwners)->unique();

		return !$owners->contains($this->planet->user_id)
			&& $owners->count() < (int) config('ai.max_bots_per_system', 3);
	}

	private function targetIsCurrent(Planet $target): bool
	{
		if (($this->state['targets'][$target->id]['attacked_at'] ?? 0)
				> now()->subMinutes((int) config('ai.attack_cooldown_minutes', 180))->timestamp) {
			return false;
		}

		$current = Planet::query()
			->with('user')
			->where('user_id', $target->user_id)
			->whereNull('destroyed_at')
			->coordinates($target->coordinates)
			->find($target->id);
		$user = $current?->user;

		if (!$user || $user->isVacation() || $user->blocked_at
			|| $user->roles->isNotEmpty()
			|| $user->id === $this->planet->user_id) {
			return false;
		}

		return true;
	}

	private function combatState(): array
	{
		$user = $this->planet->user;

		return [
			'ships' => new FleetComposition($this->planet)->available(true),
			'technologies' => $user->technologies->pluck('level', 'id')
				->filter()
				->sortKeys()
				->all(),
			'race' => $user->race,
			'mercenary' => $user->officier_mercenary?->isFuture() ?? false,
			'admiral' => $user->officier_admiral?->isFuture() ?? false,
		];
	}

	private function colonize(): void
	{
		if ($this->planet->getLevel(208) < 1) {
			return;
		}

		$user = $this->planet->user;
		$max = min((int) config('game.maxPlanets', 9), $user->getTechLevel('colonization') + 1);
		$count = $this->colonies()->count();
		$pending = $this->context->fleets($user->id)
			->where('mission', MissionType::Colonization)
			->where('mess', 0)
			->count();

		if ($count + $pending >= $max || $pending > 0) {
			return;
		}

		$ships = [208 => 1];
		$searched = 0;

		foreach ($this->context->colonization()->candidates($this->planet->user_id) as $position) {
			$flight = $this->flight($ships, $position);

			if ($flight->capacity < 0 || $flight->fuel > $this->planet->deuterium
				|| $flight->duration > FlightPlan::maxDuration(MissionType::Colonization)) {
				continue;
			}

			if (++$searched > (int) config('ai.colonization_system_limit', 32)) {
				$this->context->decision($this->planet->user_id, 'colonization_search_limit');

				return;
			}

			$positions = Galaxy::getFreePositions($position, 4, min(12, (int) config('game.maxPlanetInSystem')));
			shuffle($positions);

			foreach ($positions as $slot) {
				$target = new Coordinates($position->getGalaxy(), $position->getSystem(), $slot, PlanetType::PLANET);

				if (Fleet::query()
					->coordinates(FleetDirection::END, $target)
					->where('mission', MissionType::Colonization)
					->where('mess', 0)
					->exists()) {
					continue;
				}

				$resources = $this->cargo(
					$flight,
					0.2,
					[
						'metal' => 5000,
						'crystal' => 3000,
						'deuterium' => 2000,
					],
				);

				if ($this->send($target, MissionType::Colonization, $ships, $resources)) {
					return;
				}
			}
		}
	}

	private function report(Planet $target): ?array
	{
		$key = $target->galaxy . ':' . $target->system . ':' . $target->planet . ':' . $target->planet_type->value;
		$report = $this->reports[$key] ?? null;

		if (!$report || $report['user_id'] !== $target->user_id
			|| $report['date'] <= ($this->state['targets'][$target->id]['attacked_at'] ?? 0)
			|| $report['date'] < now()->subMinutes((int) config('ai.report_lifetime_minutes', 120))->timestamp) {
			return null;
		}

		return $report;
	}

	/** @param array<Planet> $targets */
	private function scout(array $targets): void
	{
		if ($this->planet->getLevel(210) < 1) {
			return;
		}

		foreach ($targets as $target) {
			$memory = $this->state['targets'][$target->id] ?? [];
			$report = $this->report($target);

			if (($report && $report['fleet_known'] && $report['defense_known'])
				|| ($memory['scouted_at'] ?? 0) > now()->subMinutes((int) config('ai.scout_cooldown_minutes', 20))->timestamp
				|| $this->flightExists($target->coordinates, [MissionType::Spy, MissionType::Attack])) {
				continue;
			}

			$count = $report ? max(7, ($memory['probes'] ?? 7) * 2) : max(7, $memory['probes'] ?? 7);
			$required = min($count, (int) config('ai.max_probes', 32));
			$count = min($required, $this->planet->getLevel(210));

			if ($this->send(
				$target->coordinates,
				MissionType::Spy,
				[210 => $count],
				[],
				fn() => $this->targetIsCurrent($target),
				function () use ($target, $count, $required) {
					$this->state['targets'][$target->id] = array_merge(
						$this->state['targets'][$target->id] ?? [],
						[
							'scouted_at' => now()->timestamp,
							'probes' => $count,
							'required_probes' => $required,
						],
					);
				},
			)) {
				return;
			}
		}
	}

	/** @param array<Planet> $targets */
	private function attack(array $targets, TargetFinder $finder): void
	{
		$combatState = $this->combatState();
		$candidates = [];

		foreach ($targets as $target) {
			$report = $this->report($target);

			if (!$report
				|| !$report['fleet_known']
				|| !$report['defense_known']
				|| ($this->state['targets'][$target->id]['attacked_at'] ?? 0)
					> now()->subMinutes((int) config('ai.attack_cooldown_minutes', 180))->timestamp
				|| $this->flightExists($target->coordinates, [MissionType::Attack, MissionType::Assault])) {
				continue;
			}

			$candidates[] = [
				'target' => $target,
				'report' => $report,
				'weight' => $finder->weight($target->id),
			];
		}

		$planner = new RaidPlanner($this->planet, $this->strategy, $this->context);

		try {
			$candidate = $planner->choose($candidates);
		} catch (Throwable $exception) {
			Log::warning('ai.forecast_failed', ['user_id' => $this->planet->user_id, 'exception' => $exception]);

			return;
		}

		if (!empty($planner->requests())) {
			$this->state['fleet_requests'][$this->planet->id] = [
				'units' => $planner->requests(),
				'expires_at' => now()->addMinutes((int) config('ai.report_lifetime_minutes', 120))->timestamp,
			];
		}

		if (!$candidate) {
			return;
		}

		$target = $candidate['target'];
		$flight = $candidate['flight'];

		$this->send(
			$target->coordinates,
			MissionType::Attack,
			$flight->ships,
			[],
			function () use ($target, $flight, $combatState, $candidate): bool {
				if ($combatState !== $this->combatState() || !$this->targetIsCurrent($target)
					|| !$this->report($target)) {
					return false;
				}

				$current = $this->flight($flight->ships, $target->coordinates);
				$profit = $current->profit($candidate['report'], $candidate['forecast']['loss'], $candidate['forecast']['capacity']);

				return $current->isAffordable($this->planet, MissionType::Attack, 0.5)
					&& $profit >= (float) config('ai.min_raid_profit', 1000);
			},
			function () use ($target) {
				$this->state['targets'][$target->id]['attacked_at'] = now()->timestamp;
			},
		);
	}

	private function recycle(): void
	{
		$count = $this->planet->getLevel(209);

		if ($count < 1) {
			return;
		}

		$targets = Planet::query()
			->where('galaxy', $this->planet->galaxy)
			->where('planet_type', PlanetType::PLANET)
			->whereBetween('system', [$this->planet->system - 10, $this->planet->system + 10])
			->where(function (Builder $query) {
				$query->where('debris_metal', '>', 0)->orWhere('debris_crystal', '>', 0);
			})
			->orderByRaw('ABS(`system` - ?)', [$this->planet->system])
			->limit(10)
			->get();

		foreach ($targets as $target) {
			$coordinates = new Coordinates($target->galaxy, $target->system, $target->planet, PlanetType::DEBRIS);

			if ($this->flightExists($coordinates, [MissionType::Recycling])) {
				continue;
			}

			$debris = $target->debris_metal + $target->debris_crystal;
			$ships = [
				209 => min(
					$count,
					max(1, (int) ceil($debris / max(1, Ship::createEntity(209, 1, $this->planet)->getStorage()))),
				),
			];
			$flight = $this->flight($ships, $coordinates);

			if (min($debris, $flight->capacity) > $flight->fuel * 2 + (float) config('ai.min_raid_profit', 1000)
				&& $this->send($coordinates, MissionType::Recycling, $ships)) {
				return;
			}
		}
	}

	private function evacuate(): bool
	{
		$assessment = new ThreatAssessment($this->planet, $this->context);
		$threats = $assessment->fleets();

		if (!$assessment->shouldEvacuate($threats)) {
			return false;
		}

		$ships = new FleetComposition($this->planet)->available();

		if (empty($ships)) {
			return false;
		}

		$destinations = $this->colonies()
			->where('id', '!=', $this->planet->id)
			->sortBy(fn(Planet $colony) => [
				abs($colony->galaxy - $this->planet->galaxy),
				abs($colony->system - $this->planet->system),
			]);

		foreach ($destinations as $destination) {
			if (ThreatAssessment::incoming(1440)
				->coordinates(FleetDirection::END, $destination->coordinates)
				->exists()) {
				continue;
			}

			$flight = $this->flight($ships, $destination->coordinates);

			if ($this->send(
				$destination->coordinates,
				MissionType::Stay,
				$ships,
				$this->cargo($flight, 1.0),
				emergency: true,
			)) {
				return true;
			}
		}

		// При единственной планете сохраняем флот на сборе собственных обломков.
		if (isset($ships[209]) && $this->planet->debris_metal + $this->planet->debris_crystal > 0) {
			$target = new Coordinates($this->planet->galaxy, $this->planet->system, $this->planet->planet, PlanetType::DEBRIS);
			$returnAfter = $threats->max('start_date')->timestamp + 60;

			for ($speed = 10; $speed >= 1; $speed--) {
				$flight = new FlightPlan($this->planet, $target, $ships, $speed);

				if (now()->timestamp + $flight->duration * 2 <= $returnAfter) {
					continue;
				}

				if ($this->send(
					$target,
					MissionType::Recycling,
					$ships,
					$this->cargo($flight, 1.0),
					speed: $speed,
					emergency: true,
				)) {
					return true;
				}
			}
		}

		$this->context->decision($this->planet->user_id, 'no_escape_route');

		return false;
	}

	private function supplyColony(): void
	{
		$supply = new ColonySupply($this->planet, $this->context, $this->state['resource_requests'] ?? []);
		$plan = $supply->choose($this->colonies());

		if (!$plan) {
			return;
		}

		$flight = $plan['flight'];
		$this->send(
			$flight->target,
			MissionType::Transport,
			$flight->ships,
			$plan['resources'],
			fn() => $supply->canSend($plan),
		);
	}

	private function cargo(FlightPlan $flight, float $fraction, array $limits = []): array
	{
		$resources = [];
		$capacity = max(0, $flight->capacity);

		foreach (['deuterium', 'crystal', 'metal'] as $resource) {
			$available = max(0, $this->planet->{$resource} - ($resource === 'deuterium' ? $flight->fuel : 0));
			$count = (int) floor(min($capacity, $available * $fraction, $limits[$resource] ?? $available));
			$resources[$resource] = $count;
			$capacity -= $count;
		}

		return $resources;
	}
}
