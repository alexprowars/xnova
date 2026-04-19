<?php

namespace App\Engine\Entity;

use App\Engine\Enums\Resources;

trait Unit
{
	public function getBaseTime(): int
	{
		$time = parent::getTime();

		$time *= (1 / ($this->planet->getLevel('hangar') + 1));
		$time *= (1 / 2) ** $this->planet->getLevel('nano_factory');

		return max(1, (int) $time);
	}

	public function getMaxConstructible(): int
	{
		$max = null;

		$price = $this->getPrice();

		foreach ($price as $type => $count) {
			if (!in_array($type, array_column(Resources::cases(), 'value')) || $count <= 0) {
				continue;
			}

			$count = (int) floor($this->planet->{$type} / $count);

			$max = min($max ?? $count, $count);
		}

		if ($this->getObject()->getMaxConstructable()) {
			$max = min($max, $this->getObject()->getMaxConstructable());
		}

		return $max ?? 0;
	}

	public function getAttack(): int
	{
		$user = $this->planet->user;

		$tech = 1 + $user->getTechLevel('military') * 0.05;

		if ($this->getObject()->getWeaponType() == 1) {
			$tech += $user->getTechLevel('laser') * 0.05;
		} elseif ($this->getObject()->getWeaponType() == 2) {
			$tech += $user->getTechLevel('ionic') * 0.05;
		} elseif ($this->getObject()->getWeaponType() == 3) {
			$tech += $user->getTechLevel('buster') * 0.05;
		}

		return (int) round($this->getObject()->getAttack() * (1 + $tech));
	}

	public function getArmor(): int
	{
		$user = $this->planet->user;

		return (int) round($this->getObject()->getArmor() * (1 + $user->getTechLevel('defence') * 0.05));
	}
}
