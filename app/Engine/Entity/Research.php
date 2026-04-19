<?php

namespace App\Engine\Entity;

use App\Engine\Game;

class Research extends Entity
{
	protected function getBasePrice(): array
	{
		$cost  = parent::getBasePrice();
		$price = $this->object->getPrice();

		return array_map(
			fn (int $value) => (int) floor($value * (($price['factor'] ?? 1) ** $this->level)),
			$cost
		);
	}

	public function getTime(): int
	{
		$networkLevel = $this->planet->network_level ?? [];

		if (!empty($networkLevel)) {
			$lablevel = 0;

			foreach ($networkLevel as $level) {
				$req = $this->object->getRequeriments();

				if (!isset($req[31]) || $level >= $req[31]) {
					$lablevel += $level;
				}
			}
		} else {
			$lablevel = $this->planet->getLevel('laboratory');
		}

		$cost = $this->getBasePrice();
		$cost = $cost['metal'] + $cost['crystal'];

		$time = 3600 * ($cost) / (1000 * (1.0 + $lablevel));
		$time /= Game::getSpeed('build');
		$time *= $this->planet->user->bonus('time_research');

		return (int) max(1, $time);
	}
}
