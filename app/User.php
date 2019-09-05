<?php

namespace Xnova;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Xnova\Exceptions\Exception;
use Xnova\Models\Alliance;
use Xnova\Models\LogCredits;
use Xnova\Models\Messages;
use Xnova\User\Tech;
use Xnova\Queue as QueueManager;

class User extends Models\Users
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
	private $bonusData = null;
	public $ally = [];
	private $_planet;
	public $message_block;
	public $deltime;
	public $ally_name;

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

	private function fillBobusData ()
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

	public function bonusValue ($key, $default = false)
	{
		if (!$this->bonusData)
			$this->fillBobusData();

		return $this->bonusData[$key] ?? ($default !== false ? $default : 1);
	}

	public function setBonusValue ($key, $value)
	{
		if (!$this->bonusData)
			$this->fillBobusData();

		$this->bonusData[$key] = $value;
	}

	public function getAllyInfo ()
	{
		$this->ally = [];

		if ($this->ally_id > 0)
		{
			$ally = Cache::get('user::ally_' . $this->id . '_' . $this->ally_id);

			if ($ally === null)
			{
				$ally = DB::selectOne("SELECT a.id, a.owner, a.name, a.ranks, m.rank FROM alliance a, alliance_members m WHERE m.a_id = a.id AND m.u_id = " . $this->id . " AND a.id = " . $this->ally_id);

				Cache::put('user::ally_' . $this->id . '_' . $this->ally_id, $ally, 300);
			}

			if ($ally)
			{
				if (!$ally->ranks)
					$ally->ranks = 'a:0:{}';

				$ranks = json_decode($ally->ranks, true);

				$this->ally = (array) $ally;
				$this->ally['rights'] = isset($ranks[$ally->rank - 1]) ? $ranks[$ally->rank - 1] : ['name' => '', 'planet' => 0];
			}
		}
	}

	public function checkLevel ()
	{
		$indNextXp = pow($this->lvl_minier, 3);
		$warNextXp = pow($this->lvl_raid, 2);

		$giveCredits = 0;

		if ($this->xpminier >= $indNextXp && $this->lvl_minier < Config::get('game.level.max_ind', 100))
		{
			$this->lvl_minier++;
			$this->credits += Config::get('game.level.credits', 10);
			$this->xpminier -= $indNextXp;

			$this->update();

			self::sendMessage($this->getId(), 0, 0, 1, '', '<a href="/officier/">Получен новый промышленный уровень</a>');

			$giveCredits += Config::get('game.level.credits', 10);
		}

		if ($this->xpraid >= $warNextXp && $this->lvl_raid < Config::get('game.level.max_war', 100))
		{
			$this->lvl_raid++;
			$this->credits += Config::get('game.level.credits', 10);
			$this->xpraid -= $warNextXp;

			$this->update();

			User::sendMessage($this->getId(), 0, 0, 1, '', '<a href="/officier/">Получен новый военный уровень</a>');

			$giveCredits += Config::get('game.level.credits', 10);
		}

		if ($giveCredits != 0)
		{
			LogCredits::query()->create([
				'uid' 		=> $this->getId(),
				'time' 		=> time(),
				'credits' 	=> $giveCredits,
				'type' 		=> 4,
			]);

			$reffer = DB::selectOne("SELECT u_id FROM refs WHERE r_id = " . $this->getId());

			if (isset($reffer['u_id']))
			{
				DB::table('users')->where('id', $reffer['u_id'])->increment('credits', round($giveCredits / 2));

				LogCredits::query()->create([
					'uid' 		=> $reffer['u_id'],
					'time' 		=> time(),
					'credits' 	=> round($giveCredits / 2),
					'type' 		=> 3,
				]);
			}
		}
	}

	public function setSelectedPlanet ()
	{
		if (Request::has('chpl') && is_numeric(Request::input('chpl')))
		{
			$selectPlanet = (int) Request::input('chpl');

			if ($this->planet_current == $selectPlanet || $selectPlanet <= 0)
				return true;

			$isExistPlanet = DB::selectOne("SELECT id, id_owner, id_ally FROM planets WHERE id = '" . $selectPlanet . "' AND (id_owner = '" . $this->getId() . "')");

			if (!$isExistPlanet)
				return false;

			$this->planet_current = $selectPlanet;
			$this->update();
		}

		return true;
	}

	public function getPlanets ($moons = true)
	{
		$qryPlanets = "SELECT id, name, image, galaxy, `system`, planet, planet_type, destruyed FROM planets WHERE id_owner = '" . $this->id . "' ";

		$qryPlanets .= ($this->ally_id > 0 ? " OR id_ally = '".$this->ally_id."'" : "");

		if (!$moons)
			$qryPlanets .= " AND planet_type != 3 ";

		$sort = self::getPlanetListSortQuery(
			$this->getUserOption('planet_sort'),
			$this->getUserOption('planet_sort_order')
		);

		$qryPlanets .= ' ORDER BY '.$sort['fields'].' '.$sort['order'];

		return DB::select($qryPlanets);
	}

	public function getCurrentPlanet (): Planet
	{
		if ($this->_planet)
			return $this->_planet;

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
			/** @var Planet $planet */
			$planet = Planet::query()->find($this->planet_current);

			if (!$planet && $this->planet_id > 0)
			{
				$this->planet_current = $this->planet_id;
				$this->update();

				$planet = Planet::query()->find($this->planet_current);
			}

			if ($planet)
			{
				$planet->assignUser($this);
				$planet->checkOwnerPlanet();

				// Проверяем корректность заполненных полей
				$planet->checkUsedFields();

				$controller = Route::current()->getName();
				$action = Route::current()->getActionName();

				// Обновляем ресурсы на планете когда это необходимо
				if (((($controller == "fleet" && $action != 'fleet_3') || in_array($controller, ['overview', 'galaxy', 'resources', 'imperium', 'credits', 'tutorial', 'tech', 'search', 'support', 'sim', 'tutorial'])) && $planet->last_update > (time() - 60)))
					$planet->resourceUpdate(time(), true);
				else
				{
					$planet->resourceUpdate();

					$queueManager = new QueueManager($this, $planet);
					$queueManager->checkUnitQueue();
				}

				$this->_planet = $planet;
			}
		}

		if (!$this->_planet)
			throw new Exception('planet not found');

		return $this->_planet;
	}

	public static function getPlanetListSortQuery ($sort = '', $order = 0)
	{
		$qryPlanets = '';

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

		return [
			'fields' => $qryPlanets,
			'order' => ($order == 1) ? "DESC" : "ASC",
		];
	}

	public static function sendMessage ($owner, $sender, $time, $type, $from, $message): bool
	{
		if (!$time)
			$time = time();

		/** @var self|bool $user */
		$user = Auth::check() ? Auth::user() : false;

		if (!$owner && $user)
			$owner = $user->id;

		if (!$owner)
			return false;

		if ($sender === false && $user)
			$sender = $user->id;

		if ($user && $owner == $user->getId())
			$user->messages++;

		$obj = new Messages;

		$obj->user_id = $owner;
		$obj->from_id = $sender;
		$obj->time = $time;
		$obj->type = $type;
		$obj->theme = $from;
		$obj->text = addslashes($message);

		if ($obj->save())
		{
			DB::table('users')->where('id', $owner)->increment('messages');

			return true;
		}

		return false;
	}

	public static function getRankId ($lvl)
	{
		if ($lvl <= 1)
			$lvl = 0;

		if ($lvl <= 80)
			return (ceil($lvl / 4) + 1);
		else
			return 22;
	}

	public static function deleteById (int $userId)
	{
		/** @var Models\Users $userInfo */
		$userInfo = Models\Users::query()->find((int) $userId, ['id', 'ally_id']);

		if (!$userInfo)
			return false;

		if ($userInfo->ally_id > 0)
		{
			/** @var Alliance $ally */
			$ally = Alliance::query()->find($userInfo->ally_id);

			if ($ally)
			{
				if ($ally->owner != $userId)
					$ally->deleteMember($userId);
				else
					$ally->deleteAlly();
			}
		}

		$planets = Models\Planets::query()->where('id_owner', $userId)->get(['id'])->pluck('id');

		foreach ($planets as $planet)
		{
			Models\PlanetsBuildings::query()->where('planet_id', $planet)->delete();
			Models\PlanetsUnits::query()->where('planet_id', $planet)->delete();
		}

		DB::table('alliance_requests')->where('u_id', $userId)->delete();
		DB::table('statpoints')->where('stat_type', 1)->where('id_owner', $userId)->delete();
		DB::table('planets')->where('id_owner', $userId)->delete();
		DB::table('notes')->where('user_id', $userId)->delete();
		DB::table('fleets')->where('owner', $userId)->delete();
		DB::table('buddy')->where('sender', $userId)->orWhere('owner', $userId)->delete();
		DB::table('refs')->where('r_id', $userId)->orWhere('u_id', $userId)->delete();
		DB::table('log_attack')->where('uid', $userId)->delete();
		DB::table('log_credits')->where('uid', $userId)->delete();
		DB::table('log_email')->where('user_id', $userId)->delete();
		DB::table('log_history')->where('user_id', $userId)->delete();
		DB::table('log_transfers')->where('user_id', $userId)->delete();
		DB::table('log_username')->where('user_id', $userId)->delete();
		DB::table('log_stats')->where('id', $userId)->where('type', 1)->delete();
		DB::table('logs')->where('s_id', $userId)->orWhere('e_id', $userId)->delete();
		DB::table('messages')->where('user_id', $userId)->where('from_id', $userId)->delete();
		DB::table('banned')->where('who', $userId)->delete();
		DB::table('log_ip')->where('id', $userId)->delete();
		DB::table('users_tech')->where('user_id', $userId)->delete();
		DB::table('users_quests')->where('user_id', $userId)->delete();

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
			'raids_win' => 0,
			'raids_lose' => 0,
			'raids' => 0,
			'bonus_multi' => 0,
			'message_block' => 0,
			'credits' => 0
		];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) AS $oId)
			$update[Vars::getName($oId)] = 0;

		Models\Users::query()->where('id', $userId)->update($update);

		return true;
	}

	public static function getPlanetsId ($userId)
	{
		$result = [];

		$rows = DB::select('SELECT id FROM game_planets WHERE id_owner = ?', [(int) $userId]);

		foreach ($rows as $row)
			$result[] = (int) $row->id;

		return $result;
	}
}