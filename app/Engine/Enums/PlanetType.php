<?php

namespace App\Engine\Enums;

enum PlanetType: int
{
	case PLANET = 1;
	case DEBRIS = 2;
	case MOON = 3;
	case MILITARY_BASE = 5;

	public function title(): string
	{
		return __('main.type_planet.' . $this->value);
	}
}
