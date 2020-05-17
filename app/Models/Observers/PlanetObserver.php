<?php

namespace Xnova\Models\Observers;

use Xnova\Planet;

class PlanetObserver
{
	public function saved(Planet $model)
	{
		$model->_afterUpdateBuildings();
		$model->_afterUpdateUnits();
	}
}
