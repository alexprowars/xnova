<?php

namespace App\Models;

use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Traits\User\HasBonuses;
use App\Engine\Traits\User\HasOptions;
use App\Engine\Traits\User\HasTechnologies;
use App\Exceptions\Exception;
use App\Facades\Galaxy;
use App\Helpers;
use App\Notifications\MessageNotification;
use App\Notifications\UserRegistrationNotification;
use App\Settings;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use Throwable;

class User extends Authenticatable implements FilamentUser, HasName, HasMedia
{
	use HasRoles;
	use Notifiable;
	use InteractsWithMedia;
	use HasFactory;
	use SoftDeletes;

	use HasBonuses;
	use HasTechnologies;
	use HasOptions;

	protected ?Planet $currentPlanet = null;

	protected $guarded = false;

	protected $hidden = [
		'password',
		'remember_token',
	];

	protected $casts = [
		'options' => 'json:unicode',
		'username_change' => 'immutable_datetime',
		'blocked_at' => 'immutable_datetime',
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

	protected static function newFactory()
	{
		return UserFactory::new();
	}

	/** @return HasMany<FleetShortcut, $this> */
	public function shortcuts(): HasMany
	{
		return $this->hasMany(FleetShortcut::class);
	}

	/** @return HasMany<UserQuest, $this> */
	public function quests(): HasMany
	{
		return $this->hasMany(UserQuest::class);
	}

	/** @return HasOne<Statistic, $this> */
	public function statistics(): HasOne
	{
		return $this->hasOne(Statistic::class)->where('stat_type', 1);
	}

	/** @return HasMany<Planet, $this> */
	public function planets(): HasMany
	{
		return $this->hasMany(Planet::class);
	}

	/** @return HasMany<Queue, $this> */
	public function queue(): HasMany
	{
		return $this->hasMany(Queue::class, 'user_id');
	}

	/** @return BelongsTo<Alliance, $this> */
	public function alliance(): BelongsTo
	{
		return $this->belongsTo(Alliance::class);
	}

	public function registerMediaCollections(): void
	{
		$this->addMediaCollection('default')
			->storeConversionsOnDisk('resize')
			->singleFile()
			->useDisk('upload');
	}

	public function registerMediaConversions(?Media $media = null): void
	{
		$this->addMediaConversion('thumb')->width(300)->height(300);
	}

	public function isAdmin()
	{
		if ($this->id > 0) {
			return $this->hasRole('admin');
		} else {
			return false;
		}
	}

	public function isVacation(): bool
	{
		return !empty($this->vacation);
	}

	public function isOnline(): bool
	{
		return $this->onlinetime->diffInSeconds() < 180;
	}

	public function getAllyInfo()
	{
		if ($this->alliance) {
			$this->alliance->member = $this->alliance->getMember($this);
		}
	}

	public function checkLevel()
	{
		$indNextXp = $this->lvl_minier ** 3;
		$warNextXp = $this->lvl_raid ** 2;

		$giveCredits = 0;

		if ($this->xpminier >= $indNextXp && $this->lvl_minier < config('game.level.max_ind', 100)) {
			$this->setAttribute('lvl_minier', $this->lvl_minier + 1);
			$this->setAttribute('credits', $this->credits + config('game.level.credits', 10));
			$this->setAttribute('xpminier', $this->xpminier - $indNextXp);

			$this->update();

			$this->notify(new MessageNotification(null, MessageType::System, '', '<a href="/officier/">Получен новый промышленный уровень</a>'));

			$giveCredits += config('game.level.credits', 10);
		}

		if ($this->xpraid >= $warNextXp && $this->lvl_raid < config('game.level.max_war', 100)) {
			$this->setAttribute('lvl_raid', $this->lvl_raid + 1);
			$this->setAttribute('credits', $this->credits + config('game.level.credits', 10));
			$this->setAttribute('xpraid', $this->xpraid - $warNextXp);

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
				static::query()->whereKey($reffer['u_id'])
					->increment('credits', round($giveCredits / 2));

				LogCredit::create([
					'user_id'	=> $reffer['u_id'],
					'amount' 	=> round($giveCredits / 2),
					'type' 		=> 3,
				]);
			}
		}
	}

	public function setSelectedPlanet(int $planetId): bool
	{
		if ($this->planet_current == $planetId || $planetId <= 0) {
			return true;
		}

		$isExistPlanet = Planet::query()
			->whereKey($planetId)
			->whereBelongsTo($this)
			->exists();

		if (!$isExistPlanet) {
			return false;
		}

		$this->setAttribute('planet_current', $planetId);
		$this->update();

		return true;
	}

	public function getPlanets(bool $withMoons = true)
	{
		$query = Planet::query();

		if ($this->alliance_id) {
			$query->where(function (Builder $query) {
				$query->whereBelongsTo($this)->orWhere('alliance_id', $this->alliance_id);
			});
		} else {
			$query->whereBelongsTo($this);
		}

		if (!$withMoons) {
			$query->whereNot('planet_type', PlanetType::MOON);
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
			Galaxy::createPlanetByUser($this);
		}

		if ($this->planet_current && $this->planet_id) {
			$planet = Planet::findOne($this->planet_current);

			if (!$planet) {
				$this->setAttribute('planet_current', $this->planet_id);
				$this->update();

				$planet = Planet::findOne($this->planet_current);
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

	public static function getPlanetsId(User $user): array
	{
		return Planet::query()->whereBelongsTo($user)
			->pluck('id')->all();
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
				$refer = self::query()->find((int) Session::get('ref'), ['id']);

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

		if ($protectionPoints <= 0 || $this->onlinetime->diffInDays() > 7 || $this->blocked_at) {
			return false;
		}

		$points = $this->getPoints();

		return ($points->total_points ?? 0) < $protectionPoints;
	}
}
