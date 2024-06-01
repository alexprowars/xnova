<?php

namespace App\Engine\Entity;

class Defence extends Unit
{
	public function getTime(): int
	{
		$time = parent::getTime();
		$time *= $this->planet->user->bonusValue('time_defence');

		return max(1, floor($time));
	}
}
