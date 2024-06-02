<?php

namespace App\Engine\Enums;

enum Resources: string
{
	case METAL = 'metal';
	case CRYSTAL = 'crystal';
	case DEUTERIUM = 'deuterium';
	case ENERGY = 'energy';

	public function title(): string
	{
		return __('main.res.' . $this->value);
	}
}
