<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Lang;
use App\Missions\Mission;
use App\UpdateStatistics;

class UpdateTask extends ApplicationTask
{
	public function onlineAction ()
	{
		$online = $this->db->fetchColumn("SELECT COUNT(*) as online FROM game_users WHERE onlinetime > '" . (time() - $this->config->game->onlinetime * 60) . "'");

		$this->game->updateConfig('users_online', $online);

		echo $online." users online\n";
	}

	public function statAction ()
	{
		$start = microtime(true);

		$statUpdate = new UpdateStatistics();

		$statUpdate->inactiveUsers();
		//$statUpdate->deleteUsers();
		$statUpdate->clearOldStats();
		$statUpdate->update();
		$statUpdate->addToLog();
		$statUpdate->clearGame();
		$statUpdate->buildRecordsCache();

		$end = microtime(true);

		echo "stats updated in ".($end - $start)." sec\n";
	}

	public function fleetAction ()
	{
		if (function_exists('sys_getloadavg'))
		{
			$load = sys_getloadavg();

			if ($load[0] > 3)
				die('Server too busy. Please try again later.');
		}

		define('MAX_RUNS', 12);
		define('TIME_LIMIT', 60);

		$missionObjPattern = [
			1	=> 'MissionCaseAttack',
			2   => 'MissionCaseACS',
			3   => 'MissionCaseTransport',
			4   => 'MissionCaseStay',
			5   => 'MissionCaseStayAlly',
			6   => 'MissionCaseSpy',
			7   => 'MissionCaseColonisation',
			8   => 'MissionCaseRecycling',
			9   => 'MissionCaseDestruction',
			10  => 'MissionCaseCreateBase',
			15  => 'MissionCaseExpedition',
			20  => 'MissionCaseRak'
		];

		Lang::includeLang("fleet_engine");

		$totalRuns = 1;

		while ($totalRuns < MAX_RUNS)
		{
			if (function_exists('sys_getloadavg'))
			{
				$load = sys_getloadavg();

				if ($load[0] > 3)
					die('Server too busy. Please try again later.');
			}

			/**
			 * @var $_fleets \App\Models\Fleet[]
			 */
			$_fleets = \App\Models\Fleet::find([
				'(start_time <= :time: AND mess = 0) OR (end_stay <= :time: AND mess != 1 AND end_stay != 0) OR (end_time < :time: AND mess != 0)',
				'bind' 	=> ['time' => time()],
				'order'	=> 'update_time asc',
				'limit'	=> 10
			]);

			if (count($_fleets) > 0)
			{
				foreach ($_fleets AS $fleetRow)
				{
					if (!isset($missionObjPattern[$fleetRow->mission]))
					{
						$fleetRow->delete();

						continue;
					}

					$missionName = $missionObjPattern[$fleetRow->mission];

					$missionName = 'App\Missions\\'.$missionName;

					/**
					 * @var $mission Mission
					 */
					$mission = new $missionName($fleetRow);

					if ($fleetRow->mess == 0 && $fleetRow->start_time <= time())
						$mission->TargetEvent();

					if ($fleetRow->mess == 3 && $fleetRow->end_stay <= time())
						$mission->EndStayEvent();

					if ($fleetRow->mess == 1 && $fleetRow->end_time <= time())
						$mission->ReturnEvent();

					unset($mission);
				}
			}

			$totalRuns++;
			sleep(TIME_LIMIT / MAX_RUNS);
		}

		echo "all fleet updated\n";
	}
}