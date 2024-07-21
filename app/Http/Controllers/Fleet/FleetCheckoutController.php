<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Coordinates;
use App\Engine\Entity as PlanetEntity;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet;
use App\Engine\Fleet\Mission;
use App\Engine\Vars;
use App\Exceptions\PageException;
use App\Exceptions\RedirectException;
use App\Http\Controllers\Controller;
use App\Models\Assault;
use App\Models\Planet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FleetCheckoutController extends Controller
{
	public function index(Request $request)
	{
		if ($this->user->isVacation()) {
			throw new PageException('Нет доступа!');
		}

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

		$ships = $request->post('ship');

		if (!is_array($ships)) {
			$ships = [];
		}

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
			throw new RedirectException('/fleet');
		}

		$target = new Coordinates($galaxy, $system, $planet, $type);

		$parse['fleet'] = str_rot13(base64_encode(json_encode($fleets)));
		$parse['target'] = $target->toArray();
		$parse['galaxy_max'] = (int) config('settings.maxGalaxyInWorld');
		$parse['system_max'] = (int) config('settings.maxSystemInGalaxy');
		$parse['planet_max'] = (int) config('settings.maxPlanetInSystem') + 1;

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
				->where(function (Builder $planet) {
					$planet->where('planet_type', 3)->orWhere('planet_type', 5);
				})
				->get();

			if ($moons->count()) {
				$timer = $this->planet->getNextJumpTime();

				if ($timer) {
					$parse['gate_time'] = now()->addSeconds($timer)->utc()->toAtomString();
				}

				foreach ($moons as $moon) {
					if ($moon->getLevel('jumpgate') <= 0) {
						continue;
					}

					$gateTime = $moon->getNextJumpTime();

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
				'planet_type' => (int) $assault->planet_type,
			];
		}

		$acs 	= (int) $request->post('alliance', 0);
		$mission 	= (int) $request->post('mission', 0);

		$YourPlanet = false;
		$UsedPlanet = false;

		$targetPlanet = Planet::findByCoordinates($target);

		if ($targetPlanet) {
			$UsedPlanet = true;

			if ($targetPlanet->user_id == $this->user->id) {
				$YourPlanet = true;
			}
		}

		$missions = Fleet::getFleetMissions($fleets, $target, $YourPlanet, $UsedPlanet, ($acs > 0));

		if ($targetPlanet && ($targetPlanet->user_id == 1 || $this->user->isAdmin()) && !in_array(Mission::Stay, $missions)) {
			$missions[] = Mission::Stay;
		}

		if (in_array(Mission::Expedition, $missions)) {
			if ($this->user->getTechLevel('expedition') <= 0) {
				unset($missions[array_search(Mission::Expedition, $missions)]);
			} else {
				$parse['expedition_hours'] = round($this->user->getTechLevel('expedition') / 2) + 1;
			}
		}

		if (!$mission && $acs && in_array(Mission::Assault, $missions)) {
			$mission = Mission::Assault;
		}

		$parse['missions'] = [];

		if (count($missions) > 0) {
			foreach ($missions as $i => $id) {
				if (($mission > 0 && $mission == $id) || ($i == 0 && !in_array($mission, $missions)) || count($missions) == 1) {
					$mission = $id;
				}

				$parse['missions'][] = $id;
			}

			if (!$mission) {
				$mission = $missions[0];
			}
		}

		$parse['mission'] = $mission;

		return response()->state($parse);
	}
}
