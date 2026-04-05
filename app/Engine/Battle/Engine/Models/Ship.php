<?php

namespace App\Engine\Battle\Engine\Models;

class Ship extends ShipType
{
	public function getType(): string
	{
		return 'Ship';
	}

	public function getRepairProb(): int
	{
		return ($this->repairProb > 0 ? $this->repairProb : config('battle.SHIP_REPAIR_PROB', 0));
	}
}
