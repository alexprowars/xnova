<?php

namespace App\Planet\Entity;

use App\Exceptions\Exception;
use App\Planet\Entity\Unit;
use App\Vars;

class Defence extends Unit
{
	public function getTime(): int
	{
		$time = parent::getTime();
		$time *= $this->planet->user->bonusValue('time_defence');

		return max(1, floor($time));
	}
}
