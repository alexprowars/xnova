<?php

namespace App\Engine\Fleet\CombatEngine\Models;

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
