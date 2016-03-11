<?php

namespace App;

use App\Mail\PHPMailer;
use App\Models\User;
use Phalcon\Di\Injectable;

/**
 * Class UpdateStatistics
 * @package App
 * @property \App\Database db
 * @property \Phalcon\Config|\stdClass config
 * @property \App\Game game
 */
class UpdateStatistics extends Injectable
{
	private $maxinfos = [];
	public $start = 0;

	private $user;

	private $StatRace = [
		1 => ['count' => 0, 'total' => 0, 'fleet' => 0, 'tech' => 0, 'defs' => 0, 'build' => 0],
		2 => ['count' => 0, 'total' => 0, 'fleet' => 0, 'tech' => 0, 'defs' => 0, 'build' => 0],
		3 => ['count' => 0, 'total' => 0, 'fleet' => 0, 'tech' => 0, 'defs' => 0, 'build' => 0],
		4 => ['count' => 0, 'total' => 0, 'fleet' => 0, 'tech' => 0, 'defs' => 0, 'build' => 0],
	];

	public function __construct()
	{
		$this->start = time();
		$this->user = new User;
	}

	private function SetMaxInfo ($ID, $Count, $Data)
	{
		if ($Data['authlevel'] == 3 || $Data['banned'] != 0)
			return;

		if (!isset($this->maxinfos[$ID]))
			$this->maxinfos[$ID] = ['maxlvl' => 0, 'username' => ''];

		if ($this->maxinfos[$ID]['maxlvl'] < $Count)
			$this->maxinfos[$ID] = ['maxlvl' => $Count, 'username' => $Data['username']];
	}

	private function GetTechnoPoints ($CurrentUser)
	{
		$TechCounts = 0;
		$TechPoints = 0;

		$res_array = array_merge($this->game->reslist['tech'], $this->game->reslist['tech_f']);

		foreach ($res_array as $Techno)
		{
			if ($CurrentUser[$this->game->resource[$Techno]] == 0)
				continue;

			if ($CurrentUser['records'] == 1 && $Techno < 300)
				$this->SetMaxInfo($Techno, $CurrentUser[$this->game->resource[$Techno]], $CurrentUser);

			$Units = $this->game->pricelist[$Techno]['metal'] + $this->game->pricelist[$Techno]['crystal'] + $this->game->pricelist[$Techno]['deuterium'];

			for ($Level = 1; $Level <= $CurrentUser[$this->game->resource[$Techno]]; $Level++)
			{
				$TechPoints += $Units * pow($this->game->pricelist[$Techno]['factor'], $Level);
			}
			$TechCounts += $CurrentUser[$this->game->resource[$Techno]];
		}
		$RetValue['TechCount'] = $TechCounts;
		$RetValue['TechPoint'] = $TechPoints;

		return $RetValue;
	}

	private function GetBuildPoints ($CurrentPlanet, $User)
	{
		$BuildCounts = 0;
		$BuildPoints = 0;
		foreach ($this->game->reslist['build'] as $Build)
		{
			if ($CurrentPlanet[$this->game->resource[$Build]] == 0)
				continue;

			if ($User['records'] == 1)
				$this->SetMaxInfo($Build, $CurrentPlanet[$this->game->resource[$Build]], $User);

			$Units = $this->game->pricelist[$Build]['metal'] + $this->game->pricelist[$Build]['crystal'] + $this->game->pricelist[$Build]['deuterium'];
			for ($Level = 1; $Level <= $CurrentPlanet[$this->game->resource[$Build]]; $Level++)
			{
				$BuildPoints += $Units * pow($this->game->pricelist[$Build]['factor'], $Level);
			}
			$BuildCounts += $CurrentPlanet[$this->game->resource[$Build]];
		}
		$RetValue['BuildCount'] = $BuildCounts;
		$RetValue['BuildPoint'] = $BuildPoints;

		return $RetValue;
	}

	private function GetDefensePoints ($CurrentPlanet, &$RecordArray)
	{
		$DefenseCounts = 0;
		$DefensePoints = 0;

		foreach ($this->game->reslist['defense'] as $Defense)
		{
			if ($CurrentPlanet[$this->game->resource[$Defense]] > 0)
			{
				if (isset($RecordArray[$Defense]))
					$RecordArray[$Defense] += $CurrentPlanet[$this->game->resource[$Defense]];
				else
					$RecordArray[$Defense] = $CurrentPlanet[$this->game->resource[$Defense]];

				$Units = $this->game->pricelist[$Defense]['metal'] + $this->game->pricelist[$Defense]['crystal'] + $this->game->pricelist[$Defense]['deuterium'];
				$DefensePoints += ($Units * $CurrentPlanet[$this->game->resource[$Defense]]);
				$DefenseCounts += $CurrentPlanet[$this->game->resource[$Defense]];
			}
		}
		$RetValue['DefenseCount'] = $DefenseCounts;
		$RetValue['DefensePoint'] = $DefensePoints;

		return $RetValue;
	}

	private function GetFleetPoints ($CurrentPlanet, &$RecordArray)
	{
		$FleetCounts = 0;
		$FleetPoints = 0;

		foreach ($this->game->reslist['fleet'] as $Fleet)
		{
			if ($CurrentPlanet[$this->game->resource[$Fleet]] > 0)
			{
				if (isset($RecordArray[$Fleet]))
					$RecordArray[$Fleet] += $CurrentPlanet[$this->game->resource[$Fleet]];
				else
					$RecordArray[$Fleet] = $CurrentPlanet[$this->game->resource[$Fleet]];

				$Units = $this->game->pricelist[$Fleet]['metal'] + $this->game->pricelist[$Fleet]['crystal'] + $this->game->pricelist[$Fleet]['deuterium'];
				$FleetPoints += ($Units * $CurrentPlanet[$this->game->resource[$Fleet]]);
				if ($Fleet != 212)
					$FleetCounts += $CurrentPlanet[$this->game->resource[$Fleet]];
			}
		}
		$RetValue['FleetCount'] = $FleetCounts;
		$RetValue['FleetPoint'] = $FleetPoints;

		return $RetValue;
	}

	private function GetFleetPointsOnTour ($CurrentFleet)
	{
		$FleetCounts = 0;
		$FleetPoints = 0;
		$FleetArray = [];

		$split = trim(str_replace(';', ' ', $CurrentFleet));
		$split = explode(' ', $split);

		foreach ($split as $ship)
		{
			list($typ, $temp) = explode(',', $ship);
			list($amount) = explode('!', $temp);
			$Units = $this->game->pricelist[$typ]['metal'] + $this->game->pricelist[$typ]['crystal'] + $this->game->pricelist[$typ]['deuterium'];
			$FleetPoints += ($Units * $amount);
			if ($typ != 212)
				$FleetCounts += $amount;

			if (isset($FleetArray[$typ]))
				$FleetArray[$typ] += $amount;
			else
				$FleetArray[$typ] = $amount;
		}

		$RetValue['FleetCount'] = $FleetCounts;
		$RetValue['FleetPoint'] = $FleetPoints;
		$RetValue['fleet_array'] = $FleetArray;

		return $RetValue;
	}

	public function deleteUsers ()
	{
		$result = [];

		$list = $this->db->query("SELECT id, username FROM game_users WHERE `deltime` < ".time()." AND `deltime`> 0");

		while ($user = $list->fetch())
		{
			if ($this->user->deleteById($user['id']))
				$result[] = $user['username'];
		}

		return $result;
	}

	public function inactiveUsers ()
	{
		$result = [];

		$list = $this->db->query("SELECT u.id, u.username, i.email FROM game_users u, game_users_info i WHERE i.id = u.id AND u.`onlinetime` < ".(time() - $this->config->stat->get('inactiveTime', (21 * 86400)))." AND u.`onlinetime` > '0' AND (u.`vacation` = '0' OR (u.vacation < " . time() . " - 15184000 AND u.vacation > 1)) AND u.`banned` = '0' AND u.`deltime` = '0' ORDER BY u.onlinetime LIMIT 250");

		while ($user = $list->fetch())
		{
			$this->db->query("UPDATE game_users SET `deltime` = '" . (time() + $this->config->stat->get('deleteTime', (7 * 86400))) . "' WHERE `id` = '" . $user['id'] . "'");

			if (Helpers::is_email($user['email']))
			{
				$mail = new PHPMailer();

				$mail->IsMail();
				$mail->IsHTML(true);
				$mail->CharSet = 'utf-8';
				$mail->SetFrom($this->config->app->email, $this->config->app->name);
				$mail->AddAddress($user['email']);
				$mail->Subject = $this->config->app->name.': Уведомление об удалении аккаунта: ' . $this->config->game->universe . ' вселенная';
				$mail->Body = "Уважаемый \"" . $user['username'] . "\"! Уведомляем вас, что ваш аккаунт перешел в режим удаления и через " . floor($this->config->stat->get('deleteTime', (7 * 86400)) / 86400) . " дней будет удалён из игры.<br>
				<br><br>Во избежании удаления аккаунта вам нужно будет зайти в игру и через <a href=\"http://uni" . $this->config->game->universe . ".xnova.su/options/\">настройки профиля</a> отменить процедуру удаления.<br><br>С уважением, команда <a href=\"http://uni" . $this->config->game->universe . ".xnova.su\">XNOVA.SU</a>";
				$mail->Send();
			}

			$result[] = $user['username'];
		}

		return $result;
	}

	public function clearOldStats ()
	{
		$this->db->delete('game_statpoints', 'stat_code >= 2');
		$this->db->query("UPDATE game_statpoints SET `stat_code` = `stat_code` + '1';");
	}

	public function getTotalFleetPoints ()
	{
		$fleetPoints = [];

		$UsrFleets = $this->db->query("SELECT * FROM game_fleets");

		while ($CurFleet = $UsrFleets->fetch())
		{
			$Points = $this->GetFleetPointsOnTour($CurFleet['fleet_array']);

			if (!isset($fleetPoints[$CurFleet['owner']]))
			{
				$fleetPoints[$CurFleet['owner']] = [];
				$fleetPoints[$CurFleet['owner']]['points'] = 0;
				$fleetPoints[$CurFleet['owner']]['count'] = 0;
				$fleetPoints[$CurFleet['owner']]['array'] = [];
			}

			$fleetPoints[$CurFleet['owner']]['points'] += ($Points['FleetPoint'] / 1000);
			$fleetPoints[$CurFleet['owner']]['count'] += $Points['FleetCount'];
			$fleetPoints[$CurFleet['owner']]['array'][] = $Points['fleet_array'];
		}

		return $fleetPoints;
	}

	public function update ()
	{
		$active_users = 0;

		$fleetPoints = $this->getTotalFleetPoints();

		$list = $this->db->query("SELECT u.*, s.total_rank, s.tech_rank, s.fleet_rank, s.build_rank, s.defs_rank FROM (game_users u, game_users_info ui) LEFT JOIN game_statpoints s ON s.id_owner = u.id AND s.stat_type = 1 WHERE ui.id = u.id AND u.authlevel < 3 AND u.banned = 0");

		$this->db->query("DELETE FROM game_statpoints WHERE `stat_type` = '1';");

		while ($user = $list->fetch())
		{
			$options = $this->user->unpackOptions($user['options']);
			$user['records'] = $options['records'];

			if ($user['banned'] != 0 || ($user['vacation'] != 0 && $user['vacation'] < (time() - 1036800)))
				$hide = 1;
			else
				$hide = 0;

			if ($hide == 0)
				$active_users++;

			// Запоминаем старое место в стате
			if ($user['total_rank'] != "")
			{
				$OldTotalRank 	= $user['total_rank'];
				$OldTechRank 	= $user['tech_rank'];
				$OldFleetRank 	= $user['fleet_rank'];
				$OldBuildRank	= $user['build_rank'];
				$OldDefsRank 	= $user['defs_rank'];
			}
			else
			{
				$OldTotalRank 	= 0;
				$OldTechRank 	= 0;
				$OldBuildRank 	= 0;
				$OldDefsRank 	= 0;
				$OldFleetRank 	= 0;
			}

			$Points = $this->GetTechnoPoints($user);
			$TTechCount = $Points['TechCount'];
			$TTechPoints = ($Points['TechPoint'] / 1000);

			$TBuildCount = 0;
			$TBuildPoints = 0;
			$TDefsCount = 0;
			$TDefsPoints = 0;
			$TFleetCount = 0;
			$TFleetPoints = 0;
			$GCount = $TTechCount;
			$GPoints = $TTechPoints;

			$planets = $this->db->query("SELECT * FROM game_planets WHERE `id_owner` = '" . $user['id'] . "';");

			$RecordArray = [];

			while ($planet = $planets->fetch())
			{
				$Points = $this->GetBuildPoints($planet, $user);
				$TBuildCount += $Points['BuildCount'];
				$GCount += $Points['BuildCount'];
				$PlanetPoints = ($Points['BuildPoint'] / 1000);
				$TBuildPoints += ($Points['BuildPoint'] / 1000);

				$Points = $this->GetDefensePoints($planet, $RecordArray);
				$TDefsCount += $Points['DefenseCount'];
				$GCount += $Points['DefenseCount'];
				$PlanetPoints += ($Points['DefensePoint'] / 1000);
				$TDefsPoints += ($Points['DefensePoint'] / 1000);

				$Points = $this->GetFleetPoints($planet, $RecordArray);
				$TFleetCount += $Points['FleetCount'];
				$GCount += $Points['FleetCount'];
				$PlanetPoints += ($Points['FleetPoint'] / 1000);
				$TFleetPoints += ($Points['FleetPoint'] / 1000);

				$GPoints += $PlanetPoints;
			}

			// Складываем очки флота
			if (isset($fleetPoints[$user['id']]['points']))
			{
				$TFleetCount += $fleetPoints[$user['id']]['count'];
				$GCount += $fleetPoints[$user['id']]['count'];
				$TFleetPoints += $fleetPoints[$user['id']]['points'];
				$PlanetPoints = $fleetPoints[$user['id']]['points'];
				$GPoints += $PlanetPoints;

				foreach ($fleetPoints[$user['id']]['array'] AS $fleet)
				{
					foreach ($fleet AS $id => $amount)
					{
						if (isset($RecordArray[$id]))
							$RecordArray[$id] += $amount;
						else
							$RecordArray[$id] = $amount;
					}
				}
			}

			if ($user['records'] == 1)
			{
				foreach ($RecordArray AS $id => $amount)
				{
					$this->SetMaxInfo($id, $amount, $user);
				}
			}

			if ($user['race'] != 0)
			{
				$this->StatRace[$user['race']]['count'] += 1;
				$this->StatRace[$user['race']]['total'] += $GPoints;
				$this->StatRace[$user['race']]['fleet'] += $TFleetPoints;
				$this->StatRace[$user['race']]['tech'] += $TTechPoints;
				$this->StatRace[$user['race']]['build'] += $TBuildPoints;
				$this->StatRace[$user['race']]['defs'] += $TDefsPoints;
			}

			// Заносим данные в таблицу
			$QryInsertStats = "INSERT INTO game_statpoints SET ";
			$QryInsertStats .= "`id_owner` = '" . $user['id'] . "', ";
			$QryInsertStats .= "`username` = '" . addslashes($user['username']) . "', ";
			$QryInsertStats .= "`race` = '" . $user['race'] . "', ";
			$QryInsertStats .= "`id_ally` = '" . $user['ally_id'] . "', ";
			$QryInsertStats .= "`ally_name` = '" . addslashes($user['ally_name']) . "', ";
			$QryInsertStats .= "`stat_type` = '1', ";
			$QryInsertStats .= "`stat_code` = '1', ";
			$QryInsertStats .= "`tech_points` = '" . $TTechPoints . "', ";
			$QryInsertStats .= "`tech_count` = '" . $TTechCount . "', ";
			$QryInsertStats .= "`tech_old_rank` = '" . $OldTechRank . "', ";
			$QryInsertStats .= "`build_points` = '" . $TBuildPoints . "', ";
			$QryInsertStats .= "`build_count` = '" . $TBuildCount . "', ";
			$QryInsertStats .= "`build_old_rank` = '" . $OldBuildRank . "', ";
			$QryInsertStats .= "`defs_points` = '" . $TDefsPoints . "', ";
			$QryInsertStats .= "`defs_count` = '" . $TDefsCount . "', ";
			$QryInsertStats .= "`defs_old_rank` = '" . $OldDefsRank . "', ";
			$QryInsertStats .= "`fleet_points` = '" . $TFleetPoints . "', ";
			$QryInsertStats .= "`fleet_count` = '" . $TFleetCount . "', ";
			$QryInsertStats .= "`fleet_old_rank` = '" . $OldFleetRank . "', ";
			$QryInsertStats .= "`total_points` = '" . $GPoints . "', ";
			$QryInsertStats .= "`total_count` = '" . $GCount . "', ";
			$QryInsertStats .= "`total_old_rank` = '" . $OldTotalRank . "', ";
			$QryInsertStats .= "`stat_hide` = '" . $hide . "';";
			$this->db->query($QryInsertStats);
		}

		$this->calcPositions();

		$active_alliance = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_statpoints WHERE `stat_type` = '2' AND `stat_hide` = 0");

		$this->game->updateConfig('stat_update', time());
		$this->game->updateConfig('active_users', $active_users);
		$this->game->updateConfig('active_alliance', $active_alliance);
	}

	private function calcPositions ()
	{
		$qryFormat = 'UPDATE game_statpoints SET `%1$s_rank` = (SELECT @rownum:=@rownum+1) WHERE `stat_type` = %2$d AND `stat_code` = 1 AND stat_hide = 0 ORDER BY `%1$s_points` DESC, `id_owner` ASC;';

		$rankNames = ['tech', 'fleet', 'defs', 'build', 'total'];

		foreach ($rankNames as $rankName)
		{
			$this->db->query('SET @rownum=0;');
			$this->db->query(sprintf($qryFormat, $rankName, 1));
		}

		$this->db->query("INSERT INTO game_statpoints
		      (`tech_points`, `tech_count`, `build_points`, `build_count`, `defs_points`, `defs_count`,
		        `fleet_points`, `fleet_count`, `total_points`, `total_count`, `id_owner`, `id_ally`, `stat_type`, `stat_code`,
		        `tech_old_rank`, `build_old_rank`, `defs_old_rank`, `fleet_old_rank`, `total_old_rank`
		      )
		      SELECT
		        SUM(u.`tech_points`), SUM(u.`tech_count`), SUM(u.`build_points`), SUM(u.`build_count`), SUM(u.`defs_points`),
		        SUM(u.`defs_count`), SUM(u.`fleet_points`), SUM(u.`fleet_count`), SUM(u.`total_points`), SUM(u.`total_count`),
		        u.`id_ally`, 0, 2, 1,
		        a.tech_rank, a.build_rank, a.defs_rank, a.fleet_rank, a.total_rank
		      FROM game_statpoints as u
		        LEFT JOIN game_statpoints as a ON a.id_owner = u.id_ally AND a.stat_code = 2 AND a.stat_type = 2
		      WHERE u.`stat_type` = 1 AND u.stat_code = 1 AND u.id_ally<>0
		      GROUP BY u.`id_ally`");

		$this->db->query("UPDATE game_statpoints as new
		      LEFT JOIN game_statpoints as old ON old.id_owner = new.id_owner AND old.stat_code = 2 AND old.stat_type = 1
		    SET
		      new.tech_old_rank = old.tech_rank,
		      new.build_old_rank = old.build_rank,
		      new.defs_old_rank  = old.defs_rank ,
		      new.fleet_old_rank = old.fleet_rank,
		      new.total_old_rank = old.total_rank
		    WHERE
		      new.stat_type = 2 AND new.stat_code = 2;");

		$this->db->query("DELETE FROM game_statpoints WHERE `stat_code` >= 2");

		foreach ($rankNames as $rankName)
		{
			$this->db->query('SET @rownum=0;');
			$this->db->query(sprintf($qryFormat, $rankName, 2));
		}

		foreach ($this->StatRace AS $race => $arr)
		{
			$QryInsertStats = "INSERT INTO game_statpoints SET ";
			$QryInsertStats .= "`race` = '" . $race . "', ";
			$QryInsertStats .= "`stat_type` = '3', ";
			$QryInsertStats .= "`stat_code` = '1', ";
			$QryInsertStats .= "`tech_points` = '" . $arr['tech'] . "', ";
			$QryInsertStats .= "`build_points` = '" . $arr['build'] . "', ";
			$QryInsertStats .= "`defs_points` = '" . $arr['defs'] . "', ";
			$QryInsertStats .= "`fleet_points` = '" . $arr['fleet'] . "', ";
			$QryInsertStats .= "`total_count` = '" . $arr['count'] . "', ";
			$QryInsertStats .= "`total_points` = '" . $arr['total'] . "';";
			$this->db->query($QryInsertStats);
		}

		foreach ($rankNames as $rankName)
		{
			$this->db->query('SET @rownum=0;');
			$this->db->query(sprintf($qryFormat, $rankName, 3));
		}

		$this->db->query("OPTIMIZE TABLE game_statpoints");
	}

	public function addToLog ()
	{
		$this->db->query("INSERT INTO game_log_stats
			(`tech_points`, `tech_rank`, `build_points`, `build_rank`, `defs_points`, `defs_rank`, `fleet_points`, `fleet_rank`, `total_points`, `total_rank`, `id`, `type`, `time`)
			SELECT
				u.`tech_points`, u.`tech_rank`, u.`build_points`, u.`build_rank`, u.`defs_points`,
		        u.`defs_rank`, u.`fleet_points`, u.`fleet_rank`, u.`total_points`, u.`total_rank`,
		        u.`id_owner`, 1, ".$this->start."
		    FROM game_statpoints as u
		    WHERE
		    	u.`stat_type` = 1 AND u.stat_code = 1");

		$this->db->query("INSERT INTO game_log_stats
			(`tech_points`, `tech_rank`, `build_points`, `build_rank`, `defs_points`, `defs_rank`, `fleet_points`, `fleet_rank`, `total_points`, `total_rank`, `id`, `type`, `time`)
			SELECT
				u.`tech_points`, u.`tech_rank`, u.`build_points`, u.`build_rank`, u.`defs_points`,
		        u.`defs_rank`, u.`fleet_points`, u.`fleet_rank`, u.`total_points`, u.`total_rank`,
		        u.`id_owner`, 2, ".$this->start."
		    FROM game_statpoints as u
		    WHERE
		    	u.`stat_type` = 2 AND u.stat_code = 1");
	}

	public function clearGame ()
	{
		$this->db->query("DELETE FROM game_messages WHERE `time` <= '" . (time() - 432000) . "';");
		$this->db->query("DELETE FROM game_rw WHERE `time` <= '" . (time() - 172800) . "';");
		$this->db->query("DELETE FROM game_alliance_chat WHERE `timestamp` <= '" . (time() - 604800) . "';");
		$this->db->query("DELETE FROM game_lostpasswords WHERE `time` <= '" . (time() - 86400) . "';");
		$this->db->query("DELETE FROM game_logs WHERE `time` <= '" . (time() - 259200) . "';");
		$this->db->query("DELETE FROM game_log_attack WHERE `time` <= '" . (time() - 604800) . "';");
		$this->db->query("DELETE FROM game_log_credits WHERE `time` <= '" . (time() - 604800) . "';");
		$this->db->query("DELETE FROM game_log_ip WHERE `time` <= '" . (time() - 604800) . "';");
		$this->db->query("DELETE FROM game_log_load WHERE `time` <= '" . (time() - 604800) . "';");
		$this->db->query("DELETE FROM game_log_history WHERE `time` <= '" . (time() - 604800) . "';");
		$this->db->query("DELETE FROM game_log_stats WHERE `time` <= '" . (time() - (86400 * 30)) . "';");
		$this->db->query("DELETE FROM game_log_sim WHERE `time` <= '" . (time() - (86400 * 7)) . "';");
	}

	public function buildRecordsCache ()
	{
		$Elements = array_merge($this->game->reslist['build'], $this->game->reslist['tech'], $this->game->reslist['fleet'], $this->game->reslist['defense']);

		$array = "";
		foreach ($Elements as $ElementID)
		{
			if ($ElementID != 407 && $ElementID != 408)
				$array .= $ElementID . " => array('username' => '" . (isset($this->maxinfos[$ElementID]['username']) ? $this->maxinfos[$ElementID]['username'] : '-') . "', 'maxlvl' => '" . (isset($this->maxinfos[$ElementID]['maxlvl']) ? $this->maxinfos[$ElementID]['maxlvl'] : '-') . "'),\n";
		}
		$file = "<?php \n//The File is created on " . date("d. M y H:i:s", time()) . "\n$" . "RecordsArray = [\n" . $array . "\n];\n?>";

		if (!file_exists(APP_PATH . $this->config->application->cacheDir))
			mkdir(APP_PATH . $this->config->application->cacheDir, 0777);

		file_put_contents(APP_PATH.$this->config->application->cacheDir."/CacheRecords.php", $file);
	}
}
 
?>