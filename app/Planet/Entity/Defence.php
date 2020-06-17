<?php

namespace Xnova\Planet\Entity;

use Xnova\Exceptions\Exception;
use Xnova\Planet\Entity\Unit;
use Xnova\Vars;

class Defence extends Unit
{
	public function getTime(): int
	{
		$user = $this->getPlanet()->getUser();

		$time = parent::getTime();
		$time *= $user->bonusValue('time_defence');

		return max(1, floor($time));
	}
}
