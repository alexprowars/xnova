<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Database;
use Xnova\Exceptions\RedirectException;
use Xnova\Galaxy;
use Phalcon\Mvc\Model;
use Friday\Core\Models\User as BaseUser;
use Xnova\Models\User\Tech;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static User[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static User findFirst(mixed $parameters = null)
 * @method Database getWriteConnection
 */
class User extends BaseUser
{
	use Tech;

	private $_optionsDefault = [
		'bb_parser' 		=> true,
		'planetlist' 		=> false,
		'planetlistselect' 	=> false,
		'chatbox' 			=> true,
		'records' 			=> true,
		'only_available' 	=> false,

		'planet_sort'		=> 0,
		'planet_sort_order'	=> 0,
		'color'				=> 0,
		'timezone'			=> 0,
		'spy'				=> 1,
	];

	private $optionsData = [];

	private $bonusData = [];

	public $group_id;
	public $username;
	public $authlevel;
	public $onlinetime;
	public $banned;
	public $planet_current;
	public $planet_id;
	public $race;
	public $sex;
	public $ally_id;
	public $vacation;
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

	public $rpg_geologue;
	public $rpg_ingenieur;
	public $rpg_admiral;
	public $rpg_constructeur;
	public $rpg_technocrate;
	public $rpg_meta;
	public $rpg_komandir;

	public $message_block;
	public $deltime;
	public $ally_name;

	public function onConstruct()
	{
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

	public function getFullName ()
	{
		return trim($this->username);
	}

	public function afterUpdate ()
	{
		parent::afterUpdate();

		$this->_afterUpdateTechs();
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
			$this->bonusData['fleet_fuel'] 		-= 0.2;
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

		return true;
	}

	public function isOnline ()
	{
		return (time() - $this->onlinetime < 180);
	}

	public function setOptions ($data, $clear = true)
	{
		if ($clear)
			$this->optionsData = [];

		if (!is_array($data))
			return;

		foreach ($data as $key => $value)
			$this->optionsData[trim($key)] = $value;
	}

	public function getUserOption ($key = false)
	{
		if ($key === false)
			return $this->optionsData;

		return (isset($this->optionsData[$key]) ? $this->optionsData[$key] : (isset($this->_optionsDefault[$key]) ? $this->_optionsDefault[$key] : 0));
	}

	public function setUserOption ($key, $value)
	{
		$this->optionsData[$key] = $value;
	}

	public function loadPlanet ()
	{
		if ($this->getDI()->has('planet'))
			return;

		if (!$this->planet_current && !$this->planet_id)
		{
			if ($this->race > 0)
			{
				$galaxy = new Galaxy();

				$this->planet_id = $galaxy->createPlanetByUserId($this->getId());
				$this->planet_current = $this->planet_id;
			}
		}

		if ($this->planet_current && $this->planet_id)
		{
			/** Выбираем информацию о планете */
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

			if ($ally === null)
			{
				$ally = $this->getWriteConnection()->query("SELECT a.id, a.owner, a.name, a.ranks, m.rank FROM game_alliance a, game_alliance_members m WHERE m.a_id = a.id AND m.u_id = " . $this->id . " AND a.id = " . $this->ally_id)->fetch();

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
			$selectPlanet = (int) $request->getQuery('chpl', 'int');

			if ($this->planet_current == $selectPlanet || $selectPlanet <= 0)
				return true;

			$isExistPlanet = $this->getWriteConnection()->query("SELECT id, id_owner, id_ally FROM game_planets WHERE id = '" . $selectPlanet . "' AND (id_owner = '" . $this->getId() . "' OR (id_ally > 0 AND id_ally = '".$this->ally_id."'))")->fetch();

			if ($isExistPlanet)
			{
				if ($isExistPlanet['id_ally'] && $isExistPlanet['id_owner'] != $this->getId() && !$this->ally['rights']['planet'])
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

	public function setBonusValue ($key, $value)
	{
		$this->bonusData[$key] = $value;
	}

	public function saveData ($fields, $userId = 0)
	{
		$this->getWriteConnection()->updateAsDict($this->getSource(), $fields, [
			'conditions' => 'id = ?',
			'bind' => array(($userId > 0 ? $userId : $this->id))
		]);
	}
}