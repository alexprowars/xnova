<?php

namespace App\Http\Controllers;

use App\Engine\Enums\PlanetType;

class DefenseController extends ShipyardController
{
	protected string $mode = 'defense';

	public function index(): array
	{
		if ($this->planet->planet_type == PlanetType::MILITARY_BASE) {
			$this->user->setOption('only_available', true);
		}

		return parent::index();
	}
}
