<?php

namespace App\Models\Observers;

use App\Planet;

class PlanetObserver
{
	public function saved(Planet $model)
	{
		$model->afterUpdate();
	}
}
