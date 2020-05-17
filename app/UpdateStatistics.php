<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova;

use Backpack\Settings\app\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Xnova\Mail\UserDelete;
use Xnova\Models\Fleet;
use Xnova\Models\Statpoints;

class UpdateStatistics
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
		$this->user = new Models\Users();
	}

	private function SetMaxInfo($ID, $Count, Models\Users $Data)
	{
		if ($Data->isAdmin() || $Data->banned != 0) {
			return;
		}

		if (!isset($this->maxinfos[$ID])) {
			$this->maxinfos[$ID] = ['maxlvl' => 0, 'username' => ''];
		}

		if ($this->maxinfos[$ID]['maxlvl'] < $Count) {
			$this->maxinfos[$ID] = ['maxlvl' => $Count, 'username' => $Data->username];
		}
	}

	private function GetTechnoPoints(Models\Users $user)
	{
		$TechCounts = 0;
		$TechPoints = 0;

		$items = Models\UsersTech::query()
			->where('user_id', $user->id)
			->get();

		foreach ($items as $item) {
			if ($item->level <= 0) {
				continue;
			}

			if ($user->records == 1 && $item->tech_id < 300) {
				$this->SetMaxInfo($item->tech_id, $item->level, $user);
			}

			$price = Vars::getItemPrice($item->tech_id);

			$Units = $price['metal'] + $price['crystal'] + $price['deuterium'];

			for ($Level = 1; $Level <= $item->level; $Level++) {
				$TechPoints += $Units * pow($price['factor'], $Level);
			}

			$TechCounts += $item->level;
		}

		$RetValue['TechCount'] = $TechCounts;
		$RetValue['TechPoint'] = $TechPoints;

		return $RetValue;
	}

	private function GetBuildPoints(Models\Planets $planet, $user)
	{
		$BuildCounts = 0;
		$BuildPoints = 0;

		$items = Models\PlanetsBuildings::query()
			->where('planet_id', $planet->id)
			->get();

		foreach ($items as $item) {
			if ($item->level <= 0) {
				continue;
			}

			if ($user['records'] == 1) {
				$this->SetMaxInfo($item->build_id, $item->level, $user);
			}

			$price = Vars::getItemPrice($item->build_id);

			$Units = $price['metal'] + $price['crystal'] + $price['deuterium'];

			for ($Level = 1; $Level <= $item->level; $Level++) {
				$BuildPoints += $Units * pow($price['factor'], $Level);
			}

			$BuildCounts += $item->level;
		}

		$RetValue['BuildCount'] = $BuildCounts;
		$RetValue['BuildPoint'] = $BuildPoints;

		return $RetValue;
	}

	private function GetDefensePoints(Models\Planets $planet, &$RecordArray)
	{
		$UnitsCounts = 0;
		$UnitsPoints = 0;

		$items = Models\PlanetsUnits::query()
			->where('planet_id', $planet->id)
			->whereIn('unit_id', Vars::getItemsByType(Vars::ITEM_TYPE_DEFENSE))
			->get();

		foreach ($items as $item) {
			if ($item->amount <= 0) {
				continue;
			}

			if (!isset($RecordArray[$item->unit_id])) {
				$RecordArray[$item->unit_id] = 0;
			}

			$RecordArray[$item->unit_id] += $item->amount;

			$Units = Vars::getItemTotalPrice($item->unit_id, true);

			$UnitsPoints += ($Units * $item->amount);
			$UnitsCounts += $item->amount;
		}

		$RetValue['DefenseCount'] = $UnitsCounts;
		$RetValue['DefensePoint'] = $UnitsPoints;

		return $RetValue;
	}

	private function GetFleetPoints(Models\Planets $planet, &$RecordArray)
	{
		$UnitsCounts = 0;
		$UnitsPoints = 0;

		$items = Models\PlanetsUnits::query()
			->where('planet_id', $planet->id)
			->whereIn('unit_id', Vars::getItemsByType(Vars::ITEM_TYPE_FLEET))
			->get();

		foreach ($items as $item) {
			if ($item->amount <= 0) {
				continue;
			}

			if (!isset($RecordArray[$item->unit_id])) {
				$RecordArray[$item->unit_id] = 0;
			}

			$RecordArray[$item->unit_id] += $item->amount;

			$Units = Vars::getItemTotalPrice($item->unit_id, true);

			$UnitsPoints += ($Units * $item->amount);

			if ($item->unit_id != 212) {
				$UnitsCounts += $item->amount;
			}
		}

		$RetValue['FleetCount'] = $UnitsCounts;
		$RetValue['FleetPoint'] = $UnitsPoints;

		return $RetValue;
	}

	private function GetFleetPointsOnTour($CurrentFleet)
	{
		$FleetCounts = 0;
		$FleetPoints = 0;
		$FleetArray = [];

		foreach ($CurrentFleet as $type => $ship) {
			$Units = Vars::getItemTotalPrice($type, true);
			$FleetPoints += ($Units * $ship['count']);

			if ($type != 212) {
				$FleetCounts += $ship['count'];
			}

			if (isset($FleetArray[$type])) {
				$FleetArray[$type] += $ship['count'];
			} else {
				$FleetArray[$type] = $ship['count'];
			}
		}

		$RetValue['FleetCount'] = $FleetCounts;
		$RetValue['FleetPoint'] = $FleetPoints;
		$RetValue['fleet_array'] = $FleetArray;

		return $RetValue;
	}

	public function deleteUsers()
	{
		$result = [];

		$list = Models\Users::query()
			->where('deltime', '<', time())
			->where('deltime', '>', 0)
			->get(['id', 'username']);

		foreach ($list as $user) {
			if (User::deleteById($user->id)) {
				$result[] = $user->username;
			}
		}

		return $result;
	}

	public function inactiveUsers()
	{
		$result = [];

		$list = DB::select("SELECT u.id, u.username, i.email FROM users u, users_info i WHERE i.id = u.id AND u.`onlinetime` < " . (time() - Config::get('settings.stat.inactiveTime', (21 * 86400))) . " AND u.`onlinetime` > '0' AND planet_id > 0 AND (u.`vacation` = '0' OR (u.vacation < " . time() . " - 15184000 AND u.vacation > 1)) AND u.`banned` = '0' AND u.`deltime` = '0' ORDER BY u.onlinetime LIMIT 250");

		foreach ($list as $user) {
			DB::statement("UPDATE users SET `deltime` = '" . (time() + Config::get('settings.stat.deleteTime', (7 * 86400))) . "' WHERE `id` = '" . $user->id . "'");

			if (Helpers::is_email($user->email)) {
				Mail::to($user->email)->send(new UserDelete([
					'#NAME#' => $user->username,
				]));
			}

			$result[] = $user->username;
		}

		return $result;
	}

	public function clearOldStats()
	{
		Statpoints::query()->where('stat_code', '>=', 2)->delete();
		Statpoints::query()->increment('stat_code', 1);
	}

	public function getTotalFleetPoints()
	{
		$fleetPoints = [];

		$UsrFleets = Fleet::query()->get();

		foreach ($UsrFleets as $CurFleet) {
			$Points = $this->GetFleetPointsOnTour($CurFleet->getShips());

			if (!isset($fleetPoints[$CurFleet->owner])) {
				$fleetPoints[$CurFleet->owner] = [];
				$fleetPoints[$CurFleet->owner]['points'] = 0;
				$fleetPoints[$CurFleet->owner]['count'] = 0;
				$fleetPoints[$CurFleet->owner]['array'] = [];
			}

			$fleetPoints[$CurFleet->owner]['points'] += ($Points['FleetPoint'] / 1000);
			$fleetPoints[$CurFleet->owner]['count'] += $Points['FleetCount'];
			$fleetPoints[$CurFleet->owner]['array'][] = $Points['fleet_array'];
		}

		return $fleetPoints;
	}

	public function update()
	{
		$active_users = 0;

		$fleetPoints = $this->getTotalFleetPoints();

		$list = DB::select("SELECT u.*, ui.settings, s.total_rank, s.tech_rank, s.fleet_rank, s.build_rank, s.defs_rank FROM (users u, users_info ui) LEFT JOIN statpoints s ON s.id_owner = u.id AND s.stat_type = 1 WHERE u.planet_id > 0 AND ui.id = u.id AND u.authlevel < 3 AND u.banned = 0");

		Statpoints::query()->where('stat_code', 1)->delete();

		foreach ($list as $user) {
			$options = json_decode($user->settings, true);
			$user->records = $options['records'] ?? true;

			if ($user->banned != 0 || ($user->vacation != 0 && $user->vacation < (time() - 1036800))) {
				$hide = 1;
			} else {
				$hide = 0;
			}

			if ($hide == 0) {
				$active_users++;
			}

			// Запоминаем старое место в стате
			if ($user->total_rank != '') {
				$OldTotalRank 	= $user->total_rank;
				$OldTechRank 	= $user->tech_rank;
				$OldFleetRank 	= $user->fleet_rank;
				$OldBuildRank	= $user->build_rank;
				$OldDefsRank 	= $user->defs_rank;
			} else {
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

			$planets = Models\Planets::query()->where('id_owner', $user->id)->get();

			$RecordArray = [];

			foreach ($planets as $planet) {
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
			if (isset($fleetPoints[$user['id']]['points'])) {
				$TFleetCount += $fleetPoints[$user['id']]['count'];
				$GCount += $fleetPoints[$user['id']]['count'];
				$TFleetPoints += $fleetPoints[$user['id']]['points'];
				$PlanetPoints = $fleetPoints[$user['id']]['points'];
				$GPoints += $PlanetPoints;

				foreach ($fleetPoints[$user['id']]['array'] as $fleet) {
					foreach ($fleet as $id => $amount) {
						if (isset($RecordArray[$id])) {
							$RecordArray[$id] += $amount;
						} else {
							$RecordArray[$id] = $amount;
						}
					}
				}
			}

			if ($user['records']) {
				foreach ($RecordArray as $id => $amount) {
					$this->SetMaxInfo($id, $amount, $user);
				}
			}

			if ($user['race'] != 0) {
				$this->StatRace[$user['race']]['count'] += 1;
				$this->StatRace[$user['race']]['total'] += $GPoints;
				$this->StatRace[$user['race']]['fleet'] += $TFleetPoints;
				$this->StatRace[$user['race']]['tech'] += $TTechPoints;
				$this->StatRace[$user['race']]['build'] += $TBuildPoints;
				$this->StatRace[$user['race']]['defs'] += $TDefsPoints;
			}

			Statpoints::query()->insert([
				'id_owner' => $user->id,
				'username' => addslashes($user->username),
				'race' => $user->race,
				'id_ally' => $user->ally_id,
				'ally_name' => addslashes($user->ally_name),
				'stat_type' => 1,
				'stat_code' => 1,
				'tech_points' => $TTechPoints,
				'tech_count' => $TTechCount,
				'tech_old_rank' => $OldTechRank,
				'build_points' => $TBuildPoints,
				'build_count' => $TBuildCount,
				'build_old_rank' => $OldBuildRank,
				'defs_points' => $TDefsPoints,
				'defs_count' => $TDefsCount,
				'defs_old_rank' => $OldDefsRank,
				'fleet_points' => $TFleetPoints,
				'fleet_count' => $TFleetCount,
				'fleet_old_rank' => $OldFleetRank,
				'total_points' => $GPoints,
				'total_count' => $GCount,
				'total_old_rank' => $OldTotalRank,
				'stat_hide' => $hide,
			]);
		}

		$this->calcPositions();

		$active_alliance = Statpoints::query()->where('stat_type', 2)->where('stat_hide', 0)->count();

		Setting::set('stat_update', time());
		Setting::set('active_users', $active_users);
		Setting::set('active_alliance', $active_alliance);
	}

	private function calcPositions()
	{
		$qryFormat = 'UPDATE statpoints SET `%1$s_rank` = (SELECT @rownum:=@rownum+1) WHERE `stat_type` = %2$d AND `stat_code` = 1 AND stat_hide = 0 ORDER BY `%1$s_points` DESC, `id_owner` ASC;';

		$rankNames = ['tech', 'fleet', 'defs', 'build', 'total'];

		foreach ($rankNames as $rankName) {
			DB::statement('SET @rownum=0;');
			DB::statement(sprintf($qryFormat, $rankName, 1));
		}

		DB::statement("INSERT INTO statpoints
		      (`tech_points`, `tech_count`, `build_points`, `build_count`, `defs_points`, `defs_count`,
		        `fleet_points`, `fleet_count`, `total_points`, `total_count`, `id_owner`, `id_ally`, `stat_type`, `stat_code`,
		        `tech_old_rank`, `build_old_rank`, `defs_old_rank`, `fleet_old_rank`, `total_old_rank`
		      )
		      SELECT
		        SUM(u.`tech_points`), SUM(u.`tech_count`), SUM(u.`build_points`), SUM(u.`build_count`), SUM(u.`defs_points`),
		        SUM(u.`defs_count`), SUM(u.`fleet_points`), SUM(u.`fleet_count`), SUM(u.`total_points`), SUM(u.`total_count`),
		        u.`id_ally`, 0, 2, 1,
		        a.tech_rank, a.build_rank, a.defs_rank, a.fleet_rank, a.total_rank
		      FROM statpoints as u
		        LEFT JOIN statpoints as a ON a.id_owner = u.id_ally AND a.stat_code = 2 AND a.stat_type = 2
		      WHERE u.`stat_type` = 1 AND u.stat_code = 1 AND u.id_ally<>0
		      GROUP BY u.`id_ally`");

		DB::statement("UPDATE statpoints as new
		      LEFT JOIN statpoints as old ON old.id_owner = new.id_owner AND old.stat_code = 2 AND old.stat_type = 1
		    SET
		      new.tech_old_rank = old.tech_rank,
		      new.build_old_rank = old.build_rank,
		      new.defs_old_rank  = old.defs_rank ,
		      new.fleet_old_rank = old.fleet_rank,
		      new.total_old_rank = old.total_rank
		    WHERE
		      new.stat_type = 2 AND new.stat_code = 2;");

		DB::statement("DELETE FROM statpoints WHERE `stat_code` >= 2");

		foreach ($rankNames as $rankName) {
			DB::statement('SET @rownum=0;');
			DB::statement(sprintf($qryFormat, $rankName, 2));
		}

		foreach ($this->StatRace as $race => $arr) {
			Statpoints::query()->insert([
				'race' => $race,
				'stat_type' => 3,
				'stat_code' => 1,
				'tech_points' => $arr['tech'],
				'build_points' => $arr['build'],
				'defs_points' => $arr['defs'],
				'fleet_points' => $arr['fleet'],
				'total_count' => $arr['count'],
				'total_points' => $arr['total'],
			]);
		}

		foreach ($rankNames as $rankName) {
			DB::statement('SET @rownum=0;');
			DB::statement(sprintf($qryFormat, $rankName, 3));
		}

		DB::statement("OPTIMIZE TABLE statpoints");
	}

	public function addToLog()
	{
		DB::statement("INSERT INTO log_stats
			(`tech_points`, `tech_rank`, `build_points`, `build_rank`, `defs_points`, `defs_rank`, `fleet_points`, `fleet_rank`, `total_points`, `total_rank`, `object_id`, `type`, `time`)
			SELECT
				u.`tech_points`, u.`tech_rank`, u.`build_points`, u.`build_rank`, u.`defs_points`,
		        u.`defs_rank`, u.`fleet_points`, u.`fleet_rank`, u.`total_points`, u.`total_rank`,
		        u.`id_owner`, 1, " . $this->start . "
		    FROM statpoints as u
		    WHERE
		    	u.`stat_type` = 1 AND u.stat_code = 1");

		DB::statement("INSERT INTO log_stats
			(`tech_points`, `tech_rank`, `build_points`, `build_rank`, `defs_points`, `defs_rank`, `fleet_points`, `fleet_rank`, `total_points`, `total_rank`, `object_id`, `type`, `time`)
			SELECT
				u.`tech_points`, u.`tech_rank`, u.`build_points`, u.`build_rank`, u.`defs_points`,
		        u.`defs_rank`, u.`fleet_points`, u.`fleet_rank`, u.`total_points`, u.`total_rank`,
		        u.`id_owner`, 2, " . $this->start . "
		    FROM statpoints as u
		    WHERE
		    	u.`stat_type` = 2 AND u.stat_code = 1");
	}

	public function clearGame()
	{
		DB::statement("DELETE FROM messages WHERE `time` <= '" . (time() - (86400 * 14)) . "' AND type != 1;");
		DB::statement("DELETE FROM rw WHERE `time` <= '" . (time() - 172800) . "';");
		//DB::statement("DELETE FROM alliance_chat WHERE `timestamp` <= '" . (time() - 1209600) . "';");
		DB::statement("DELETE FROM lostpasswords WHERE `time` <= '" . (time() - 86400) . "';");
		DB::statement("DELETE FROM logs WHERE `time` <= '" . (time() - 259200) . "';");
		DB::statement("DELETE FROM log_attack WHERE `time` <= '" . (time() - 604800) . "';");
		DB::statement("DELETE FROM log_credits WHERE `time` <= '" . (time() - 604800) . "';");
		DB::statement("DELETE FROM log_ip WHERE `time` <= '" . (time() - 604800) . "';");
		//DB::statement("DELETE FROM log_load WHERE `time` <= '" . (time() - 604800) . "';");
		DB::statement("DELETE FROM log_history WHERE `time` <= '" . (time() - 604800) . "';");
		DB::statement("DELETE FROM log_stats WHERE `time` <= '" . (time() - (86400 * 30)) . "';");
		DB::statement("DELETE FROM log_sim WHERE `time` <= '" . (time() - (86400 * 7)) . "';");
	}

	public function buildRecordsCache()
	{
		$Elements = Vars::getItemsByType([Vars::ITEM_TYPE_BUILING, Vars::ITEM_TYPE_TECH, Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]);

		$array = "";

		foreach ($Elements as $ElementID) {
			if ($ElementID != 407 && $ElementID != 408) {
				$array .= $ElementID . " => array('username' => '" . (isset($this->maxinfos[$ElementID]['username']) ? $this->maxinfos[$ElementID]['username'] : '-') . "', 'maxlvl' => '" . (isset($this->maxinfos[$ElementID]['maxlvl']) ? $this->maxinfos[$ElementID]['maxlvl'] : '-') . "'),\n";
			}
		}

		$file = "<?php \n//The File is created on " . date("d. M y H:i:s", time()) . "\n$" . "RecordsArray = [\n" . $array . "\n];\n?>";

		file_put_contents(base_path('bootstrap/cache/CacheRecords.php'), $file);
	}
}
