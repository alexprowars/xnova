<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Coordinates;
use App\Engine\Entity as PlanetEntity;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet;
use App\Engine\Fleet\Mission;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Factories\PlanetServiceFactory;
use App\Http\Controllers\Controller;
use App\Models\Assault;
use App\Models\Planet;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class FleetCheckoutController extends Controller
{
	public function index(Request $request)
	{
		$galaxy = (int) $request->post('galaxy', 0);
		$system = (int) $request->post('system', 0);
		$planet = (int) $request->post('planet', 0);

		$type = (int) $request->post('planet_type', 0);
		$type = PlanetType::tryFrom($type);

		if (!$galaxy) {
			$galaxy = (int) $this->planet->galaxy;
		}

		if (!$system) {
			$system = (int) $this->planet->system;
		}

		if (!$planet) {
			$planet = (int) $this->planet->planet;
		}

		if (!$type) {
			$type = PlanetType::PLANET;
		}

		$parse = [];
		$parse['ships'] = [];
		$fleets = [];

		$ships = Arr::wrap($request->post('ships', []));

		foreach (Vars::getItemsByType(ItemType::FLEET) as $i) {
			if (isset($ships[$i]) && (int) $ships[$i] > 0) {
				$cnt = (int) $ships[$i];

				if ($cnt > $this->planet->getLevel($i)) {
					continue;
				}

				$fleets[$i] = $cnt;

				$ship = PlanetEntity\Ship::createEntity($i, 1, $this->planet)->getInfo();
				$ship['count'] = $cnt;

				$parse['ships'][] = $ship;
			}
		}

		if (empty($fleets)) {
			throw new Exception('Не выбран флот');
		}

		$target = new Coordinates($galaxy, $system, $planet, $type);

		$parse['fleet'] = Crypt::encrypt($fleets);
		$parse['target'] = $target->toArray();
		$parse['galaxy_max'] = (int) config('game.maxGalaxyInWorld');
		$parse['system_max'] = (int) config('game.maxSystemInGalaxy');
		$parse['planet_max'] = (int) config('game.maxPlanetInSystem') + 1;
		$parse['expedition_hours'] = round($this->user->getTechLevel('expedition') / 2) + 1;

		$parse['shortcuts'] = [];

		foreach ($this->user->shortcuts as $shortcut) {
			$parse['shortcuts'][] = [
				'id' => $shortcut->id,
				'name' => $shortcut->name,
				'galaxy' => $shortcut->galaxy,
				'system' => $shortcut->system,
				'planet' => $shortcut->planet,
				'planet_type' => $shortcut->planet_type,
			];
		}

		$parse['planets'] = [];

		$planets = $this->user->getPlanets();

		if (count($planets) > 1) {
			foreach ($planets as $row) {
				if ($row->id == $this->planet->id) {
					continue;
				}

				if ($row->planet_type == PlanetType::MOON) {
					$row->name .= ' ' . __('fleet.fl_shrtcup3');
				}

				$parse['planets'][] = $row->only(['id', 'name', 'galaxy', 'system', 'planet', 'planet_type']);
			}
		}

		$parse['gate_time'] = null;
		$parse['moons'] = [];

		if ($this->planet->planet_type == PlanetType::MOON || $this->planet->planet_type == PlanetType::MILITARY_BASE) {
			$moons = $this->user->planets()
				->where('id', '!=', $this->planet->id)
				->whereIn('planet_type', [PlanetType::MOON, PlanetType::MILITARY_BASE])
				->get();

			if ($moons->count()) {
				$timer = resolve(PlanetServiceFactory::class)->make($this->planet)
					->getNextJumpTime();

				if ($timer) {
					$parse['gate_time'] = now()->addSeconds($timer)->utc()->toAtomString();
				}

				foreach ($moons as $moon) {
					if ($moon->getLevel('jumpgate') <= 0) {
						continue;
					}

					$gateTime = resolve(PlanetServiceFactory::class)->make($moon)
						->getNextJumpTime();

					$parse['moons'][] = [
						'id' => $moon->id,
						'name' => $moon->name,
						'galaxy' => $moon->galaxy,
						'system' => $moon->system,
						'planet' => $moon->planet,
						'jumpgate' => $gateTime > 0 ? now()->addSeconds($gateTime)->utc()->toAtomString() : null,
					];
				}
			}
		}

		$parse['alliances'] = [];

		$assaults = Assault::query()
			->whereRelation('users', 'user_id', $this->user->id)
			->get();

		foreach ($assaults as $assault) {
			$parse['alliances'][] = [
				'id' => (int) $assault->id,
				'name' => $assault->name,
				'galaxy' => (int) $assault->galaxy,
				'system' => (int) $assault->system,
				'planet' => (int) $assault->planet,
				'planet_type' => $assault->planet_type->value,
			];
		}

		$acs = (int) $request->post('alliance', 0);

		$mission = (int) $request->post('mission');
		$mission = Mission::tryFrom($mission);

		$targetPlanet = Planet::findByCoordinates($target);

		$missions = [];

		foreach (Mission::cases() as $m) {
			if (Fleet\MissionFactory::getMission($m)::isMissionPossible($this->planet, $target, $targetPlanet, $fleets, $acs > 0)) {
				$missions[] = $m;
			}
		}

		if (!$mission && $acs && in_array(Mission::Assault, $missions)) {
			$mission = Mission::Assault;
		}

		$parse['missions'] = [];

		if (!empty($missions)) {
			foreach ($missions as $i => $id) {
				if (($mission && $mission == $id) || ($i == 0 && !in_array($mission, $missions)) || count($missions) == 1) {
					$mission = $id;
				}

				$parse['missions'][] = $id;
			}

			if (!$mission) {
				$mission = $missions[0];
			}
		}

		$parse['mission'] = $mission;

		return $parse;
	}
}
