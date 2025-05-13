<?php

namespace App\Engine\CombatEngine\Models;

class Ship extends ShipType
{
	public function getType()
	{
		return 'Ship';
	}

	public function getRepairProb()
	{
		return ($this->repairProb > 0 ? $this->repairProb : config('battle.SHIP_REPAIR_PROB'));
	}
}
