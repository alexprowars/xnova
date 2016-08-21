<?php

namespace Xnova\Battle\Models;

class Defense extends ShipType
{
	public function getType ()
	{
		return 'Defense';
	}

	public function getRepairProb()
	{
		return ($this->repairProb > 0 ? $this->repairProb : DEFENSE_REPAIR_PROB);
	}
}

?>