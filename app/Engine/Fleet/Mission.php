<?php

namespace App\Engine\Fleet;

enum Mission: int
{
	case Attack = 1;
	case Assault = 2;
	case Transport = 3;
	case Stay = 4;
	case StayAlly = 5;
	case Spy = 6;
	case Colonization = 7;
	case Recycling = 8;
	case Destruction = 9;
	case CreateBase = 10;
	case Expedition = 15;
	case Rak = 20;

	public function title(): string
	{
		return __('main.type_mission.' . $this->value);
	}
}
