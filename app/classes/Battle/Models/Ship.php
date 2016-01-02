<?php

namespace App\Battle\Models;

class Ship extends ShipType
{
	public function getRepairProb()
	{
		return SHIP_REPAIR_PROB;
	}
}

?>