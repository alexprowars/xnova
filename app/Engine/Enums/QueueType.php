<?php

namespace App\Engine\Enums;

enum QueueType: string
{
	case BUILDING = 'build';
	case RESEARCH = 'tech';
	case SHIPYARD = 'unit';
}
