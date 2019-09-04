<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Lang;
use Friday\Core\Modules;
use Friday\Core\Options;
use Xnova\Missions\Mission;
use Xnova\UpdateStatistics;
use Xnova\Vars;
use Xnova\Models;

class UpdateTask extends ApplicationTask
{
	public function onlineAction ()
	{
		Modules::init('xnova');

		$online = $this->db->fetchColumn("SELECT COUNT(*) as online FROM game_users WHERE onlinetime > '" . (time() - $this->config->game->onlinetime * 60) . "'");

		Options::set('users_online', $online);

		echo $online." users online\n";
	}

	public function statAction ()
	{
		Modules::init('xnova');
		Lang::setLang($this->config->app->language, 'xnova');

		Vars::init();

		$start = microtime(true);

		$statUpdate = new UpdateStatistics();

		//$statUpdate->inactiveUsers();
		$statUpdate->deleteUsers();
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

			if ($load[0] > 1.5)
				die('Server too busy. Please try again later.');
		}

		Modules::init('xnova');
		Lang::setLang($this->config->app->language, 'xnova');
		Lang::includeLang('fleet_engine', 'xnova');

		Vars::init();

		include_once(ROOT_PATH."/app/config/battle.php");

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

		Lang::includeLang("fleet_engine", 'xnova');

		if (function_exists('sys_getloadavg'))
		{
			$load = sys_getloadavg();

			if ($load[0] > 3)
				die('Server too busy. Please try again later.');
		}

		$totalRuns = 1;

		while ($totalRuns < MAX_RUNS)
		{
			$_fleets = Models\Fleet::find([
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

					$missionName = 'Xnova\Missions\\'.$missionName;

					/**
					 * @var $mission Mission
					 */
					$mission = new $missionName($fleetRow);

					if ($fleetRow->mess == 0 && $fleetRow->start_time <= time())
						$mission->TargetEvent();

					elseif ($fleetRow->mess == 3 && $fleetRow->end_stay <= time())
						$mission->EndStayEvent();

					elseif ($fleetRow->mess == 1 && $fleetRow->end_time <= time())
						$mission->ReturnEvent();

					unset($mission);
				}
			}

			$totalRuns++;
			sleep(TIME_LIMIT / MAX_RUNS);
		}

		echo "all fleet updated\n";
	}

	public function queueAction ()
	{
		Modules::init('xnova');
		Lang::setLang($this->config->app->language, 'xnova');

		Vars::init();

		define('MAX_RUNS', 15);
		define('TIME_LIMIT', 60);

		if (function_exists('sys_getloadavg'))
		{
			$load = sys_getloadavg();

			if ($load[0] > 3)
				die('Server too busy. Please try again later.');
		}

		$totalRuns = 1;

		while ($totalRuns < MAX_RUNS)
		{
			$items = Models\Queue::find([
				'conditions' => 'time > 0 AND time_end <= :time: AND type != :type:',
				'group' => 'user_id, planet_id',
				'bind' => [
					'time' => time() + 10,
					'type' => 'unit'
				],
				'order' => 'id ASC'
			]);

			foreach ($items as $item)
			{
				try
				{
					$user = Models\User::findFirst((int) $item->user_id);

					$planet = Models\Planet::findFirst((int) $item->planet_id);

					if ($planet)
						$planet->assignUser($user);

					if (!$user || !$planet)
					{
						throw new Exception('Cron::update::queueAction::user or planet not found: '.$item->user_id.'-'.$item->planet_id);
					}

					$queueManager = new \Xnova\Queue($user, $planet);
					$queueManager->update();
				}
				catch (Exception $e)
				{
					file_put_contents(ROOT_PATH.'/php_errors.log', "\n\n".$e->getMessage()."\n\n", FILE_APPEND);

					echo $e->getMessage();
				}
			}

			$totalRuns++;
			sleep(TIME_LIMIT / MAX_RUNS);
		}

		echo "all queue updated\n";
	}
}