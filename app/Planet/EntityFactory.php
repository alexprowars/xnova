<?php

namespace Xnova\Planet;

use Xnova\Entity\Research;
use Xnova\Planet\Entity\Building;
use Xnova\Planet\Entity\Context;
use Xnova\Exceptions\Exception;
use Xnova\Planet\Contracts\PlanetEntityInterface;
use Xnova\Planet\Entity\Defence;
use Xnova\Planet\Entity\Ship;
use Xnova\Vars;

class EntityFactory
{
	public static function create(int $entityId, int $level = 1, ?Context $context = null): PlanetEntityInterface
	{
		$entityType = Vars::getItemType($entityId);

		switch ($entityType) {
			case Vars::ITEM_TYPE_BUILING:
				return new Building($entityId, $level, $context);
			case Vars::ITEM_TYPE_TECH:
				return new Research($entityId, $level, $context);
			case Vars::ITEM_TYPE_FLEET:
				return new Ship($entityId, $level, $context);
			case Vars::ITEM_TYPE_DEFENSE:
				return new Defence($entityId, $level, $context);
			default:
				throw new Exception('unknown entity');
		}
	}
}
