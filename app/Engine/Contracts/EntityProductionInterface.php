<?php

namespace App\Engine\Contracts;

use App\Engine\Resources;

interface EntityProductionInterface
{
	public function getProduction(?int $factor = null): Resources;
}
