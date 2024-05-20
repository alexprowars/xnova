<?php

namespace App;

use App\Planet\EntityFactory;
use Illuminate\Support\Facades\DB;
use App\Entity\Coordinates;
use App\Models\PlanetEntity;
use App\Planet\Entity\BaseEntity;
use App\Planet\EntityCollection;
use App\Planet\Production;

class Planet extends Models\Planet
{
	/** @var User */
	private $user;
	public $ally = [];

	public $planet_updated = false;
	public $spaceLabs;
	public $merchand;
	public $energy_used;
	public $energy_max = 0;

	/** @var EntityCollection */
	public $entities;

	private $production;

	public function setUser(User $user)
	{
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function checkOwnerPlanet()
	{
		if ($this->id_owner != $this->user->id && $this->id_ally > 0 && ($this->id_ally != $this->user->ally_id || !$this->user->ally['rights']['planet'])) {
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
			if (isset($buildings[$type])) {
				$count += $buildings[$type]->amount;
			}
		}

		if ($this->field_current != $count) {
			$this->field_current = $count;
			$this->update();
		}
	}

	public function getMaxFields()
	{
		$fields = (int) $this->field_max;

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

		if (!$entity) {
			$entity = new PlanetEntity([
				'planet_id' => $this->id,
				'entity_id' => $entityId,
				'amount' => 0,
			]);

			$this->entities->add($entity);
		}

		if ($isDifferent) {
			$entity->amount += $amount;
		} else {
			$entity->amount = $amount;
		}
	}

	public function getProduction(int $updateTime = 0): Production
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
			$items = DB::select(
				'SELECT b.id, b.level FROM planets_buildings b
				LEFT JOIN planets p ON p.id = b.planet_id
					WHERE
				b.build_id = :build AND p.id_owner = :user AND b.planet_id != :planet AND b.level > 0 AND p.destruyed = 0 AND p.planet_type = 1
					ORDER BY
				b.level DESC
					LIMIT :level',
				[
					'build' => 31,
					'user' => $this->user->id,
					'planet' => $this->id,
					'level' => $this->user->getTechLevel('intergalactic')
				]
			);

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
			$nextJumpTime = $this->last_jump_time + $waitTime;

			if ($nextJumpTime >= time()) {
				return $nextJumpTime - time();
			}
		}

		return 0;
	}
}
