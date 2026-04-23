<?php

namespace App\Engine\Ai;

enum StrategyType: string
{
	case ECONOMY  = 'economy';
	case MILITARY = 'military';
	case BALANCED = 'balanced';
}
