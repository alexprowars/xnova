<?php

namespace App\Models;

use App\Planet\Entity\BaseEntity;
use App\Planet\EntityCollection;
use App\Planet\EntityFactory;
use App\Planet\Production;
use App\Vars;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Coordinates;
use Illuminate\Support\Carbon;
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
	public $spaceLabs;
	public $energy_used;
	public $energy_max = 0;

	/** @var EntityCollection */
	public $entities;

	private $production;

	protected function casts(): array
	{
		return [
			'last_update' => 'immutable_datetime',
			'last_active' => 'immutable_datetime',
			'last_jump_time' => 'immutable_datetime',
			'merchand' => 'immutable_datetime',
			'destruyed' => 'immutable_datetime',
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

	private function collectEntities()
	{
		if (!($this->entities instanceof EntityCollection)) {
			$this->entities = EntityCollection::getForPlanet($this);
		}
	}

	public function reset()
	{
		$this->entities = null;
	}

	public function checkUsedFields()
	{
		if (!$this->entities) {
			$this->collectEntities();
		}

		$count = 0;

		$buildings = $this->entities->getForTypes(Vars::ITEM_TYPE_BUILING);

		foreach (Vars::getAllowedBuilds($this->planet_type) as $type) {
			$count += $buildings->getEntityAmount($type);
		}

		if ($this->field_current != $count) {
			$this->field_current = $count;
			$this->update();
		}
	}

	public function getMaxFields()
	{
		$fields = $this->field_max;

		$fields += $this->entities->getEntityAmount('terraformer') * 5;
		$fields += config('settings.fieldsByMoonBase', 0) * $this->entities->getEntityAmount('moonbase');

		return $fields;
	}

	public function getCoordinates(): Coordinates
	{
		return new Coordinates($this->galaxy, $this->system, $this->planet, $this->planet_type);
	}

	public function getLevel($entityId): int
	{
		if (!$this->entities) {
			$this->collectEntities();
		}

		return $this->entities->getEntityAmount($entityId);
	}

	public function getEntity($entityId): ?BaseEntity
	{
		if (!$this->entities) {
			$this->collectEntities();
		}

		return $this->entities->getEntity($entityId)
			?? EntityFactory::createFromModel(PlanetEntity::createEmpty($entityId), $this);
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
	}

	public function getProduction(Carbon $updateTime = null): Production
	{
		if (!$this->production) {
			$this->production = new Production($this, $updateTime);
		}

		return $this->production;
	}

	public function afterUpdate()
	{
		if (!$this->entities) {
			return;
		}

		$this->entities->each(function (PlanetEntity $entity) {
			if ($entity->isDirty() && $entity->planet_id > 0) {
				$entity->save();
			}
		});
	}

	public function getNetworkLevel()
	{
		$list = [$this->entities->getEntityAmount('laboratory')];

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
		return ($this->planet_type == 3 || $this->planet_type == 5) && $this->entities->getEntityAmount('jumpgate') > 0;
	}

	public function getNextJumpTime()
	{
		$jumpGate = $this->entities->getEntity('jumpgate');

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
			->where('planet_type', $target->getType() ?: Coordinates::TYPE_PLANET)
			->first();
	}
}
