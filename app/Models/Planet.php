<?php

namespace App\Models;

use App\Engine\Coordinates;
use App\Engine\Entity\Entity as BaseEntity;
use App\Engine\EntityCollection;
use App\Engine\EntityFactory;
use App\Engine\Production;
use App\Engine\Vars;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
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
	protected $spaceLabs;
	public $energy_used;
	public $energy_max = 0;

	/** @var EntityCollection */
	public $entityCollection;

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
		if (!($this->entityCollection instanceof EntityCollection)) {
			$this->entityCollection = EntityCollection::getForPlanet($this);
		}
	}

	public function checkUsedFields()
	{
		if (!$this->entityCollection) {
			$this->collectEntities();
		}

		$count = 0;

		$buildings = $this->entityCollection->getForTypes(Vars::ITEM_TYPE_BUILING);

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

		$fields += $this->entityCollection->getEntityAmount('terraformer') * 5;
		$fields += config('settings.fieldsByMoonBase', 0) * $this->entityCollection->getEntityAmount('moonbase');

		return $fields;
	}

	public function getCoordinates(): Coordinates
	{
		return new Coordinates($this->galaxy, $this->system, $this->planet, $this->planet_type);
	}

	public function getLevel($entityId): int
	{
		if (!$this->entityCollection) {
			$this->collectEntities();
		}

		return $this->entityCollection->getEntityAmount($entityId);
	}

	public function getEntity($entityId): ?BaseEntity
	{
		if (!$this->entityCollection) {
			$this->collectEntities();
		}

		return $this->entityCollection->getEntity($entityId)
			?? EntityFactory::get($entityId, 1, $this);
	}

	public function updateAmount($entityId, int $amount, bool $isDifferent = false)
	{
		if (!is_numeric($entityId)) {
			$entityId = Vars::getIdByName($entityId);
		}

		$entity = $this->getEntity($entityId);

		if (!$entity->id) {
			$this->entityCollection->add($entity);
		}

		if ($isDifferent) {
			$entity->amount += $amount;
		} else {
			$entity->amount = $amount;
		}
	}

	public function getProduction(Carbon|CarbonImmutable $updateTime = null): Production
	{
		if (!$this->production) {
			$this->production = new Production($this, $updateTime);
		}

		return $this->production;
	}

	public function afterUpdate()
	{
		if (!$this->relationLoaded('entities')) {
			return;
		}

		$this->entities->each(function (PlanetEntity $entity) {
			$entity->save();
		});
	}

	public function getNetworkLevel()
	{
		if ($this->spaceLabs !== null) {
			return $this->spaceLabs;
		}

		$list = [$this->entityCollection->getEntityAmount('laboratory')];

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

		$this->spaceLabs = $list;

		return $list;
	}

	public function isAvailableJumpGate()
	{
		return ($this->planet_type == 3 || $this->planet_type == 5) && $this->entityCollection->getEntityAmount('jumpgate') > 0;
	}

	public function getNextJumpTime()
	{
		$jumpGate = $this->entityCollection->getEntity('jumpgate');

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
