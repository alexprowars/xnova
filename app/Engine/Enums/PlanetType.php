<?php

namespace App\Engine\Enums;

use Filament\Support\Contracts\HasLabel;

enum PlanetType: int implements HasLabel
{
	case PLANET = 1;
	case DEBRIS = 2;
	case MOON = 3;
	case MILITARY_BASE = 5;

	public function title(): string
	{
		return __('main.type_planet.' . $this->value);
	}

	public function getLabel(): ?string
	{
		return $this->title();
	}
}
