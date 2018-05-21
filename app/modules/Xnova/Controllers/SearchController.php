<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Format;
use Xnova\Controller;

/**
 * @RoutePrefix("/search")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class SearchController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		$parse = [];

		$searchtext = $this->request->getPost('searchtext', 'string', '');
		$type = $this->request->getPost('type', 'string', '');

		if ($searchtext != '' && $type != '')
		{
			switch ($type)
			{
				case "playername":
					$search = $this->db->query("SELECT u.id, u.username, u.race, p.name AS planet_name, u.ally_name, u.galaxy AS g, u.system AS s, u.planet AS p, s.total_rank FROM game_users u LEFT JOIN game_planets p ON p.id = u.planet_id LEFT JOIN game_statpoints s ON s.id_owner = u.id AND s.stat_type = 1 WHERE u.username LIKE '%" . $searchtext . "%' LIMIT 30;");
					break;
				case "planetname":
					$search = $this->db->query("SELECT u.id, u.username, u.race, p.name AS planet_name, u.ally_name, p.galaxy AS g, p.system AS s, p.planet AS p, s.total_rank FROM game_planets p LEFT JOIN game_users u ON u.id = p.id_owner LEFT JOIN game_statpoints s ON s.id_owner = u.id AND s.stat_type = 1 WHERE p.name LIKE '%" . $searchtext . "%' LIMIT 30");
					break;
				case "allytag":
					$search = $this->db->query("SELECT a.id, a.name, a.tag, a.members, s.total_points FROM game_alliance a LEFT JOIN game_statpoints s ON s.id_owner = a.id AND s.stat_type = 2 WHERE a.tag LIKE '%" . $searchtext . "%' LIMIT 30");
					break;
				case "allyname":
					$search = $this->db->query("SELECT a.id, a.name, a.tag, a.members, s.total_points FROM game_alliance a LEFT JOIN game_statpoints s ON s.id_owner = a.id AND s.stat_type = 2 WHERE a.name LIKE '%" . $searchtext . "%' LIMIT 30");
			}

			$parse['result'] = [];

			if (isset($search))
			{
				while ($r = $search->fetch())
				{
					if ($type == 'playername' || $type == 'planetname')
					{
						if (!$r['total_rank'])
							$r['total_rank'] = 0;
						if (!$r['ally_name'])
							$r['ally_name'] = '-';

						$parse['result'][] = $r;
					}
					elseif ($type == 'allytag' || $type == 'allyname')
					{
						$r['total_points'] = Format::number($r['total_points']);

						$parse['result'][] = $r;
					}
				}
			}
		}

		$parse['searchtext'] = $searchtext;
		$parse['type'] = $type;

		$this->view->setVar('parse', $parse);
		$this->tag->setTitle('Поиск');
	}
}