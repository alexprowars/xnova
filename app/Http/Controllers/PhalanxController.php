<?php

namespace App\Http\Controllers;

use App\Engine\Coordinates;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet;
use App\Engine\Fleet\MissionType;
use App\Exceptions\PageException;
use App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhalanxController extends Controller
{
	public function index(Request $request)
	{
		if ($this->planet->destroyed_at) {
			throw new PageException(__('fleet.phalanx_destroyed_moon'));
		}

		$galaxy = $request->integer('galaxy');
		$system = $request->integer('system');
		$planet = $request->integer('planet');

		$consumption = 5000;

		if ($galaxy < 1 || $galaxy > config('game.maxGalaxyInWorld')) {
			$galaxy = $this->planet->galaxy;
		}

		if ($system < 1 || $system > config('game.maxSystemInGalaxy')) {
			$system = $this->planet->system;
		}

		if ($planet < 1 || $planet > config('game.maxPlanetInSystem')) {
			$planet = $this->planet->planet;
		}

		$phalanx = $this->planet->getLevel('phalanx');

		$systemFrom = $this->planet->system - ($phalanx ** 2);
		$systemTo 	= $this->planet->system + ($phalanx ** 2);

		$target = new Coordinates($galaxy, $system, $planet);

		if ($this->planet->planet_type != PlanetType::MOON) {
			throw new PageException(__('fleet.phalanx_moon_only'));
		}

		if ($phalanx == 0) {
			throw new PageException(__('fleet.phalanx_required'));
		}

		if ($this->planet->deuterium < $consumption) {
			throw new PageException(__('fleet.phalanx_not_enough_deuterium', ['amount' => $consumption]));
		}

		if (($target->getSystem() <= $systemFrom || $target->getSystem() >= $systemTo) || $target->getGalaxy() != $this->planet->galaxy) {
			throw new PageException(__('fleet.phalanx_out_of_range'));
		}

		$planetExist = Models\Planet::query()->coordinates($target)
			->exists();

		if (!$planetExist) {
			throw new PageException(__('fleet.phalanx_planet_not_found'));
		}

		$this->planet->deuterium -= $consumption;
		$this->planet->update();

		$fleets = Models\Fleet::query()
			->where(
				fn (Builder $query) => $query->coordinates(FleetDirection::START, $target)
					->where('start_type', '!=', PlanetType::MOON)
			)
			->orWhere(
				fn (Builder $query) => $query->coordinates(FleetDirection::END, $target)
					->where('end_type', PlanetType::PLANET)
			)
			->orderBy('start_date')
			->get();

		$items = [];

		foreach ($fleets as $row) {
			$end = !($row->start_galaxy == $galaxy && $row->start_system == $system && $row->start_planet == $planet && $row->start_type == PlanetType::PLANET);

			$type1 = $row->start_type == PlanetType::MOON ? 'moon' : 'planet';
			$type2 = $row->end_type == PlanetType::MOON ? 'moon' : 'planet';

			if ($row->start_date->isFuture() && $end && !($row->start_type == PlanetType::MOON && ($row->end_type == PlanetType::DEBRIS || $row->end_type == PlanetType::MOON))) {
				$items[] = [
					'time' => $row->start_date->utc()->toAtomString(),
					'fleet' => Fleet::createFleetPopupedFleetLink($row, $this->user),
					'type_1' => $type1,
					'type_2' => $type2,
					'planet_name' => $row->user_name,
					'planet_position' => $row->splitStartPosition(),
					'target_name' => $row->target_user_name,
					'target_position' => $row->splitTargetPosition(),
					'mission' => $row->mission,
					'direction' => 1
				];
			}

			if ($row->end_date !== null && $row->mission != MissionType::Stay && !$end && $row->start_type != PlanetType::MOON) {
				$items[] = [
					'time' => $row->end_date->utc()->toAtomString(),
					'fleet' => Fleet::createFleetPopupedFleetLink($row, $this->user),
					'type_1' => $type2,
					'type_2' => $type1,
					'planet_name' => $row->target_user_name,
					'planet_position' => $row->splitTargetPosition(),
					'target_name' => $row->user_name,
					'target_position' => $row->splitStartPosition(),
					'mission' => $row->mission,
					'direction' => 2
				];
			}
		}

		return Inertia::render('Phalanx', [
			'items' => $items,
		]);
	}
}
