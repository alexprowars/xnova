<?php

namespace App\Models;

use App\Engine\Coordinates;
use App\Engine\Enums\AllianceAccess;
use App\Engine\Enums\PlanetType;
use App\Engine\Production;
use App\Facades\Vars;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Planet extends Model
{
	use MassPrunable;
	use SoftDeletes;

	protected $hidden = ['planet_updated'];
	protected $guarded = false;

	protected $attributes = [
		'metal' => 0,
		'crystal' => 0,
		'deuterium' => 0,
	];

	public $planet_updated = false;
	public $energy_used;
	public $energy = 0;

	private $production;

	protected $casts = [
		'last_update' => 'immutable_datetime',
		'last_active' => 'immutable_datetime',
		'last_jump_time' => 'immutable_datetime',
		'merchand' => 'immutable_datetime',
		'destruyed_at' => 'immutable_datetime',
		'planet_type' => PlanetType::class,
	];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/** @return BelongsTo<Planet, $this> */
	public function moon(): BelongsTo
	{
		return $this->belongsTo(Planet::class, 'moon_id');
	}

	/** @return HasMany<PlanetEntity, $this> */
	public function entities(): HasMany
	{
		return $this->hasMany(PlanetEntity::class, 'planet_id');
	}

	/** @return HasMany<Queue, $this> */
	public function queue(): HasMany
	{
		return $this->hasMany(Queue::class, 'planet_id');
	}

	public function prunable()
	{
		return static::query()->withTrashed()->where('deleted_at', '<', now()->subWeek());
	}

	public function checkOwnerPlanet()
	{
		if ($this->user_id != $this->user->id) {
			return false;
		}

		if ($this->alliance_id && ($this->alliance_id != $this->user->alliance_id || !$this->user->alliance?->canAccess(AllianceAccess::PLANET_ACCESS))) {
			return false;
		}

		return true;
	}

	public function checkUsedFields()
	{
		$count = 0;

		foreach (Vars::getAllowedBuilds($this->planet_type) as $type) {
			$count += $this->getLevel($type);
		}

		if ($this->field_current != $count) {
			$this->field_current = $count;
			$this->update();
		}
	}

	public function getMaxFields()
	{
		$fields = $this->field_max;

		$fields += $this->getLevel('terraformer') * 5;
		$fields += config('game.fieldsByMoonBase', 0) * $this->getLevel('moonbase');

		return $fields;
	}

	/** @return Attribute<Coordinates, never> */
	protected function coordinates(): Attribute
	{
		return Attribute::get(fn() => new Coordinates($this->galaxy, $this->system, $this->planet, $this->planet_type));
	}

	public function getLevel($entityId): int
	{
		return $this->getEntity($entityId)->amount ?? 0;
	}

	public function getEntity($entityId): ?PlanetEntity
	{
		if (!is_numeric($entityId)) {
			$entityId = Vars::getIdByName($entityId);
		}

		if (!$entityId) {
			return null;
		}

		$entity = $this->entities->firstWhere('entity_id', $entityId);

		if (!$entity) {
			if (!Vars::getName($entityId)) {
				return null;
			}

			$entity = new PlanetEntity(['entity_id' => $entityId]);

			$this->entities->add($entity);
		}

		$entity->planet()->associate($this);

		return $entity;
	}

	public function updateAmount($entityId, int $amount, bool $isDifferent = false)
	{
		if (!is_numeric($entityId)) {
			$entityId = Vars::getIdByName($entityId);
		}

		$entity = $this->getEntity($entityId);

		if (!$entity->id) {
			$this->entities->add($entity);
		}

		if ($isDifferent) {
			$entity->amount += $amount;
		} else {
			$entity->amount = $amount;
		}

		$entity->save();
	}

	public function getProduction(Carbon|CarbonImmutable $updateTime = null): Production
	{
		if (!$this->production) {
			$this->production = new Production($this, $updateTime);
		}

		return $this->production;
	}

	/** @return Attribute<array<int>, never> */
	public function networkLevel(): Attribute
	{
		return Attribute::get(fn() => $this->getNetworkLevel())->shouldCache();
	}

	/**
	 * @return array<int>
	 */
	protected function getNetworkLevel(): array
	{
		$list = [$this->getLevel('laboratory')];

		if ($this->user->getTechLevel('intergalactic') > 0) {
			$items = DB::table('planets_entities', 'pe')
				->leftJoin('planets as p', 'p.id', '=', 'pe.planet_id')
				->where('pe.entity_id', 31)
				->where('pe.planet_id', '!=', $this->id)
				->where('pe.amount', '>', 0)
				->where('p.user_id', $this->user->id)
				->where('p.planet_type', PlanetType::PLANET)
				->whereNull('p.destruyed_at')
				->orderByDesc('pe.amount')
				->limit($this->user->getTechLevel('intergalactic'))
				->pluck('pe.amount');

			foreach ($items as $item) {
				$list[] = (int) $item;
			}
		}

		return $list;
	}

	public function isAvailableJumpGate()
	{
		return ($this->planet_type == PlanetType::MOON || $this->planet_type == PlanetType::MILITARY_BASE) && $this->getLevel('jumpgate') > 0;
	}

	public function getNextJumpTime()
	{
		$jumpGate = $this->getEntity('jumpgate');

		if ($jumpGate && $jumpGate->amount > 0) {
			$waitTime = (60 * 60) * (1 / $jumpGate->amount);
			$nextJumpTime = $this->last_jump_time->timestamp + $waitTime;

			if ($nextJumpTime >= time()) {
				return $nextJumpTime - time();
			}
		}

		return 0;
	}

	public static function findByCoordinates(Coordinates $target): ?static
	{
		return self::query()->where('galaxy', $target->getGalaxy())
			->where('system', $target->getSystem())
			->where('planet', $target->getPlanet())
			->where('planet_type', $target->getType() ?: PlanetType::PLANET)
			->first();
	}

	/**
	 * @param Builder<$this> $query
	 * @param Coordinates $target
	 * @return Builder<$this>
	 */
	public function scopeCoordinates(Builder $query, Coordinates $target): Builder
	{
		return $query->where('galaxy', $target->getGalaxy())
			->where('system', $target->getSystem())
			->where('planet', $target->getPlanet())
			->when(
				$target->getType(),
				fn(Builder $query) => $query->where('planet_type', $target->getType())
			);
	}
}
