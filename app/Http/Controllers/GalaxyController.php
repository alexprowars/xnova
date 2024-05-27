<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Files;
use App\Fleet;
use App\Controller;
use App\Models;

class GalaxyController extends Controller
{
	public function index(Request $request)
	{
		$fleetmax = $this->user->getTechLevel('computer') + 1;

		if ($this->user->rpg_admiral?->isFuture()) {
			$fleetmax += 2;
		}

		$maxfleet_count = Models\Fleet::query()
			->where('user_id', $this->user->id)
			->count();

		$records = Cache::remember('app::records_' . $this->user->id, 1800, function () {
			$records = Models\Statistic::query()
				->select(['build_points', 'tech_points', 'fleet_points', 'defs_points', 'total_points', 'total_old_rank', 'total_rank'])
				->where('stat_type', 1)
				->where('stat_code', 1)
				->where('user_id', $this->user->id)
				->first();

			return $records?->toArray();
		});

		$galaxy = $this->planet->galaxy;
		$system = $this->planet->system;

		if ($request->post('direction')) {
			$direction = trim($request->post('direction', ''));

			if ($direction == 'galaxyLeft') {
				$galaxy = (int) $request->post('galaxy') - 1;
			} elseif ($direction == 'galaxyRight') {
				$galaxy = (int) $request->post('galaxy') + 1;
			} elseif ($request->post('galaxy')) {
				$galaxy = (int) $request->post('galaxy');
			}

			if ($direction == 'systemLeft') {
				$system = (int) $request->post('system') - 1;
			} elseif ($direction == 'systemRight') {
				$system = (int) $request->post('system') + 1;
			} elseif ($request->post('system')) {
				$system = (int) $request->post('system');
			}
		} else {
			if ($request->post('galaxy')) {
				$galaxy = (int) $request->query('galaxy', 1);
			}

			if ($request->post('system')) {
				$system = (int) $request->query('system', 1);
			}
		}

		$galaxy = min(max($galaxy, 1), config('settings.maxGalaxyInWorld'));
		$system = min(max($system, 1), config('settings.maxSystemInGalaxy'));

		$Phalanx = 0;

		if ($this->planet->getLevel('phalanx') > 0) {
			$Range = Fleet::getPhalanxRange($this->planet->getLevel('phalanx'));

			$SystemLimitMin = max(1, $this->planet->system - $Range);
			$SystemLimitMax = $this->planet->system + $Range;

			if ($system <= $SystemLimitMax && $system >= $SystemLimitMin) {
				$Phalanx = 1;
			}
		}

		if ($this->planet->getLevel('interplanetary_misil') > 0) {
			if ($galaxy == $this->planet->galaxy) {
				$Range = Fleet::getMissileRange($this->user);

				$SystemLimitMin = max(1, $this->planet->system - $Range);
				$SystemLimitMax = $this->planet->system + $Range;

				if ($system <= $SystemLimitMax) {
					$MissileBtn = ($system >= $SystemLimitMin) ? 1 : 0;
				} else {
					$MissileBtn = 0;
				}
			} else {
				$MissileBtn = 0;
			}
		} else {
			$MissileBtn = 0;
		}

		$Destroy = 0;

		if ($this->planet->getLevel('dearth_star') > 0) {
			$Destroy = 1;
		}

		$jsUser = [
			'phalanx' => $Phalanx,
			'destroy' => $Destroy,
			'missile' => $MissileBtn,
			'stat_points' => $records ? $records['total_points'] : 0,
			'colonizer' => $this->planet->getLevel('colonizer'),
			'spy_sonde' => $this->planet->getLevel('spy_sonde'),
			'spy' => (int) $this->user->getOption('spy'),
			'recycler' => $this->planet->getLevel('recycler'),
			'interplanetary_misil' => $this->planet->getLevel('interplanetary_misil'),
			'allowExpedition' => $this->user->getTechLevel('expedition') > 0,
			'fleets' => $maxfleet_count,
			'max_fleets' => $fleetmax
		];

		$parse = [];
		$parse['galaxy'] = (int) $galaxy;
		$parse['system'] = (int) $system;
		$parse['user'] = $jsUser;
		$parse['items'] = [];
		$parse['shortcuts'] = [];

		$planets = $this->user->getPlanets(false);

		foreach ($planets as $planet) {
			$parse['shortcuts'][] = [
				'name' => $planet->name,
				'galaxy' => $planet->galaxy,
				'system' => $planet->system,
				'planet' => $planet->planet,
				'planet_type' => $planet->planet_type,
			];
		}

		foreach ($this->user->shortcuts as $shortcut) {
			$parse['shortcuts'][] = [
				'name' => $shortcut->name,
				'galaxy' => $shortcut->galaxy,
				'system' => $shortcut->system,
				'planet' => $shortcut->planet,
				'planet_type' => $shortcut->planet_type,
			];
		}

		$GalaxyRow = DB::select("SELECT
								p.galaxy, p.system, p.planet, p.id AS p_id, p.debris_metal AS p_metal, p.debris_crystal AS p_crystal, p.name as p_name, p.planet_type as p_type, p.destruyed as p_delete, p.image as p_image, p.last_active as p_active, p.parent_planet as p_parent,
								p2.id AS l_id, p2.name AS l_name, p2.destruyed AS l_delete, p2.last_active AS l_update, p2.diameter AS l_diameter, p2.temp_min AS l_temp,
								u.id AS u_id, u.username as u_name, u.race as u_race, u.alliance_id as a_id, u.authlevel as u_admin, u.onlinetime as u_online, u.vacation as u_vacation, u.banned_time as u_ban, u.sex as u_sex, u.avatar as u_avatar, u.image AS u_image,
								a.name AS a_name, a.members AS a_members, a.web AS a_web, a.tag AS a_tag,
								ad.type as d_type,
								s.total_rank as s_rank, s.total_points as s_points
				FROM planets p
				LEFT JOIN planets p2 ON (p.parent_planet = p2.id AND p.parent_planet != 0)
				LEFT JOIN users u ON (u.id = p.user_id AND p.user_id != 0)
				LEFT JOIN alliances a ON (a.id = u.alliance_id AND u.alliance_id != 0)
				LEFT JOIN alliances_diplomacies ad ON ((ad.alliance_id = u.alliance_id AND ad.diplomacy_id = " . ($this->user->alliance_id ?? 0) . ") AND ad.status = 1 AND u.alliance_id != 0)
				LEFT JOIN statistics s ON (s.user_id = u.id AND s.stat_type = 1 AND s.stat_code = 1)
				WHERE p.planet_type <> 3 AND p.`galaxy` = '" . $galaxy . "' AND p.`system` = '" . $system . "'");

		foreach ($GalaxyRow as $row) {
			if (!empty($row->l_update) && strtotime($row->l_update) > strtotime($row->p_active)) {
				$row->p_active = $row->l_update;
			}

			if (!empty($row->p_delete) && strtotime($row->p_delete) <= time()) {
				Models\Planet::find($row->p_id)->delete();

				if ($row->p_parent) {
					Models\Planet::find($row->p_parent)->delete();
				}
			}

			if (!empty($row->l_id) && $row->l_delete && strtotime($row->l_delete) <= time()) {
				Models\Planet::find($row->l_id)->delete();
				Models\Planet::query()->where('parent_planet', $row->l_id)->update(['parent_planet' => null]);

				$row->l_id = null;
			}

			if (strtotime($row->u_online) < time() - 60 * 60 * 24 * 7 && strtotime($row->u_online) > time() - 60 * 60 * 24 * 28) {
				$row->u_online = 1;
			} elseif (strtotime($row->u_online) < time() - 60 * 60 * 24 * 28) {
				$row->u_online = 2;
			} else {
				$row->u_online = 0;
			}

			if ($row->u_vacation) {
				$row->u_vacation = 1;
			}

			if (strtotime($row->p_active) > time() - 59 * 60) {
				$row->p_active = floor((time() - strtotime($row->p_active)) / 60);
			} else {
				$row->p_active = 60;
			}

			if ($row->u_image > 0) {
				$file = Files::getById($row->u_image);

				if ($file) {
					$row->u_image = $file['src'];
				} else {
					$row->u_image = '';
				}
			}

			unset($row->p_parent, $row->l_update);

			$parse['items'][] = (array) $row;
		}

		return $parse;
	}
}
