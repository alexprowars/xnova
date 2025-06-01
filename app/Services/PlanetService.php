<?php

namespace App\Services;

use App\Engine\Enums\PlanetType;
use App\Facades\Vars;
use App\Models\Planet;
use App\Models\PlanetEntity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

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
		$list = [$this->planet->getLevel('laboratory')];

		$irnLevel = $this->planet->user->getTechLevel('intergalactic');

		if ($irnLevel > 0) {
			$items = PlanetEntity::query()
				->where('entity_id', Vars::getIdByName('laboratory'))
				->where('amount', '>', 0)
				->whereNot('planet_id', $this->planet->id)
				->whereHas('planet', function (Builder $query) {
					return $query->whereBelongsTo($this->planet->user)
						->where('planet_type', PlanetType::PLANET)
						->whereNull('destroyed_at');
				})
				->orderByDesc('amount')
				->limit($irnLevel)
				->pluck('amount');

			foreach ($items as $lvl) {
				$list[] = (int) $lvl;
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
