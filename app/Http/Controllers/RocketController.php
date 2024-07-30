<?php

namespace App\Http\Controllers;

use App\Engine\Coordinates;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\Mission;
use App\Engine\Vars;
use App\Exceptions\Exception;
use App\Models\Fleet;
use App\Models\Planet;
use Illuminate\Http\Request;

class RocketController extends Controller
{
	public function index(Request $request)
	{
		$galaxy = (int) $request->post('galaxy', 0);
		$system = (int) $request->post('system', 0);
		$planet = (int) $request->post('planet', 0);

		if ($galaxy <= 0 || $system <= 0 || $planet <= 0) {
			throw new Exception('Координаты не определены');
		}

		$count = (int) $request->post('count', 1);
		$destroyType = $request->post('target', 'all');

		$distance = abs($system - $this->planet->system);
		$maxDistance = ($this->user->getTechLevel('impulse_motor') * 5) - 1;

		$targetPlanet = Planet::findByCoordinates(new Coordinates($galaxy, $system, $planet, PlanetType::PLANET));
		$targetUser = $targetPlanet->user;

		if (!$targetUser) {
			throw new Exception('Игрока не существует');
		}

		if ($targetUser->isVacation()) {
			throw new Exception('Игрок в режиме отпуска');
		}

		if (!$targetPlanet) {
			throw new Exception('Планета не найдена');
		} elseif ($this->planet->getLevel('missile_facility') < 4) {
			throw new Exception('Постройте ракетную шахту');
		} elseif ($this->user->getTechLevel('impulse_motor') == 0) {
			throw new Exception('Необходима технология "Импульсный двигатель"');
		} elseif ($distance >= $maxDistance || $galaxy != $this->planet->galaxy) {
			throw new Exception('Превышена дистанция ракетной атаки');
		} elseif ($count > $this->planet->getLevel('interplanetary_misil')) {
			throw new Exception('У вас нет такого кол-ва ракет');
		} elseif ((!is_numeric($destroyType) && $destroyType != 'all') || (!in_array($destroyType, Vars::getItemsByType(ItemType::DEFENSE)) && $destroyType != 'all')) {
			throw new Exception('Не найдена цель');
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
			'mission' 			=> Mission::Rak,
			'fleet_array' 		=> [['id' => 503, 'count' => $count, 'target' => $destroyType]],
			'start_time' 		=> now()->addSeconds($time),
			'start_galaxy' 		=> $this->planet->galaxy,
			'start_system' 		=> $this->planet->system,
			'start_planet' 		=> $this->planet->planet,
			'start_type' 		=> PlanetType::PLANET,
			'end_time' 			=> null,
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
	}
}
