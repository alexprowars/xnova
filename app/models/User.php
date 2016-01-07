<?php
namespace App\Models;

use App\Sql;
use Phalcon\Mvc\Model;

/**
 * Class User
 * @package App\Models
 * @property \App\Database db
 */
class User extends Model
{
	private $db;
	private $optionsData =
	[
		'security' 			=> 0,
		'widescreen' 		=> 0,
		'bb_parser' 		=> 0,
		'ajax_navigation' 	=> 0,
		'planetlist' 		=> 0,
		'planetlistselect' 	=> 0,
		'gameactivity' 		=> 0,
		'records' 			=> 0,
		'only_available' 	=> 0
	];

	private $bonusData = [];

	public $id;
	public $username;
	public $authlevel;
	public $onlinetime;
	public $banned;
	public $options;
	public $planet_current;
	public $planet_id;
	public $race;
	public $sex;
	public $ally_id;
	public $vacation;
	public $b_tech_planet;
	public $tutorial;
	public $ip;

	public $ally = [];

	public $lvl_minier;
	public $lvl_raid;
	public $xpminier;
	public $xpraid;

	public $credits;
	public $messages;
	public $messages_ally;
	public $avatar;
	public $raids_win;
	public $raids_lose;
	public $raids;
	public $links;
	public $bonus;
	public $bonus_multi;
	public $refers;

	public $galaxy;
	public $system;
	public $planet;

	public $planet_sort;
	public $planet_sort_order;

	public $spy_tech;
	public $computer_tech;
	public $military_tech;
	public $shield_tech;
	public $defence_tech;
	public $energy_tech;
	public $hyperspace_tech;
	public $combustion_tech;
	public $impulse_motor_tech;
	public $hyperspace_motor_tech;
	public $laser_tech;
	public $ionic_tech;
	public $buster_tech;
	public $intergalactic_tech;
	public $expedition_tech;
	public $colonisation_tech;
	public $fleet_base_tech;
	public $graviton_tech;

	public $rpg_geologue;
	public $rpg_ingenieur;
	public $rpg_admiral;
	public $rpg_constructeur;
	public $rpg_technocrate;
	public $rpg_meta;
	public $rpg_komandir;

	public $fleet_202;
	public $fleet_203;
	public $fleet_204;
	public $fleet_205;
	public $fleet_206;
	public $fleet_207;
	public $fleet_209;
	public $fleet_211;
	public $fleet_213;
	public $fleet_214;
	public $fleet_215;
	public $fleet_220;
	public $fleet_221;
	public $fleet_222;
	public $fleet_223;
	public $fleet_401;
	public $fleet_402;
	public $fleet_403;
	public $fleet_404;
	public $fleet_405;
	public $fleet_406;

	public $tutorial_value;
	public $message_block;
	public $color;
	public $timezone;
	public $spy;
	public $deltime;
	public $ally_name;

	public function onConstruct()
	{
		$this->db = $this->getDi()->getShared('db');
	}

	public function isAdmin()
	{
		if ($this->id > 0)
			return ($this->authlevel == 3);
		else
			return false;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getSource()
	{
		return "game_users";
	}

	public function afterFetch()
	{
		$bonusArrays = array
		(
			'storage', 'metal', 'crystal', 'deuterium', 'energy', 'solar',
			'res_fleet', 'res_defence', 'res_research', 'res_building', 'res_levelup',
			'time_fleet', 'time_defence', 'time_research', 'time_building',
			'fleet_fuel', 'fleet_speed', 'queue'
		);

		$this->bonusData = [];

		// Значения по умолчанию
		foreach ($bonusArrays AS $name)
		{
			$this->bonusData[$name] = 1;
		}

		$this->bonusData['queue'] = 0;

		// Расчет бонусов от офицеров
		if ($this->rpg_geologue > time())
		{
			$this->bonusData['metal'] 			+= 0.25;
			$this->bonusData['crystal'] 		+= 0.25;
			$this->bonusData['deuterium'] 		+= 0.25;
			$this->bonusData['storage'] 		+= 0.25;
		}
		if ($this->rpg_ingenieur > time())
		{
			$this->bonusData['energy'] 			+= 0.15;
			$this->bonusData['solar'] 			+= 0.15;
			$this->bonusData['res_defence'] 	-= 0.1;
		}
		if ($this->rpg_admiral > time())
		{
			$this->bonusData['res_fleet'] 		-= 0.1;
			$this->bonusData['fleet_speed'] 	+= 0.25;
		}
		if ($this->rpg_constructeur > time())
		{
			$this->bonusData['time_fleet'] 		-= 0.25;
			$this->bonusData['time_defence'] 	-= 0.25;
			$this->bonusData['time_building'] 	-= 0.25;
			$this->bonusData['queue'] 			+= 2;
		}
		if ($this->rpg_technocrate > time())
		{
			$this->bonusData['time_research'] 	-= 0.25;
		}
		if ($this->rpg_meta > time())
		{
			$this->bonusData['fleet_fuel'] 		-= 0.1;
		}

		// Расчет бонусов от рас
		if ($this->race == 1)
		{
			$this->bonusData['metal'] 			+= 0.15;
			$this->bonusData['solar'] 			+= 0.15;
			$this->bonusData['res_levelup'] 	-= 0.1;
			$this->bonusData['time_fleet'] 		-= 0.1;
		}
		elseif ($this->race == 2)
		{
			$this->bonusData['deuterium'] 		+= 0.15;
			$this->bonusData['solar'] 			+= 0.05;
			$this->bonusData['storage'] 		+= 0.2;
			$this->bonusData['res_fleet'] 		-= 0.1;
		}
		elseif ($this->race == 3)
		{
			$this->bonusData['metal'] 			+= 0.05;
			$this->bonusData['crystal'] 		+= 0.05;
			$this->bonusData['deuterium'] 		+= 0.05;
			$this->bonusData['res_defence'] 	-= 0.05;
			$this->bonusData['res_building'] 	-= 0.05;
			$this->bonusData['time_building'] 	-= 0.1;
		}
		elseif ($this->race == 4)
		{
			$this->bonusData['crystal'] 		+= 0.15;
			$this->bonusData['energy'] 			+= 0.05;
			$this->bonusData['res_research'] 	-= 0.1;
			$this->bonusData['fleet_speed'] 	+= 0.1;
		}

		if (false)
		{
			include_once(APP_PATH . 'app/varsArtifacts.php');

			$artifacts = $this->db->query("SELECT * FROM game_artifacts WHERE user_id = " . $this->id . " AND expired > 0 AND expired < " . time() . "");

			while ($artifact = $artifacts->fetch())
			{
				/**
				 * @var $artifactsData array
				 */
				$data = $artifactsData[$artifact['element_id']];

				if (isset($data['resources']))
				{
					foreach ($data['resources'] AS $res => $lvl)
					{
						if (!isset($this->bonusData[$res]))
							continue;

						$factor = (($lvl[1] - $lvl[0]) / $data['level']) * $artifact['level'];

						$this->bonusData[$res] += round($factor / 100, 5);
					}
				}

				if (isset($data['build']))
				{
					foreach ($data['build'] AS $res => $lvl)
					{
						if (!isset($this->bonusData['time_' . $res]))
							continue;

						$factor = (($lvl[1] - $lvl[0]) / $data['level']) * $artifact['level'];

						$this->bonusData['time_' . $res] -= round($factor / 100, 5);
					}
				}

				if (isset($data['cost']))
				{
					foreach ($data['cost'] AS $res => $lvl)
					{
						if (!isset($this->bonusData['res_' . $res]))
							continue;

						$factor = (($lvl[1] - $lvl[0]) / $data['level']) * $artifact['level'];

						$this->bonusData['res_' . $res] -= round($factor / 100, 5);
					}
				}

				if (isset($data['queue']))
				{
					$factor = (($data['queue'][1] - $data['queue'][0]) / $data['level']) * $artifact['level'];

					$this->bonusData['queue'] += $factor;
				}

				if (isset($data['fleet']))
				{
					foreach ($data['fleet'] AS $res => $lvl)
					{
						if (!isset($this->bonusData['fleet_' . $res]))
							continue;

						$factor = (($lvl[1] - $lvl[0]) / $data['level']) * $artifact['level'];

						$this->bonusData['fleet_' . $res] -= round($factor / 100, 5);
					}
				}
			}
		}

		$this->optionsData = $this->unpackOptions($this->options);

		return true;
	}

	public function isOnline ()
	{
		return (time() - $this->onlinetime < 180);
	}

	public function unpackOptions ($opt, $isToggle = true)
	{
		$result = array();

		if ($isToggle)
		{
			$o = array_reverse(str_split(decbin($opt)));

			$i = 0;

			foreach ($this->optionsData as $k => $v)
			{
				$result[$k] = (isset($o[$i]) ? $o[$i] : 0);

				$i++;
			}
		}

		return $result;
	}

	public function packOptions ($opt, $isToggle = true)
	{
		if ($isToggle)
		{
			$r = array();

			foreach ($this->optionsData as $k => $v)
			{
				if (isset($opt[$k]))
					$v = $opt[$k];

				$r[] = $v;
			}

			return bindec(implode('', array_reverse($r)));
		}
		else
			return 0;
	}

	public function getUserOption ($key = false)
	{
		if ($key === false)
			return $this->optionsData;

		return (isset($this->optionsData[$key]) ? $this->optionsData[$key] : 0);
	}

	public function setUserOption ($key, $value)
	{
		$this->optionsData[$key] = $value;
	}

	public function loadPlanet ()
	{
		if ($this->getDi()->has('planet'))
			return;

		if ($this->planet_current == 0 && $this->planet_id == 0)
		{
			if ($this->race > 0)
			{
				$planet = new Planet;

				$this->planet_id = $planet->createByUserId($this->getId());
				$this->planet_current = $this->planet_id;
			}
		}

		if ($this->planet_current > 0 && $this->planet_id > 0)
		{
			/**
			 * Выбираем информацию о планете
			 * @var \App\Models\Planet $planet
			 */
			$planet = Planet::findFirst($this->planet_current);
			$planet->assignUser($this);
			$planet->checkOwnerPlanet();

			// Проверяем корректность заполненных полей
			$planet->CheckPlanetUsedFields();

			if (isset($planet->id))
			{
				$dispatcher = $this->getDi()->getShared('dispatcher');
				$controller = $dispatcher->getControllerName();
				$action = $dispatcher->getActionName();

				// Обновляем ресурсы на планете когда это необходимо
				if ((($controller == "overview" || ($controller == "fleet" && $action != 'fleet_3') || $controller == "galaxy" || $controller == "resources" || $controller == "imperium" || $controller == "credits" || $controller == "tutorial" || $controller == "tech" || $controller == "search" || $controller == "support" || $controller == "sim" || $controller == "tutorial") && $planet->last_update > (time() - 60)))
					$planet->PlanetResourceUpdate(time(), true);
				else
					$planet->PlanetResourceUpdate();
			}

			// Проверка наличия законченных построек и исследований
			if ($planet->UpdatePlanetBatimentQueueList())
				$planet->PlanetResourceUpdate(time(), true);

			$this->getDi()->setShared('planet', $planet);
		}
	}

	public function getAllyInfo ()
	{
		$this->ally = [];

		if ($this->ally_id > 0)
		{
			$cache = $this->getDi()->getShared('cache');

			$ally = $cache->get('user::ally_' . $this->id . '_' . $this->ally_id);

			if ($ally === NULL)
			{
				$ally = $this->db->query("SELECT a.id, a.ally_owner, a.ally_name, a.ally_ranks, m.rank FROM game_alliance a, game_alliance_members m WHERE m.a_id = a.id AND m.u_id = " . $this->id . " AND a.id = " . $this->ally_id)->fetch();

				$cache->save('user::ally_' . $this->id . '_' . $this->ally_id, $ally, 300);
			}

			if (isset($ally['id']))
			{
				if (!$ally['ally_ranks'])
					$ally['ally_ranks'] = 'a:0:{}';

				$ally_ranks = json_decode($ally['ally_ranks'], true);

				$this->ally = $ally;
				$this->ally['rights'] = isset($ally_ranks[$ally['rank'] - 1]) ? $ally_ranks[$ally['rank'] - 1] : array('name' => '', 'planet' => 0);
			}
		}
	}

	public function setSelectedPlanet ()
	{
		$request = $this->getDi()->getShared('request');

		if ($request->hasQuery('cp') && is_numeric($request->getQuery('cp')) && $request->hasQuery('re') && $request->getQuery('re', 'int') == 0)
		{
			$selectPlanet = $request->getQuery('cp', 'int');

			if ($this->planet_current == $selectPlanet)
				return true;

			$IsPlanetMine = $this->db->query("SELECT `id`, `id_owner`, `id_ally` FROM game_planets WHERE `id` = '" . $selectPlanet . "' AND (`id_owner` = '" . $this->getId() . "' OR (`id_ally` > 0 AND `id_ally` = '".$this->ally_id."'))")->fetch();

			if (isset($IsPlanetMine['id']))
			{
				if ($IsPlanetMine['id_ally'] > 0 && $IsPlanetMine['id_owner'] != $this->getId() && !$this->ally['rights']['planet'])
				{
					$this->getDi()->getShared('game')->message("Вы не можете переключится на эту планету. Недостаточно прав.", "Альянс", "/overview/", 2);
				}

				$this->planet_current = $selectPlanet;

				$this->saveData(array('planet_current' => $this->planet_current));
			}
			else
				return false;
		}

		return true;
	}

	public function bonusValue ($key, $default = false)
	{
		return (isset($this->bonusData[$key]) ? $this->bonusData[$key] : ($default !== false ? $default : 1));
	}

	public function getUserPlanets ($userId, $moons = true, $allyId = 0)
	{
		if (!$userId)
			return array();

		$qryPlanets = "SELECT `id`, `name`, `image`, `galaxy`, `system`, `planet`, `planet_type`, `destruyed` FROM game_planets WHERE `id_owner` = '" . $userId . "' ";

		$qryPlanets .= ($allyId > 0 ? " OR id_ally = '".$allyId."'" : "");

		if (!$moons)
			$qryPlanets .= " AND planet_type != 3 ";

		$qryPlanets .= $this->getPlanetListSortQuery();

		return $this->db->extractResult($this->db->query($qryPlanets));
	}

	public function getPlanetListSortQuery ($sort = false, $order = false)
	{
		if ($this->getId())
		{
			if (!$sort)
				$sort 	= $this->planet_sort;
			if (!$order)
				$order 	= $this->planet_sort_order;
		}

		$qryPlanets = ' ORDER BY ';

		switch ($sort)
		{
			case 1:
				$qryPlanets .= "`galaxy`, `system`, `planet`, `planet_type` ";
				break;
			case 2:
				$qryPlanets .= "`name` ";
				break;
			case 3:
				$qryPlanets .= "`planet_type` ";
				break;
			default:
				$qryPlanets .= "`id` ";
		}

		$qryPlanets .= ($order == 1) ? "DESC" : "ASC";

		return $qryPlanets;
	}

	public function getRankId ($lvl)
	{
		if ($lvl == 1)
			$lvl = 0;

		if ($lvl <= 80)
			return (ceil($lvl / 4) + 1);
		else
			return 22;
	}
	
	public function deleteById ($userId)
	{
		$userInfo = $this->db->query("SELECT id, ally_id FROM game_users WHERE id = ".intval($userId)."")->fetch();

		if (!isset($userInfo['id']))
			return false;

		if ($userInfo['ally_id'] != 0)
		{
			$ally = $this->db->query("SELECT * FROM game_alliance WHERE `id` = '" . $userInfo['ally_id'] . "';")->fetch();

			if ($ally['ally_owner'] != $userId)
			{
				$this->db->query("UPDATE game_alliance SET `ally_members` = '" . ($ally['ally_members'] - 1) . "' WHERE `id` = '" . $ally['id'] . "';");
				$this->db->query("DELETE FROM game_alliance_members WHERE `u_id` = '" . $userId . "';");
			}
			else
			{
				$this->db->query("UPDATE game_users SET `ally_id` = '0', `ally_name` = '' WHERE ally_id = '" . $ally['id'] . "' AND id != " . $userId . "");
				$this->db->query("DELETE FROM game_alliance WHERE `id` = '" . $ally['id'] . "';");
				$this->db->query("DELETE FROM game_alliance_chat WHERE ally_id = '" . $ally['id'] . "'");
				$this->db->query("DELETE FROM game_alliance_members WHERE a_id = '" . $ally['id'] . "'");
				$this->db->query("DELETE FROM game_alliance_requests WHERE a_id = '" . $ally['id'] . "'");
				$this->db->query("DELETE FROM game_alliance_diplomacy WHERE a_id = '" . $ally['id'] . "' OR d_id = '" . $ally['id'] . "';");
				$this->db->query("DELETE FROM game_statpoints WHERE `stat_type` = '2' AND `id_owner` = '" . $ally['id'] . "';");
			}
		}

		$this->db->query("DELETE FROM game_alliance_requests WHERE `u_id` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_statpoints WHERE `stat_type` = '1' AND `id_owner` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_planets WHERE `id_owner` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_notes WHERE `owner` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_fleets WHERE `fleet_owner` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_buddy WHERE `sender` = '" . $userId . "' OR `owner` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_refs WHERE `r_id` = '" . $userId . "' OR `u_id` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_log_attack WHERE `uid` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_log_credits WHERE `uid` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_log_email WHERE `user_id` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_log_history WHERE `user_id` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_log_transfers WHERE `user_id` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_log_username WHERE `user_id` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_log_stats WHERE `id` = '" . $userId . "' AND type = 1;");
		$this->db->query("DELETE FROM game_logs WHERE `s_id` = '" . $userId . "' OR `e_id` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_users_auth WHERE `user_id` = '" . $userId . "';");
		
		$this->db->query("DELETE FROM game_messages WHERE `message_sender` = '" . $userId . "' OR `message_owner` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_users WHERE `id` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_users_info WHERE `id` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_banned WHERE `who` = '" . $userId . "';");
		$this->db->query("DELETE FROM game_log_ip WHERE `id` = '" . $userId . "';");

		return true;
	}

	public function saveData ($fields, $userId = 0)
	{
		Sql::build()->update('game_users')->set($fields)->where('id', '=', ($userId > 0 ? $userId : $this->id))->execute();
	}
}

?>