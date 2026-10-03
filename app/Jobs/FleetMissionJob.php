<?php

namespace App\Jobs;

use App\Engine\Fleet\MissionFactory;
use App\Engine\Fleet\Missions\Mission;
use App\Models\Fleet;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class FleetMissionJob implements ShouldQueue, ShouldBeUnique
{
	use Queueable;

	public function __construct(public int $fleetId)
	{
	}

	public function handle(): void
	{
		DB::transaction(function () {
			$fleet = Fleet::query()->find($this->fleetId);

			if (!$fleet) {
				return;
			}

			/** @var class-string<Mission> $mission */
			$mission = MissionFactory::getMission($fleet->mission);
			$mission = new $mission($fleet);

			if ($fleet->mess == 0 && $fleet->start_date->isNowOrPast()) {
				$mission->targetEvent();
			}

			if ($fleet->mess == 3 && $fleet->end_stay->isNowOrPast()) {
				$mission->endStayEvent();
			}

			if ($fleet->mess == 1 && $fleet->end_date->isNowOrPast()) {
				$mission->returnEvent();
			}
		}, 5);
	}

	public function uniqueId(): string
	{
		return (string) $this->fleetId;
	}
}
