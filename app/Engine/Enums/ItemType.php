<?php

namespace App\Engine\Enums;

enum ItemType: string
{
	case BUILDING = 'build';
	case TECH = 'tech';
	case FLEET = 'fleet';
	case DEFENSE = 'defense';
	case OFFICIER = 'officier';
}
