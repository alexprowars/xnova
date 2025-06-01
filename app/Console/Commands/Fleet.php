<?php

namespace App\Console\Commands;

use App\Engine\Fleet\MissionFactory;
use App\Engine\Fleet\Missions\Mission;
use App\Models;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class Fleet extends Command
{
	protected $signature = 'game:fleet';
	protected $description = '';

	public function handle()
	{
		if (function_exists('sys_getloadavg')) {
			$load = sys_getloadavg();

			if ($load[0] > 1.5) {
				die('Server too busy. Please try again later.');
			}
		}

		define('MAX_RUNS', 12);
		define('TIME_LIMIT', 60);

		$totalRuns = 1;

		while ($totalRuns < MAX_RUNS) {
			$fleets = Models\Fleet::query()
				->where(function (Builder $query) {
					$query->whereNowOrPast('start_date')
						->where('mess', 0);
				})
				->orWhere(function (Builder $query) {
					$query->whereNotNull('end_stay')
						->whereNowOrPast('end_stay')
						->whereNot('mess', 0);
				})
				->orWhere(function (Builder $query) {
					$query->whereNowOrPast('end_date')
						->whereNot('mess', 0);
				})
				->orderBy('updated_at')
				->limit(10)
				->get();

			foreach ($fleets as $fleet) {
				if (!$fleet->mission?->name) {
					$fleet->delete();

					continue;
				}

				/** @var class-string<Mission> $mission */
				$mission = MissionFactory::getMission($fleet->mission);
				$mission = new $mission($fleet);

				if ($fleet->mess == 0 && $fleet->start_date->isNowOrPast()) {
					$mission->targetEvent();
				} elseif ($fleet->mess == 3 && $fleet->end_stay->isNowOrPast()) {
					$mission->endStayEvent();
				} elseif ($fleet->mess == 1 && $fleet->end_date->isNowOrPast()) {
					$mission->returnEvent();
				}

				unset($mission);
			}

			$totalRuns++;
			sleep(TIME_LIMIT / MAX_RUNS);
		}

		echo "all fleet updated\n";
	}
}
