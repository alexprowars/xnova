<?php

namespace App\Engine\Entity;

use App\Engine\Vars;

class Research extends Entity
{
	protected function getBasePrice(): array
	{
		$cost  = parent::getBasePrice();
		$price = Vars::getItemPrice($this->entityId);

		return array_map(
			fn ($value) => floor($value * (($price['factor'] ?? 1) ** $this->level)),
			$cost
		);
	}

	public function getTime(): int
	{
		$networkLevel = $this->planet->network_level ?? [];

		if (!empty($networkLevel)) {
			$lablevel = 0;

			foreach ($networkLevel as $level) {
				$req = Vars::getItemRequirements($this->entityId);

				if (!isset($req[31]) || $level >= $req[31]) {
					$lablevel += $level;
				}
			}
		} else {
			$lablevel = $this->planet->getLevel('laboratory');
		}

		$time  = parent::getTime();
		$time /= ($lablevel + 1) * 2;
		$time *= $this->planet->user->bonus('time_research');

		return (int) max(1, $time);
	}
}
