<?php

namespace App\Http\Controllers;

use App\Engine\Fleet;
use App\Files;
use App\Models;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class GalaxyController extends Controller
{
	public function index(Request $request)
	{
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

		if ($request->input('galaxy')) {
			$galaxy = (int) $request->input('galaxy', 1);
		}

		if ($request->input('system')) {
			$system = (int) $request->input('system', 1);
		}

		$galaxy = min(max($galaxy, 1), config('game.maxGalaxyInWorld'));
		$system = min(max($system, 1), config('game.maxSystemInGalaxy'));

		$phalanx = false;

		if ($this->planet->getLevel('phalanx') > 0) {
			$range = Fleet::getPhalanxRange($this->planet->getLevel('phalanx'));

			$systemLimitMin = max(1, $this->planet->system - $range);
			$systemLimitMax = $this->planet->system + $range;

			if ($system <= $systemLimitMax && $system >= $systemLimitMin) {
				$phalanx = true;
			}
		}

		if ($this->planet->getLevel('interplanetary_misil') > 0 && $galaxy == $this->planet->galaxy) {
			$range = Fleet::getMissileRange($this->user);

			$systemLimitMin = max(1, $this->planet->system - $range);
			$systemLimitMax = $this->planet->system + $range;

			if ($system <= $systemLimitMax) {
				$missileBtn = $system >= $systemLimitMin;
			} else {
				$missileBtn = false;
			}
		} else {
			$missileBtn = false;
		}

		$jsUser = [
			'phalanx' => $phalanx,
			'missile' => $missileBtn,
			'stat_points' => $records ? $records['total_points'] : 0,
			'fleets' => $maxfleet_count,
		];

		$parse = [];
		$parse['galaxy'] = (int) $galaxy;
		$parse['galaxy_max'] = (int) config('game.maxGalaxyInWorld');
		$parse['system'] = (int) $system;
		$parse['system_max'] = (int) config('game.maxSystemInGalaxy');
		$parse['user'] = $jsUser;
		$parse['items'] = [];
		$parse['shortcuts'] = [];

		$planets = $this->user->getPlanets(false);

		foreach ($planets as $planet) {
			$parse['shortcuts'][] = $planet->only(['name', 'galaxy', 'system', 'planet']);
		}

		foreach ($this->user->shortcuts as $shortcut) {
			$parse['shortcuts'][] = $shortcut->only(['name', 'galaxy', 'system', 'planet']);
		}

		$rows = DB::select("SELECT
								p.galaxy, p.system, p.planet, p.id AS p_id, p.debris_metal AS p_metal, p.debris_crystal AS p_crystal, p.name as p_name, p.planet_type as p_type, p.destruyed as p_delete, p.image as p_image, p.last_active as p_active, p.parent_planet as p_parent,
								p2.id AS l_id, p2.name AS l_name, p2.destruyed AS l_delete, p2.last_active AS l_update, p2.diameter AS l_diameter, p2.temp_min AS l_temp,
								u.id AS u_id, u.username as u_name, u.race as u_race, u.alliance_id as a_id, u.authlevel as u_admin, u.onlinetime as u_online, u.vacation as u_vacation, u.banned_time as u_ban, u.sex as u_sex, u.avatar as u_avatar, u.image AS u_image,
								a.name AS a_name, a.members_count AS a_members, a.web AS a_web, a.tag AS a_tag,
								ad.type as d_type,
								s.total_rank as s_rank, s.total_points as s_points
				FROM planets p
				LEFT JOIN planets p2 ON (p.parent_planet = p2.id AND p.parent_planet != 0)
				LEFT JOIN users u ON (u.id = p.user_id AND p.user_id != 0)
				LEFT JOIN alliances a ON (a.id = u.alliance_id AND u.alliance_id != 0)
				LEFT JOIN alliances_diplomacies ad ON ((ad.alliance_id = u.alliance_id AND ad.diplomacy_id = " . ($this->user->alliance_id ?? 0) . ") AND ad.status = 1 AND u.alliance_id != 0)
				LEFT JOIN statistics s ON (s.user_id = u.id AND s.stat_type = 1 AND s.stat_code = 1)
				WHERE p.planet_type <> 3 AND p.`galaxy` = '" . $galaxy . "' AND p.`system` = '" . $system . "'");

		foreach ($rows as $row) {
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

			if ($row->u_ban) {
				$row->u_ban = Date::make($row->u_ban)->utc()->toAtomString();
			}

			unset($row->p_parent, $row->l_update);

			$parse['items'][] = (array) $row;
		}

		return response()->state($parse);
	}
}
