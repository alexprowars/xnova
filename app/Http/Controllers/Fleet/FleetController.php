<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Entity\Ship;
use App\Engine\Enums\ItemType;
use App\Engine\Fleet\Mission;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Http\Controllers\Controller;
use App\Http\Resources\FleetRow;
use App\Models;
use App\Models\Fleet;
use Illuminate\Http\Request;

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
			$curExpeditions = Models\Fleet::query()->whereBelongsTo($this->user)->where('mission', Mission::Expedition)->count();
			$maxExpeditions = 1 + floor($expeditionTech / 3);
		}

		$galaxy = (int) $request->query('galaxy', 0);
		$system = (int) $request->query('system', 0);
		$planet = (int) $request->query('planet', 0);
		$planet_type = (int) $request->query('type', 0);
		$mission = (int) $request->query('mission', 0);

		if (!$galaxy) {
			$galaxy = (int) $this->planet->galaxy;
		}

		if (!$system) {
			$system = (int) $this->planet->system;
		}

		if (!$planet) {
			$planet = (int) $this->planet->planet;
		}

		if (!$planet_type) {
			$planet_type = 1;
		}

		$parse = [];
		$parse['curExpeditions'] = $curExpeditions;
		$parse['maxExpeditions'] = $maxExpeditions;
		$parse['mission'] = $mission;

		$fleets = Models\Fleet::query()
			->whereBelongsTo($this->user)
			->get();

		$parse['fleets'] = [];

		foreach ($fleets as $fleet) {
			$parse['fleets'][] = [
				'id' => $fleet->id,
				'mission' => $fleet->mission,
				'amount' => $fleet->getTotalShips(),
				'units' => $fleet->getShips(),
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

		$isCurrent = $galaxy == $this->planet->galaxy && $system == $this->planet->system && $planet == $this->planet->planet;

		$parse['selected'] = [
			'mission' => $mission,
			'galaxy' => !$isCurrent ? $galaxy : 0,
			'system' => !$isCurrent ? $system : 0,
			'planet' => !$isCurrent ? $planet : 0,
			'planet_type' => $planet_type,
		];

		$parse['ships'] = [];

		foreach (Vars::getItemsByType(ItemType::FLEET) as $i) {
			if ($this->planet->getLevel($i) > 0) {
				$parse['ships'][] = Ship::createEntity($i, $this->planet->getLevel($i), $this->planet)->getInfo();
			}
		}

		return $parse;
	}

	public function list()
	{
		$fleets = Fleet::query()
			->with(['user'])
			->whereBelongsTo($this->user)
			->orWhereBelongsTo($this->user, 'target')
			->get();

		$result = [];
		$aks = [];

		foreach ($fleets as $fleet) {
			if ($fleet->user_id == $this->user->id) {
				if ($fleet->start_date->isFuture()) {
					$result[] = FleetRow::make($fleet, 0, true);
				}

				if ($fleet->end_stay?->isFuture()) {
					$result[] = FleetRow::make($fleet, 1, true);
				}

				if (!($fleet->mission == Mission::Colonization && $fleet->mess == 0)) {
					if (($fleet->end_date->isFuture() && $fleet->mission != Mission::Stay) or ($fleet->mess == 1 && $fleet->mission == Mission::Stay)) {
						$result[] = FleetRow::make($fleet, 2, true);
					}
				}

				if ($fleet->assault_id && !in_array($fleet->assault_id, $aks)) {
					$assaultFleets = Fleet::query()
						->where('assault_id', $fleet->assault_id)
						->whereNot('user_id', $this->user->id)
						->where('mess', 0)
						->get();

					foreach ($assaultFleets as $AKFleet) {
						$result[] = FleetRow::make($AKFleet, 0, false);
					}

					$aks[] = $fleet->assault_id;
				}
			} elseif ($fleet->mission != Mission::Recycling) {
				if ($fleet->start_date->isFuture()) {
					$result[] = FleetRow::make($fleet, 0, false);
				}

				if ($fleet->mission == Mission::StayAlly && $fleet->end_stay?->isFuture()) {
					$result[] = FleetRow::make($fleet, 1, false);
				}
			}
		}

		usort($result, fn ($a, $b) => $a['time'] <=> $b['time']);

		return $result;
	}
}
