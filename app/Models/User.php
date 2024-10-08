<?php

namespace App\Models;

use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Galaxy;
use App\Engine\Traits\User\HasBonuses;
use App\Engine\Traits\User\HasOptions;
use App\Engine\Traits\User\HasTechnologies;
use App\Exceptions\Exception;
use App\Helpers;
use App\Notifications\MessageNotification;
use App\Notifications\UserRegistrationNotification;
use App\Settings;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Throwable;

/**
 * @mixin User
 */
class User extends Authenticatable implements FilamentUser, HasName
{
	use HasRoles;
	use Notifiable;

	use HasBonuses;
	use HasTechnologies;
	use HasOptions;

	protected ?Planet $currentPlanet = null;

	protected $guarded = false;
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
			'message_block' => 'immutable_datetime',
		];
	}

	protected static function booted(): void
	{
		static::deleting(function (User $model) {
			if ($model->alliance) {
				if ($model->alliance->user_id != $model->id) {
					$model->alliance->deleteMember($model->id);
				} else {
					$model->alliance->delete();
				}
			}

			LogStat::query()->where('object_id', $model->id)->where('type', 1)->delete();
		});
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
		return $this->belongsTo(Alliance::class);
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

	public function getAllyInfo()
	{
		if ($this->alliance) {
			$this->alliance->member = $this->alliance->getMember($this->id);
		}
	}

	public function checkLevel()
	{
		$indNextXp = $this->lvl_minier ** 3;
		$warNextXp = $this->lvl_raid ** 2;

		$giveCredits = 0;

		if ($this->xpminier >= $indNextXp && $this->lvl_minier < config('game.level.max_ind', 100)) {
			$this->lvl_minier++;
			$this->credits += config('game.level.credits', 10);
			$this->xpminier -= $indNextXp;

			$this->update();

			$this->notify(new MessageNotification(null, MessageType::System, '', '<a href="/officier/">Получен новый промышленный уровень</a>'));

			$giveCredits += config('game.level.credits', 10);
		}

		if ($this->xpraid >= $warNextXp && $this->lvl_raid < config('game.level.max_war', 100)) {
			$this->lvl_raid++;
			$this->credits += config('game.level.credits', 10);
			$this->xpraid -= $warNextXp;

			$this->update();

			$this->notify(new MessageNotification(null, MessageType::System, '', '<a href="/officier/">Получен новый военный уровень</a>'));

			$giveCredits += config('game.level.credits', 10);
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

	public function getPlanets(bool $withMoons = true)
	{
		$query = Planet::query();

		if ($this->alliance_id) {
			$query->where(function (Builder $query) {
				$query->where('user_id', $this->id)->orWhere('alliance_id', $this->alliance_id);
			});
		} else {
			$query->where('user_id', $this->id);
		}

		if (!$withMoons) {
			$query->where('planet_type', '!=', PlanetType::MOON);
		}

		$this->getPlanetListSortQuery($query);

		return $query->get()->all();
	}

	public function getCurrentPlanet(): ?Planet
	{
		if ($this->currentPlanet) {
			return $this->currentPlanet;
		}

		if (!$this->planet_current && !$this->planet_id && $this->race) {
			(new Galaxy())->createPlanetByUser($this);
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
		return Planet::query()->select(['id'])->where('user_id', $userId)
			->get()->pluck('id')->all();
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

			$settings = app(Settings::class);
			$settings->usersTotal++;
			$settings->save();

			if (!empty($user->email)) {
				try {
					$user->notify(new UserRegistrationNotification($data['password']));
				} catch (Throwable) {
				}
			}

			return $user;
		});
	}

	public function canAccessPanel(Panel $panel): bool
	{
		return $this->id === 1;
	}

	public function getFilamentName(): string
	{
		return $this->username;
	}

	public function getPoints(): ?Statistic
	{
		return Cache::remember('app::statistics_' . $this->id, 1800, function () {
			return $this->statistics()
				->where('stat_code', 1)
				->first();
		});
	}

	public function isNoobProtection(): bool
	{
		$protection = (int) config('game.noobprotection') > 0;

		if (!$protection) {
			return false;
		}

		$protectionPoints = (int) config('game.noobprotectionPoints');

		if ($protectionPoints <= 0 || $this->onlinetime->diffInDays() > 7 || $this->banned_time) {
			return false;
		}

		$points = $this->getPoints();

		return ($points?->total_points ?? 0) < $protectionPoints;
	}
}
