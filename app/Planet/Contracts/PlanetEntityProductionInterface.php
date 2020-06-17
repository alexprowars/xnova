<?php

namespace Xnova\Planet\Contracts;

use Xnova\Planet\Resources;

interface PlanetEntityProductionInterface
{
	public function getProduction(?int $factor = null): Resources;
}
