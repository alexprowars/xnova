<?php

namespace App\Engine\Ai\Combat;

use App\Engine\Ai\Runtime\RunContext;
use App\Engine\Enums\FleetDirection;
use App\Engine\Fleet\MissionType;
use App\Models\Fleet;
use App\Models\Planet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class ThreatAssessment
{
	public function __construct(private Planet $planet, private RunContext $context)
	{
	}

	/** @return Builder<Fleet> */
	public static function incoming(?int $minutes = null): Builder
	{
		return Fleet::query()
			->where('mess', 0)
			->whereIn('mission', [MissionType::Attack, MissionType::Assault, MissionType::Destruction])
			->where('start_date', '<=', now()->addMinutes($minutes ?? (int) config('ai.threat_minutes', 10)));
	}

	/** @return Collection<int, Fleet> */
	public function fleets(): Collection
	{
		return self::incoming()
			->coordinates(FleetDirection::END, $this->planet->coordinates)
			->whereNot('user_id', $this->planet->user_id)
			->orderBy('start_date')
			->get();
	}

	/** @param Collection<int, Fleet> $fleets */
	public function shouldEvacuate(Collection $fleets): bool
	{
		if ($fleets->isEmpty()) {
			return false;
		}

		// Такой же порог видимости состава, как у игрока в обзоре флотов.
		if ($this->planet->user->getTechLevel('spy') < 8) {
			return true;
		}

		$attackers = [];

		foreach ($fleets as $fleet) {
			foreach ($fleet->entities as $entity) {
				$attackers[$fleet->user_id][$entity->id] = ($attackers[$fleet->user_id][$entity->id] ?? 0) + $entity->count;
			}
		}

		try {
			$safe = new BattleForecast($this->context)->canDefend($this->planet, $attackers);
		} catch (Throwable $exception) {
			Log::warning('ai.defense_forecast_failed', ['user_id' => $this->planet->user_id, 'exception' => $exception]);
			$safe = false;
		}
		$this->context->decision($this->planet->user_id, $safe ? 'hold_position' : 'evacuate');

		return !$safe;
	}
}
