<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Entity\Ship;
use App\Engine\Enums\ItemType;
use App\Engine\Fleet\MissionType;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Http\Controllers\Controller;
use App\Models;
use App\Services\FleetService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FleetController extends Controller
{
	public function index(Request $request)
	{
		if (!$this->planet) {
			throw new Exception(__('fleet.fl_noplanetrow'));
		}

		$expeditionTech = $this->user->getTechLevel('expedition');
		$curExpeditions = 0;
		$maxExpeditions = 0;

		if ($expeditionTech >= 1) {
			$curExpeditions = Models\Fleet::query()->whereBelongsTo($this->user)->where('mission', MissionType::Expedition)->count();
			$maxExpeditions = 1 + floor($expeditionTech / 3);
		}

		$galaxy = $request->integer('galaxy');
		$system = $request->integer('system');
		$planet = $request->integer('planet');
		$planet_type = $request->integer('type');
		$mission = $request->integer('mission');

		if (!$galaxy) {
			$galaxy = $this->planet->galaxy;
		}

		if (!$system) {
			$system = $this->planet->system;
		}

		if (!$planet) {
			$planet = $this->planet->planet;
		}

		if (!$planet_type) {
			$planet_type = 1;
		}

		$isCurrentPlanet = $galaxy == $this->planet->galaxy
			&& $system == $this->planet->system
			&& $planet == $this->planet->planet;

		$result = [
			'curExpeditions' => $curExpeditions,
			'maxExpeditions' => $maxExpeditions,
			'selected' => [
				'mission' => $mission,
				'galaxy' => !$isCurrentPlanet ? $galaxy : 0,
				'system' => !$isCurrentPlanet ? $system : 0,
				'planet' => !$isCurrentPlanet ? $planet : 0,
				'planet_type' => $planet_type,
			],
			'mission' => $mission,
			'fleets' => [],
			'ships' => [],
		];

		$fleets = Models\Fleet::query()
			->whereBelongsTo($this->user)
			->get();

		foreach ($fleets as $fleet) {
			$result['fleets'][] = [
				'id' => $fleet->id,
				'mission' => $fleet->mission,
				'amount' => $fleet->entities->getTotal(),
				'units' => $fleet->entities,
				'start' => [
					'galaxy' => $fleet->start_galaxy,
					'system' => $fleet->start_system,
					'planet' => $fleet->start_planet,
					'date' => $fleet->start_date?->utc()->toAtomString(),
				],
				'target' => [
					'id' => $fleet->target_user_id,
					'galaxy' => $fleet->end_galaxy,
					'system' => $fleet->end_system,
					'planet' => $fleet->end_planet,
					'date' => $fleet->end_date?->utc()->toAtomString(),
				],
				'stage' => $fleet->mess,
			];
		}

		foreach (Vars::getItemsByType(ItemType::FLEET) as $i) {
			if ($this->planet->getLevel($i) > 0) {
				$result['ships'][] = Ship::createEntity($i, $this->planet->getLevel($i), $this->planet)->getInfo();
			}
		}

		return Inertia::render('Fleet/Fleet', $result);
	}

	public function list(): array
	{
		return FleetService::list($this->user);
	}
}
