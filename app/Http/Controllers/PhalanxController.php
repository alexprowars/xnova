<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Exceptions\PageException;
use App\Exceptions\RedirectException;
use App\Fleet;
use App\Models;
use App\Controller;

class PhalanxController extends Controller
{
	protected $loadPlanet = true;

	public function index(Request $request)
	{
		if ($this->user->vacation > 0) {
			throw new PageException('Нет доступа!');
		}

		$g = (int) $request->post('galaxy');
		$s = (int) $request->post('system');
		$i = (int) $request->post('planet');

		$consomation = 5000;

		if ($g < 1 || $g > config('settings.maxGalaxyInWorld')) {
			$g = $this->planet->galaxy;
		}
		if ($s < 1 || $s > config('settings.maxSystemInGalaxy')) {
			$s = $this->planet->system;
		}
		if ($i < 1 || $i > config('settings.maxPlanetInSystem')) {
			$i = $this->planet->planet;
		}

		$phalanx = $this->planet->getLevel('phalanx');

		$systemdol 	= $this->planet->system - pow($phalanx, 2);
		$systemgora = $this->planet->system + pow($phalanx, 2);

		if ($this->planet->planet_type != 3) {
			throw new PageException('Вы можете использовать фалангу только на луне!');
		} elseif ($phalanx == 0) {
			throw new PageException('Постройте сначало сенсорную фалангу');
		} elseif ($this->planet->deuterium < $consomation) {
			throw new PageException('Недостаточно дейтерия для использования. Необходимо: ' . $consomation . '.');
		} elseif (($s <= $systemdol or $s >= $systemgora) or $g != $this->planet->galaxy) {
			throw new PageException('Вы не можете сканировать данную планету. Недостаточный уровень сенсорной фаланги.');
		}

		$this->planet->deuterium -= $consomation;
		$this->planet->update();

		$planet = Models\Planet::query()
			->where('galaxy', $g)
			->where('system', $s)
			->where('planet', $i)
			->count();

		if (!$planet) {
			throw new RedirectException('Чит детектед! Режим бога активирован! Приятной игры!', '');
		}

		$fleets = Models\Fleet::query()
			->where(function (Builder $query) use ($g, $s, $i) {
				$query->where('start_galaxy', $g)
					->where('start_system', $s)
					->where('start_planet', $i)
					->where('start_type', '!=', 3);
			})
			->orWhere(function (Builder $query) use ($g, $s, $i) {
				$query->where('end_galaxy', $g)
					->where('end_system', $s)
					->where('end_planet', $i);
			})
			->orderBy('start_time', 'asc')
			->get();

		$list = [];

		foreach ($fleets as $ii => $row) {
			$end = !($row->start_galaxy == $g && $row->start_system == $s && $row->start_planet == $i);

			$color = ($row->mission != 6) ? 'lime' : 'orange';

			if ($row->start_type == 3) {
				$type = "лун";
			} else {
				$type = "планет";
			}

			if ($row->end_type == 3) {
				$type2 = "лун";
			} else {
				$type2 = "планет";
			}

			if ($row->start_time->isFuture() && $end && !($row->start_type == 3 && ($row->end_type == 2 || $row->end_type == 3))) {
				$list[] = [
					'time' => $row->start_time->getTimestamp(),
					'fleet' => Fleet::CreateFleetPopupedFleetLink($row, 'флот', '', $this->user),
					'type_1' => $type . 'ы',
					'type_2' => $type2 . 'у',
					'planet_name' => $row->user_name,
					'planet_position' => $row->splitStartPosition(),
					'target_name' => $row->target_user_name,
					'target_position' => $row->splitTargetPosition(),
					'mission' => __('main.type_mission.' . $row->mission),
					'color' => $color,
					'direction' => 1
				];
			}

			if ($row->mission <> 4 && !$end && $row->start_type != 3) {
				$list[] = [
					'time' => $row->end_time->getTimestamp(),
					'fleet' => Fleet::CreateFleetPopupedFleetLink($row, 'флот', '', $this->user),
					'type_1' => $type2 . 'ы',
					'type_2' => $type . 'у',
					'planet_name' => $row->target_user_name,
					'planet_position' => $row->splitTargetPosition(),
					'target_name' => $row->user_name,
					'target_position' => $row->splitStartPosition(),
					'mission' => __('main.type_mission.' . $row->mission),
					'color' => $color,
					'direction' => 2
				];
			}
		}

		return [
			'items' => $list
		];
	}
}
