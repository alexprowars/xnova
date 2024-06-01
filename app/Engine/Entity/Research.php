<?php

namespace App\Engine\Entity;

use App\Engine\Vars;

class Research extends Entity
{
	protected function getBasePrice(): array
	{
		$cost = parent::getBasePrice();

		$price = Vars::getItemPrice($this->entityId);

		return array_map(
			fn ($value) => floor($value * (($price['factor'] ?? 1) ** $this->level)),
			$cost
		);
	}

	public function getTime(): int
	{
		$time = parent::getTime();

		$planet = $this->planet;

		if (isset($planet->spaceLabs) && is_array($planet->spaceLabs) && count($planet->spaceLabs)) {
			$lablevel = 0;

			foreach ($planet->spaceLabs as $Levels) {
				$req = Vars::getItemRequirements($this->entityId);

				if (!isset($req[31]) || $Levels >= $req[31]) {
					$lablevel += $Levels;
				}
			}
		} else {
			$lablevel = $planet->getLevel('laboratory');
		}

		$time /= ($lablevel + 1) * 2;
		$time *= $planet->user->bonusValue('time_research');

		return max(1, $time);
	}
}
