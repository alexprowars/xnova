<?php

namespace App\Engine\CombatEngine\Models;

class Defense extends ShipType
{
	public function getType()
	{
		return 'Defense';
	}

	public function getRepairProb()
	{
		return ($this->repairProb > 0 ? $this->repairProb : config('battle.DEFENSE_REPAIR_PROB'));
	}
}
