<?php

namespace App\Entity;

use App\Exceptions\Exception;
use App\Planet\Entity\BaseEntity;
use App\Vars;

class Research extends BaseEntity
{
	public function getTime(): int
	{
		$time = parent::getTime();

		$planet = $this->planet;

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
		$time *= $planet->user->bonusValue('time_research');

		return max(1, $time);
	}
}
