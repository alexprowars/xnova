<?php

namespace App\Battle\Models;

class Ship extends ShipType
{
	public function getType ()
	{
		return 'Ship';
	}

	public function getRepairProb()
	{
		return ($this->repairProb > 0 ? $this->repairProb : SHIP_REPAIR_PROB);
	}
}

?>