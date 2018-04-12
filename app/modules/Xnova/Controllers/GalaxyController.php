<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Files;
use Xnova\Fleet;
use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Request;
use Xnova\User;

/**
 * @RoutePrefix("/galaxy")
 * @Route("/")
 * @Private
 */
class GalaxyController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		$this->user->loadPlanet();
		
		Lang::includeLang('galaxy', 'xnova');
	}

	/**
	 * @Route("/{galaxy:[0-9]{1,2}}/{system:[0-9]{1,3}}{params:(/.*)*}", paths={r="-"})
	 * @Route("/{galaxy:[0-9]{1,2}}/{system:[0-9]{1,3}}/{r:[0-9]}{params:(/.*)*}")
	 * @Route("/r/{r:[0-9]}{params:(/.*)*}")
	 */
	public function indexAction ()
	{
		$parse = [];

		$fleetmax = $this->user->getTechLevel('computer') + 1;
		
		if ($this->user->rpg_admiral > time())
			$fleetmax += 2;
		
		$maxfleet_count = \Xnova\Models\Fleet::count(['owner = ?0', 'bind' => [$this->user->id]]);

		$records = $this->cache->get('app::records_'.$this->user->getId());

		if ($records === null)
		{
			$records = $this->db->query("SELECT `build_points`, `tech_points`, `fleet_points`, `defs_points`, `total_points`, `total_old_rank`, `total_rank` FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $this->user->getId() . "';")->fetch();

			if (!is_array($records))
				$records = [];

			$this->cache->save('app::records_'.$this->user->getId(), $records, 1800);
		}

		if ($this->request->get('r', 'string', '') == '-')
			$mode = 3;
		else
			$mode = (int) $this->request->get('r', 'int', 0);

		$galaxy = 1;
		$system = 1;
		$planet = 1;
		
		if ($mode == 0)
		{
			$galaxy = $this->planet->galaxy;
			$system = $this->planet->system;
			$planet = $this->planet->planet;
		}
		elseif ($mode == 1)
		{
			$direction = trim($this->request->getPost('direction', 'string', ''));

			if ($direction == 'galaxyLeft')
				$galaxy = $this->request->getPost('galaxy', 'int') - 1;
			elseif ($direction == 'galaxyRight')
				$galaxy = $this->request->getPost('galaxy', 'int') + 1;
			elseif ($this->request->hasPost('galaxy'))
				$galaxy = $this->request->getPost('galaxy', 'int');
			else
				$galaxy = $this->planet->galaxy;
		
			if ($direction == 'systemLeft')
				$system = $this->request->getPost('system', 'int') - 1;
			elseif ($direction == 'systemRight')
				$system = $this->request->getPost('system', 'int') + 1;
			elseif ($this->request->hasPost('system'))
				$system = $this->request->getPost('system', 'int');
			else
				$system = $this->planet->system;
		}
		elseif ($mode == 2)
		{
			$galaxy = $this->request->getQuery('galaxy', 'int', 1);
			$system = $this->request->getQuery('system', 'int', 1);
			$planet = $this->request->getQuery('planet', 'int', 1);
		}
		elseif ($mode == 3)
		{
			$galaxy = $this->request->getQuery('galaxy', 'int', 1);
			$system = $this->request->getQuery('system', 'int', 1);
		}

		$galaxy = min(max($galaxy, 1), $this->config->game->maxGalaxyInWorld);
		$system = min(max($system, 1), $this->config->game->maxSystemInGalaxy);
		$planet = min(max($planet, 1), $this->config->game->maxPlanetInSystem);
		
		if (!$this->session->has('fleet_shortcut'))
		{
			$array = User::getPlanets($this->user->getId(), false, $this->user->ally_id);
			$j = [];
		
			foreach ($array AS $a)
				$j[] = [base64_encode($a['name']), $a['galaxy'], $a['system'], $a['planet']];
		
			$shortcuts = $this->db->fetchColumn("SELECT fleet_shortcut FROM game_users_info WHERE id = " . $this->user->id . ";");
		
			if (isset($shortcuts))
			{
				$scarray = explode("\r\n", $shortcuts);
		
				foreach ($scarray as $b)
				{
					if ($b != "")
					{
						$c = explode(',', $b);
						$j[] = [base64_encode($c[0]), intval($c[1]), intval($c[2]), intval($c[3])];
					}
				}
			}

			$this->session->set('fleet_shortcut', json_encode($j));
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
		
		$html = '';
		
		if ($mode == 2)
			$html .= $this->ShowGalaxyMISelector($galaxy, $system, $planet, $this->planet->id, $this->planet->getUnitCount('interplanetary_misil'));

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

		if ($this->session->has('fleet_shortcut'))
		{
			$array = json_decode($_SESSION['fleet_shortcut'], true);

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

		$GalaxyRow = $this->db->query("SELECT
								p.planet, p.id AS p_id, p.debris_metal AS p_metal, p.debris_crystal AS p_crystal, p.name as p_name, p.planet_type as p_type, p.destruyed as p_delete, p.image as p_image, p.last_active as p_active, p.parent_planet as p_parent,
								p2.id AS l_id, p2.name AS l_name, p2.destruyed AS l_delete, p2.last_active AS l_update, p2.diameter AS l_diameter, p2.temp_min AS l_temp,
								u.id AS u_id, u.username as u_name, u.race as u_race, u.ally_id as a_id, u.authlevel as u_admin, u.onlinetime as u_online, u.vacation as u_vacation, u.banned as u_ban, u.sex as u_sex, u.avatar as u_avatar,
								ui.image AS u_image,
								a.name AS a_name, a.members AS a_members, a.web AS a_web, a.tag AS a_tag,
								ad.type as d_type,
								s.total_rank as s_rank, s.total_points as s_points
				FROM game_planets p 
				LEFT JOIN game_planets p2 ON (p.parent_planet = p2.id AND p.parent_planet != 0) 
				LEFT JOIN game_users u ON (u.id = p.id_owner AND p.id_owner != 0)
				LEFT JOIN game_users_info ui ON (ui.id = p.id_owner AND p.id_owner != 0)
				LEFT JOIN game_alliance a ON (a.id = u.ally_id AND u.ally_id != 0)
				LEFT JOIN game_alliance_diplomacy ad ON ((ad.a_id = u.ally_id AND ad.d_id = " . $this->user->ally_id . ") AND ad.status = 1 AND u.ally_id != 0)
				LEFT JOIN game_statpoints s ON (s.id_owner = u.id AND s.stat_type = '1' AND s.stat_code = '1') 
				WHERE p.planet_type <> 3 AND p.`galaxy` = '" . $galaxy . "' AND p.`system` = '" . $system . "';", '');
		
		while ($row = $GalaxyRow->fetch())
		{
			if ($row['l_update'] != "" && $row['l_update'] > $row['p_active'])
				$row['p_active'] = $row['l_update'];
		
			if ($row['p_delete'] > 0 && $row['p_delete'] <= time())
			{
				$this->db->delete('game_planets', 'id = ?', [$row['p_id']]);

				if ($row['p_parent'] != 0)
					$this->db->delete('game_planets', 'id = ?', [$row['p_parent']]);
			}

			if ($row["l_id"] != "" && $row["l_delete"] != 0 && $row['l_delete'] <= time())
			{
				$this->db->delete('game_planets', 'id = ?', [$row['l_id']]);
				$this->db->updateAsDict('game_planets', ['parent_planet' => 0], 'parent_planet = '.$row['l_id']);

				$row['l_id'] = 0;
			}

			if ($row['u_online'] < (time() - 60 * 60 * 24 * 7) && $row['u_online'] > (time() - 60 * 60 * 24 * 28))
				$row['u_online'] = 1;
			elseif ($row['u_online'] < (time() - 60 * 60 * 24 * 28))
				$row['u_online'] = 2;
			else
				$row['u_online'] = 0;
		
			if ($row['u_vacation'] > 0)
				$row['u_vacation'] = 1;
		
			if ($row['p_active'] > (time() - 59 * 60))
				$row['p_active'] = floor((time() - $row['p_active']) / 60);
			else
				$row['p_active'] = 60;

			if ($row['u_image'] > 0)
			{
				$file = Files::getById($row['u_image']);

				if ($file)
					$row['u_image'] = $file['src'];
				else
					$row['u_image'] = '';
			}

			unset($row['p_parent'], $row['l_update'], $row['p_id']);
		
			foreach ($row AS &$v)
			{
				if (is_numeric($v))
					$v = (int) $v;
			}

			unset($v);
		
			$parse['items'][$row['planet'] - 1] = $row;
		}
		
		Request::addData('page', $parse);

		$this->tag->setTitle('Галактика');
		$this->showTopPanel(false);
	}

	private function ShowGalaxyMISelector ($Galaxy, $System, $Planet, $Current, $MICount)
	{
		$Result = "<form action=\"/rocket/?c=" . $Current . "&mode=2&galaxy=" . $Galaxy . "&system=" . $System . "&planet=" . $Planet . "\" method=\"POST\">";
		$Result .= "<table border=\"0\" class=\"table\">";
		$Result .= "<tr>";
		$Result .= "<td class=\"c\" colspan=\"3\">";
		$Result .= _getText('gm_launch') . " [" . $Galaxy . ":" . $System . ":" . $Planet . "]";
		$Result .= "</td>";
		$Result .= "</tr>";
		$Result .= "<tr>";
		$String = sprintf(_getText('gm_restmi'), $MICount);
		$Result .= "<td class=\"c\">" . $String . " <input type=\"text\" name=\"SendMI\" size=\"2\" maxlength=\"7\" /></td>";
		$Result .= "<td class=\"c\">" . _getText('gm_target') . " <select name=\"Target\">";
		$Result .= "<option value=\"all\" selected>" . _getText('gm_all') . "</option>";
		$Result .= "<option value=\"0\">" . _getText('tech', 401) . "</option>";
		$Result .= "<option value=\"1\">" . _getText('tech', 402) . "</option>";
		$Result .= "<option value=\"2\">" . _getText('tech', 403) . "</option>";
		$Result .= "<option value=\"3\">" . _getText('tech', 404) . "</option>";
		$Result .= "<option value=\"4\">" . _getText('tech', 405) . "</option>";
		$Result .= "<option value=\"5\">" . _getText('tech', 406) . "</option>";
		$Result .= "<option value=\"6\">" . _getText('tech', 407) . "</option>";
		$Result .= "<option value=\"7\">" . _getText('tech', 408) . "</option>";
		$Result .= "</select>";
		$Result .= "</td>";
		$Result .= "</tr>";
		$Result .= "<tr>";
		$Result .= "<td class=\"c\" colspan=\"2\"><input type=\"submit\" name=\"aktion\" value=\"" . _getText('gm_send') . "\"></td>";
		$Result .= "</tr>";
		$Result .= "</table>";
		$Result .= "</form>";

		return $Result;
	}
}