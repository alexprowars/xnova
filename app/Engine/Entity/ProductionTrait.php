<?php

namespace App\Engine\Entity;

use App\Engine\Resources;
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
			Resources::ENERGY => 0,
		];

		foreach (Vars::getResources() as $res) {
			$return[$res] = 0;

			if (isset($production[$res])) {
				$return[$res] = floor(eval($production[$res]) * config('settings.resource_multiplier') * $user->bonus($res));
			}
		}

		if (isset($production[Resources::ENERGY])) {
			$energy = floor(eval($production[Resources::ENERGY]));

			if ($this->entityId < 4) {
				$return[Resources::ENERGY] = $energy;
			} elseif ($this->entityId == 4 || $this->entityId == 12) {
				$return[Resources::ENERGY] = floor($energy * $user->bonus('energy'));
			} elseif ($this->entityId == 212) {
				$return[Resources::ENERGY] = floor($energy * $user->bonus('solar'));
			}
		}

		return new Resources($return);
	}
}
