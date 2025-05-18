<?php

namespace App\Engine\Contracts;

use App\Engine\Enums\Resources;

interface EntityBuildingInterface
{
	/**
	 * @return array<value-of<Resources>, int>
	 */
	public function getDestroyPrice(): array;
}
