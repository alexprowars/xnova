<?php

namespace App\Http\Controllers;

use App\Engine\Enums\PlanetType;

class DefenseController extends ShipyardController
{
	protected $mode = 'defense';

	public function index()
	{
		if ($this->planet->planet_type == PlanetType::MILITARY_BASE) {
			$this->user->setOption('only_available', true);
		}

		return parent::index();
	}
}
