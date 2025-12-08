<?php

namespace App\Engine\Entity;

use App\Engine\Game;
use App\Engine\Resources;
use App\Engine\Enums\Resources as ResourcesEnum;
use App\Facades\Vars;

trait ProductionTrait
{
	public function getProduction(?int $factor = null): Resources
	{
		if ($factor === null) {
			$factor = $this->planet?->entities->getByEntityId($this->entityId)->factor ?? 10;
		}

		$factor = min(max($factor, 0), 10);

		$production = Vars::getBuildProduction($this->entityId);

		if (!$production) {
			return new Resources();
		}

		$planet = $this->planet;
		$user = $planet->user;

		$return = [
			ResourcesEnum::ENERGY->value => 0,
		];

		foreach (Vars::getResources() as $res) {
			$return[$res] = 0;

			if (isset($production[$res])) {
				$return[$res] = floor(call_user_func($production[$res], $this->level, $factor, $planet) * Game::getSpeed('mine') * $user->bonus($res));
			}
		}

		if (isset($production[ResourcesEnum::ENERGY->value])) {
			$energy = floor(call_user_func($production[ResourcesEnum::ENERGY->value], $this->level, $factor, $planet));

			if ($this->entityId < 4) {
				$return[ResourcesEnum::ENERGY->value] = $energy;
			} elseif ($this->entityId == 4 || $this->entityId == 12) {
				$return[ResourcesEnum::ENERGY->value] = floor($energy * $user->bonus('energy'));
			} elseif ($this->entityId == 212) {
				$return[ResourcesEnum::ENERGY->value] = floor($energy * $user->bonus('solar'));
			}
		}

		return new Resources($return);
	}
}
