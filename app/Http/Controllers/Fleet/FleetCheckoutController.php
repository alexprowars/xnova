<?php

namespace App\Http\Controllers\Fleet;

use App\Exceptions\RedirectException;
use App\Models\Assault;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Controller;
use App\Exceptions\PageException;
use App\Entity;
use App\Fleet;
use App\Models\Planet;
use App\Planet\Entity as PlanetEntity;
use App\Vars;

class FleetCheckoutController extends Controller
{
	public function index(Request $request)
	{
		if ($this->user->vacation) {
			throw new PageException('Нет доступа!');
		}

		$parse = [];

		$galaxy = (int) $request->post('galaxy', 0);
		$system = (int) $request->post('system', 0);
		$planet = (int) $request->post('planet', 0);
		$type = (int) $request->post('planet_type', 0);

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
			$type = 1;
		}

		$parse['ships'] = [];
		$fleets = [];

		$ships = $request->post('ship');

		if (!is_array($ships)) {
			$ships = [];
		}

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $i) {
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

		if (!count($fleets)) {
			throw new RedirectException('', '/fleet');
		}

		$parse['fleet'] = str_rot13(base64_encode(json_encode($fleets)));

		$parse['target'] = [
			'galaxy' => $galaxy,
			'system' => $system,
			'planet' => $planet,
			'planet_type' => $type,
		];

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

				if ($row->planet_type == 3) {
					$row->name .= ' ' . __('fleet.fl_shrtcup3');
				}

				$parse['planets'][] =  [
					'id' => $row->id,
					'name' => $row->name,
					'galaxy' => $row->galaxy,
					'system' => $row->system,
					'planet' => $row->planet,
					'planet_type' => $row->planet_type,
				];
			}
		}

		$parse['gate_time'] = 0;
		$parse['moons'] = [];

		if ($this->planet->planet_type == 3 || $this->planet->planet_type == 5) {
			$moons = $this->user->planets()
				->where('id', '!=', $this->planet->id)
				->where(function (Builder $planet) {
					$planet->where('planet_type', 3)->orWhere('planet_type', 5);
				})
				->get();

			if ($moons->count()) {
				$timer = $this->planet->getNextJumpTime();

				if ($timer != 0) {
					$parse['gate_time'] = $timer;
				}

				foreach ($moons as $moon) {
					if ($moon->getLevel('jumpgate') <= 0) {
						continue;
					}

					$parse['moons'][] = [
						'id' => $moon->id,
						'name' => $moon->name,
						'galaxy' => $moon->galaxy,
						'system' => $moon->system,
						'planet' => $moon->planet,
						'timer' => $moon->getNextJumpTime()
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

		$targetPlanet = Planet::findByCoordinates(new Entity\Coordinates($galaxy, $system, $planet, $type));

		if ($targetPlanet) {
			$UsedPlanet = true;

			if ($targetPlanet->user_id == $this->user->id) {
				$YourPlanet = true;
			}
		}

		$missions = Fleet::getFleetMissions($fleets, new Entity\Coordinates($galaxy, $system, $planet, $type), $YourPlanet, $UsedPlanet, ($acs > 0));

		if ($targetPlanet && ($targetPlanet->user_id == 1 || $this->user->isAdmin())) {
			$missions[] = 4;
		}

		$missions = array_values(array_unique($missions));

		if (in_array(15, $missions)) {
			if ($this->user->getTechLevel('expedition') <= 0) {
				unset($missions[array_search(15, $missions)]);
			} else {
				$parse['expedition_hours'] = round($this->user->getTechLevel('expedition') / 2) + 1;
			}
		}

		if (!$mission && $acs && in_array(2, $missions)) {
			$mission = 2;
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

		return $parse;
	}
}
