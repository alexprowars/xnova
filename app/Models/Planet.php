<?php

namespace App\Models;

use App\Engine\Coordinates;
use App\Engine\Enums\PlanetType;
use App\Engine\Production;
use App\Engine\Vars;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Planet
 */
class Planet extends Model
{
	use CrudTrait;

	public $timestamps = false;
	protected $hidden = ['planet_updated'];
	protected $guarded = [];

	protected $attributes = [
		'metal' => 0,
		'crystal' => 0,
		'deuterium' => 0,
	];

	public $planet_updated = false;
	public $energy_used;
	public $energy_max = 0;

	private $production;

	protected function casts(): array
	{
		return [
			'last_update' => 'immutable_datetime',
			'last_active' => 'immutable_datetime',
			'last_jump_time' => 'immutable_datetime',
			'merchand' => 'immutable_datetime',
			'destruyed' => 'immutable_datetime',
			'planet_type' => PlanetType::class,
		];
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function entities()
	{
		return $this->hasMany(PlanetEntity::class, 'planet_id');
	}

	public function checkOwnerPlanet()
	{
		if ($this->user_id != $this->user->id && $this->alliance_id > 0 && ($this->alliance_id != $this->user->alliance_id || !$this->user->alliance['rights']['planet'])) {
			$this->user->planet_current = $this->user->planet_id;
			$this->user->update();

			$data = $this->find($this->user->planet->id);

			if ($data) {
				$this->fill($data->toArray());
			}

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
		$fields += config('settings.fieldsByMoonBase', 0) * $this->getLevel('moonbase');

		return $fields;
	}

	public function getCoordinates(): Coordinates
	{
		return new Coordinates($this->galaxy, $this->system, $this->planet, $this->planet_type);
	}

	public function getLevel($entityId): int
	{
		return $this->getEntity($entityId)?->amount ?? 0;
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

	public function networkLevel(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->getNetworkLevel()
		)->shouldCache();
	}

	protected function getNetworkLevel()
	{
		$list = [$this->getLevel('laboratory')];

		if ($this->user->getTechLevel('intergalactic') > 0) {
			$items = DB::table('planets_entities', 'pe')
				->leftJoin('planets p', 'p.id', '=', 'pe.planet_id')
				->where('pe.entity_id', 31)
				->where('pe.planet_id', '!=', $this->id)
				->where('pe.amount', '>', 0)
				->where('p.user_id', $this->user->id)
				->where('p.planet_type', 1)
				->whereNull('pe.destruyed')
				->orderByDesc('level')
				->limit($this->user->getTechLevel('intergalactic'))
				->get();

			foreach ($items as $item) {
				$list[] = (int) $item->level;
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

	public function scopeCoordinates(Builder $query, Coordinates $target): void
	{
		$query->where('galaxy', $target->getGalaxy())
			->where('system', $target->getSystem())
			->where('planet', $target->getPlanet());

		if ($target->getType()) {
			$query->where('planet_type', $target->getType());
		}
	}
}
