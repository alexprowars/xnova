<?php

namespace App\Engine\Ai\Fleet;

use App\Engine\Ai\Runtime\RunContext;
use App\Engine\Coordinates;
use App\Engine\Fleet\FleetSend;
use App\Engine\Fleet\MissionType;
use App\Exceptions\Exception;
use App\Models\Ai;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Log;

class FleetDispatcher
{
	public function __construct(private Planet $planet, private Ai $ai, private RunContext $context)
	{
	}

	public function send(
		Coordinates $target,
		MissionType $mission,
		array $ships,
		array $resources = [],
		?Closure $validate = null,
		?Closure $onSent = null,
		int $speed = 10,
	): bool
	{
		if (empty($ships)) {
			return false;
		}

		$origin = $this->planet->coordinates;

		try {
			$fleet = $this->planet->getConnection()->transaction(function () use (
				$origin,
				$target,
				$mission,
				$ships,
				$resources,
				$validate,
				$onSent,
				$speed,
			): ?Fleet {
				$user = User::query()->lockForUpdate()->find($this->ai->user_id);
				$this->planet->refreshForUpdate();

				if (!$user || $user->isVacation() || $user->blocked_at || $this->planet->trashed()
					|| $this->planet->destroyed_at
					|| $this->planet->user_id !== $user->id
					|| !$origin->isSame($this->planet->coordinates)) {
					return null;
				}

				$this->planet->setRelation('user', $user);
				$this->planet->setRelation('entities', $this->planet->entities()->lockForUpdate()->get());
				$this->planet->getProduction()->reset();

				if ($validate && !$validate()) {
					return null;
				}

				$flight = new FlightPlan($this->planet, $target, $ships, $speed);

				if ($flight->capacity < array_sum($resources)
					|| $flight->fuel + ($resources['deuterium'] ?? 0) > $this->planet->deuterium
					|| $flight->duration > FlightPlan::maxDuration($mission)) {
					return null;
				}

				$sender = new FleetSend($this->planet, $target, $mission);
				$sender->setFleets($ships);
				$sender->setFleetSpeed($speed);
				$sender->setResources($resources);
				$fleet = $sender->send();

				if ($onSent) {
					$onSent();
				}

				$this->planet->getConnection()->afterCommit(fn() => $this->context->recordFleet($fleet));

				return $fleet;
			});

			if (!$fleet) {
				return false;
			}

			if (config('ai.log_decisions', true)) {
				Log::info('ai.fleet', [
					'user_id' => $this->planet->user_id,
					'planet_id' => $this->planet->id,
					'fleet_id' => $fleet->id,
					'mission' => $mission->name,
					'target' => $target->toArray(),
				]);
			}

			return true;
		} catch (Exception $exception) {
			// Проверка миссии могла отклонить полёт после изменения игрового состояния.
			$this->planet->refresh();
			$this->planet->load('entities', 'user');
			$this->planet->getProduction()->reset();
			Log::debug('ai.fleet_rejected', [
				'user_id' => $this->planet->user_id,
				'mission' => $mission->name,
				'reason' => $exception->getMessage(),
			]);

			return false;
		}
	}
}
