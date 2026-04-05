<?php

namespace App\Engine\Battle\Engine;

enum BattleResult: int
{
	case WIN = 1;
	case LOSE = -1;
	case DRAW = 0;
}
