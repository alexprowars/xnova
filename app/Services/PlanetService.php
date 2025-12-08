<?php

namespace App\Services;

use App\Engine\Enums\PlanetType;
use App\Facades\Vars;
use App\Models\Planet;
use App\Models\User;

class PlanetService
{
	public function __construct(protected Planet $planet)
	{
	}

	public function getPlanet(): Planet
	{
		return $this->planet;
	}

	public function getUser(): User
	{
		return $this->planet->user;
	}

	/**
		 * @return array<int>
		 */
	public function getResearchNetworkLabLevel(): array
	{
		$list = [
			$this->planet->getLevel('laboratory'),
		];

		$irnLevel = $this->planet->user->getTechLevel('intergalactic');

		if ($irnLevel > 0) {
			$items = $this->planet->user->planets()
				->select(['entities'])
				->where('planet_type', PlanetType::PLANET)
				->whereKeyNot($this->planet)
				->whereNull('destroyed_at')
				->get()
				->map(fn(Planet $planet) => $planet->entities->getByEntityId(Vars::getIdByName('laboratory'))->level)
				->filter(fn(int $level) => $level > 0)
				->sortDesc()
				->take($irnLevel);

			foreach ($items as $lvl) {
				$list[] = $lvl;
			}
		}

		return $list;
	}

	public function isAvailableJumpGate(): bool
	{
		return ($this->planet->planet_type == PlanetType::MOON || $this->planet->planet_type == PlanetType::MILITARY_BASE) && $this->planet->getLevel('jumpgate') > 0;
	}

	public function getNextJumpTime(): int
	{
		if (!$this->planet->last_jump_time) {
			return 0;
		}

		$jumpGate = $this->planet->getLevel('jumpgate');

		if ($jumpGate > 0) {
			$waitTime = 3600 * (1 / $jumpGate);
			$jumpDiff = (int) $this->planet->last_jump_time->diffInSeconds(absolute: true);

			if ($jumpDiff < $waitTime) {
				return $waitTime - $jumpDiff;
			}
		}

		return 0;
	}
}
