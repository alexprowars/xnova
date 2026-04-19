<?php

namespace App\Engine\Objects;

use App\Engine\Enums\PlanetType;

class BuildingObject extends BaseObject
{
	public function hasAllowedBuild(PlanetType $planetType): bool
	{
		if (!isset($this->data['planet_type'])) {
			return false;
		}

		return in_array($planetType->value, $this->data['planet_type']);
	}

	public function hasExperience(): bool
	{
		return (bool) ($this->data['experience'] ?? false);
	}
}
