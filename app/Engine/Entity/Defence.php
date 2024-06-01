<?php

namespace App\Engine\Entity;

class Defence extends Unit
{
	public function getTime(): int
	{
		$time = parent::getTime();
		$time *= $this->planet->user->bonus('time_defence');

		return (int) max(1, floor($time));
	}
}
