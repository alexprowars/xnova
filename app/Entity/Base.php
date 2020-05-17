<?php

namespace Xnova\Entity;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Xnova\User;
use Xnova\Vars;

class Base
{
	protected $elementId;
	protected $context;
	protected $level = 0;

	public function __construct($elementId, int $level = 1, ?Context $context = null)
	{
		$this->elementId = $elementId;
		$this->level = $level;
		$this->context = $context;
	}

	protected function getContext(): Context
	{
		if (!$this->context) {
			/** @var User $user */
			$user = Auth::user();
			$this->context = new Context($user);
		}

		return $this->context;
	}

	public function getLevel(): int
	{
		return $this->level;
	}

	public function getBasePrice(): array
	{
		$price = Vars::getItemPrice($this->elementId);

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

		$elementType = Vars::getItemType($this->elementId);

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

		$time = ($cost / Config::get('settings.game_speed')) * 3600;

		return max(1, $time);
	}

	public function isAvailable()
	{
		$requeriments = Vars::getItemRequirements($this->elementId);

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
				if ($planet->planet_type == 5 && in_array($this->elementId, [43, 502, 503])) {
					if (in_array($reqElement, [21, 41])) {
						continue;
					}
				}

				if ($planet->getBuildLevel($reqElement) < $level) {
					return false;
				}
			} else {
				return false;
			}
		}

		return true;
	}

	public function canBuy(?array $cost = null)
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
