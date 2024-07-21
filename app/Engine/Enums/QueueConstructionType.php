<?php

namespace App\Engine\Enums;

enum QueueConstructionType: string
{
	case BUILDING = 'build';
	case DESTROY = 'destroy';
}
