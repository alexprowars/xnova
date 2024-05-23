<?php

namespace App;

use App\Models\Log;
use Backpack\Settings\app\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Exceptions\Exception;
use App\Mail\UserRegistration;
use App\Models\Message;
use App\User\Tech;
use App\Queue as QueueManager;

class User extends Models\User
{
	use Tech;

	private $bonusData;
	public $ally = [];
	/** @var Planet */
	private $planet;
	public $message_block;
	public $deltime;
	public $ally_name;

	public function isOnline()
	{
		return (time() - $this->onlinetime < 180);
	}

	private function fillBobusData()
	{
		$bonusArrays = [
			'storage', 'metal', 'crystal', 'deuterium', 'energy', 'solar',
			'res_fleet', 'res_defence', 'res_research', 'res_building', 'res_levelup',
			'time_fleet', 'time_defence', 'time_research', 'time_building',
			'fleet_fuel', 'fleet_speed', 'queue'
		];

		$this->bonusData = [];

		// Значения по умолчанию
		foreach ($bonusArrays as $name) {
			$this->bonusData[$name] = 1;
		}

		$this->bonusData['queue'] = 0;

		// Расчет бонусов от офицеров
		if ($this->rpg_geologue > time()) {
			$this->bonusData['metal'] 			+= 0.25;
			$this->bonusData['crystal'] 		+= 0.25;
			$this->bonusData['deuterium'] 		+= 0.25;
			$this->bonusData['storage'] 		+= 0.25;
		}
		if ($this->rpg_ingenieur > time()) {
			$this->bonusData['energy'] 			+= 0.15;
			$this->bonusData['solar'] 			+= 0.15;
			$this->bonusData['res_defence'] 	-= 0.1;
		}
		if ($this->rpg_admiral > time()) {
			$this->bonusData['res_fleet'] 		-= 0.1;
			$this->bonusData['fleet_speed'] 	+= 0.25;
		}
		if ($this->rpg_constructeur > time()) {
			$this->bonusData['time_fleet'] 		-= 0.25;
			$this->bonusData['time_defence'] 	-= 0.25;
			$this->bonusData['time_building'] 	-= 0.25;
			$this->bonusData['queue'] 			+= 2;
		}
		if ($this->rpg_technocrate > time()) {
			$this->bonusData['time_research'] 	-= 0.25;
		}
		if ($this->rpg_meta > time()) {
			$this->bonusData['fleet_fuel'] 		-= 0.2;
		}

		// Расчет бонусов от рас
		if ($this->race == 1) {
			$this->bonusData['metal'] 			+= 0.15;
			$this->bonusData['solar'] 			+= 0.15;
			$this->bonusData['res_levelup'] 	-= 0.1;
			$this->bonusData['time_fleet'] 		-= 0.1;
		} elseif ($this->race == 2) {
			$this->bonusData['deuterium'] 		+= 0.15;
			$this->bonusData['solar'] 			+= 0.05;
			$this->bonusData['storage'] 		+= 0.2;
			$this->bonusData['res_fleet'] 		-= 0.1;
		} elseif ($this->race == 3) {
			$this->bonusData['metal'] 			+= 0.05;
			$this->bonusData['crystal'] 		+= 0.05;
			$this->bonusData['deuterium'] 		+= 0.05;
			$this->bonusData['res_defence'] 	-= 0.05;
			$this->bonusData['res_building'] 	-= 0.05;
			$this->bonusData['time_building'] 	-= 0.1;
		} elseif ($this->race == 4) {
			$this->bonusData['crystal'] 		+= 0.15;
			$this->bonusData['energy'] 			+= 0.05;
			$this->bonusData['res_research'] 	-= 0.1;
			$this->bonusData['fleet_speed'] 	+= 0.1;
		}

		return true;
	}

	public function bonusValue($key, $default = false)
	{
		if (!$this->bonusData) {
			$this->fillBobusData();
		}

		return $this->bonusData[$key] ?? ($default !== false ? $default : 1);
	}

	public function setBonusValue($key, $value)
	{
		if (!$this->bonusData) {
			$this->fillBobusData();
		}

		$this->bonusData[$key] = $value;
	}

	public function getAllyInfo()
	{
		if ($this->alliance) {
			$this->ally['rights'] = $this->alliance->ranks[$ally->rank - 1] ?? ['name' => '', 'planet' => 0];
		}
	}

	public function checkLevel()
	{
		$indNextXp = $this->lvl_minier ** 3;
		$warNextXp = $this->lvl_raid ** 2;

		$giveCredits = 0;

		if ($this->xpminier >= $indNextXp && $this->lvl_minier < config('settings.level.max_ind', 100)) {
			$this->lvl_minier++;
			$this->credits += config('settings.level.credits', 10);
			$this->xpminier -= $indNextXp;

			$this->update();

			static::sendMessage($this->id, 0, 0, 2, '', '<a href="/officier/">Получен новый промышленный уровень</a>');

			$giveCredits += config('settings.level.credits', 10);
		}

		if ($this->xpraid >= $warNextXp && $this->lvl_raid < config('settings.level.max_war', 100)) {
			$this->lvl_raid++;
			$this->credits += config('settings.level.credits', 10);
			$this->xpraid -= $warNextXp;

			$this->update();

			static::sendMessage($this->id, 0, 0, 2, '', '<a href="/officier/">Получен новый военный уровень</a>');

			$giveCredits += config('settings.level.credits', 10);
		}

		if ($giveCredits != 0) {
			Models\LogCredit::create([
				'user_id' 	=> $this->id,
				'amount' 	=> $giveCredits,
				'type' 		=> 4,
			]);

			$reffer = Models\Referal::query()
				->where('r_id', $this->id)
				->first();

			if ($reffer) {
				static::query()->where('id', $reffer['u_id'])
					->increment('credits', round($giveCredits / 2));

				Models\LogCredit::create([
					'user_id'	=> $reffer['u_id'],
					'amount' 	=> round($giveCredits / 2),
					'type' 		=> 3,
				]);
			}
		}
	}

	public function setSelectedPlanet(int $planetId)
	{
		if ($this->planet_current == $planetId || $planetId <= 0) {
			return true;
		}

		$isExistPlanet = Planet::query()
			->where('id', $planetId)
			->where('user_id', $this->id)
			->exists();

		if (!$isExistPlanet) {
			return false;
		}

		$this->planet_current = $planetId;
		$this->update();
	}

	public function getPlanets(bool $moons = true): array
	{
		$query = Planet::query()
			->select(['id', 'name', 'image', 'galaxy', 'system', 'planet', 'planet_type', 'destruyed'])
			->where('user_id', $this->id);

		if ($this->alliance_id > 0) {
			$query->orWhere('alliance_id', $this->alliance_id);
		}

		if (!$moons) {
			$query->where('planet_type', '!=', 3);
		}

		$sort = self::getPlanetListSortQuery(
			$this->getOption('planet_sort'),
			$this->getOption('planet_sort_order')
		);

		$query->orderBy($sort['fields'], $sort['order']);

		return $query->get()->all();
	}

	public function getCurrentPlanet(bool $loading = false): ?Planet
	{
		if ($this->planet || !$loading) {
			return $this->planet;
		}

		if (!$this->planet_current && !$this->planet_id) {
			if ($this->race > 0) {
				$galaxy = new Galaxy();

				$this->planet_id = $galaxy->createPlanetByUserId($this->id);
				$this->planet_current = $this->planet_id;
			}
		}

		if ($this->planet_current && $this->planet_id) {
			$planet = Planet::query()->find($this->planet_current);

			if (!$planet && $this->planet_id > 0) {
				$this->planet_current = $this->planet_id;
				$this->update();

				$planet = Planet::query()->find($this->planet_current);
			}

			if ($planet) {
				$planet->setRelation('user', $this);
				$planet->checkOwnerPlanet();

				// Проверяем корректность заполненных полей
				$planet->checkUsedFields();

				$controller = Route::current()->getName();
				$action = Route::current()->getActionName();

				// Обновляем ресурсы на планете когда это необходимо
				if (((($controller == "fleet" && $action != 'fleet_3') || in_array($controller, ['overview', 'galaxy', 'resources', 'imperium', 'credits', 'tutorial', 'tech', 'search', 'support', 'sim', 'tutorial'])) && $planet->last_update > (time() - 60))) {
					$planet->getProduction()->update(true);
				} else {
					$planet->getProduction()->update();

					$queueManager = new QueueManager($this, $planet);
					$queueManager->checkUnitQueue();
				}

				$this->planet = $planet;
			}
		}

		//if (!$this->_planet)
		//	throw new Exception('planet not found');

		return $this->planet;
	}

	public static function getPlanetListSortQuery($sort = '', $order = 0)
	{
		$qryPlanets = '';

		switch ($sort) {
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

	public static function sendMessage($owner, $sender, $time, $type, $from, $message): bool
	{
		if ($time instanceof Carbon) {
			$time = $time->getTimestamp();
		}

		if (empty($time)) {
			$time = time();
		}

		$user = Auth::check() ? Auth::user() : false;

		if (!$owner && $user) {
			$owner = $user->id;
		}

		if (!$owner) {
			return false;
		}

		if ($sender === false && $user) {
			$sender = $user->id;
		}

		if ($user && $owner == $user->id) {
			$user->messages++;
		}

		$obj = new Message();

		$obj->user_id = $owner;
		$obj->from_id = $sender;
		$obj->time = $time;
		$obj->type = $type;
		$obj->theme = $from;
		$obj->text = addslashes($message);

		if ($obj->save()) {
			User::find($owner)->increment('messages');

			return true;
		}

		return false;
	}

	public static function getRankId($lvl)
	{
		if ($lvl <= 1) {
			$lvl = 0;
		}

		if ($lvl <= 80) {
			return (ceil($lvl / 4) + 1);
		} else {
			return 22;
		}
	}

	public static function deleteById(int $id)
	{
		$user = Models\User::find($id);

		if (!$user) {
			return false;
		}

		if ($user->alliance) {
			if ($user->alliance->user_id != $id) {
				$user->alliance->deleteMember($id);
			} else {
				$user->alliance->deleteAlly();
			}
		}

		$planets = Models\Planet::query()->where('user_id', $id)->get(['id'])->pluck('id');

		foreach ($planets as $planet) {
			Models\PlanetEntity::query()->where('planet_id', $planet)->delete();
		}

		Models\AllianceRequest::query()->where('user_id', $id)->delete();
		Models\Statistic::query()->where('stat_type', 1)->where('user_id', $id)->delete();
		Models\Planet::query()->where('user_id', $id)->delete();
		Models\Note::query()->where('user_id', $id)->delete();
		Models\Fleet::query()->where('user_id', $id)->delete();
		Models\Friend::query()->where('sender', $id)->orWhere('owner', $id)->delete();
		Models\Referal::query()->where('r_id', $id)->orWhere('u_id', $id)->delete();
		DB::table('log_attacks')->where('user_id', $id)->delete();
		Models\LogCredit::query()->where('user_id', $id)->delete();
		Models\LogHistory::query()->where('user_id', $id)->delete();
		DB::table('log_transfers')->where('user_id', $id)->delete();
		DB::table('log_stats')->where('user_id', $id)->where('type', 1)->delete();
		Log::query()->where('s_id', $id)->orWhere('e_id', $id)->delete();
		Models\Message::query()->where('user_id', $id)->where('from_id', $id)->delete();
		Models\Blocked::query()->where('user_id', $id)->delete();
		DB::table('log_ips')->where('user_id', $id)->delete();
		Models\UserTech::query()->where('user_id', $id)->delete();
		Models\UserQuest::query()->where('user_id', $id)->delete();

		$update = [
			'authlevel' => 0,
			'group_id' => 0,
			'banned' => 0,
			'planet_id' => 0,
			'planet_current' => 0,
			'bonus' => 0,
			'alliance_id' => null,
			'alliance_name' => null,
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

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) as $oId) {
			$update[Vars::getName($oId)] = 0;
		}

		$user->update($update);

		return true;
	}

	public static function getPlanetsId(int $userId): array
	{
		return Planet::query()->where('user_id', $userId)
			->get(['id'])->pluck('id')->all();
	}

	public static function creation(array $data)
	{
		if (empty($data['password'])) {
			$data['password'] = Str::random(10);
		}

		return DB::transaction(function () use ($data) {
			$user = Models\User::create([
				'email' => $data['email'] ?? '',
				'password' => Hash::make($data['password']),
				'username' => $data['name'] ?? '',
				'sex' => 0,
				'planet_id' => 0,
				'ip' => Helpers::convertIp(Request::ip()),
				'bonus' => time(),
				'onlinetime' => time(),
			]);

			if (!$user->id) {
				throw new Exception('create user error');
			}

			Models\UserDetail::create([
				'id' => $user->id,
			]);

			if (Session::has('ref')) {
				$refer = Models\User::query()->find((int) Session::get('ref'), ['id']);

				if ($refer) {
					Models\Referal::insert([
						'r_id' => $user->id,
						'u_id' => $refer->id
					]);
				}
			}

			Setting::set('users_total', (Setting::get('users_total') ?? 0) + 1);

			if (isset($data['email']) && $data['email'] != '') {
				try {
					Mail::to($data['email'])->send(new UserRegistration([
						'#EMAIL#' => $data['email'],
						'#PASSWORD#' => $data['password'],
					]));
				} catch (\Exception $e) {
				}
			}

			return $user->id;
		});
	}
}
