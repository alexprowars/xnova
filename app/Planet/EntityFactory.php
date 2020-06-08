<?php

namespace Xnova\Planet;

use Xnova\Planet\Entity\Context;
use Xnova\Exceptions\Exception;
use Xnova\Planet\Contracts\PlanetEntityInterface;
use Xnova\Vars;

class EntityFactory
{
	public static function create(int $entityId, int $level = 1, ?Context $context = null): PlanetEntityInterface
	{
		$entityType = Vars::getItemType($entityId);

		switch ($entityType) {
			case Vars::ITEM_TYPE_BUILING:
				$className = 'Building';
				break;
			case Vars::ITEM_TYPE_TECH:
				$className = 'Research';
				break;
			case Vars::ITEM_TYPE_FLEET:
				$className = 'Fleet';
				break;
			case Vars::ITEM_TYPE_DEFENSE:
				$className = 'Defence';
				break;
			default:
				throw new Exception('unknown entity');
		}

		$className = 'Xnova\Entity\\' . $className;

		return new $className($entityId, $level, $context);
	}
}
