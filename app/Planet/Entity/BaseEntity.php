<?php

namespace App\Planet\Entity;

use Illuminate\Support\Facades\Auth;
use App\Models\PlanetEntity;
use App\Planet;
use App\Planet\Contracts\PlanetEntityInterface;
use App\Planet\Contracts\PlanetEntityProductionInterface;
use App\Vars;

class BaseEntity extends PlanetEntity implements PlanetEntityInterface, PlanetEntityProductionInterface
{
	use ProductionTrait;

	public static function createEntity(int $entityId, int $level = 1, Planet $context = null): self
	{
		$object = self::createEmpty($entityId, $level);
		$object->setRelation('planet', $context);

		return $object;
	}

	public function getLevel(): int
	{
		return $this->amount;
	}

	public function setLevel(int $level)
	{
		$this->amount = $level;
	}

	protected function getBasePrice(): array
	{
		$price = Vars::getItemPrice($this->entity_id);

		$cost = [];

		foreach (array_merge(Vars::getItemsByType('res'), ['energy']) as $ResType) {
			if (!isset($price[$ResType])) {
				continue;
			}

			$cost[$ResType] = floor($price[$ResType]);
		}

		return $cost;
	}

	public function getPrice(): array
	{
		$cost = $this->getBasePrice();
		$user = $this->planet->user;

		$elementType = Vars::getItemType($this->entity_id);

		foreach ($cost as $resType => $value) {
			switch ($elementType) {
				case Vars::ITEM_TYPE_BUILING:
					$cost[$resType] *= $user->bonusValue('res_building');
					break;
				case Vars::ITEM_TYPE_TECH:
					$cost[$resType] *= $user->bonusValue('res_research');
					break;
				case Vars::ITEM_TYPE_FLEET:
					$cost[$resType] *= $user->bonusValue('res_fleet');
					break;
				case Vars::ITEM_TYPE_DEFENSE:
					$cost[$resType] *= $user->bonusValue('res_defence');
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

		return max(1, $time);
	}

	public function isAvailable(): bool
	{
		$requeriments = Vars::getItemRequirements($this->entity_id);

		if (!count($requeriments)) {
			return true;
		}

		$planet = $this->planet;

		foreach ($requeriments as $reqElement => $level) {
			if ($reqElement == 700) {
				if ($planet->user->race != $level) {
					return false;
				}
			} elseif (Vars::getItemType($reqElement) == Vars::ITEM_TYPE_TECH) {
				if ($planet->user->getTechLevel($reqElement) < $level) {
					return false;
				}
			} elseif (Vars::getItemType($reqElement) == Vars::ITEM_TYPE_BUILING) {
				if ($planet->planet_type == 5 && in_array($this->entity_id, [43, 502, 503])) {
					if (in_array($reqElement, [21, 41])) {
						continue;
					}
				}

				if ($planet->getLevel($reqElement) < $level) {
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
