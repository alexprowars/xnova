<?php

namespace App\Factories;

use App\Models\Planet;
use App\Services\PlanetService;

class PlanetServiceFactory
{
	/**
	 * @var array<int, PlanetService>
	 */
	protected array $instances = [];

	public function make(Planet $planet): PlanetService
	{
		if (!isset($this->instancesById[$planet->id])) {
			$service = resolve(PlanetService::class, [
				'planet' => $planet,
			]);

			$this->instances[$planet->id] = $service;
		}

		return $this->instances[$planet->id];
	}
}
