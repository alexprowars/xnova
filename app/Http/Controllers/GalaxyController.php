<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Xnova\Files;
use Xnova\Fleet;
use Xnova\Controller;
use Xnova\Models;

class GalaxyController extends Controller
{
	private $loadPlanet = true;

	public function index ()
	{
		$parse = [];

		$fleetmax = $this->user->getTechLevel('computer') + 1;

		if ($this->user->rpg_admiral > time())
			$fleetmax += 2;

		$maxfleet_count = Models\Fleet::query()
			->where('owner', $this->user->id)
			->count();

		$records = Cache::get('app::records_'.$this->user->getId());

		if ($records === null)
		{
			$records = DB::table('statpoints')
				->select(['build_points', 'tech_points', 'fleet_points', 'defs_points', 'total_points', 'total_old_rank', 'total_rank'])
				->where('stat_type', 1)
				->where('stat_code', 1)
				->where('id_owner', $this->user->getId())
				->first();

			if (!$records)
				$records = [];
			else
				$records = $records->toArray();

			Cache::put('app::records_'.$this->user->getId(), $records, 1800);
		}

		$galaxy = $this->planet->galaxy;
		$system = $this->planet->system;

		if (Request::post('direction'))
		{
			$direction = trim(Request::post('direction', ''));

			if ($direction == 'galaxyLeft')
				$galaxy = (int) Request::post('galaxy') - 1;
			elseif ($direction == 'galaxyRight')
				$galaxy = (int) Request::post('galaxy') + 1;
			elseif (Request::post('galaxy'))
				$galaxy = (int) Request::post('galaxy');

			if ($direction == 'systemLeft')
				$system = (int) Request::post('system') - 1;
			elseif ($direction == 'systemRight')
				$system = (int) Request::post('system') + 1;
			elseif (Request::post('system'))
				$system = (int) Request::post('system');
		}
		else
		{
			if (Request::post('galaxy'))
				$galaxy = (int) Request::query('galaxy', 1);

			if (Request::post('system'))
				$system = (int) Request::query('system', 1);
		}

		$galaxy = min(max($galaxy, 1), Config::get('game.maxGalaxyInWorld'));
		$system = min(max($system, 1), Config::get('game.maxSystemInGalaxy'));

		if (!Session::has('fleet_shortcut'))
		{
			$array = $this->user->getPlanets(false);
			$j = [];

			foreach ($array AS $a)
				$j[] = [base64_encode($a->name), $a->galaxy, $a->system, $a->planet];

			$shortcuts = DB::table('users_info')
				->select(['fleet_shortcut'])
				->where('id', $this->user->id)
				->first();

			if ($shortcuts)
			{
				$scarray = explode("\r\n", $shortcuts->fleet_shortcut);

				foreach ($scarray as $b)
				{
					if ($b != "")
					{
						$c = explode(',', $b);
						$j[] = [base64_encode($c[0]), intval($c[1]), intval($c[2]), intval($c[3])];
					}
				}
			}

			Session::put('fleet_shortcut', json_encode($j));
		}

		$Phalanx = 0;

		if ($this->planet->getBuildLevel('phalanx') > 0)
		{
			$Range = Fleet::GetPhalanxRange($this->planet->getBuildLevel('phalanx'));

			$SystemLimitMin = max(1, $this->planet->system - $Range);
			$SystemLimitMax = $this->planet->system + $Range;

			if ($system <= $SystemLimitMax && $system >= $SystemLimitMin)
				$Phalanx = 1;
		}

		if ($this->planet->getUnitCount('interplanetary_misil') > 0)
		{
			if ($galaxy == $this->planet->galaxy)
			{
				$Range = Fleet::GetMissileRange($this->user);

				$SystemLimitMin = max(1, $this->planet->system - $Range);
				$SystemLimitMax = $this->planet->system + $Range;

				if ($system <= $SystemLimitMax)
					$MissileBtn = ($system >= $SystemLimitMin) ? 1 : 0;
				else
					$MissileBtn = 0;
			}
			else
				$MissileBtn = 0;
		}
		else
			$MissileBtn = 0;

		$Destroy = 0;

		if ($this->planet->getUnitCount('dearth_star') > 0)
			$Destroy = 1;

		$jsUser = [
			'phalanx' => $Phalanx,
			'destroy' => $Destroy,
			'missile' => $MissileBtn,
			'stat_points' => isset($records['total_points']) ? $records['total_points'] : 0,
			'colonizer' => $this->planet->getUnitCount('colonizer'),
			'spy_sonde' => $this->planet->getUnitCount('spy_sonde'),
			'spy' => (int) $this->user->getUserOption('spy'),
			'recycler' => $this->planet->getUnitCount('recycler'),
			'interplanetary_misil' => $this->planet->getUnitCount('interplanetary_misil'),
			'allowExpedition' => $this->user->getTechLevel('expedition') > 0,
			'fleets' => $maxfleet_count,
			'max_fleets' => $fleetmax
		];

		$parse['galaxy'] = (int) $galaxy;
		$parse['system'] = (int) $system;
		$parse['user'] = $jsUser;
		$parse['items'] = [];
		$parse['shortcuts'] = [];

		for ($i = 1; $i <= 15; $i++)
			$parse['items'][$i - 1] = false;

		if (Session::get('fleet_shortcut'))
		{
			$array = json_decode(Session::get('fleet_shortcut'), true);

			if (!is_array($array))
				$array = [];

			foreach ($array AS $id => $a)
			{
				$parse['shortcuts'][] = [
					'n' => base64_decode($a[0]),
					'g' => (int) $a[1],
					's' => (int) $a[2],
					'p' => (int) $a[3],
					'c'	=> $a[1] == $galaxy && $a[2] == $system
				];
			}
		}

		$GalaxyRow = DB::select("SELECT
								p.planet, p.id AS p_id, p.debris_metal AS p_metal, p.debris_crystal AS p_crystal, p.name as p_name, p.planet_type as p_type, p.destruyed as p_delete, p.image as p_image, p.last_active as p_active, p.parent_planet as p_parent,
								p2.id AS l_id, p2.name AS l_name, p2.destruyed AS l_delete, p2.last_active AS l_update, p2.diameter AS l_diameter, p2.temp_min AS l_temp,
								u.id AS u_id, u.username as u_name, u.race as u_race, u.ally_id as a_id, u.authlevel as u_admin, u.onlinetime as u_online, u.vacation as u_vacation, u.banned as u_ban, u.sex as u_sex, u.avatar as u_avatar,
								ui.image AS u_image,
								a.name AS a_name, a.members AS a_members, a.web AS a_web, a.tag AS a_tag,
								ad.type as d_type,
								s.total_rank as s_rank, s.total_points as s_points
				FROM planets p 
				LEFT JOIN planets p2 ON (p.parent_planet = p2.id AND p.parent_planet != 0) 
				LEFT JOIN users u ON (u.id = p.id_owner AND p.id_owner != 0)
				LEFT JOIN users_info ui ON (ui.id = p.id_owner AND p.id_owner != 0)
				LEFT JOIN alliance a ON (a.id = u.ally_id AND u.ally_id != 0)
				LEFT JOIN alliance_diplomacy ad ON ((ad.a_id = u.ally_id AND ad.d_id = " . $this->user->ally_id . ") AND ad.status = 1 AND u.ally_id != 0)
				LEFT JOIN statpoints s ON (s.id_owner = u.id AND s.stat_type = '1' AND s.stat_code = '1') 
				WHERE p.planet_type <> 3 AND p.`galaxy` = '" . $galaxy . "' AND p.`system` = '" . $system . "'");

		foreach ($GalaxyRow as $row)
		{
			if ($row->l_update != "" && $row->l_update > $row->p_active)
				$row->p_active = $row->l_update;

			if ($row->p_delete > 0 && $row->p_delete <= time())
			{
				DB::table('planets')->delete($row->p_id);

				if ($row->p_parent != 0)
					DB::table('planets')->delete($row->p_parent);
			}

			if ($row->l_id != '' && $row->l_delete != 0 && $row->l_delete <= time())
			{
				DB::table('planets')->delete($row->l_id);
				DB::table('planets')->where('parent_planet', $row->l_id)->update(['parent_planet' => 0]);

				$row->l_id = 0;
			}

			if ($row->u_online < (time() - 60 * 60 * 24 * 7) && $row->u_online > (time() - 60 * 60 * 24 * 28))
				$row->u_online = 1;
			elseif ($row->u_online < (time() - 60 * 60 * 24 * 28))
				$row->u_online = 2;
			else
				$row->u_online = 0;

			if ($row->u_vacation > 0)
				$row->u_vacation = 1;

			if ($row->p_active > (time() - 59 * 60))
				$row->p_active = floor((time() - $row->p_active) / 60);
			else
				$row->p_active = 60;

			if ($row->u_image > 0)
			{
				$file = Files::getById($row['u_image']);

				if ($file)
					$row->u_image = $file['src'];
				else
					$row->u_image = '';
			}

			unset($row->p_parent, $row->l_update, $row->p_id);

			foreach ($row AS &$v)
			{
				if (is_numeric($v))
					$v = (int) $v;
			}

			unset($v);

			$parse['items'][$row->planet - 1] = (array) $row;
		}

		$this->setTitle('Галактика');
		$this->showTopPanel(false);

		return $parse;
	}
}