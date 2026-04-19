<?php

namespace App\Engine\Entity;

use App\Engine\Contracts\EntityUnitInterface;
use App\Engine\Objects\DefenceObject;

/**
 * @extends Entity<DefenceObject>
 */
class Defence extends Entity implements EntityUnitInterface
{
	use Unit;

	public function getTime(): int
	{
		$time = $this->getBaseTime();
		$time *= $this->planet->user->bonus('time_defence');

		return max(1, (int) floor($time));
	}
}
