<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Facades\Vars;
use Inertia\Inertia;

class DefenseController extends ShipyardController
{
	protected string $mode = 'defense';

	public function index()
	{
		if ($this->planet->planet_type == PlanetType::MILITARY_BASE) {
			$this->user->setOption('only_available', true);
		}

		$elements = Vars::getObjectsByType(ItemType::DEFENSE);

		return Inertia::render('Defense', [
			'items' => $this->getItems($elements),
		]);
	}
}
