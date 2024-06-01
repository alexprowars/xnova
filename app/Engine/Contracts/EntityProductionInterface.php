<?php

namespace App\Engine\Contracts;

use App\Planet\Resources;

interface EntityProductionInterface
{
	public function getProduction(int $factor): Resources;
}
