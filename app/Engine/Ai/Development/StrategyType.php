<?php

namespace App\Engine\Ai\Development;

enum StrategyType: string
{
	case ECONOMY  = 'economy';
	case MILITARY = 'military';
	case BALANCED = 'balanced';

	public function getLabel(): string
	{
		return __('admin.strategy_' . $this->value);
	}
}
