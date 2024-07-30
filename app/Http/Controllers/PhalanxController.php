<?php

namespace App\Http\Controllers;

use App\Engine\Coordinates;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet;
use App\Engine\Fleet\Mission;
use App\Exceptions\Exception;
use App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PhalanxController extends Controller
{
	public function index(Request $request)
	{
		$galaxy = (int) $request->post('galaxy');
		$system = (int) $request->post('system');
		$planet = (int) $request->post('planet');

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

		$systemdol 	= $this->planet->system - ($phalanx ** 2);
		$systemgora = $this->planet->system + ($phalanx ** 2);

		$target = new Coordinates($galaxy, $system, $planet);

		if ($this->planet->planet_type != PlanetType::MOON) {
			throw new Exception('Вы можете использовать фалангу только на луне!');
		} elseif ($phalanx == 0) {
			throw new Exception('Постройте сначало сенсорную фалангу');
		} elseif ($this->planet->deuterium < $consumption) {
			throw new Exception('Недостаточно дейтерия для использования. Необходимо: ' . $consumption . '.');
		} elseif (($target->getSystem() <= $systemdol or $target->getSystem() >= $systemgora) || $target->getGalaxy() != $this->planet->galaxy) {
			throw new Exception('Вы не можете сканировать данную планету. Недостаточный уровень сенсорной фаланги.');
		}

		$this->planet->deuterium -= $consumption;
		$this->planet->update();

		$planetExist = Models\Planet::coordinates($target)
			->exists();

		if (!$planetExist) {
			throw new Exception('Чит детектед! Режим бога активирован! Приятной игры!');
		}

		$fleets = Models\Fleet::query()
			->where(
				fn (Builder $query) => $query->coordinates(FleetDirection::START, $target)
					->where('start_type', '!=', PlanetType::MOON)
			)
			->orWhere(
				fn (Builder $query) => $query->coordinates(FleetDirection::END, $target)
			)
			->orderBy('start_time')
			->get();

		$list = [];

		foreach ($fleets as $row) {
			$end = !($row->start_galaxy == $galaxy && $row->start_system == $system && $row->start_planet == $planet);

			if ($row->start_type == PlanetType::MOON) {
				$type = 'лун';
			} else {
				$type = 'планет';
			}

			if ($row->end_type == PlanetType::MOON) {
				$type2 = 'лун';
			} else {
				$type2 = 'планет';
			}

			if ($row->start_time->isFuture() && $end && !($row->start_type == PlanetType::MOON && ($row->end_type == PlanetType::DEBRIS || $row->end_type == PlanetType::MOON))) {
				$list[] = [
					'time' => $row->start_time->utc()->toAtomString(),
					'fleet' => Fleet::createFleetPopupedFleetLink($row, 'флот', '', $this->user),
					'type_1' => $type . 'ы',
					'type_2' => $type2 . 'у',
					'planet_name' => $row->user_name,
					'planet_position' => $row->splitStartPosition(),
					'target_name' => $row->target_user_name,
					'target_position' => $row->splitTargetPosition(),
					'mission' => $row->mission,
					'direction' => 1
				];
			}

			if ($row->mission != Mission::Stay && !$end && $row->start_type != PlanetType::MOON) {
				$list[] = [
					'time' => $row->end_time->utc()->toAtomString(),
					'fleet' => Fleet::createFleetPopupedFleetLink($row, 'флот', '', $this->user),
					'type_1' => $type2 . 'ы',
					'type_2' => $type . 'у',
					'planet_name' => $row->target_user_name,
					'planet_position' => $row->splitTargetPosition(),
					'target_name' => $row->user_name,
					'target_position' => $row->splitStartPosition(),
					'mission' => $row->mission,
					'direction' => 2
				];
			}
		}

		return response()->state([
			'items' => $list,
		]);
	}
}
