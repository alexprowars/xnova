<?php

namespace App\Engine\Fleet\CombatEngine\Core;

enum BattleResult: int
{
	case WIN = 1;
	case LOSE = -1;
	case DRAW = 0;
}
