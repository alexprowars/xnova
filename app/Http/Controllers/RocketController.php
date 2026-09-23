<?php

namespace App\Http\Controllers;

use App\Engine\Coordinates;
use App\Engine\Entity\Model\FleetEntity;
use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\MissionType;
use App\Engine\Formulas;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Models\Fleet;
use App\Models\Planet;
use App\Support\ToastType;
use Illuminate\Http\Request;

class RocketController extends Controller
{
	public function index(Request $request): void
	{
		$data = $request->validate([
			'galaxy' => ['required', 'integer', 'min:1', 'max:' . config('game.maxGalaxyInWorld')],
			'system' => ['required', 'integer', 'min:1', 'max:' . config('game.maxSystemInGalaxy')],
			'planet' => ['required', 'integer', 'min:1', 'max:' . config('game.maxPlanetInSystem')],
		]);

		$galaxy = (int) $data['galaxy'];
		$system = (int) $data['system'];
		$planet = (int) $data['planet'];

		$count = (int) $request->post('count', 1);
		$destroyType = $request->post('target', 'all');

		$distance = abs($system - $this->planet->system);
		$maxDistance = Formulas::getMissileRange($this->user->getTechLevel('impulse_motor'));

		$targetPlanet = Planet::findByCoordinates(new Coordinates($galaxy, $system, $planet, PlanetType::PLANET));

		if (!$targetPlanet) {
			throw new Exception(__('fleet.noplanetrow'));
		}

		if ($targetPlanet->user_id == $this->user->id) {
			throw new Exception(__('fleet.ownpl_err'));
		}

		$targetUser = $targetPlanet->user;

		if (!$targetUser) {
			throw new Exception(__('fleet.rocket_player_not_found'));
		}

		if ($targetUser->isVacation()) {
			throw new Exception(__('fleet.vacation_pla'));
		}

		if ($this->planet->getLevel('missile_facility') < 4) {
			throw new Exception(__('fleet.rocket_silo_required'));
		} elseif ($this->user->getTechLevel('impulse_motor') == 0) {
			throw new Exception(__('fleet.rocket_impulse_required'));
		} elseif ($distance > $maxDistance || $galaxy != $this->planet->galaxy) {
			throw new Exception(__('fleet.rocket_out_of_range'));
		} elseif ($count <= 0 || $count > $this->planet->getLevel('interplanetary_misil')) {
			throw new Exception(__('fleet.rocket_not_enough_missiles'));
		} elseif ((!is_numeric($destroyType) && $destroyType != 'all') || (!in_array($destroyType, Vars::getItemsByType(ItemType::DEFENSE)) && $destroyType != 'all')) {
			throw new Exception(__('fleet.rocket_target_not_found'));
		}

		if ($destroyType == 'all') {
			$destroyType = 0;
		} else {
			$destroyType = (int) $destroyType;
		}

		$time = 30 + (60 * $distance);

		$fleet = Fleet::create([
			'user_id' 			=> $this->user->id,
			'user_name' 		=> $this->planet->name,
			'mission' 			=> MissionType::MissileAttack,
			'entities' 			=> new FleetEntityCollection([FleetEntity::create(503, $count, ['target' => $destroyType])]),
			'start_date' 		=> now()->addSeconds($time),
			'start_galaxy' 		=> $this->planet->galaxy,
			'start_system' 		=> $this->planet->system,
			'start_planet' 		=> $this->planet->planet,
			'start_type' 		=> PlanetType::PLANET,
			'end_date' 			=> null,
			'end_galaxy' 		=> $galaxy,
			'end_system' 		=> $system,
			'end_planet' 		=> $planet,
			'end_type' 			=> PlanetType::PLANET,
			'target_user_id' 	=> $targetPlanet->user_id,
			'target_user_name' 	=> $targetPlanet->name,
			'updated_at' 		=> now()->addSeconds($time),
		]);

		if ($fleet->id > 0) {
			$this->planet->updateAmount('interplanetary_misil', -$count, true);
			$this->planet->update();
		}

		toast(ToastType::SUCCESS, __('fleet.rocket_launched', ['count' => $count]));
	}
}
