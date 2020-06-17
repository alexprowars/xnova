<?php

namespace Xnova\Entity;

use Xnova\Exceptions\Exception;
use Xnova\Planet\Entity\BaseEntity;
use Xnova\Vars;

class Research extends BaseEntity
{
	public function getTime(): int
	{
		$time = parent::getTime();

		$user = $this->getPlanet()->getUser();
		$planet = $this->getPlanet();

		if (isset($planet->spaceLabs) && is_array($planet->spaceLabs) && count($planet->spaceLabs)) {
			$lablevel = 0;

			foreach ($planet->spaceLabs as $Levels) {
				$req = Vars::getItemRequirements($this->entity_id);

				if (!isset($req[31]) || $Levels >= $req[31]) {
					$lablevel += $Levels;
				}
			}
		} else {
			$lablevel = $planet->getLevel('laboratory');
		}

		$time /= ($lablevel + 1) * 2;
		$time *= $user->bonusValue('time_research');

		return max(1, $time);
	}
}
