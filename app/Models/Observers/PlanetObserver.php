<?php

namespace App\Models\Observers;

use App\Models\Planet;

class PlanetObserver
{
	public function saved(Planet $model)
	{
		$model->afterUpdate();
	}
}
