<?php

namespace Xnova\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Xnova\Missions\Mission;
use Xnova\Models;
use Xnova\Vars;

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

		$missionObjPattern = [
			1	=> 'Attack',
			2   => 'ACS',
			3   => 'Transport',
			4   => 'Stay',
			5   => 'StayAlly',
			6   => 'Spy',
			7   => 'Colonisation',
			8   => 'Recycling',
			9   => 'Destruction',
			10  => 'CreateBase',
			15  => 'Expedition',
			20  => 'Rak',
		];

		$totalRuns = 1;

		while ($totalRuns < MAX_RUNS) {
			$_fleets = Models\Fleet::query()
				->where(function (Builder $query) {
					$query->where('start_time', '<=', time())
						->where('mess', 0);
				})
				->orWhere(function (Builder $query) {
					$query->where('end_stay', '<=', time())
						->where('mess', '!=', 0)
						->where('end_stay', '!=', 0);
				})
				->orWhere(function (Builder $query) {
					$query->where('end_time', '<=', time())
						->where('mess', '!=', 0);
				})
				->orderBy('update_time', 'asc')
				->limit(10)
				->get();

			if ($_fleets->count()) {
				/** @var Models\Fleet $fleetRow */
				foreach ($_fleets as $fleetRow) {
					if (!isset($missionObjPattern[$fleetRow->mission])) {
						$fleetRow->delete();

						continue;
					}

					$missionName = $missionObjPattern[$fleetRow->mission];

					$missionName = '\Xnova\Missions\\' . $missionName;

					/** @var $mission Mission */
					$mission = new $missionName($fleetRow);

					if ($fleetRow->mess == 0 && $fleetRow->start_time <= time()) {
						$mission->TargetEvent();
					} elseif ($fleetRow->mess == 3 && $fleetRow->end_stay <= time()) {
						$mission->EndStayEvent();
					} elseif ($fleetRow->mess == 1 && $fleetRow->end_time <= time()) {
						$mission->ReturnEvent();
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
