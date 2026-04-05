<?php

namespace App\Engine\Battle\Engine\Models;

class Defense extends ShipType
{
	public function getType(): string
	{
		return 'Defense';
	}

	public function getRepairProb(): int
	{
		return ($this->repairProb > 0 ? $this->repairProb : config('battle.DEFENSE_REPAIR_PROB', 0));
	}
}
