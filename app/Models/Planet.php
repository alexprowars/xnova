<?php

namespace App\Models;

use App\Engine\Coordinates;
use App\Engine\Entity\Entity;
use App\Engine\Entity\Model\PlanetEntity;
use App\Engine\Entity\Model\PlanetEntityCollection;
use App\Engine\EntityFactory;
use App\Engine\Enums\AllianceAccess;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Objects\BaseObject;
use App\Engine\Objects\BuildingObject;
use App\Engine\Production;
use App\Exceptions\Exception;
use App\Facades\Vars;
use App\Factories\PlanetServiceFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property PlanetEntityCollection $entities
 */
class Planet extends Model
{
	use MassPrunable;
	use SoftDeletes;

	protected $hidden = ['planet_updated'];
	protected $guarded = [];

	protected $attributes = [
		'metal' => 0,
		'crystal' => 0,
		'deuterium' => 0,
		'entities' => '[]',
	];

	public bool $planet_updated = false;
	public int $energy_used;
	public int $energy = 0;

	private Production $production;

	protected $casts = [
		'last_update' => 'immutable_datetime',
		'last_active' => 'immutable_datetime',
		'last_jump_time' => 'immutable_datetime',
		'merchand' => 'immutable_datetime',
		'destroyed_at' => 'immutable_datetime',
		'planet_type' => PlanetType::class,
		'metal' => 'float',
		'crystal' => 'float',
		'deuterium' => 'float',
	];

	/**
	 * @return array{
	 *     entities: 'Illuminate\Database\Eloquent\Casts\AsCollection',
	 * }
	 */
	protected function casts(): array
	{
		return [
			'entities' => AsCollection::using(PlanetEntityCollection::class, PlanetEntity::class),
		];
	}

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

	/** @return HasMany<Queue, $this> */
	public function queue(): HasMany
	{
		return $this->hasMany(Queue::class, 'planet_id')->chaperone();
	}

	/**
	 * @return Builder<static>
	 */
	public function prunable(): Builder
	{
		/** @phpstan-ignore-next-line  */
		return static::query()->where('deleted_at', '<', now()->subWeek())->withTrashed();
	}

	public function checkOwnerPlanet(): bool
	{
		if ($this->user_id != $this->user->id) {
			return false;
		}

		if ($this->alliance_id && ($this->alliance_id != $this->user->alliance_id || !$this->user->alliance?->canAccess(AllianceAccess::PLANET_ACCESS))) {
			return false;
		}

		return true;
	}

	public function checkUsedFields(): void
	{
		$count = 0;

		$objects = Vars::getObjectsByType(ItemType::BUILDING);

		/** @var BuildingObject $object */
		foreach ($objects as $object) {
			if ($object->hasAllowedBuild($this->planet_type)) {
				$count += $this->getLevel($object->getId());
			}
		}

		if ($this->field_current != $count) {
			$this->update(['field_current' => $count]);
		}
	}

	public function getMaxFields(): int
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

	public function getLevel(int|string|BaseObject $entityId): int
	{
		return $this->getEntity($entityId)->level ?? 0;
	}

	public function getEntity(int|string|BaseObject $entityId): ?PlanetEntity
	{
		if ($entityId instanceof BaseObject) {
			return $this->entities->getByEntityId($entityId->getId());
		}

		if (!is_numeric($entityId)) {
			$entityId = Vars::getIdByName($entityId);
		}

		if (!$entityId || !Vars::getName($entityId)) {
			return null;
		}

		return $this->entities->getByEntityId($entityId);
	}

	/**
	 * @param int|string|BaseObject $entityId
	 * @return Entity<BaseObject>|null
	 */
	public function getEntityUnit(int|string|BaseObject $entityId): ?Entity
	{
		$entity = $this->getEntity($entityId);

		if (!$entity) {
			return null;
		}

		return EntityFactory::get($entity->id, $entity->level, $this);
	}

	public function updateAmount(int|string|BaseObject $entityId, int $amount, bool $isDifferent = false): void
	{
		$entity = $this->getEntity($entityId);

		if (!$entity) {
			throw new Exception('update unknown entity');
		}

		if ($isDifferent) {
			$entity->level += $amount;
		} else {
			$entity->level = $amount;
		}

		$this->save();
	}

	public function getProduction(?CarbonImmutable $updateTime = null): Production
	{
		if (!isset($this->production)) {
			$this->production = new Production($this, $updateTime);
		}

		return $this->production;
	}

	/** @return Attribute<array<int>, never> */
	public function networkLevel(): Attribute
	{
		return Attribute::get(fn() => resolve(PlanetServiceFactory::class)->make($this)->getResearchNetworkLabLevel())->shouldCache();
	}

	public static function findByCoordinates(Coordinates $target): ?self
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
