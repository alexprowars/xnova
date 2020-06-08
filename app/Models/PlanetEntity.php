<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;
use Xnova\Planet\Entity\Context;
use Xnova\Planet\Resources;
use Xnova\Vars;

/**
 * @property int $id
 * @property int $planet_id
 * @property int $entity_id
 * @property int $amount
 * @property int $factor
 */
class PlanetEntity extends Model
{
	public $timestamps = false;

	public function getProduction(Context $context, ?int $factor = null): Resources
	{
		if (!$factor) {
			$factor = $this->factor;
		}

		$factor = min(max($factor, 0), 10);

		$return = [];

		foreach (Vars::getResources() as $res) {
			$return[$res] = 0;
		}

		$return[Resources::ENERGY] = 0;

		$production = Vars::getBuildProduction($this->entity_id);

		if (!$production) {
			return new Resources($return);
		}

		$user = $context->getUser();
		$planet = $context->getPlanet();

		/** @noinspection PhpUnusedLocalVariableInspection */
		$energyTech = $user->getTechLevel('energy');
		/** @noinspection PhpUnusedLocalVariableInspection */
		$BuildTemp = $planet->temp_max;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$BuildLevel = $this->amount;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$BuildLevelFactor = $this->$factor;

		foreach (Vars::getResources() as $res) {
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
