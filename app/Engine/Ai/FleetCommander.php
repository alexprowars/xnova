<?php

namespace App\Engine\Ai;

use App\Engine\Coordinates;
use App\Engine\Entity\Ship;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\FleetCollection;
use App\Engine\Fleet\FleetSend;
use App\Engine\Fleet\MissionType;
use App\Engine\Objects\ShipObject;
use App\Exceptions\Exception;
use App\Facades\Galaxy;
use App\Facades\Vars;
use App\Models\Ai;
use App\Models\AllianceDiplomacy;
use App\Models\Fleet;
use App\Models\Friend;
use App\Models\Message;
use App\Models\LogsFleet;
use App\Models\Planet;
use App\Models\Statistic;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Throwable;

class FleetCommander
{
	private array $reports = [];
	private array $targetWeights = [];

	public function __construct(private Planet $planet, private StrategyType $strategy, private array $state)
	{
	}

	public function getState(): array
	{
		return $this->state;
	}

	public function run(): void
	{
		foreach ($this->state['targets'] ?? [] as $id => $target) {
			if (max($target['scouted_at'] ?? 0, $target['attacked_at'] ?? 0) < now()->subDays(2)->timestamp) {
				unset($this->state['targets'][$id]);
			}
		}

		if (!$this->hasSlot() || (empty($this->attackShips()) && $this->planet->getLevel(208) < 1 && $this->planet->getLevel(209) < 1 && $this->planet->getLevel(210) < 1)) {
			return;
		}

		if ($this->evacuate()) {
			return;
		}

		$this->colonize();

		if (!$this->hasSlot()) {
			return;
		}

		$this->loadReports();
		$targets = $this->targets();
		$this->attack($targets);

		if ($this->hasSlot()) {
			$this->scout($targets);
		}

		if ($this->hasSlot()) {
			$this->recycle();
		}

		if ($this->hasSlot()) {
			$this->supplyColony();
		}
	}

	private function hasSlot(): bool
	{
		$max = 1 + $this->planet->user->getTechLevel('computer');

		if ($this->planet->user->officier_admiral?->isFuture()) {
			$max += 2;
		}

		return Fleet::query()->whereBelongsTo($this->planet->user)->count() < $max;
	}

	private function flightExists(Coordinates $target, array $missions): bool
	{
		return Fleet::query()->whereBelongsTo($this->planet->user)
			->coordinates(FleetDirection::END, $target)->whereIn('mission', $missions)->where('mess', 0)->exists();
	}

	/** @return array{duration: int, fuel: int, capacity: int} */
	private function flight(array $ships, Coordinates $target): array
	{
		$collection = FleetCollection::createFromArray($ships, $this->planet);
		$distance = $collection->getDistance($this->planet->coordinates, $target);
		$duration = $collection->getDuration(10, $distance);
		$fuel = $collection->getConsumption($duration, $distance);

		return ['duration' => $duration, 'fuel' => $fuel, 'capacity' => max(0, $collection->getStorage() - $fuel)];
	}

	private function send(Coordinates $target, MissionType $mission, array $ships, array $resources = []): bool
	{
		if (empty($ships) || !$this->hasSlot()) {
			return false;
		}

		$flight = $this->flight($ships, $target);

		if ($flight['fuel'] + ($resources['deuterium'] ?? 0) > $this->planet->deuterium || $flight['duration'] > (float) config('ai.max_flight_hours', 4) * 3600) {
			return false;
		}

		try {
			$sender = new FleetSend($this->planet, $target, $mission);
			$sender->setFleets($ships);
			$sender->setResources($resources);
			$fleet = $sender->send();

			if (config('ai.log_decisions', true)) {
				Log::info('ai.fleet', ['user_id' => $this->planet->user_id, 'planet_id' => $this->planet->id, 'fleet_id' => $fleet->id, 'mission' => $mission->name, 'target' => $target->toArray()]);
			}

			return true;
		} catch (Exception $exception) {
			// Проверка миссии могла отклонить полёт после изменения игрового состояния.
			$this->planet->refresh();
			$this->planet->load('entities', 'user');
			$this->planet->getProduction()->reset();
			Log::debug('ai.fleet_rejected', ['user_id' => $this->planet->user_id, 'mission' => $mission->name, 'reason' => $exception->getMessage()]);

			return false;
		}
	}

	private function colonize(): void
	{
		if ($this->planet->getLevel(208) < 1) {
			return;
		}

		$user = $this->planet->user;
		$max = min((int) config('game.maxPlanets', 9), $user->getTechLevel('colonization') + 1);
		$count = $user->planets()->where('planet_type', PlanetType::PLANET)->whereNull('destroyed_at')->count();
		$pending = Fleet::query()->whereBelongsTo($user)->where('mission', MissionType::Colonization)->where('mess', 0)->count();

		if ($count + $pending >= $max || $pending > 0) {
			return;
		}

		$radius = (int) config('ai.search_radius', 50);
		$systems = range(max(1, $this->planet->system - $radius), min((int) config('game.maxSystemInGalaxy'), $this->planet->system + $radius));
		usort($systems, fn(int $a, int $b) => abs($a - $this->planet->system) <=> abs($b - $this->planet->system));

		foreach ($systems as $system) {
			$position = new Coordinates($this->planet->galaxy, $system);
			$positions = Galaxy::getFreePositions($position, 4, min(12, (int) config('game.maxPlanetInSystem')));
			// Разным ботам нужны разные места, иначе они летят в одну и ту же колонию.
			usort($positions, fn(int $a, int $b) => (($a + $user->id) % 9) <=> (($b + $user->id) % 9));

			foreach ($positions as $slot) {
				$target = new Coordinates($this->planet->galaxy, $system, $slot, PlanetType::PLANET);

				if (Fleet::query()->coordinates(FleetDirection::END, $target)->where('mission', MissionType::Colonization)->where('mess', 0)->exists()) {
					continue;
				}

				$ships = [208 => 1];
				$flight = $this->flight($ships, $target);
				if ($flight['fuel'] > $this->planet->deuterium || $flight['duration'] > (float) config('ai.max_flight_hours', 4) * 3600) {
					return;
				}

				$resources = $this->cargo($flight, 0.2, ['metal' => 5000, 'crystal' => 3000, 'deuterium' => 2000]);

				if ($this->send($target, MissionType::Colonization, $ships, $resources)) {
					return;
				}
			}
		}
	}

	/** @return array<Planet> */
	private function targets(): array
	{
		$radius = (int) config('ai.search_radius', 50);
		$targets = Planet::query()->with(['user.roles'])
			->whereNot('user_id', $this->planet->user_id)
			->where('planet_type', PlanetType::PLANET)->whereNull('destroyed_at')
			->where('galaxy', $this->planet->galaxy)
			->whereBetween('system', [max(1, $this->planet->system - $radius), $this->planet->system + $radius])
			->whereHas('user', function (Builder $query) {
				$query->whereNull('vacation')->whereNull('blocked_at')->whereDoesntHave('roles');
			})
			->orderByRaw('ABS(`system` - ?)', [$this->planet->system])
			->orderBy('id')->limit((int) config('ai.target_limit', 30) * 5)->get();

		$bots = Ai::query()->whereIn('user_id', $targets->pluck('user_id'))->pluck('user_id')->all();
		$humans = User::query()->whereNotIn('id', Ai::query()->select('user_id'))
			->whereNull('vacation')->whereNull('blocked_at')->whereDoesntHave('roles')
			->where('onlinetime', '>=', now()->subHours((int) config('ai.active_humans_hours', 24)))
			->count();
		$preferBots = $humans < (int) config('ai.active_humans_threshold', 10);
		$points = Statistic::query()->where('stat_type', 1)->where('stat_code', 1)
			->whereIn('user_id', [...$targets->pluck('user_id')->all(), $this->planet->user_id])->pluck('total_points', 'user_id');
		$cappedTargets = LogsFleet::query()->where('s_id', $this->planet->user_id)
			->where('mission', MissionType::Attack)->where('amount', '>=', 3)
			->where('created_at', '>=', now()->startOfDay())
			->get(['e_galaxy', 'e_system', 'e_planet'])
			->mapWithKeys(fn(LogsFleet $log) => [$log->e_galaxy . ':' . $log->e_system . ':' . $log->e_planet => true])->all();
		$eligible = [];

		foreach ($targets as $target) {
			$user = $target->user;

			if (($this->planet->user->alliance_id && $user->alliance_id === $this->planet->user->alliance_id) || Friend::hasFriends($this->planet->user, $user)) {
				continue;
			}

			$diplomacy = null;
			if ($user->alliance_id && $this->planet->user->alliance_id) {
				$diplomacy = AllianceDiplomacy::query()->where('alliance_id', $user->alliance_id)
					->where('diplomacy_id', $this->planet->user->alliance_id)->where('status', 1)->first();
				if ($diplomacy && $diplomacy->type < 3) {
					continue;
				}
			}

			$key = $target->galaxy . ':' . $target->system . ':' . $target->planet;
			if (isset($cappedTargets[$key]) && (!$diplomacy || $diplomacy->type != 3)) {
				continue;
			}

			if (config('game.noobprotection') && (int) config('game.noobprotectionPoints') > 0 && $user->onlinetime?->greaterThan(now()->subDays(7))) {
				$theirPoints = $points[$user->id] ?? 0;
				$factor = (int) config('game.noobprotectionFactor');

				if ($theirPoints < (int) config('game.noobprotectionPoints') || ($factor > 0 && ($points[$this->planet->user_id] ?? 0) > $theirPoints * $factor)) {
					continue;
				}
			}

			$memory = $this->state['targets'][$target->id] ?? [];

			if (($memory['attacked_at'] ?? 0) > now()->subMinutes((int) config('ai.attack_cooldown_minutes', 180))->timestamp) {
				continue;
			}

			$this->targetWeights[$target->id] = $preferBots && in_array($user->id, $bots) ? (float) config('ai.bot_target_bonus', 3) : 1.0;
			$eligible[] = $target;
		}

		usort($eligible, function (Planet $a, Planet $b) {
			$scoreA = $this->targetWeights[$a->id] / (1 + abs($a->system - $this->planet->system) / 10);
			$scoreB = $this->targetWeights[$b->id] / (1 + abs($b->system - $this->planet->system) / 10);

			return $scoreB <=> $scoreA ?: ($this->state['targets'][$a->id]['scouted_at'] ?? 0) <=> ($this->state['targets'][$b->id]['scouted_at'] ?? 0);
		});

		return array_slice($eligible, 0, (int) config('ai.target_limit', 30));
	}

	private function loadReports(): void
	{
		$messages = Message::query()->whereBelongsTo($this->planet->user)->where('type', MessageType::Spy)
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

			if (isset($this->reports[$key])) {
				continue;
			}

			$report = ['date' => $message->date->timestamp, 'user_id' => $resourceRow['user']['id'] ?? null, 'resources' => $resourceRow['resources'] ?? [], 'units' => [], 'technologies' => [], 'technologies_known' => false, 'fleet_known' => false, 'defense_known' => false];

			foreach ($data['rows'] ?? [] as $row) {
				$title = $row['title'] ?? '';
				$isFleet = $title === 'fleet_engine.sys_spy_fleet';
				$isDefense = $title === 'fleet_engine.sys_spy_defenses';
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

			$this->reports[$key] = $report;
		}
	}

	private function report(Planet $target): ?array
	{
		$key = $target->galaxy . ':' . $target->system . ':' . $target->planet . ':' . $target->planet_type->value;
		$report = $this->reports[$key] ?? null;

		if (!$report || $report['user_id'] !== $target->user_id || $report['date'] <= ($this->state['targets'][$target->id]['attacked_at'] ?? 0)) {
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

			if (($report && $report['fleet_known'] && $report['defense_known']) || ($memory['scouted_at'] ?? 0) > now()->subMinutes((int) config('ai.scout_cooldown_minutes', 20))->timestamp || $this->flightExists($target->coordinates, [MissionType::Spy, MissionType::Attack])) {
				continue;
			}

			$count = $report ? max(7, ($memory['probes'] ?? 7) * 2) : max(7, $memory['probes'] ?? 7);
			$required = min($count, (int) config('ai.max_probes', 32));
			$count = min($required, $this->planet->getLevel(210));

			if ($this->send($target->coordinates, MissionType::Spy, [210 => $count])) {
				$this->state['targets'][$target->id] = array_merge($memory, ['scouted_at' => now()->timestamp, 'probes' => $count, 'required_probes' => $required]);
				return;
			}
		}
	}

	/** @param array<Planet> $targets */
	private function attack(array $targets): void
	{
		$ships = $this->attackShips();

		if (empty($ships)) {
			return;
		}

		$candidates = [];

		foreach ($targets as $target) {
			$report = $this->report($target);

			if (!$report || !$report['fleet_known'] || !$report['defense_known'] || $this->flightExists($target->coordinates, [MissionType::Attack, MissionType::Assault])) {
				continue;
			}

			$memory = $this->state['targets'][$target->id] ?? [];

			if (($memory['evaluated_at'] ?? 0) > now()->subMinutes(10)->timestamp && ($memory['evaluated_report'] ?? 0) === $report['date']) {
				continue;
			}

			$flight = $this->flight($ships, $target->coordinates);
			$loot = array_sum(array_intersect_key($report['resources'], array_flip(['metal', 'crystal', 'deuterium']))) / 2;
			$profit = min($loot, $flight['capacity']) - $flight['fuel'] * 2;

			if ($flight['fuel'] > $this->planet->deuterium * 0.5 || $flight['duration'] > (float) config('ai.max_flight_hours', 4) * 3600 || $profit < (float) config('ai.min_raid_profit', 1000)) {
				continue;
			}

			$candidates[] = ['target' => $target, 'report' => $report, 'flight' => $flight, 'loot' => $loot, 'score' => $profit * $this->targetWeights[$target->id] / max(60, $flight['duration'] * 2)];
		}

		usort($candidates, fn(array $a, array $b) => $b['score'] <=> $a['score']);
		$fleetCost = 0.0;

		foreach ($ships as $id => $count) {
			$price = Ship::createEntity($id, 1, $this->planet)->getPrice();
			$fleetCost += $count * ($price['metal'] + $price['crystal'] * 1.5 + $price['deuterium'] * 2);
		}

		foreach (array_slice($candidates, 0, 3) as $candidate) {
			$target = $candidate['target'];

			try {
				$forecast = new BattleForecast()->evaluate($this->planet, $target, $ships, $candidate['report']);
			} catch (Throwable $exception) {
				Log::warning('ai.forecast_failed', ['user_id' => $this->planet->user_id, 'reason' => $exception->getMessage()]);
				return;
			}

			$this->state['targets'][$target->id]['evaluated_at'] = now()->timestamp;
			$this->state['targets'][$target->id]['evaluated_report'] = $candidate['report']['date'];
			$lossRatio = (float) config('ai.max_loss_ratio', 0.15) * ($this->strategy === StrategyType::ECONOMY ? 0.5 : 1);

			if (!$forecast || $forecast['loss'] > $fleetCost * $lossRatio) {
				continue;
			}

			$profit = min($candidate['loot'], max(0, $forecast['capacity'] - $candidate['flight']['fuel'])) - $forecast['loss'] - $candidate['flight']['fuel'] * 2;

			if ($profit < (float) config('ai.min_raid_profit', 1000)) {
				continue;
			}

			if ($this->send($target->coordinates, MissionType::Attack, $ships)) {
				$this->state['targets'][$target->id]['attacked_at'] = now()->timestamp;
				return;
			}
		}
	}

	/** @return array<int, int> */
	private function attackShips(): array
	{
		$ships = [];

		foreach (Vars::getObjectsByType(ItemType::FLEET) as $object) {
			$id = $object->getId();

			if (!$object instanceof ShipObject || $object->getSpeed() <= 0 || in_array($id, [208, 209, 210, 216], true)) {
				continue;
			}

			$count = $this->planet->getLevel($id);

			if ($count > 0) {
				$ships[$id] = $count;
			}
		}

		return $ships;
	}

	private function recycle(): void
	{
		$count = $this->planet->getLevel(209);

		if ($count < 1) {
			return;
		}

		$targets = Planet::query()->where('galaxy', $this->planet->galaxy)->where('planet_type', PlanetType::PLANET)
			->whereBetween('system', [$this->planet->system - 10, $this->planet->system + 10])
			->where(function (Builder $query) {
				$query->where('debris_metal', '>', 0)->orWhere('debris_crystal', '>', 0);
			})->orderByRaw('ABS(`system` - ?)', [$this->planet->system])->limit(10)->get();

		foreach ($targets as $target) {
			$coordinates = new Coordinates($target->galaxy, $target->system, $target->planet, PlanetType::DEBRIS);

			if ($this->flightExists($coordinates, [MissionType::Recycling])) {
				continue;
			}

			$debris = $target->debris_metal + $target->debris_crystal;
			$ships = [209 => min($count, max(1, (int) ceil($debris / max(1, Ship::createEntity(209, 1, $this->planet)->getStorage()))))];
			$flight = $this->flight($ships, $coordinates);

			if (min($debris, $flight['capacity']) > $flight['fuel'] * 2 + (float) config('ai.min_raid_profit', 1000) && $this->send($coordinates, MissionType::Recycling, $ships)) {
				return;
			}
		}
	}

	private function evacuate(): bool
	{
		$threat = Fleet::query()->coordinates(FleetDirection::END, $this->planet->coordinates)
			->whereNot('user_id', $this->planet->user_id)->where('mess', 0)
			->whereIn('mission', [MissionType::Attack, MissionType::Assault, MissionType::Destruction])
			->where('start_date', '<=', now()->addMinutes(10))->exists();

		if (!$threat) {
			return false;
		}

		$ships = [];

		foreach (Vars::getObjectsByType(ItemType::FLEET) as $object) {
			if ($object instanceof ShipObject && $object->getSpeed() > 0 && $this->planet->getLevel($object) > 0) {
				$ships[$object->getId()] = $this->planet->getLevel($object);
			}
		}

		if (empty($ships)) {
			return false;
		}

		$destinations = $this->planet->user->planets()->whereNot('id', $this->planet->id)
			->whereNull('destroyed_at')->where('planet_type', PlanetType::PLANET)
			->orderByRaw('ABS(`galaxy` - ?), ABS(`system` - ?)', [$this->planet->galaxy, $this->planet->system])->get();

		foreach ($destinations as $destination) {
			if (Fleet::query()->coordinates(FleetDirection::END, $destination->coordinates)->where('mess', 0)
				->whereIn('mission', [MissionType::Attack, MissionType::Assault, MissionType::Destruction])->exists()) {
				continue;
			}

			$flight = $this->flight($ships, $destination->coordinates);

			if ($this->send($destination->coordinates, MissionType::Stay, $ships, $this->cargo($flight, 1.0))) {
				return true;
			}
		}

		return false;
	}

	private function supplyColony(): void
	{
		if ($this->planet->getLevel(1) < 12) {
			return;
		}

		$ships = [];

		foreach ([202, 203] as $id) {
			if ($this->planet->getLevel($id) > 0) {
				$ships[$id] = min(5, $this->planet->getLevel($id));
			}
		}

		if (empty($ships)) {
			return;
		}

		$colonies = $this->planet->user->planets()->with('entities')->whereNot('id', $this->planet->id)
			->whereNull('destroyed_at')->where('planet_type', PlanetType::PLANET)->orderByDesc('id')->get();

		foreach ($colonies as $colony) {
			if ($colony->getLevel(1) >= 10 || $this->flightExists($colony->coordinates, [MissionType::Transport, MissionType::Stay])) {
				continue;
			}

			$flight = $this->flight($ships, $colony->coordinates);
			$resources = $this->cargo($flight, 0.1, ['metal' => max(0, 10000 - $colony->metal), 'crystal' => max(0, 6000 - $colony->crystal), 'deuterium' => max(0, 3000 - $colony->deuterium)]);

			if (array_sum($resources) >= 2000 && $this->send($colony->coordinates, MissionType::Transport, $ships, $resources)) {
				return;
			}
		}
	}

	private function cargo(array $flight, float $fraction, array $limits = []): array
	{
		$resources = [];
		$capacity = $flight['capacity'];

		foreach (['deuterium', 'crystal', 'metal'] as $resource) {
			$available = max(0, $this->planet->{$resource} - ($resource === 'deuterium' ? $flight['fuel'] : 0));
			$count = (int) floor(min($capacity, $available * $fraction, $limits[$resource] ?? $available));
			$resources[$resource] = $count;
			$capacity -= $count;
		}

		return $resources;
	}
}
