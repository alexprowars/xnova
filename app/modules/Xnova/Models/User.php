<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Galaxy;
use Phalcon\Di;
use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static User[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static User findFirst(mixed $parameters = null)
 * @property \Xnova\Database db
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
	public $group_id;
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
		$this->db = $this->getDI()->getShared('db');

		$this->useDynamicUpdate(true);
	}

	public function isAdmin()
	{
		if ($this->id > 0)
			return ($this->authlevel == 3);
		else
			return false;
	}
	
	public function isVacation()
	{
		return $this->vacation > 0;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getSource()
	{
		return DB_PREFIX."users";
	}

	public function afterUpdate ()
	{
		$this->setSnapshotData($this->toArray());
	}

	public function afterFetch()
	{
		$bonusArrays = [
			'storage', 'metal', 'crystal', 'deuterium', 'energy', 'solar',
			'res_fleet', 'res_defence', 'res_research', 'res_building', 'res_levelup',
			'time_fleet', 'time_defence', 'time_research', 'time_building',
			'fleet_fuel', 'fleet_speed', 'queue'
		];

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

		$this->optionsData = $this->unpackOptions($this->options);

		return true;
	}

	public function isOnline ()
	{
		return (time() - $this->onlinetime < 180);
	}

	public function unpackOptions ($opt, $isToggle = true)
	{
		$result = [];

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
			$r = [];

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
		if ($this->getDI()->has('planet'))
			return;

		if ($this->planet_current == 0 && $this->planet_id == 0)
		{
			if ($this->race > 0)
			{
				$galaxy = new Galaxy();

				$this->planet_id = $galaxy->createPlanetByUserId($this->getId());
				$this->planet_current = $this->planet_id;
			}
		}

		if ($this->planet_current > 0 && $this->planet_id > 0)
		{
			/**
			 * Выбираем информацию о планете
			 */
			$planet = Planet::findFirst($this->planet_current);

			if (!$planet && $this->planet_id > 0)
			{
				$this->planet_current = $this->planet_id;
				$this->update();

				$planet = Planet::findFirst($this->planet_current);
			}

			if ($planet)
			{
				$planet->assignUser($this);
				$planet->checkOwnerPlanet();

				// Проверяем корректность заполненных полей
				$planet->checkUsedFields();

				$dispatcher = $this->getDI()->getShared('dispatcher');
				$controller = $dispatcher->getControllerName();
				$action = $dispatcher->getActionName();

				// Обновляем ресурсы на планете когда это необходимо
				if (((($controller == "fleet" && $action != 'fleet_3') || in_array($controller, ['overview', 'galaxy', 'resources', 'imperium', 'credits', 'tutorial', 'tech', 'search', 'support', 'sim', 'tutorial'])) && $planet->last_update > (time() - 60)))
					$planet->resourceUpdate(time(), true);
				else
					$planet->resourceUpdate();

				// Проверка наличия законченных построек и исследований
				if ($planet->updateQueueList())
					$planet->resourceUpdate(time(), true);

				$this->getDI()->setShared('planet', $planet);
			}
		}

		if (!$this->getDI()->has('planet'))
			throw new \Exception('planet not found');
	}

	public function getAllyInfo ()
	{
		$this->ally = [];

		if ($this->ally_id > 0)
		{
			$cache = $this->getDI()->getShared('cache');

			$ally = $cache->get('user::ally_' . $this->id . '_' . $this->ally_id);

			if ($ally === NULL)
			{
				$ally = $this->db->query("SELECT a.id, a.owner, a.name, a.ranks, m.rank FROM game_alliance a, game_alliance_members m WHERE m.a_id = a.id AND m.u_id = " . $this->id . " AND a.id = " . $this->ally_id)->fetch();

				$cache->save('user::ally_' . $this->id . '_' . $this->ally_id, $ally, 300);
			}

			if (isset($ally['id']))
			{
				if (!$ally['ranks'])
					$ally['ranks'] = 'a:0:{}';

				$ranks = json_decode($ally['ranks'], true);

				$this->ally = $ally;
				$this->ally['rights'] = isset($ranks[$ally['rank'] - 1]) ? $ranks[$ally['rank'] - 1] : ['name' => '', 'planet' => 0];
			}
		}
	}

	public function setSelectedPlanet ()
	{
		$request = $this->getDI()->getShared('request');

		if ($request->hasQuery('chpl') && is_numeric($request->getQuery('chpl')))
		{
			$selectPlanet = $request->getQuery('chpl', 'int');

			if ($this->planet_current == $selectPlanet || $selectPlanet <= 0)
				return true;

			$IsPlanetMine = $this->db->query("SELECT id, id_owner, id_ally FROM game_planets WHERE id = '" . $selectPlanet . "' AND (id_owner = '" . $this->getId() . "' OR (id_ally > 0 AND id_ally = '".$this->ally_id."'))")->fetch();

			if (isset($IsPlanetMine['id']))
			{
				if ($IsPlanetMine['id_ally'] > 0 && $IsPlanetMine['id_owner'] != $this->getId() && !$this->ally['rights']['planet'])
				{
					$this->getDI()->getShared('game')->message("Вы не можете переключится на эту планету. Недостаточно прав.", "Альянс", "/overview/", 2);
				}

				$this->planet_current = $selectPlanet;
				$this->update();
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
			return [];

		$qryPlanets = "SELECT id, name, image, galaxy, system, planet, planet_type, destruyed FROM game_planets WHERE id_owner = '" . $userId . "' ";

		$qryPlanets .= ($allyId > 0 ? " OR id_ally = '".$allyId."'" : "");

		if (!$moons)
			$qryPlanets .= " AND planet_type != 3 ";

		$qryPlanets .= ' ORDER BY '.$this->getPlanetListSortQuery();

		return $this->db->query($qryPlanets)->fetchAll();
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

		$qryPlanets = ' ';

		switch ($sort)
		{
			case 1:
				$qryPlanets .= "galaxy, system, planet, planet_type";
				break;
			case 2:
				$qryPlanets .= "name";
				break;
			case 3:
				$qryPlanets .= "planet_type";
				break;
			default:
				$qryPlanets .= "id";
		}

		$qryPlanets .= ($order == 1) ? " DESC" : " ASC";

		return $qryPlanets;
	}

	public function getRankId ($lvl)
	{
		if ($lvl <= 1)
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
			$ally = Alliance::findFirst($userInfo['ally_id']);

			if ($ally)
			{
				if ($ally->owner != $userId)
					$ally->deleteMember($userId);
				else
					$ally->deleteAlly();
			}
		}

		$this->db->delete('game_alliance_requests', 'u_id = ?', [$userId]);
		$this->db->delete('game_statpoints', 'stat_type = 1 AND id_owner = ?', [$userId]);
		$this->db->delete('game_planets', 'id_owner = ?', [$userId]);
		$this->db->delete('game_notes', 'owner = ?', [$userId]);
		$this->db->delete('game_fleets', 'owner = ?', [$userId]);
		$this->db->delete('game_buddy', 'sender = ? OR owner = ?', [$userId, $userId]);
		$this->db->delete('game_refs', 'r_id = ? OR u_id = ?', [$userId, $userId]);
		$this->db->delete('game_log_attack', 'uid = ?', [$userId]);
		$this->db->delete('game_log_credits', 'uid = ?', [$userId]);
		$this->db->delete('game_log_email', 'user_id = ?', [$userId]);
		$this->db->delete('game_log_history', 'user_id = ?', [$userId]);
		$this->db->delete('game_log_transfers', 'user_id = ?', [$userId]);
		$this->db->delete('game_log_username', 'user_id = ?', [$userId]);
		$this->db->delete('game_log_stats', 'id = ?  AND type = 1', [$userId]);
		$this->db->delete('game_logs', 's_id = ? OR e_id = ?', [$userId, $userId]);
		$this->db->delete('game_messages', 'sender = ? OR owner = ?', [$userId, $userId]);
		$this->db->delete('game_banned', 'who = ?', [$userId]);
		$this->db->delete('game_log_ip', 'id = ?', [$userId]);

		$update = [
			'authlevel' => 0,
			'group_id' => 0,
			'banned' => 0,
			'planet_id' => 0,
			'planet_current' => 0,
			'bonus' => 0,
			'ally_id' => 0,
			'ally_name' => '',
			'lvl_minier' => 1,
			'lvl_raid' => 1,
			'xpminier' => 0,
			'xpraid' => 0,
			'messages' => 0,
			'messages_ally' => 0,
			'galaxy' => 0,
			'system' => 0,
			'planet' => 0,
			'vacation' => 0,
			'deltime' => 0,
			'b_tech_planet' => 0,
			'raids_win' => 0,
			'raids_lose' => 0,
			'raids' => 0,
			'tutorial' => 0,
			'tutorial_value' => 0,
			'bonus_multi' => 0,
			'message_block' => 0
		];

		$storage = $this->getDI()->getShared('registry');

		foreach ($storage->reslist['officier'] AS $oId)
			$update[$storage->resource[$oId]] = 0;

		foreach ($storage->reslist['tech'] AS $oId)
			$update[$storage->resource[$oId]] = 0;

		foreach ($storage->reslist['tech_f'] AS $oId)
			$update[$storage->resource[$oId]] = 0;

		$this->saveData($update, $userId);

		//$this->db->delete('game_users', 'id = ?', [$userId]);
		//$this->db->delete('game_users_info', 'id = ?', [$userId]);
		//$this->db->delete('game_users_auth', 'user_id = ?', [$userId]);

		return true;
	}

	public function saveData ($fields, $userId = 0)
	{
		$this->db->updateAsDict($this->getSource(), $fields, ['conditions' => 'id = ?', 'bind' => array(($userId > 0 ? $userId : $this->id))]);
	}

	public static function sendMessage ($owner, $sender, $time, $type, $from, $message)
	{
		if (!$time)
			$time = time();

		$di = Di::getDefault();

		$user = $di->has('user') ? $di->getShared('user') : false;

		if (!$owner && $user)
			$owner = $user->id;

		if (!$owner)
			return false;

		if ($sender === false && $user)
			$sender = $user->id;

		if ($user && $owner == $user->getId())
			$user->messages++;

		$obj = new Message;

		$obj->owner = $owner;
		$obj->sender = $sender;
		$obj->time = $time;
		$obj->type = $type;
		$obj->from = $from;
		$obj->text = addslashes($message);

		if ($obj->create())
		{
			$di->getShared('db')->updateAsDict('game_users', ['+messages' => 1], ['conditions' => 'id = ?', 'bind' => [$owner]]);

			return true;
		}

		return false;
	}
}