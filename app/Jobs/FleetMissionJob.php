<?php

namespace App\Jobs;

use App\Engine\Fleet\MissionFactory;
use App\Engine\Fleet\Missions\Mission;
use App\Models\Fleet;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\WithoutRelations;

class FleetMissionJob implements ShouldQueue, ShouldBeUnique
{
	use Queueable;

	public function __construct(#[WithoutRelations] public Fleet $fleet)
	{
	}

	public function handle(): void
	{
		/** @var class-string<Mission> $mission */
		$mission = MissionFactory::getMission($this->fleet->mission);
		$mission = new $mission($this->fleet);

		if ($this->fleet->mess == 0 && $this->fleet->start_date->isNowOrPast()) {
			$mission->targetEvent();
		}

		if ($this->fleet->mess == 3 && $this->fleet->end_stay->isNowOrPast()) {
			$mission->endStayEvent();
		}

		if ($this->fleet->mess == 1 && $this->fleet->end_date->isNowOrPast()) {
			$mission->returnEvent();
		}
	}

	public function uniqueId(): string
	{
		return (string) $this->fleet->id;
	}
}
