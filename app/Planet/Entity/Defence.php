<?php

namespace Xnova\Planet\Entity;

use Xnova\Exceptions\Exception;
use Xnova\Planet\Entity\Unit;
use Xnova\Vars;

class Defence extends Unit
{
	public function __construct($entityId, $count = 1, $context = null)
	{
		if (Vars::getItemType($entityId) !== Vars::ITEM_TYPE_DEFENSE) {
			throw new Exception('wrong entity type');
		}

		parent::__construct($entityId, $count, $context);
	}

	public function getTime(): int
	{
		$user = $this->getContext()->getUser();

		$time = parent::getTime();
		$time *= $user->bonusValue('time_defence');

		return max(1, floor($time));
	}
}
