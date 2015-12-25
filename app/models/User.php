<?php
namespace App\Models;

use App\Sql;
use Phalcon\Mvc\Model;

/**
 * Class User
 * @package App\Models
 * @property \Phalcon\Db\Adapter\Pdo\Mysql db
 */
class User extends Model
{
	private $optionsData = array('security' => 0);

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

	public $ally = [];

	public $lvl_minier;
	public $lvl_raid;
	public $xpminier;
	public $xpraid;

	public $credits;
	public $messages;
	public $avatar;

	public function onConstruct()
	{

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
				$this->planet_id = system::CreateRegPlanet($this->getId());
				$this->planet_current = $this->planet_id;
			}
		}

		if ($this->planet_current > 0 && $this->planet_id > 0)
		{
			// Выбираем информацию о планете
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
				if ((($controller == "overview" || ($controller == "fleet" && $action != 'fleet_3') || $controller == "galaxy" || $controller == "resources" || $controller == "imperium" || $controller == "infokredits" || $controller == "tutorial" || $controller == "techtree" || $controller == "search" || $controller == "support" || $controller == "sim" || $controller == "tutorial") && $planet->last_update > (time() - 60)))
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
			$ally = $this->cache->get('user::ally_' . $this->id . '_' . $this->ally_id);

			if ($ally === false)
			{
				$ally = $this->db->query("SELECT a.id, a.ally_owner, a.ally_name, a.ally_ranks, m.rank FROM game_alliance a, game_alliance_members m WHERE m.a_id = a.id AND m.u_id = " . $this->id . " AND a.id = " . $this->ally_id)->fetch();

				$this->cache->set('user::ally_' . $this->id . '_' . $this->ally_id, $ally, 300);
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
					$this->getDi()->getShared('game')->message("Вы не можете переключится на эту планету. Недостаточно прав.", "Альянс", "?set=overview", 2);
				}

				$this->planet_current = $selectPlanet;

				$this->saveData(array('planet_current' => $this->planet_current));
			}
			else
				return false;
		}

		return true;
	}

	public function saveData ($fields, $userId = 0)
	{
		Sql::build()->update('game_users')->set($fields)->where('id', '=', ($userId > 0 ? $userId : $this->id))->execute();
	}
}

?>