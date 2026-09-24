<?php

namespace App\Engine\Ai;

use Filament\Support\Contracts\HasLabel;

enum StrategyType: string implements HasLabel
{
	case ECONOMY  = 'economy';
	case MILITARY = 'military';
	case BALANCED = 'balanced';

	public function getLabel(): string
	{
		return __('admin.ai.strategy_' . $this->value);
	}
}
