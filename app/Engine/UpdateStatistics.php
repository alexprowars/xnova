<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Helpers;
use App\Models;
use App\Models\Fleet;
use App\Models\LogStat;
use App\Models\Statistic;
use App\Models\User;
use App\Notifications\UserDeleteNotification;
use App\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class UpdateStatistics
{
	private $maxinfos = [];
	public $start = 0;

	private $StatRace = [
		1 => ['count' => 0, 'total' => 0, 'fleet' => 0, 'tech' => 0, 'defs' => 0, 'build' => 0],
		2 => ['count' => 0, 'total' => 0, 'fleet' => 0, 'tech' => 0, 'defs' => 0, 'build' => 0],
		3 => ['count' => 0, 'total' => 0, 'fleet' => 0, 'tech' => 0, 'defs' => 0, 'build' => 0],
		4 => ['count' => 0, 'total' => 0, 'fleet' => 0, 'tech' => 0, 'defs' => 0, 'build' => 0],
	];

	public function __construct()
	{
		$this->start = time();
	}

	private function setMaxInfo($ID, $Count, Models\User $Data)
	{
		if ($Data->isAdmin() || $Data->blocked_at) {
			return;
		}

		if (!isset($this->maxinfos[$ID])) {
			$this->maxinfos[$ID] = ['maxlvl' => 0, 'username' => ''];
		}

		if ($this->maxinfos[$ID]['maxlvl'] < $Count) {
			$this->maxinfos[$ID] = ['maxlvl' => $Count, 'username' => $Data->username];
		}
	}

	private function getTechnoPoints(Models\User $user)
	{
		$TechCounts = 0;
		$TechPoints = 0;

		$items = Models\UserTech::query()
			->whereBelongsTo($user)
			->get();

		foreach ($items as $item) {
			if ($item->level <= 0) {
				continue;
			}

			if ($user->getOption('records') && $item->tech_id < 300) {
				$this->setMaxInfo($item->tech_id, $item->level, $user);
			}

			$price = Vars::getItemPrice($item->tech_id);

			$Units = $price['metal'] + $price['crystal'] + $price['deuterium'];

			for ($Level = 1; $Level <= $item->level; $Level++) {
				$TechPoints += $Units * ($price['factor'] ** $Level);
			}

			$TechCounts += $item->level;
		}

		$RetValue['TechCount'] = $TechCounts;
		$RetValue['TechPoint'] = $TechPoints;

		return $RetValue;
	}

	private function getBuildPoints(Models\Planet $planet, Models\User $user)
	{
		$BuildCounts = 0;
		$BuildPoints = 0;

		$items = $planet->entities()
			->whereIn('entity_id', Vars::getItemsByType(ItemType::BUILDING))
			->get();

		foreach ($items as $item) {
			if ($item->amount <= 0) {
				continue;
			}

			if ($user->getOption('records')) {
				$this->setMaxInfo($item->entity_id, $item->amount, $user);
			}

			$price = Vars::getItemPrice($item->entity_id);

			$Units = $price['metal'] + $price['crystal'] + $price['deuterium'];

			for ($Level = 1; $Level <= $item->amount; $Level++) {
				$BuildPoints += $Units * ($price['factor'] ** $Level);
			}

			$BuildCounts += $item->amount;
		}

		$RetValue['BuildCount'] = $BuildCounts;
		$RetValue['BuildPoint'] = $BuildPoints;

		return $RetValue;
	}

	private function getDefensePoints(Models\Planet $planet, &$RecordArray)
	{
		$UnitsCounts = 0;
		$UnitsPoints = 0;

		$items = $planet->entities()
			->whereIn('entity_id', Vars::getItemsByType(ItemType::DEFENSE))
			->get();

		foreach ($items as $item) {
			if ($item->amount <= 0) {
				continue;
			}

			if (!isset($RecordArray[$item->entity_id])) {
				$RecordArray[$item->entity_id] = 0;
			}

			$RecordArray[$item->entity_id] += $item->amount;

			$Units = Vars::getItemTotalPrice($item->entity_id, true);

			$UnitsPoints += ($Units * $item->amount);
			$UnitsCounts += $item->amount;
		}

		$RetValue['DefenseCount'] = $UnitsCounts;
		$RetValue['DefensePoint'] = $UnitsPoints;

		return $RetValue;
	}

	private function getFleetPoints(Models\Planet $planet, &$RecordArray)
	{
		$UnitsCounts = 0;
		$UnitsPoints = 0;

		$items = $planet->entities()
			->whereIn('entity_id', Vars::getItemsByType(ItemType::FLEET))
			->get();

		foreach ($items as $item) {
			if ($item->amount <= 0) {
				continue;
			}

			if (!isset($RecordArray[$item->entity_id])) {
				$RecordArray[$item->entity_id] = 0;
			}

			$RecordArray[$item->entity_id] += $item->amount;

			$Units = Vars::getItemTotalPrice($item->entity_id, true);

			$UnitsPoints += ($Units * $item->amount);

			if ($item->entity_id != 212) {
				$UnitsCounts += $item->amount;
			}
		}

		$RetValue['FleetCount'] = $UnitsCounts;
		$RetValue['FleetPoint'] = $UnitsPoints;

		return $RetValue;
	}

	private function getFleetPointsOnTour($CurrentFleet)
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

		$list = Models\User::query()
			->whereNotNull('delete_time')
			->where('delete_time', '<', now())
			->get(['id', 'username']);

		foreach ($list as $user) {
			if ($user->delete()) {
				$result[] = $user->username;
			}
		}

		return $result;
	}

	public function inactiveUsers()
	{
		$result = [];

		$users = User::query()
			->where('onlinetime', '<', now()->addDays(config('game.inactiveTime', 21)))
			->whereNotNull('onlinetime')
			->whereNotNull('planet_id')
			->where(function (Builder $query) {
				$query->whereNull('vacation')
					->orWhere(function (Builder $query) {
						$query->where('vacation', '<', now()->subSeconds(15184000))
							->where('vacation', '>', Date::createFromTimestamp(0));
					});
			})
			->whereNull('blocked_at')
			->whereNull('delete_time')
			->orderBy('onlinetime')
			->limit(250)
			->get();

		foreach ($users as $user) {
			$user->update([
				'delete_time' => now()->addDays(config('game.deleteTime', 7)),
			]);

			if (Helpers::is_email($user->email)) {
				$user->notify(new UserDeleteNotification());
			}

			$result[] = $user->username;
		}

		return $result;
	}

	public function clearOldStats()
	{
		Statistic::query()->where('stat_code', '>=', 2)->delete();
		Statistic::query()->increment('stat_code');
	}

	public function getTotalFleetPoints()
	{
		$fleetPoints = [];

		$UsrFleets = Fleet::query()->get();

		foreach ($UsrFleets as $CurFleet) {
			$Points = $this->getFleetPointsOnTour($CurFleet->getShips());

			if (!isset($fleetPoints[$CurFleet->user_id])) {
				$fleetPoints[$CurFleet->user_id] = [];
				$fleetPoints[$CurFleet->user_id]['points'] = 0;
				$fleetPoints[$CurFleet->user_id]['count'] = 0;
				$fleetPoints[$CurFleet->user_id]['array'] = [];
			}

			$fleetPoints[$CurFleet->user_id]['points'] += ($Points['FleetPoint'] / 1000);
			$fleetPoints[$CurFleet->user_id]['count'] += $Points['FleetCount'];
			$fleetPoints[$CurFleet->user_id]['array'][] = $Points['fleet_array'];
		}

		return $fleetPoints;
	}

	public function update()
	{
		$active_users = 0;

		$fleetPoints = $this->getTotalFleetPoints();

		$users = User::whereNotNull('planet_id')
			->where('authlevel', '<', 3)
			->whereNull('blocked_at')
			->with(['alliance', 'statistics'])
			->get();

		Statistic::query()->where('stat_code', 1)->delete();

		foreach ($users as $user) {
			if ($user->blocked_at || $user->vacation?->lessThan(now()->subSeconds(1036800))) {
				$hide = 1;
			} else {
				$hide = 0;
			}

			if ($hide == 0) {
				$active_users++;
			}

			// Запоминаем старое место в стате
			if ($user->statistics) {
				$OldTotalRank 	= $user->statistics->total_rank;
				$OldTechRank 	= $user->statistics->tech_rank;
				$OldFleetRank 	= $user->statistics->fleet_rank;
				$OldBuildRank	= $user->statistics->build_rank;
				$OldDefsRank 	= $user->statistics->defs_rank;
			} else {
				$OldTotalRank 	= 0;
				$OldTechRank 	= 0;
				$OldBuildRank 	= 0;
				$OldDefsRank 	= 0;
				$OldFleetRank 	= 0;
			}

			$Points = $this->getTechnoPoints($user);
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

			$RecordArray = [];

			foreach ($user->planets as $planet) {
				$Points = $this->getBuildPoints($planet, $user);
				$TBuildCount += $Points['BuildCount'];
				$GCount += $Points['BuildCount'];
				$PlanetPoints = ($Points['BuildPoint'] / 1000);
				$TBuildPoints += ($Points['BuildPoint'] / 1000);

				$Points = $this->getDefensePoints($planet, $RecordArray);
				$TDefsCount += $Points['DefenseCount'];
				$GCount += $Points['DefenseCount'];
				$PlanetPoints += ($Points['DefensePoint'] / 1000);
				$TDefsPoints += ($Points['DefensePoint'] / 1000);

				$Points = $this->getFleetPoints($planet, $RecordArray);
				$TFleetCount += $Points['FleetCount'];
				$GCount += $Points['FleetCount'];
				$PlanetPoints += ($Points['FleetPoint'] / 1000);
				$TFleetPoints += ($Points['FleetPoint'] / 1000);

				$GPoints += $PlanetPoints;
			}

			// Складываем очки флота
			if (isset($fleetPoints[$user->id]['points'])) {
				$TFleetCount += $fleetPoints[$user->id]['count'];
				$GCount += $fleetPoints[$user->id]['count'];
				$TFleetPoints += $fleetPoints[$user->id]['points'];
				$PlanetPoints = $fleetPoints[$user->id]['points'];
				$GPoints += $PlanetPoints;

				foreach ($fleetPoints[$user->id]['array'] as $fleet) {
					foreach ($fleet as $id => $amount) {
						if (isset($RecordArray[$id])) {
							$RecordArray[$id] += $amount;
						} else {
							$RecordArray[$id] = $amount;
						}
					}
				}
			}

			if ($user->getOption('records')) {
				foreach ($RecordArray as $id => $amount) {
					$this->setMaxInfo($id, $amount, $user);
				}
			}

			if ($user->race != 0) {
				$this->StatRace[$user->race]['count']++;
				$this->StatRace[$user->race]['total'] += $GPoints;
				$this->StatRace[$user->race]['fleet'] += $TFleetPoints;
				$this->StatRace[$user->race]['tech'] += $TTechPoints;
				$this->StatRace[$user->race]['build'] += $TBuildPoints;
				$this->StatRace[$user->race]['defs'] += $TDefsPoints;
			}

			Statistic::insert([
				'user_id' => $user->id,
				'username' => addslashes($user->username),
				'race' => $user->race,
				'alliance_id' => $user->alliance_id,
				'alliance_name' => $user->alliance?->name,
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

			Cache::delete('app::statistics_' . $user->id);
		}

		$this->calcPositions();

		$active_alliance = Statistic::query()->where('stat_type', 2)
			->where('stat_hide', 0)->count();

		$settings = app(Settings::class);

		$settings->statUpdate = time();
		$settings->activeUsers = $active_users;
		$settings->activeAlliance = $active_alliance;
		$settings->save();
	}

	private function calcPositions()
	{
		$qryFormat = 'UPDATE ' . app(Statistic::class)->getTable() . ' SET `%1$s_rank` = (SELECT @rownum:=@rownum+1) WHERE `stat_type` = %2$d AND `stat_code` = 1 AND stat_hide = 0 ORDER BY `%1$s_points` DESC, `user_id` ASC;';

		$rankNames = ['tech', 'fleet', 'defs', 'build', 'total'];

		foreach ($rankNames as $rankName) {
			DB::statement('SET @rownum=0;');
			DB::statement(sprintf($qryFormat, $rankName, 1));
		}

		DB::statement("INSERT INTO " . app(Statistic::class)->getTable() . "
		      (`tech_points`, `tech_count`, `build_points`, `build_count`, `defs_points`, `defs_count`,
		        `fleet_points`, `fleet_count`, `total_points`, `total_count`, `user_id`, `alliance_id`, `stat_type`, `stat_code`,
		        `tech_old_rank`, `build_old_rank`, `defs_old_rank`, `fleet_old_rank`, `total_old_rank`
		      )
		      SELECT
		        SUM(u.`tech_points`), SUM(u.`tech_count`), SUM(u.`build_points`), SUM(u.`build_count`), SUM(u.`defs_points`),
		        SUM(u.`defs_count`), SUM(u.`fleet_points`), SUM(u.`fleet_count`), SUM(u.`total_points`), SUM(u.`total_count`),
		        NULL, u.`alliance_id`, 2, 1,
		        COALESCE(MIN(a.tech_rank), 0), COALESCE(MIN(a.build_rank), 0), COALESCE(MIN(a.defs_rank), 0), COALESCE(MIN(a.fleet_rank), 0), COALESCE(MIN(a.total_rank), 0)
		      FROM " . app(Statistic::class)->getTable() . " as u
		        LEFT JOIN " . app(Statistic::class)->getTable() . " as a ON a.alliance_id = u.alliance_id AND a.stat_code = 2 AND a.stat_type = 2
		      WHERE u.`stat_type` = 1 AND u.stat_code = 1 AND u.alliance_id IS NOT NULL
		      GROUP BY u.`alliance_id`");

		DB::statement("UPDATE " . app(Statistic::class)->getTable() . " as new
		      LEFT JOIN " . app(Statistic::class)->getTable() . " as old ON old.alliance_id = new.alliance_id AND old.stat_code = 2 AND old.stat_type = 1
		    SET
		      new.tech_old_rank = old.tech_rank,
		      new.build_old_rank = old.build_rank,
		      new.defs_old_rank  = old.defs_rank ,
		      new.fleet_old_rank = old.fleet_rank,
		      new.total_old_rank = old.total_rank
		    WHERE
		      new.stat_type = 2 AND new.stat_code = 2;");

		Statistic::where('stat_code', '>=', 2)->delete();

		foreach ($rankNames as $rankName) {
			DB::statement('SET @rownum=0;');
			DB::statement(sprintf($qryFormat, $rankName, 2));
		}

		foreach ($this->StatRace as $race => $arr) {
			Statistic::insert([
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

		DB::statement("OPTIMIZE TABLE " . app(Statistic::class)->getTable());
	}

	public function addToLog()
	{
		DB::statement("INSERT INTO " . app(LogStat::class)->getTable() . "
			(`tech_points`, `tech_rank`, `build_points`, `build_rank`, `defs_points`, `defs_rank`, `fleet_points`, `fleet_rank`, `total_points`, `total_rank`, `object_id`, `type`, `time`)
			SELECT
				u.`tech_points`, u.`tech_rank`, u.`build_points`, u.`build_rank`, u.`defs_points`,
		        u.`defs_rank`, u.`fleet_points`, u.`fleet_rank`, u.`total_points`, u.`total_rank`,
		        u.`user_id`, 1, '" . Date::createFromTimestamp($this->start) . "'
		    FROM statistics as u
		    WHERE
		    	u.`stat_type` = 1 AND u.stat_code = 1");

		DB::statement("INSERT INTO " . app(LogStat::class)->getTable() . "
			(`tech_points`, `tech_rank`, `build_points`, `build_rank`, `defs_points`, `defs_rank`, `fleet_points`, `fleet_rank`, `total_points`, `total_rank`, `object_id`, `type`, `time`)
			SELECT
				u.`tech_points`, u.`tech_rank`, u.`build_points`, u.`build_rank`, u.`defs_points`,
		        u.`defs_rank`, u.`fleet_points`, u.`fleet_rank`, u.`total_points`, u.`total_rank`,
		        u.`alliance_id`, 2, '" . Date::createFromTimestamp($this->start) . "'
		    FROM statistics as u
		    WHERE
		    	u.`stat_type` = 2 AND u.stat_code = 1");
	}

	public function buildRecordsCache()
	{
		$Elements = Vars::getItemsByType([ItemType::BUILDING, ItemType::TECH, ItemType::FLEET, ItemType::DEFENSE]);

		$array = "";

		foreach ($Elements as $ElementID) {
			if ($ElementID != 407 && $ElementID != 408) {
				$array .= $ElementID . " => array('username' => '" . ($this->maxinfos[$ElementID]['username'] ?? '') . "', 'maxlvl' => " . ($this->maxinfos[$ElementID]['maxlvl'] ?? 0) . "),\n";
			}
		}

		$file = "<?php \n//The File is created on " . date("d. M y H:i:s") . "\n$" . "recordsArray = [\n" . $array . "\n];\n?>";

		file_put_contents(base_path('bootstrap/cache/CacheRecords.php'), $file);
	}
}
