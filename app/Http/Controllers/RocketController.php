<?php

namespace App\Http\Controllers;

use App\Engine\Coordinates;
use App\Engine\Fleet\Mission;
use App\Exceptions\ErrorException;
use App\Exceptions\PageException;
use App\Exceptions\SuccessException;
use App\Models;
use App\Models\Fleet;
use Illuminate\Support\Facades\Request;

class RocketController extends Controller
{
	public function index()
	{
		if (!Request::instance()->isMethod('post')) {
			throw new PageException('Ошибка', '/galaxy/');
		}

		$g = (int) Request::post('galaxy', 0);
		$s = (int) Request::post('system', 0);
		$p = (int) Request::post('planet', 0);

		if ($g <= 0 || $s <= 0 || $p <= 0) {
			throw new ErrorException('Координаты не определены');
		}

		$count = (int) Request::post('count', 1);
		$destroyType = Request::post('target', 'all');

		$distance = abs($s - $this->planet->system);
		$maxDistance = ($this->user->getTechLevel('impulse_motor') * 5) - 1;

		$targetPlanet = Models\Planet::findByCoordinates(new Coordinates($g, $s, $p, 1));

		if ($this->planet->getLevel('missile_facility') < 4) {
			throw new ErrorException('Постройте ракетную шахту');
		} elseif ($this->user->getTechLevel('impulse_motor') == 0) {
			throw new ErrorException('Необходима технология "Импульсный двигатель"');
		} elseif ($distance >= $maxDistance || $g != $this->planet->galaxy) {
			throw new ErrorException('Превышена дистанция ракетной атаки');
		} elseif (!$targetPlanet) {
			throw new ErrorException('Планета не найдена');
		} elseif ($count > $this->planet->getLevel('interplanetary_misil')) {
			throw new ErrorException('У вас нет такого кол-ва ракет');
		} elseif ((!is_numeric($destroyType) && $destroyType != "all") or ($destroyType < 0 && $destroyType > 7 && $destroyType != "all")) {
			throw new ErrorException('Не найдена цель');
		}

		if ($destroyType == 'all') {
			$destroyType = 0;
		} else {
			$destroyType = (int) $destroyType;
		}

		$targetUser = Models\User::query()
			->find($targetPlanet->user_id, ['id', 'vacation']);

		if (!$targetUser) {
			throw new ErrorException('Игрока не существует');
		}

		if ($targetUser->isVacation()) {
			throw new ErrorException('Игрок в режиме отпуска');
		}

		if ($this->user->isVacation()) {
			throw new ErrorException('Вы в режиме отпуска');
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
			'start_type' 		=> 1,
			'end_time' 			=> null,
			'end_galaxy' 		=> $g,
			'end_system' 		=> $s,
			'end_planet' 		=> $p,
			'end_type' 			=> 1,
			'target_user_id' 	=> $targetPlanet->user_id,
			'target_user_name' 	=> $targetPlanet->name,
			'updated_at' 		=> now()->addSeconds($time),
		]);

		if ($fleet->id > 0) {
			$this->planet->updateAmount('interplanetary_misil', -$count, true);
			$this->planet->update();
		}

		throw new SuccessException('<b>' . $count . '</b> межпланетные ракеты запущены для атаки удалённой планеты!');
	}
}
