<?php

namespace Xnova\Entity;

use Xnova\Exceptions\Exception;
use Xnova\Vars;

class Research extends Base
{
	public function __construct ($elementId, ?int $level = null, $context = null)
	{
		if (Vars::getItemType($elementId) !== Vars::ITEM_TYPE_TECH)
			throw new Exception('wrong entity type');

		if ($level === null)
			$level = ($context ? $context : $this->getContext())->getUser()->getTechLevel($elementId);

		parent::__construct($elementId, $level, $context);
	}

	public function getTime (): int
	{
		$time = parent::getTime();

		$user = $this->getContext()->getUser();
		$planet = $this->getContext()->getPlanet();

		if (isset($planet->spaceLabs) && is_array($planet->spaceLabs) && count($planet->spaceLabs))
		{
			$lablevel = 0;

			foreach ($planet->spaceLabs as $Levels)
			{
				$req = Vars::getItemRequirements($this->elementId);

				if (!isset($req[31]) || $Levels >= $req[31])
					$lablevel += $Levels;
			}
		}
		else
			$lablevel = $planet->getBuildLevel('laboratory');

		$time /= ($lablevel + 1) * 2;
		$time *= $user->bonusValue('time_research');

		return max(1, $time);
	}
}