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
		return SHIP_REPAIR_PROB;
	}
}

?>