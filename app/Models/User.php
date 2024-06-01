<?php

namespace App\Models;

use App\Engine\Galaxy;
use App\Engine\QueueManager;
use App\Engine\User\Tech;
use App\Exceptions\Exception;
use App\Helpers;
use App\Mail\UserLostPassword;
use App\Notifications\UserRegistrationNotification;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Backpack\Settings\app\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Throwable;

/**
 * @mixin User
 */
class User extends Authenticatable
{
	use HasRoles;
	use CrudTrait;
	use Notifiable;
	use Tech;

	protected $optionsDefault = [
		'bb_parser' 		=> true,
		'planetlist' 		=> false,
		'planetlistselect' 	=> false,
		'chatbox' 			=> true,
		'records' 			=> true,
		'only_available' 	=> false,
		'planet_sort'		=> 0,
		'planet_sort_order'	=> 0,
		'color'				=> 0,
		'timezone'			=> null,
		'spy'				=> 1,
	];

	protected $bonusData;
	protected ?Planet $currentPlanet = null;

	protected $guarded = [];
	protected $hidden = ['password'];

	protected function casts(): array
	{
		return [
			'options' => 'array',
			'username_change' => 'immutable_datetime',
			'banned_time' => 'immutable_datetime',
			'onlinetime' => 'immutable_datetime',
			'vacation' => 'immutable_datetime',
			'delete_time' => 'immutable_datetime',
			'rpg_geologue' => 'immutable_datetime',
			'rpg_admiral' => 'immutable_datetime',
			'rpg_ingenieur' => 'immutable_datetime',
			'rpg_technocrate' => 'immutable_datetime',
			'rpg_constructeur' => 'immutable_datetime',
			'rpg_meta' => 'immutable_datetime',
			'rpg_komandir' => 'immutable_datetime',
			'daily_bonus' => 'immutable_datetime',
		];
	}

	public function shortcuts()
	{
		return $this->hasMany(FleetShortcut::class);
	}

	public function quests()
	{
		return $this->hasMany(UserQuest::class);
	}

	public function statistics()
	{
		return $this->hasOne(Statistic::class)->where('stat_type', 1);
	}

	public function planets()
	{
		return $this->hasMany(Planet::class);
	}

	public function queue()
	{
		return $this->hasMany(Queue::class);
	}

	public function alliance()
	{
		return $this->hasOne(Alliance::class);
	}

	public function isAdmin()
	{
		if ($this->id > 0) {
			return $this->hasRole('admin');
		} else {
			return false;
		}
	}

	public function isVacation()
	{
		return !empty($this->vacation);
	}

	public function isOnline()
	{
		return $this->onlinetime->diffInSeconds() < 180;
	}

	public function getOptions()
	{
		return array_merge($this->optionsDefault, $this->options ?? []);
	}

	public function getOption($key)
	{
		return ($this->options[$key] ?? ($this->optionsDefault[$key] ?? 0));
	}

	public function setOption($key, $value)
	{
		$options = $this->options ?? [];
		$options[$key] = $value;

		$this->options = $options;
	}

	private function fillBobusData()
	{
		$bonusArrays = [
			'storage', 'metal', 'crystal', 'deuterium', 'energy', 'solar',
			'res_fleet', 'res_defence', 'res_research', 'res_building', 'res_levelup',
			'time_fleet', 'time_defence', 'time_research', 'time_building',
			'fleet_fuel', 'fleet_speed', 'queue',
		];

		$this->bonusData = [];

		// Значения по умолчанию
		foreach ($bonusArrays as $name) {
			$this->bonusData[$name] = 1;
		}

		$this->bonusData['queue'] = 0;

		// Расчет бонусов от офицеров
		if ($this->rpg_geologue?->isFuture()) {
			$this->bonusData['metal'] 			+= 0.25;
			$this->bonusData['crystal'] 		+= 0.25;
			$this->bonusData['deuterium'] 		+= 0.25;
			$this->bonusData['storage'] 		+= 0.25;
		}
		if ($this->rpg_ingenieur?->isFuture()) {
			$this->bonusData['energy'] 			+= 0.15;
			$this->bonusData['solar'] 			+= 0.15;
			$this->bonusData['res_defence'] 	-= 0.1;
		}
		if ($this->rpg_admiral?->isFuture()) {
			$this->bonusData['res_fleet'] 		-= 0.1;
			$this->bonusData['fleet_speed'] 	+= 0.25;
		}
		if ($this->rpg_constructeur?->isFuture()) {
			$this->bonusData['time_fleet'] 		-= 0.25;
			$this->bonusData['time_defence'] 	-= 0.25;
			$this->bonusData['time_building'] 	-= 0.25;
			$this->bonusData['queue'] 			+= 2;
		}
		if ($this->rpg_technocrate?->isFuture()) {
			$this->bonusData['time_research'] 	-= 0.25;
		}
		if ($this->rpg_meta?->isFuture()) {
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

	public function bonus($key, $default = false)
	{
		if (!$this->bonusData) {
			$this->fillBobusData();
		}

		return $this->bonusData[$key] ?? ($default !== false ? $default : 1);
	}

	public function setBonus($key, $value)
	{
		if (!$this->bonusData) {
			$this->fillBobusData();
		}

		$this->bonusData[$key] = $value;
	}

	public function sendPasswordResetNotification($token)
	{
		$email = $this->getEmailForPasswordReset();

		try {
			Mail::to($email)->send(new UserLostPassword([
				'#EMAIL#' => $email,
				'#NAME#' => $this->username,
				'#URL#' => URL::route('login.reset', ['token' => $token, 'user' => $email]),
			]));
		} catch (\Exception) {
		}
	}

	public function getAllyInfo()
	{
		if ($this->alliance) {
			$this->alliance->member = $this->alliance->members()->where('user_id', $this->id)
				->first();
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
			LogCredit::create([
				'user_id' 	=> $this->id,
				'amount' 	=> $giveCredits,
				'type' 		=> 4,
			]);

			$reffer = Referal::query()
				->where('r_id', $this->id)
				->first();

			if ($reffer) {
				static::query()->where('id', $reffer['u_id'])
					->increment('credits', round($giveCredits / 2));

				LogCredit::create([
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

		return true;
	}

	public function getPlanets(bool $moons = true)
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

		$this->getPlanetListSortQuery($query);

		return $query->get()->all();
	}

	public function getCurrentPlanet(bool $loading = false): ?Planet
	{
		if ($this->currentPlanet || !$loading) {
			return $this->currentPlanet;
		}

		if (!$this->planet_current && !$this->planet_id && $this->race) {
			$this->planet_id = (new Galaxy())->createPlanetByUserId($this->id);
			$this->planet_current = $this->planet_id;
		}

		if ($this->planet_current && $this->planet_id) {
			$planet = Planet::find($this->planet_current);

			if (!$planet && $this->planet_id) {
				$this->planet_current = $this->planet_id;
				$this->update();

				$planet = Planet::find($this->planet_current);
			}

			if ($planet) {
				$planet->setRelation('user', $this);
				$planet->checkOwnerPlanet();

				// Проверяем корректность заполненных полей
				$planet->checkUsedFields();

				// Обновляем ресурсы на планете когда это необходимо
				if ($planet->last_update?->diffInSeconds() > 60) {
					$planet->getProduction()->update(true);
				} else {
					$planet->getProduction()->update();

					$queueManager = new QueueManager($this, $planet);
					$queueManager->checkUnitQueue();
				}

				$this->currentPlanet = $planet;
			}
		}

		return $this->currentPlanet;
	}

	public function getPlanetListSortQuery(Builder $query)
	{
		$qryPlanets = match ($this->getOption('planet_sort')) {
			1 => 'galaxy, system, planet, planet_type',
			2 => 'name',
			3 => 'planet_type',
			default => 'id',
		};

		$query->orderBy($qryPlanets, $this->getOption('planet_sort_order') > 0 ? 'desc' : 'asc');
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
		$obj->from_id = $sender ?: null;
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
			$user = User::create([
				'email' => $data['email'] ?? '',
				'password' => Hash::make($data['password']),
				'username' => $data['name'] ?? '',
				'ip' => Helpers::convertIp(Request::ip()),
				'daily_bonus' => now(),
				'onlinetime' => now(),
			]);

			if (!$user->id) {
				throw new Exception('create user error');
			}

			if (Session::has('ref')) {
				$refer = User::find((int) Session::get('ref'), ['id']);

				if ($refer) {
					Referal::insert([
						'r_id' => $user->id,
						'u_id' => $refer->id,
					]);
				}
			}

			Setting::set('usersTotal', (int) (Setting::get('usersTotal') ?? 0) + 1);

			if (!empty($user->email)) {
				try {
					$user->notify(new UserRegistrationNotification($data['password']));
				} catch (Throwable) {
				}
			}

			return $user;
		});
	}
}
