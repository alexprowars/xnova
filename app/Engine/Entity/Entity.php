<?php

namespace App\Engine\Entity;

use App\Engine\Contracts\EntityInterface;
use App\Engine\Contracts\EntityProductionInterface;
use App\Engine\Enums\PlanetType;
use App\Engine\Enums\Resources;
use App\Engine\Game;
use App\Engine\Objects\BaseObject;
use App\Engine\Objects\BuildingObject;
use App\Engine\Objects\DefenceObject;
use App\Engine\Objects\ObjectsFactory;
use App\Engine\Objects\ResearchObject;
use App\Engine\Objects\ShipObject;
use App\Models\Planet;

/**
 * @template TObject of BaseObject
 */
abstract class Entity implements EntityInterface, EntityProductionInterface
{
	use ProductionTrait;

	protected ?Planet $planet;
	/** @var TObject */
	protected BaseObject $object;

	protected function __construct(public int $entityId, public int $level = 0)
	{
		$this->object = ObjectsFactory::get($this->entityId);
	}

	/**
	 * @param int $entityId
	 * @param int $level
	 * @param Planet|null $planet
	 * @return static<BaseObject>
	 */
	public static function createEntity(int $entityId, int $level = 1, ?Planet $planet = null): static
	{
		/** @phpstan-ignore new.static */
		$object = new static($entityId, $level);
		$object->planet = $planet;

		return $object;
	}

	public function getEntityId(): int
	{
		return $this->entityId;
	}

	/**
	 * @return TObject
	 */
	public function getObject(): BaseObject
	{
		return $this->object;
	}

	public function getLevel(): int
	{
		return $this->level;
	}

	/**
	 * @return $this
	 */
	public function setLevel(int $level): self
	{
		$this->level = $level;

		return $this;
	}

	/** @return array<value-of<Resources>, int> */
	protected function getBasePrice(): array
	{
		$price = $this->getObject()->getPrice();

		$cost = [];

		foreach (Resources::cases() as $resource) {
			if (!isset($price[$resource->value])) {
				continue;
			}

			$cost[$resource->value] = (int) floor($price[$resource->value]);
		}

		return $cost;
	}

	/**
	 * @return array<value-of<Resources>, int>
	 */
	public function getPrice(): array
	{
		$cost = $this->getBasePrice();
		$user = $this->planet->user;

		foreach ($cost as $resType => $value) {
			switch ($this->object::class) {
				case BuildingObject::class:
					$cost[$resType] *= $user->bonus('res_building');
					break;
				case ResearchObject::class:
					$cost[$resType] *= $user->bonus('res_research');
					break;
				case ShipObject::class:
					$cost[$resType] *= $user->bonus('res_fleet');
					break;
				case DefenceObject::class:
					$cost[$resType] *= $user->bonus('res_defence');
					break;
				default:
			}

			$cost[$resType] = (int) floor($cost[$resType]);
		}

		return $cost;
	}

	public function getTime(): int
	{
		$cost = $this->getBasePrice();
		$cost = $cost['metal'] + $cost['crystal'];

		$time = ($cost / (2500 * Game::getSpeed('build'))) * 3600;

		return (int) max(1, $time);
	}

	public function isAvailable(): bool
	{
		$requeriments = $this->object->getRequeriments();

		if (empty($requeriments)) {
			return true;
		}

		foreach ($requeriments as $reqElement => $level) {
			if ($reqElement == 'race') {
				return $this->planet->user->race == $level;
			}

			$object = ObjectsFactory::get($reqElement);

			if ($object instanceof ResearchObject) {
				return $this->planet->user->getTechLevel($object->getId()) >= $level;
			}

			if ($object instanceof BuildingObject) {
				if ($this->planet->planet_type == PlanetType::MILITARY_BASE && in_array($this->entityId, [43, 502, 503]) && in_array($object->getId(), [21, 41])) {
					continue;
				}

				if ($this->planet->getLevel($object->getId()) < $level) {
					return false;
				}
			}
		}

		return true;
	}

	public function canConstruct(?array $cost = null): bool
	{
		if (!$cost) {
			$cost = $this->getPrice();
		}

		$planet = $this->planet;

		foreach ($cost as $ResType => $ResCount) {
			if ($ResType == 'energy') {
				if ($planet->energy < $ResCount) {
					return false;
				}
			} elseif (!isset($planet->{$ResType}) || $ResCount > $planet->{$ResType}) {
				return false;
			}
		}

		return true;
	}
}
