<?php

namespace App\Planet\Contracts;

use App\Planet\Resources;

interface PlanetEntityProductionInterface
{
	public function getProduction(?int $factor = null): Resources;
}
