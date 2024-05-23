<?php

namespace App\Planet\Entity;

use App\Planet\Resources;
use App\Vars;

/**
 * @mixin BaseEntity
 */
trait ProductionTrait
{
	public function getProduction(?int $factor = null): Resources
	{
		if ($factor === null) {
			$factor = $this->factor ?: 10;
		}

		$factor = min(max($factor, 0), 10);

		$production = Vars::getBuildProduction($this->entity_id);

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
		$BuildLevel = $this->amount;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$BuildLevelFactor = $factor;

		$return = [
			Resources::ENERGY => 0,
		];

		foreach (Vars::getResources() as $res) {
			$return[$res] = 0;

			if (isset($production[$res])) {
				$return[$res] = floor(eval($production[$res]) * config('settings.resource_multiplier') * $user->bonusValue($res));
			}
		}

		if (isset($production[Resources::ENERGY])) {
			$energy = floor(eval($production[Resources::ENERGY]));

			if ($this->entity_id < 4) {
				$return[Resources::ENERGY] = $energy;
			} elseif ($this->entity_id == 4 || $this->entity_id == 12) {
				$return[Resources::ENERGY] = floor($energy * $user->bonusValue('energy'));
			} elseif ($this->entity_id == 212) {
				$return[Resources::ENERGY] = floor($energy * $user->bonusValue('solar'));
			}
		}

		return new Resources($return);
	}
}
