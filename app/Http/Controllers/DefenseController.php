<?php

namespace App\Http\Controllers;

class DefenseController extends ShipyardController
{
	protected $mode = 'defense';

	public function index()
	{
		if ($this->planet->planet_type == 5) {
			$this->user->setOption('only_available', true);
		}

		return parent::index();
	}
}
