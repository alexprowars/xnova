<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Exception;
use Xnova\Exceptions\RedirectException;
use Xnova\Galaxy;
use Phalcon\Mvc\Model;
use Friday\Core\Models\User as BaseUser;
use Xnova\Vars;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static User[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static User findFirst(mixed $parameters = null)
 * @property \Xnova\Database db
 */
class User extends BaseUser
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

	public $rpg_geologue;
	public $rpg_ingenieur;
	public $rpg_admiral;
	public $rpg_constructeur;
	public $rpg_technocrate;
	public $rpg_meta;
	public $rpg_komandir;

	public $tutorial_value;
	public $message_block;
	public $color;
	public $timezone;
	public $spy;
	public $deltime;
	public $ally_name;
	/**
	 * @var bool|array
	 */
	private $technology = false;

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

	public function afterUpdate ()
	{
		$this->setSnapshotData($this->toArray());

		if ($this->technology !== false)
		{
			foreach ($this->technology as $tech)
			{
				if ($tech['id'] == 0 && $tech['level'] > 0)
				{
					$this->db->insertAsDict(DB_PREFIX.'users_tech', [
						'user_id' => $this->id,
						'tech_id' => $tech['type'],
						'level' => $tech['level'],
					]);
				}
				elseif ($tech['level'] != $tech['~level'])
				{
					if ($tech['level'] > 0)
					{
						$this->db->updateAsDict(DB_PREFIX.'users_tech', [
							'level' => $tech['level']
						], ['conditions' => 'id = ?', 'bind' => [$tech['id']]]);
					}
					else
						$this->db->delete(DB_PREFIX.'users_tech', 'id = ?', [$tech['id']]);
				}

				$building['~level'] = $tech['level'];
			}
		}
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

	private function getTechnologyData ()
	{
		if ($this->technology !== false)
			return;

		$this->technology = [];

		$items = $this->db->query('SELECT * FROM '.DB_PREFIX.'users_tech WHERE user_id = ?', [$this->id]);

		while ($item = $items->fetch())
		{
			$this->technology[$item['tech_id']] = [
				'id'		=> (int) $item['id'],
				'type'		=> (int) $item['tech_id'],
				'level'		=> (int) $item['level'],
				'~level'	=> (int) $item['level']
			];
		}
	}

	public function getTech ($techId)
	{
		if (!is_numeric($techId))
			$techId = Vars::getIdByName($techId.'_tech');

		if (!$techId)
			throw new Exception('getTech not found');

		$techId = (int) $techId;

		if (!$techId)
			return false;

		if ($this->technology === false)
			$this->getTechnologyData();

		if (isset($this->technology[$techId]))
			return $this->technology[$techId];

		if (!in_array(Vars::getItemType($techId), [Vars::ITEM_TYPE_TECH, Vars::ITEM_TYPE_TECH_FLEET]))
			return false;

		$this->technology[$techId] = [
			'id'		=> 0,
			'type'		=> $techId,
			'level'		=> 0,
			'~level'	=> 0
		];

		return $this->technology[$techId];
	}

	public function setTech ($techId, $level)
	{
		$tech = $this->getTech($techId);

		$this->technology[$tech['type']]['level'] = (int) $level;
	}

	public function getTechLevel ($techId)
	{
		$tech = $this->getTech($techId);

		return $tech ? $tech['level'] : 0;
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
					throw new RedirectException("Вы не можете переключится на эту планету. Недостаточно прав.", "Альянс", "/overview/", 2);

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

	public function saveData ($fields, $userId = 0)
	{
		$this->db->updateAsDict($this->getSource(), $fields, ['conditions' => 'id = ?', 'bind' => array(($userId > 0 ? $userId : $this->id))]);
	}
}