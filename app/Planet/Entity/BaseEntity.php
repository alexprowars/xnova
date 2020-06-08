<?php

namespace Xnova\Planet\Entity;

use Illuminate\Support\Facades\Auth;
use Xnova\Planet\Contracts\PlanetEntityInterface;
use Xnova\Vars;

class BaseEntity implements PlanetEntityInterface
{
	protected $entityId;
	protected $context;
	protected $level = 0;

	public function __construct(int $entityId, int $level = 1, ?Context $context = null)
	{
		$this->entityId = $entityId;
		$this->level = $level;
		$this->context = $context;
	}

	protected function getContext(): Context
	{
		if (!$this->context) {
			$this->context = new Context(Auth::user());
		}

		return $this->context;
	}

	public function getLevel(): int
	{
		return $this->level;
	}

	protected function getBasePrice(): array
	{
		$price = Vars::getItemPrice($this->entityId);

		$cost = [];

		foreach (Vars::getItemsByType('res') + ['energy'] as $ResType) {
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
		$user = $this->getContext()->getUser();

		$elementType = Vars::getItemType($this->entityId);

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
		$requeriments = Vars::getItemRequirements($this->entityId);

		if (!count($requeriments)) {
			return true;
		}

		$user = $this->getContext()->getUser();
		$planet = $this->getContext()->getPlanet();

		foreach ($requeriments as $reqElement => $level) {
			if ($reqElement == 700) {
				if ($user->race != $level) {
					return false;
				}
			} elseif (Vars::getItemType($reqElement) == Vars::ITEM_TYPE_TECH) {
				if ($user->getTechLevel($reqElement) < $level) {
					return false;
				}
			} elseif (Vars::getItemType($reqElement) == Vars::ITEM_TYPE_BUILING) {
				if ($planet->planet_type == 5 && in_array($this->entityId, [43, 502, 503])) {
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

		$planet = $this->getContext()->getPlanet();

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
