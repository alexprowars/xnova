<?php

namespace App\Http\Controllers\Fleet;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Controller;
use App\Entity\Coordinates;
use App\Entity\FleetCollection;
use App\Exceptions\ErrorException;
use App\Exceptions\PageException;
use App\Fleet;
use App\Format;
use App\Game;
use App\Models;
use App\Planet;
use App\Vars;

class FleetSendController extends Controller
{
	protected $loadPlanet = true;

	public function index(Request $request)
	{
		if ($this->user->vacation > 0) {
			throw new PageException("Нет доступа!");
		}

		$moon = (int) $request->post('moon', 0);

		if ($moon && $moon != $this->planet->id) {
			$this->checkJumpGate($moon);
		}

		$galaxy = (int) $request->post('galaxy', 0);
		$system = (int) $request->post('system', 0);
		$planet = (int) $request->post('planet', 0);
		$planet_type = (int) $request->post('planet_type', 0);

		$fleetMission = (int) $request->post('mission', 0);
		$expTime = (int) $request->post('expeditiontime', 0);

		$fleetarray = json_decode(base64_decode(str_rot13($request->post('fleet', ''))), true);

		if (!$fleetMission) {
			throw new ErrorException("<span class=\"error\"><b>Не выбрана миссия!</b></span>");
		}

		if (($fleetMission == 1 || $fleetMission == 6 || $fleetMission == 9 || $fleetMission == 2) && config('settings.disableAttacks', 0) > 0 && time() < config('settings.disableAttacks', 0)) {
			throw new PageException("<span class=\"error\"><b>Посылать флот в атаку временно запрещено.<br>Дата включения атак " . Game::datezone("d.m.Y H ч. i мин.", config('settings.disableAttacks', 0)) . "</b></span>", '/fleet/');
		}

		$allianceId = (int) $request->post('alliance', 0);

		$fleetGroupId = 0;

		if ($allianceId > 0 && $fleetMission == 2) {
			$assault = Models\Assault::query()
				->whereHas('users', function (Builder $query) use ($allianceId) {
					$query->where('user_id', $this->user->id)
						->where('aks_id', $allianceId);
				})
				->first();

			if ($assault && $assault->galaxy == $galaxy && $assault->system == $system && $assault->planet == $planet && $assault->planet_type == $planet_type) {
				$fleetGroupId = $allianceId;
			}
		}

		if (($allianceId == 0 || $fleetGroupId == 0) && $fleetMission == 2) {
			$fleetMission = 1;
		}

		$protection = (int) config('settings.noobprotection') > 0;

		if (!is_array($fleetarray)) {
			throw new PageException("<span class=\"error\"><b>Ошибка в передаче параметров!</b></span>", "/fleet/");
		}

		foreach ($fleetarray as $Ship => $Count) {
			if ($Count > $this->planet->getLevel($Ship)) {
				throw new PageException("<span class=\"error\"><b>Недостаточно флота для отправки на планете!</b></span>", "/fleet/");
			}
		}

		if ($planet_type != 1 && $planet_type != 2 && $planet_type != 3 && $planet_type != 5) {
			throw new ErrorException('Неизвестный тип планеты!');
		}

		if ($this->planet->galaxy == $galaxy && $this->planet->system == $system && $this->planet->planet == $planet && $this->planet->planet_type == $planet_type) {
			throw new ErrorException('Невозможно отправить флот на эту же планету!');
		}

		$targetPlanet = Planet::query()
			->where('galaxy', $galaxy)
			->where('system', $system)
			->where('planet', $planet)
			->where(function (Builder $query) use ($fleetMission, $planet_type) {
				if ($fleetMission == 8) {
					$query->where('planet_type', 1)
						->orWhere('planet_type', 5);
				} else {
					$query->where('planet_type', $planet_type);
				}
			})->first();

		if ($fleetMission != 15) {
			if (!$targetPlanet && $fleetMission != 7 && $fleetMission != 10) {
				throw new ErrorException("Данной планеты не существует! - [" . $galaxy . ":" . $system . ":" . $planet . "]");
			} elseif ($fleetMission == 9 && !$targetPlanet) {
				throw new ErrorException("Данной планеты не существует! - [" . $galaxy . ":" . $system . ":" . $planet . "]");
			} elseif (!$targetPlanet && $fleetMission == 7 && $planet_type != 1) {
				throw new PageException("<span class=\"error\"><b>Колонизировать можно только планету!</b></span>", "/fleet/");
			}
		} else {
			if ($this->user->getTechLevel('expedition') >= 1) {
				$ExpeditionEnCours = Models\Fleet::query()
					->where('owner', $this->user->id)
					->where('mission', 15)
					->count();

				$MaxExpedition = 1 + floor($this->user->getTechLevel('expedition') / 3);
			} else {
				$MaxExpedition = 0;
				$ExpeditionEnCours = 0;
			}

			if ($this->user->getTechLevel('expedition') == 0) {
				throw new PageException("<span class=\"error\"><b>Вами не изучена \"Экспедиционная технология\"!</b></span>", "/fleet/");
			} elseif ($ExpeditionEnCours >= $MaxExpedition) {
				throw new PageException("<span class=\"error\"><b>Вы уже отправили максимальное количество экспедиций!</b></span>", "/fleet/");
			}

			if ($expTime <= 0 || $expTime > (round($this->user->getTechLevel('expedition') / 2) + 1)) {
				throw new ErrorException('Вы не можете столько времени летать в экспедиции!');
			}
		}

		if (!$targetPlanet) {
			$YourPlanet = false;
			$UsedPlanet = false;
		} elseif ($targetPlanet->user_id == $this->user->id || ($this->user->alliance_id > 0 && $targetPlanet->alliance_id == $this->user->alliance_id)) {
			$YourPlanet = true;
			$UsedPlanet = true;
		} else {
			$YourPlanet = false;
			$UsedPlanet = true;
		}

		if ($fleetMission == 4 && ($targetPlanet->user_id == 1 || $this->user->isAdmin())) {
			$YourPlanet = true;
		}

		$missiontype = Fleet::getFleetMissions($fleetarray, [$galaxy, $system, $planet, $planet_type], $YourPlanet, $UsedPlanet, ($fleetGroupId > 0));

		if (!in_array($fleetMission, $missiontype)) {
			throw new ErrorException('Миссия неизвестна!');
		}

		if ($fleetMission == 8 && $targetPlanet->debris_metal == 0 && $targetPlanet->debris_crystal == 0) {
			if ($targetPlanet->debris_metal == 0 && $targetPlanet->debris_crystal == 0) {
				throw new PageException("<span class=\"error\"><b>Нет обломков для сбора.</b></span>", "/fleet/");
			}
		}

		if ($targetPlanet) {
			$targerUser = $targetPlanet->user;

			if (!$targerUser) {
				throw new PageException("<span class=\"error\"><b>Неизвестная ошибка #FLTNFU" . $targetPlanet->user_id . "</b></span>", "/fleet/");
			}
		} else {
			$targerUser = $this->user;
		}

		if (($targerUser->authlevel > 0 && $this->user->authlevel == 0) && ($fleetMission != 4 && $fleetMission != 3)) {
			throw new PageException("<span class=\"error\"><b>На этого игрока запрещено нападать</b></span>", "/fleet/");
		}

		$diplomacy = false;

		if ($this->user->alliance_id != 0 && $targerUser->alliance_id != 0 && $fleetMission == 1) {
			$diplomacy = Models\AllianceDiplomacy::query()
				->where('alliance_id', $targerUser->alliance_id)
				->where('d_id', $this->user->alliance_id)
				->where('status', 1)
				->where('type', '<', 3)
				->first();

			if ($diplomacy) {
				throw new PageException("<span class=\"error\"><b>Заключён мир или перемирие с альянсом атакуемого игрока.</b></span>", "/fleet/");
			}
		}

		if ($protection && $targetPlanet && in_array($fleetMission, [1, 2, 5, 6, 9]) && $this->user->authlevel < 2) {
			$protectionPoints = (int) config('settings.noobprotectionPoints');
			$protectionFactor = (int) config('settings.noobprotectionFactor');

			if ($protectionPoints <= 0) {
				$protection = false;
			}

			if ($targerUser->onlinetime < (time() - 86400 * 7) || $targerUser->banned > 0) {
				$protection = false;
			}

			if ($fleetMission == 5 && $targerUser->alliance_id == $this->user->alliance_id) {
				$protection = false;
			}

			if ($protection) {
				$MyPoints = Models\Statistic::query()
					->select('total_points')
					->where('stat_type', 1)
					->where('stat_code', 1)
					->where('user_id', $this->user->id)
					->value('total_points') ?? 0;

				$HePoints = Models\Statistic::query()
					->select('total_points')
					->where('stat_type', 1)
					->where('stat_code', 1)
					->where('user_id', $targerUser->id)
					->value('total_points') ?? 0;

				if ($HePoints < $protectionPoints) {
					throw new PageException("<span class=\"success\"><b>Игрок находится под защитой новичков!</b></span>", "/fleet/");
				}

				if ($protectionFactor && $MyPoints > $HePoints * $protectionFactor) {
					throw new PageException("<span class=\"success\"><b>Этот игрок слишком слабый для вас!</b></span>", "/fleet/");
				}
			}
		}

		if ($targerUser->vacation > 0 && $fleetMission != 8 && !$this->user->isAdmin()) {
			throw new PageException("<span class=\"success\"><b>Игрок в режиме отпуска!</b></span>", "/fleet/");
		}

		$flyingFleets = Models\Fleet::query()->where('owner', $this->user->id)->count();

		$maxFleets = $this->user->getTechLevel('computer') + 1;

		if ($this->user->rpg_admiral > time()) {
			$maxFleets += 2;
		}

		if ($maxFleets <= $flyingFleets) {
			throw new PageException('Все слоты флота заняты. Изучите компьютерную технологию для увеличения кол-ва летящего флота.', "/fleet/");
		}

		$resources = $request->post('resource');
		$resources = array_map('intval', $resources);

		if (array_sum($resources) < 1 && $fleetMission == 3) {
			throw new ErrorException('Нет сырья для транспорта!');
		}

		if ($fleetMission != 15) {
			if (!$targetPlanet && $fleetMission < 7) {
				throw new ErrorException('Планеты не существует!');
			}

			if ($targetPlanet && ($fleetMission == 7 || $fleetMission == 10)) {
				throw new ErrorException('Место занято');
			}

			if ($targetPlanet && $targetPlanet->getLevel('ally_deposit') == 0 && $targerUser->id != $this->user->id && $fleetMission == 5) {
				throw new ErrorException('На планете нет склада альянса!');
			}

			if ($fleetMission == 5) {
				$friend = Models\Friend::query()
					->where(function (Builder $query) use ($targerUser) {
						$query->where(function (Builder $query) use ($targerUser) {
							$query->where('sender', $this->user->id)
								->where('owner', $targerUser->id);
						})
						->orWhere(function (Builder $query) use ($targerUser) {
							$query->where('owner', $this->user->id)
								->where('sender', $targerUser->id);
						});
					})
					->where('active', 1)->first();

				if ($targerUser->alliance_id != $this->user->alliance_id && !$friend && (!$diplomacy || ($diplomacy && $diplomacy['type'] != 2))) {
					throw new ErrorException('Нельзя охранять вражеские планеты!');
				}
			}

			if ($targetPlanet && $targetPlanet->user_id == $this->user->id && ($fleetMission == 1 || $fleetMission == 2)) {
				throw new ErrorException('Невозможно атаковать самого себя!');
			}

			if ($targetPlanet && $targetPlanet->user_id == $this->user->id && $fleetMission == 6) {
				throw new ErrorException('Невозможно шпионить самого себя!');
			}

			if (!$YourPlanet && $fleetMission == 4) {
				throw new ErrorException('Выполнение данной миссии невозможно!');
			}
		}

		$speedPossible = [10, 9, 8, 7, 6, 5, 4, 3, 2, 1];

		$fleetCollection = FleetCollection::createFromArray($fleetarray, $this->planet);

		$maxFleetSpeed 		= $fleetCollection->getSpeed();
		$fleetSpeedFactor 	= $request->post('speed', 10);

		if (!in_array($fleetSpeedFactor, $speedPossible)) {
			throw new ErrorException('Читеришь со скоростью?');
		}

		if (!$planet_type) {
			throw new ErrorException('Ошибочный тип планеты!');
		}

		$errorlist = "";

		if (!$galaxy || $galaxy > config('settings.maxGalaxyInWorld') || $galaxy < 1) {
			$errorlist .= __('fleet.fl_limit_galaxy');
		}

		if (!$system || $system > config('settings.maxSystemInGalaxy') || $system < 1) {
			$errorlist .= __('fleet.fl_limit_system');
		}

		if (!$planet || $planet > (config('settings.maxPlanetInSystem') + 1) || $planet < 1) {
			$errorlist .= __('fleet.fl_limit_planet');
		}

		if ($errorlist != '') {
			throw new PageException("<span class=\"error\">" . $errorlist . "</span>", '/fleet/');
		}

		$fleet = new Models\Fleet();

		$distance = $fleetCollection->getDistance(
			new Coordinates($this->planet->galaxy, $this->planet->system, $this->planet->planet),
			new Coordinates($galaxy, $system, $planet)
		);

		$duration = $fleetCollection->getDuration($fleetSpeedFactor, $distance);
		$consumption = $fleetCollection->getConsumption($duration, $distance);

		$fleet_group_time = 0;

		if ($fleetGroupId > 0) {
			// Вычисляем время самого медленного флота в совместной атаке
			$flet = Models\Fleet::query()
				->where('group_id', $fleetGroupId)
				->get(['id', 'start_time', 'end_time']);

			$fleet_group_time = $duration + time();
			$arrr = [];

			foreach ($flet as $i => $flt) {
				if ($flt->start_time > $fleet_group_time) {
					$fleet_group_time = $flt->start_time;
				}

				$arrr[$i]['id'] = $flt->id;
				$arrr[$i]['start'] = $flt->start_time;
				$arrr[$i]['end'] = $flt->end_time;
			}
		}

		if ($fleetGroupId > 0) {
			$fleet->start_time = $fleet_group_time;
		} else {
			$fleet->start_time = $duration + time();
		}

		if ($fleetMission == 15) {
			$StayDuration = $expTime * 3600;
			$StayTime = $fleet->start_time + $StayDuration;
		} else {
			$StayDuration = 0;
			$StayTime = 0;
		}

		$fleetStorage = 0;
		$fleetArray = [];

		foreach ($fleetarray as $Ship => $Count) {
			$fleetData = Vars::getUnitData($Ship);

			$Count = (int) $Count;
			$fleetStorage += $fleetData['capacity'] * $Count;

			$fleetArray[] = [
				'id' => (int) $Ship,
				'count' => $Count,
			];

			$this->planet->updateAmount($Ship, -$Count, true);
		}

		$fleetStorage -= $consumption;
		$StorageNeeded = 0;

		if ($resources['metal'] < 1) {
			$TransMetal = 0;
		} else {
			$TransMetal = $resources['metal'];
			$StorageNeeded += $TransMetal;
		}

		if ($resources['crystal'] < 1) {
			$TransCrystal = 0;
		} else {
			$TransCrystal = $resources['crystal'];
			$StorageNeeded += $TransCrystal;
		}

		if ($resources['deuterium'] < 1) {
			$TransDeuterium = 0;
		} else {
			$TransDeuterium = $resources['deuterium'];
			$StorageNeeded += $TransDeuterium;
		}

		$TotalFleetCons = 0;

		if ($fleetMission == 5) {
			$holdTime = (int) $request->post('holdingtime', 0);

			if (!in_array($holdTime, [0, 1, 2, 4, 8, 16, 32])) {
				$holdTime = 0;
			}

			$FleetStayConsumption = $fleetCollection->getStayConsumption();

			$FleetStayAll = $FleetStayConsumption * $holdTime;

			if ($FleetStayAll >= ($this->planet->deuterium - $TransDeuterium)) {
				$TotalFleetCons = $this->planet->deuterium - $TransDeuterium;
			} else {
				$TotalFleetCons = $FleetStayAll;
			}

			if ($fleetStorage < $TotalFleetCons) {
				$TotalFleetCons = $fleetStorage;
			}

			$FleetStayTime = round(($TotalFleetCons / $FleetStayConsumption) * 3600);

			$StayDuration = $FleetStayTime;
			$StayTime = $fleet->start_time + $FleetStayTime;
		}

		if ($fleetGroupId > 0) {
			$fleet->end_time = $StayDuration + $duration + $fleet_group_time;
		} else {
			$fleet->end_time = $StayDuration + (2 * $duration) + time();
		}

		$StockMetal 	= $this->planet->metal;
		$StockCrystal 	= $this->planet->crystal;
		$StockDeuterium = $this->planet->deuterium - ($consumption + $TotalFleetCons);

		$StockOk = ($StockMetal >= $TransMetal && $StockCrystal >= $TransCrystal && $StockDeuterium >= $TransDeuterium);

		if (!$StockOk && (!$targetPlanet || $targetPlanet->user_id != 1)) {
			throw new ErrorException(__('fleet.fl_noressources') . Format::number($consumption));
		}

		if ($StorageNeeded > $fleetStorage && !$this->user->isAdmin()) {
			throw new ErrorException(__('fleet.fl_nostoragespa') . Format::number($StorageNeeded - $fleetStorage));
		}

		// Баш контроль
		if ($fleetMission == 1) {
			$night_time = mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()));

			$log = Models\Log::query()->where('s_id', $this->user->id)
				->where('mission', 1)
				->where('e_galaxy', $targetPlanet->galaxy)
				->where('e_system', $targetPlanet->system)
				->where('e_planet', $targetPlanet->planet)
				->where('time', '>', $night_time)
				->first();

			if (!$this->user->isAdmin() && $log && $log->amount > 2 && (($diplomacy && $diplomacy['type'] != 3) || !$diplomacy)) {
				throw new PageException("<span class=\"error\"><b>Баш-контроль. Лимит ваших нападений на планету исчерпан.</b></span>", "/fleet/");
			}

			if ($log) {
				Models\Log::find($log->id)->increment('amount');
			} else {
				Models\Log::insert([
					'mission' => 1,
					'time' => now(),
					'amount' => 1,
					's_id' => $this->user->id,
					's_galaxy' => $this->planet->galaxy,
					's_system' => $this->planet->system,
					's_planet' => $this->planet->planet,
					'e_id' => $targetPlanet->user_id,
					'e_galaxy' => $targetPlanet->galaxy,
					'e_system' => $targetPlanet->system,
					'e_planet' => $targetPlanet->planet,
				]);
			}
		}
		//

		// Увод флота
		//$fleets_num = $this->db->query("SELECT id FROM fleets WHERE mission = '1' AND end_galaxy = ".$this->planet->data['galaxy']." AND end_system = ".$this->planet->data['system']." AND end_planet = ".$this->planet->data['planet']." AND end_type = ".$this->planet->data['planet_type']." AND start_time < ".(time() + 5)."");

		//if (db::num_rows($fleets_num) > 0)
		//		message ("<span class=\"error\"><b>Ваш флот не может взлететь из-за находящегося поблизости от орбиты планеты атакующего флота.</b></span>", 'Ошибка', "fleet." . $phpEx, 2);
		//

		if ($fleetGroupId > 0 && $fleet_group_time > 0 && isset($arrr)) {
			foreach ($arrr as $row) {
				$end = $fleet_group_time + $row['end'] - $row['start'];

				Models\Fleet::query()
					->where('id', $row['id'])
					->update([
						'start_time' => $fleet_group_time,
						'end_time' => $end,
						'update_time' => $fleet_group_time
					]);
			}
		}

		/*if (($fleetMission == 1 || $fleetMission == 2 || $fleetMission == 3) && $targerUser['id'] != $this->user->id && !$this->user->isAdmin())
		{
			$check = $this->db->fetchColumn("SELECT COUNT(*) as num FROM log_ip WHERE id = ".$targerUser['id']." AND time > ".(time() - 86400 * 3)." AND ip IN (SELECT ip FROM game_log_ip WHERE id = ".$this->user->id." AND time > ".(time() - 86400 * 3).")");

			if ($check > 0 || $targerUser['ip'] == $this->user->ip)
				throw new RedirectException("<span class=\"error\"><b>Вы не можете посылать флот с миссией \"Транспорт\" и \"Атака\" к игрокам, с которыми были пересечения по IP адресу.</b></span>", 'Ошибка', "/fleet/", 5);
		}*/

		if ($fleetMission == 3 && $targerUser->id != $this->user->id && !$this->user->isAdmin()) {
			if ($targerUser->onlinetime < (time() - 86400 * 7)) {
				throw new ErrorException('Вы не можете посылать флот с миссией "Транспорт" к неактивному игроку.');
			}

			$cnt = DB::table('log_transfers')
				->where('user_id', $this->user->id)
				->where('target_id', $targerUser->id)
				->where('time', '>', time() - 86400 * 7)
				->count();

			if ($cnt >= 3) {
				throw new ErrorException('Вы не можете посылать флот с миссией "Транспорт" другому игроку чаще 3х раз в неделю.');
			}

			$cnt = DB::table('log_transfers')
				->where('user_id', $this->user->id)
				->where('target_id', $targerUser->id)
				->where('time', '>', time() - 86400 * 1)
				->count();

			if ($cnt > 0) {
				throw new ErrorException('Вы не можете посылать флот с миссией "Транспорт" другому игроку чаще одного раза в день.');
			}

			//$equiv = $TransMetal + $TransCrystal * 2 + $TransDeuterium * 4;

			//if ($equiv > 15000000)
			//	throw new RedirectException("<span class=\"error\"><b>Вы не можете посылать флот с миссией \"Транспорт\" другому игроку с количеством ресурсов большим чем 15кк в эквиваленте металла.</b></span>", 'Ошибка', "/fleet/", 5);

			DB::table('log_transfers')->insert([
				'time' => time(),
				'user_id' => $this->user->id,
				'data' => json_encode([
					'planet' => [
						'galaxy' => $this->planet->galaxy,
						'system' => $this->planet->system,
						'planet' => $this->planet->planet,
						'type' => $this->planet->planet_type,
					],
					'target' => [
						'galaxy' => $galaxy,
						'system' => $system,
						'planet' => $planet,
						'type' => $planet_type,
					],
					'fleet' => $fleetArray,
					'resources' => [
						'metal' => $TransMetal,
						'crystal' => $TransCrystal,
						'deuterium' => $TransDeuterium,
					],
				]),
				'target_id' => $targetPlanet->user_id,
			]);

			$str_error = "Информация о передаче ресурсов добавлена в журнал оператора.<br>";
		}

		if (false && $targetPlanet && $targetPlanet->user_id == 1) {
			$fleet->start_time = time() + 30;
			$fleet->end_time = time() + 60;

			$consumption = 0;
		}

		if (false && $this->user->isAdmin() && $fleetMission != 6) {
			$fleet->start_time 	= time() + 15;
			$fleet->end_time 	= time() + 30;

			if ($StayTime) {
				$StayTime = $fleet->start_time + 5;
			}

			$consumption = 0;
		}

		$tutorial = $this->user->quests()
			->where('finish', 0)->where('stage', 0)
			->first();

		if ($tutorial) {
			$quest = __('tutorial.tutorial', $tutorial->quest_id);

			foreach ($quest['TASK'] as $taskKey => $taskVal) {
				if ($taskKey == 'FLEET_MISSION' && $taskVal == $fleetMission) {
					$tutorial->update(['stage' => 1]);
				}
			}
		}

		if ($fleetMission == 1) {
			$raunds = $request->post('raunds', 6);
			$raunds = max(min(10, $raunds), 6);
		} else {
			$raunds = 0;
		}

		$fleet->fill([
			'owner' 				=> $this->user->id,
			'owner_name' 			=> $this->planet->name,
			'mission' 				=> $fleetMission,
			'fleet_array' 			=> $fleetArray,
			'start_galaxy' 			=> $this->planet->galaxy,
			'start_system' 			=> $this->planet->system,
			'start_planet' 			=> $this->planet->planet,
			'start_type' 			=> $this->planet->planet_type,
			'end_stay' 				=> $StayTime,
			'end_galaxy' 			=> $galaxy,
			'end_system' 			=> $system,
			'end_planet' 			=> $planet,
			'end_type' 				=> $planet_type,
			'resource_metal' 		=> $TransMetal,
			'resource_crystal' 		=> $TransCrystal,
			'resource_deuterium' 	=> $TransDeuterium,
			'target_owner' 			=> $targetPlanet ? $targetPlanet->user_id : 0,
			'target_owner_name' 	=> $targetPlanet ? $targetPlanet->name : '',
			'group_id' 				=> $fleetGroupId,
			'raunds' 				=> $raunds,
			'create_time' 			=> time(),
			'update_time' 			=> $fleet->start_time,
		]);
		$fleet->save();

		$this->planet->metal 		-= $TransMetal;
		$this->planet->crystal 		-= $TransCrystal;
		$this->planet->deuterium 	-= $TransDeuterium + $consumption + $TotalFleetCons;

		$this->planet->update();

		$html  = '<div class="table">';
		$html .= '<div class="row">';
		$html .= '<div class="c col-12"><span class="success">' . ((isset($str_error)) ? $str_error : __('fleet.fl_fleet_send')) . '</span></div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="th col-6">' . __('fleet.fl_mission') . '</div>';
		$html .= '<div class="th col-6">' . __('main.type_mission.' . $fleetMission) . '</div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="th col-6">' . __('fleet.fl_dist') . '</div>';
		$html .= '<div class="th col-6">' . Format::number($distance) . '</div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="th col-6">' . __('fleet.fl_speed') . '</div>';
		$html .= '<div class="th col-6">' . Format::number($maxFleetSpeed) . '</div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="th col-6">' . __('fleet.fl_deute_need') . '</div>';
		$html .= '<div class="th col-6">' . Format::number($consumption) . '</div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="th col-6">' . __('fleet.fl_from') . '</div>';
		$html .= '<div class="th col-6">' . $this->planet->galaxy . ":" . $this->planet->system . ":" . $this->planet->planet . '</div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="th col-6">' . __('fleet.fl_dest') . '</div>';
		$html .= '<div class="th col-6">' . $galaxy . ":" . $system . ":" . $planet . '</div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="th col-6">' . __('fleet.fl_time_go') . '</div>';
		$html .= '<div class="th col-6">' . Game::datezone("d H:i:s", $fleet->start_time) . '</div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="th col-6">' . __('fleet.fl_time_back') . '</div>';
		$html .= '<div class="th col-6">' . Game::datezone("d H:i:s", $fleet->end_time) . '</div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="c col-12">Корабли</div>';

		foreach ($fleetarray as $Ship => $Count) {
			$html .= '</div><div class="row">';
			$html .= '<div class="th col-6">' . __('main.tech.' . $Ship) . '</div>';
			$html .= '<div class="th col-6">' . Format::number($Count) . '</div>';
		}

		$html .= '</div></div>';

		throw new PageException($html);
	}

	private function checkJumpGate($planetId)
	{
		if (!$this->planet->isAvailableJumpGate()) {
			throw new PageException(__('fleet.gate_no_dest_g'), '/fleet/');
		}

		$nextJumpTime = $this->planet->getNextJumpTime();

		if ($nextJumpTime > 0) {
			throw new PageException(__('fleet.gate_wait_star') . " - " . Format::time($nextJumpTime), '/fleet/');
		}

		$targetPlanet = Planet::query()->find($planetId);

		if (!$targetPlanet->isAvailableJumpGate()) {
			throw new PageException(__('fleet.gate_no_dest_g'), '/fleet/');
		}

		$nextJumpTime = $targetPlanet->getNextJumpTime();

		if ($nextJumpTime > 0) {
			throw new PageException(__('fleet.gate_wait_dest') . " - " . Format::time($nextJumpTime), '/fleet/');
		}

		$success = false;

		$ships = request()->post('ship');
		$ships = array_map('intval', $ships);
		$ships = array_map('abs', $ships);

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $ship) {
			if (!isset($ships[$ship]) || !$ships[$ship]) {
				continue;
			}

			if ($ships[$ship] > $this->planet->getLevel($ship)) {
				$count = $this->planet->getLevel($ship);
			} else {
				$count = $ships[$ship];
			}

			if ($count > 0) {
				$this->planet->updateAmount($ship, -$count, true);
				$targetPlanet->updateAmount($ship, $count, true);

				$success = true;
			}
		}

		if (!$success) {
			throw new PageException(__('fleet.gate_wait_data'), '/fleet/');
		}

		$this->planet->last_jump_time = time();
		$this->planet->update();

		$targetPlanet->last_jump_time = time();
		$targetPlanet->update();

		$this->user->update(['planet_current' => $targetPlanet->id]);

		throw new PageException(__('fleet.gate_jump_done') . " " . Format::time($this->planet->getNextJumpTime()), '/fleet/');
	}
}
