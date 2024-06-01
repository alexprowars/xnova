<?php

namespace App\Engine\Entity;

use App\Engine\Contracts\EntityInterface;
use App\Engine\Contracts\EntityProductionInterface;
use App\Engine\Vars;
use App\Models\Planet;

class Entity implements EntityInterface, EntityProductionInterface
{
	use ProductionTrait;

	protected ?Planet $planet;

	protected function __construct(public int $entityId, public int $level = 0)
	{
	}

	public static function createEntity(int $entityId, int $level = 1, Planet $planet = null): static
	{
		$object = new static($entityId, $level);
		$object->planet = $planet;

		return $object;
	}

	public function getEntityId(): int
	{
		return $this->entityId;
	}

	public function getLevel(): int
	{
		return $this->level;
	}

	public function setLevel(int $level)
	{
		$this->level = $level;
	}

	protected function getBasePrice(): array
	{
		$price = Vars::getItemPrice($this->entityId);

		$cost = [];

		foreach (array_merge(Vars::getItemsByType('res'), ['energy']) as $resource) {
			if (!isset($price[$resource])) {
				continue;
			}

			$cost[$resource] = floor($price[$resource]);
		}

		return $cost;
	}

	public function getPrice(): array
	{
		$cost = $this->getBasePrice();
		$user = $this->planet->user;

		$elementType = Vars::getItemType($this->entityId);

		foreach ($cost as $resType => $value) {
			switch ($elementType) {
				case Vars::ITEM_TYPE_BUILING:
					$cost[$resType] *= $user->bonus('res_building');
					break;
				case Vars::ITEM_TYPE_TECH:
					$cost[$resType] *= $user->bonus('res_research');
					break;
				case Vars::ITEM_TYPE_FLEET:
					$cost[$resType] *= $user->bonus('res_fleet');
					break;
				case Vars::ITEM_TYPE_DEFENSE:
					$cost[$resType] *= $user->bonus('res_defence');
					break;
			}

			$cost[$resType] = round($cost[$resType]);
		}

		return $cost;
	}

	public function getTime(): int
	{
		$cost = $this->getBasePrice();
		$cost = $cost['metal'] + $cost['crystal'];

		$time = ($cost / config('settings.game_speed')) * 3600;

		return (int) max(1, $time);
	}

	public function isAvailable(): bool
	{
		$requeriments = Vars::getItemRequirements($this->entityId);

		if (!count($requeriments)) {
			return true;
		}

		foreach ($requeriments as $reqElement => $level) {
			if ($reqElement == 700) {
				if ($this->planet->user->race != $level) {
					return false;
				}
			} elseif (Vars::getItemType($reqElement) == Vars::ITEM_TYPE_TECH) {
				if ($this->planet->user->getTechLevel($reqElement) < $level) {
					return false;
				}
			} elseif (Vars::getItemType($reqElement) == Vars::ITEM_TYPE_BUILING) {
				if ($this->planet->planet_type == 5 && in_array($this->entityId, [43, 502, 503])) {
					if (in_array($reqElement, [21, 41])) {
						continue;
					}
				}

				if ($this->planet->getLevel($reqElement) < $level) {
					return false;
				}
			} else {
				return false;
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
				if ($planet->energy_max < $ResCount) {
					return false;
				}
			} elseif (!isset($planet->{$ResType}) || $ResCount > $planet->{$ResType}) {
				return false;
			}
		}

		return true;
	}
}
