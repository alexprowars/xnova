<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Coordinates;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet;
use App\Engine\Fleet\FleetCollection;
use App\Engine\Fleet\MissionType;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Facades\Vars;
use App\Factories\PlanetServiceFactory;
use App\Format;
use App\Http\Controllers\Controller;
use App\Models;
use App\Models\Planet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

class FleetSendController extends Controller
{
	public function index()
	{
		return to_route('fleet');
	}

	public function send(Request $request)
	{
		$moon = $request->integer('moon');

		if ($moon && $moon != $this->planet->id) {
			$this->checkJumpGate($this->user->planets()->findOrFail($moon));
		}

		$galaxy = $request->integer('galaxy');
		$system = $request->integer('system');
		$planet = $request->integer('planet');

		$planetType = $request->integer('planet_type');
		$planetType = PlanetType::tryFrom($planetType);

		$fleetMission = $request->integer('mission');
		$fleetMission = MissionType::tryFrom($fleetMission);

		$assaultId = $request->integer('alliance');

		$resources = $request->post('resource', []);
		$resources = array_map('intval', $resources);

		$fleetSpeedFactor = $request->integer('speed', 10);

		$fleetArray = Crypt::decrypt($request->post('fleet', '')) ?: [];

		$target = new Coordinates($galaxy, $system, $planet, $planetType);

		$sender = new Fleet\FleetSend($this->planet, $target, $fleetMission);
		$sender->setFleets($fleetArray);
		$sender->setResources($resources);
		$sender->setFleetSpeed($fleetSpeedFactor);

		if ($fleetMission == MissionType::Expedition) {
			$sender->setExpeditionTime($request->integer('expeditiontime'));
		}

		$holdTime = $request->integer('holdingtime');

		if ($holdTime) {
			$sender->setStayTime($holdTime);
		}

		if ($assaultId && $fleetMission == MissionType::Assault) {
			$assault = Models\Assault::query()
				->whereKey($assaultId)
				->whereHas('users', function (Builder $query) {
					$query->whereBelongsTo($this->user);
				})
				->first();

			if ($assault && $assault->coordinates->isSame($target)) {
				$sender->setAssault($assault);
			} else {
				$sender->setMission(MissionType::Attack);
			}
		}

		try {
			$fleet = $sender->send();
		} catch (Exception $e) {
			throw new PageException('<span class="error"><b>' . $e->getMessage() . '</b></span>');
		}

		$fleetCollection = FleetCollection::createFromArray($fleetArray, $this->planet);

		$maxFleetSpeed = $fleetCollection->getSpeed();

		$distance = $fleetCollection->getDistance($this->planet->coordinates, $target);
		$duration = $fleetCollection->getDuration($fleetSpeedFactor, $distance);

		$consumption = $fleetCollection->getConsumption($duration, $distance);

		$result = [
			'mission' => $fleet->mission->value,
			'distance' => $distance,
			'speed' => $maxFleetSpeed,
			'consumption' => $consumption,
			'from' => $this->planet->coordinates->toArray(),
			'target' => $target->toArray(),
			'start_date' => $fleet->start_date?->utc()->toAtomString(),
			'end_date' => $fleet->end_date?->utc()->toAtomString(),
			'units' => [],
		];

		foreach ($fleetArray as $unitId => $count) {
			$result['units'][Vars::getName($unitId)] = $count;
		}

		return Inertia::render('Fleet/Send', $result);
	}

	private function checkJumpGate(Planet $targetPlanet): void
	{
		$nextJumpTime = $this->planet->getConnection()->transaction(function () use ($targetPlanet): int {
			$this->user->refreshForUpdate();

			$planets = [$this->planet, $targetPlanet];
			usort($planets, fn (Planet $first, Planet $second) => $first->id <=> $second->id);

			foreach ($planets as $planet) {
				$planet->refreshForUpdate();

				if ($planet->trashed() || $planet->user_id != $this->user->id) {
					throw new Exception(__('fleet.gate_no_dest_g'));
				}

				$planet->setRelation('user', $this->user);
				$planet->setRelation('entities', $planet->entities()->lockForUpdate()->get());
			}

			return $this->jump($targetPlanet);
		});

		throw new Exception(__('fleet.gate_jump_done') . ' ' . Format::time($nextJumpTime));
	}

	private function jump(Planet $targetPlanet): int
	{
		$planetService = resolve(PlanetServiceFactory::class)
			->make($this->planet);

		if (!$planetService->isAvailableJumpGate()) {
			throw new Exception(__('fleet.gate_no_dest_g'));
		}

		$nextJumpTime = $planetService->getNextJumpTime();

		if ($nextJumpTime > 0) {
			throw new Exception(__('fleet.gate_wait_star') . ' - ' . Format::time($nextJumpTime));
		}

		$targetPlanetService = resolve(PlanetServiceFactory::class)
			->make($targetPlanet);

		if (!$targetPlanetService->isAvailableJumpGate()) {
			throw new Exception(__('fleet.gate_no_dest_g'));
		}

		$nextJumpTime = $targetPlanetService->getNextJumpTime();

		if ($nextJumpTime > 0) {
			throw new Exception(__('fleet.gate_wait_dest') . ' - ' . Format::time($nextJumpTime));
		}

		$success = false;

		$ships = Arr::wrap(request()->post('ship', []));
		$ships = array_map('intval', $ships);
		$ships = array_map('abs', $ships);

		foreach (Vars::getItemsByType(ItemType::FLEET) as $ship) {
			if (!isset($ships[$ship]) || !$ships[$ship]) {
				continue;
			}

			if ($ships[$ship] > $this->planet->getLevel($ship)) {
				$count = $this->planet->getLevel($ship);
			} else {
				$count = $ships[$ship];
			}

			if ($count > 0) {
				$this->planet->updateAmount($ship, -$count, true);
				$targetPlanet->updateAmount($ship, $count, true);

				$success = true;
			}
		}

		if (!$success) {
			throw new Exception(__('fleet.gate_wait_data'));
		}

		$this->planet->last_jump_time = now();
		$this->planet->update();

		$targetPlanet->last_jump_time = now();
		$targetPlanet->update();

		$this->user->update(['planet_current' => $targetPlanet->id]);

		return $planetService->getNextJumpTime();
	}
}
