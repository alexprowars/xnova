<?php

namespace App\Battle\Models;

class Defense extends ShipType
{
	public function getType ()
	{
		return 'Defense';
	}

	public function getRepairProb()
	{
		return DEFENSE_REPAIR_PROB;
	}
}

?>