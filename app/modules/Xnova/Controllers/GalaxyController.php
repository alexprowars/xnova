<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Fleet;
use Friday\Core\Lang;
use Xnova\Controller;

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
		$fleetmax = $this->user->computer_tech + 1;
		
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
			if ($this->request->hasPost('galaxyLeft'))
				$galaxy = $this->request->getPost('galaxy', 'int') - 1;
			elseif ($this->request->hasPost('galaxyRight'))
				$galaxy = $this->request->getPost('galaxy', 'int') + 1;
			elseif ($this->request->hasPost('galaxy'))
				$galaxy = $this->request->getPost('galaxy', 'int');
			else
				$galaxy = $this->planet->galaxy;
		
			if ($this->request->hasPost('systemLeft'))
				$system = $this->request->getPost('system', 'int') - 1;
			elseif ($this->request->hasPost('systemRight'))
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
			$array = $this->user->getUserPlanets($this->user->getId(), false, $this->user->ally_id);
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
		
		if ($this->planet->phalanx != 0)
		{
			$Range = Fleet::GetPhalanxRange($this->planet->phalanx);

			$SystemLimitMin = max(1, $this->planet->system - $Range);
			$SystemLimitMax = $this->planet->system + $Range;
		
			if ($system <= $SystemLimitMax && $system >= $SystemLimitMin)
				$Phalanx = 1;
		}
		
		if ($this->planet->interplanetary_misil <> 0)
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
		
		if ($this->planet->dearth_star > 0)
			$Destroy = 1;
		
		$html = '';
		
		if ($mode == 2)
			$html .= $this->ShowGalaxyMISelector($galaxy, $system, $planet, $this->planet->id, $this->planet->interplanetary_misil);

		$html .= "<div id='galaxy' class='container-fluid'></div>";
		$html .= "<script>var Deuterium = '0';var time = " . time() . "; var dpath = '".$this->url->getBaseUri()."assets/images/'; var user = {id:" . $this->user->id . ", phalanx:" . $Phalanx . ", destroy:" . $Destroy . ", missile:" . $MissileBtn . ", total_points:" . (isset($records['total_points']) ? $records['total_points'] : 0) . ", ally_id:" . $this->user->ally_id . ", planet_current:" . $this->user->planet_current . ", colonizer:" . $this->planet->colonizer . ", spy_sonde:" . $this->planet->spy_sonde . ", spy:".intval($this->user->spy).", recycler:" . $this->planet->recycler . ", interplanetary_misil:" . $this->planet->interplanetary_misil . ", fleets: " . $maxfleet_count . ", max_fleets: " . $fleetmax . "}; var galaxy = " . $galaxy . "; var system = " . $system . "; var row = []; ";
		
		$html .= " var fleet_shortcut = new Array(); ";

		if ($this->session->has('fleet_shortcut'))
		{
			$array = json_decode($_SESSION['fleet_shortcut'], true);

			if (!is_array($array))
				$array = [];

			foreach ($array AS $id => $a)
			{
				$html .= " fleet_shortcut[" . $id . "] = new Array('" . base64_decode($a[0]) . "', " . $a[1] . ", " . $a[2] . ", " . $a[3] . ", " . (($a[1] == $galaxy && $a[2] == $system) ? 1 : 0) . "); ";
			}
		}
		
		$html .= "$('#galaxy').append(PrintSelector(fleet_shortcut)); ";
		
		$galaxyRow = '';
		
		$GalaxyRow = $this->db->query("SELECT
								p.planet, p.id AS planet_id, p.id_ally AS ally_planet, p.debris_metal AS metal, p.debris_crystal AS crystal, p.name, p.planet_type, p.destruyed, p.image, p.last_active, p.parent_planet,
								p2.id AS luna_id, p2.name AS luna_name, p2.destruyed AS luna_destruyed, p2.last_active AS luna_update, p2.diameter AS luna_diameter, p2.temp_min AS luna_temp,
								u.id AS user_id, u.username, u.race, u.ally_id, u.authlevel, u.onlinetime, u.vacation, u.banned, u.sex, u.avatar,
								ui.image AS user_image,
								a.name AS ally_name, a.members AS ally_members, a.web AS ally_web, a.tag AS ally_tag,
								ad.type,
								s.total_rank, s.total_points
				FROM game_planets p 
				LEFT JOIN game_planets p2 ON (p.parent_planet = p2.id AND p.parent_planet != 0) 
				LEFT JOIN game_users u ON (u.id = p.id_owner AND p.id_owner != 0)
				LEFT JOIN game_users_info ui ON (ui.id = p.id_owner AND p.id_owner != 0)
				LEFT JOIN game_alliance a ON (a.id = u.ally_id AND u.ally_id != 0)
				LEFT JOIN game_alliance_diplomacy ad ON ((ad.a_id = u.ally_id AND ad.d_id = " . $this->user->ally_id . ") AND ad.status = 1 AND u.ally_id != 0)
				LEFT JOIN game_statpoints s ON (s.id_owner = u.id AND s.stat_type = '1' AND s.stat_code = '1') 
				WHERE p.planet_type <> 3 AND p.`galaxy` = '" . $galaxy . "' AND p.`system` = '" . $system . "';", '');
		
		$rows = [];
		
		while ($row = $GalaxyRow->fetch())
		{
			if ($row['luna_update'] != "" && $row['luna_update'] > $row['last_active'])
				$row['last_active'] = $row['luna_update'];
		
			unset($row['luna_update']);
		
			if ($row['destruyed'] != 0 && $row["planet_id"] != '')
				$this->checkAbandonPlanetState($row);
		
			if ($row["luna_id"] != "" && $row["luna_destruyed"] != 0)
				$this->checkAbandonMoonState($row);

			$online = $row['onlinetime'];
		
			if ($online < (time() - 60 * 60 * 24 * 7) && $online > (time() - 60 * 60 * 24 * 28))
				$row['onlinetime'] = 1;
			elseif ($online < (time() - 60 * 60 * 24 * 28))
				$row['onlinetime'] = 2;
			else
				$row['onlinetime'] = 0;
		
			if ($row['vacation'] > 0)
				$row['vacation'] = 1;
		
			if ($row['last_active'] > (time() - 59 * 60))
				$row['last_active'] = floor((time() - $row['last_active']) / 60);
			else
				$row['last_active'] = 60;
		
			foreach ($row AS &$v)
				if (is_numeric($v))
					$v = intval($v);

			unset($v);
		
			$rows[] = $row;
		}
		
		foreach ($rows AS $row)
			$galaxyRow .= 'row[' . $row['planet'] . '] = '.json_encode($row, true).';';
		
		$html .= $galaxyRow;
		
		$html .= "$('#galaxy').append(PrintRow());</script>";

		$this->view->setVar('html', $html);
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

	private function checkAbandonMoonState (&$lunarow)
	{
		if ($lunarow['luna_destruyed'] <= time())
		{
			$this->db->delete('game_planets', 'id = ?', [$lunarow['luna_id']]);
			$this->db->updateAsDict('game_planets', ['parent_planet' => 0], 'parent_planet = '.$lunarow['luna_id']);

			$lunarow['id_luna'] = 0;
		}
	}

	private function checkAbandonPlanetState (&$planet)
	{
		if ($planet['destruyed'] <= time())
		{
			$this->db->delete('game_planets', 'id = ?', [$planet['planet_id']]);

			if ($planet['parent_planet'] != 0)
				$this->db->delete('game_planets', 'id = ?', [$planet['parent_planet']]);
		}
	}
}