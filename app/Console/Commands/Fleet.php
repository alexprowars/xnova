<?php

namespace App\Console\Commands;

use App\Engine\Fleet\Missions\Mission;
use App\Engine\Fleet\Mission as MissionEnum;
use App\Engine\Vars;
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

		Vars::init();

		include_once(config_path('battle.php'));

		define('MAX_RUNS', 12);
		define('TIME_LIMIT', 60);

		$totalRuns = 1;

		while ($totalRuns < MAX_RUNS) {
			$_fleets = Models\Fleet::query()
				->where(function (Builder $query) {
					$query->where('start_time', '<=', now())
						->where('mess', 0);
				})
				->orWhere(function (Builder $query) {
					$query->where('end_stay', '<=', now())
						->where('mess', '!=', 0)
						->where('end_stay', '!=', 0);
				})
				->orWhere(function (Builder $query) {
					$query->where('end_time', '<=', now())
						->where('mess', '!=', 0);
				})
				->orderBy('updated_at')
				->limit(10)
				->get();

			if ($_fleets->count()) {
				foreach ($_fleets as $fleetRow) {
					if (!$missionName = $fleetRow->mission?->name) {
						$fleetRow->delete();

						continue;
					}

					$missionName = '\App\Engine\Fleet\Missions\\' . $missionName;

					/** @var $mission Mission */
					$mission = new $missionName($fleetRow);

					if ($fleetRow->mess == 0 && $fleetRow->start_time->timestamp <= time()) {
						$mission->targetEvent();
					} elseif ($fleetRow->mess == 3 && $fleetRow->end_stay->timestamp <= time()) {
						$mission->endStayEvent();
					} elseif ($fleetRow->mess == 1 && $fleetRow->end_time->timestamp <= time()) {
						$mission->returnEvent();
					}

					unset($mission);
				}
			}

			$totalRuns++;
			sleep(TIME_LIMIT / MAX_RUNS);
		}

		echo "all fleet updated\n";
	}
}
