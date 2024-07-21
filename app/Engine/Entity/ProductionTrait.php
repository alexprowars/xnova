<?php

namespace App\Engine\Entity;

use App\Engine\Resources;
use App\Engine\Enums\Resources as ResourcesEnum;
use App\Engine\Vars;

/**
 * @mixin Entity
 */
trait ProductionTrait
{
	public function getProduction(?int $factor = null): Resources
	{
		if ($factor === null) {
			$factor = $this->planet?->entities->where('entity_id', $this->entityId)
				->first()?->factor ?? 10;
		}

		$factor = min(max($factor, 0), 10);

		$production = Vars::getBuildProduction($this->entityId);

		if (!$production) {
			return new Resources();
		}

		$planet = $this->planet;
		$user = $planet->user;

		/** @noinspection PhpUnusedLocalVariableInspection */
		$energyTech = $user->getTechLevel('energy');
		/** @noinspection PhpUnusedLocalVariableInspection */
		$BuildTemp = $planet->temp_max;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$BuildLevel = $this->level;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$BuildLevelFactor = $factor;

		$return = [
			ResourcesEnum::ENERGY->value => 0,
		];

		foreach (Vars::getResources() as $res) {
			$return[$res] = 0;

			if (isset($production[$res])) {
				$return[$res] = floor(eval($production[$res]) * config('game.resource_multiplier') * $user->bonus($res));
			}
		}

		if (isset($production[ResourcesEnum::ENERGY->value])) {
			$energy = floor(eval($production[ResourcesEnum::ENERGY->value]));

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
