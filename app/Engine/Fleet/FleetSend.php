<?php

namespace App\Engine\Fleet;

use App\Engine\Coordinates;
use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Enums\PlanetType;
use App\Engine\Game;
use App\Events\FleetSended;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Format;
use App\Models\AllianceDiplomacy;
use App\Models\Assault;
use App\Models\Fleet;
use App\Models\Friend;
use App\Models\LogsFleet;
use App\Models\LogsTransfer;
use App\Models\Planet;
use App\Models\Statistic;

class FleetSend
{
	protected ?Planet $targetPlanet = null;
	protected ?Assault $assault = null;
	protected array $fleetArray = [];
	protected array $resources = [];
	protected int $expeditionTime = 0;
	protected int $stayTime = 0;
	protected int $fleetSpeed = 10;
	protected ?AllianceDiplomacy $diplomacy = null;

	public function __construct(protected Planet $planet, protected Coordinates $target, protected Mission $mission)
	{
		$this->targetPlanet = Planet::query()->coordinates(new Coordinates($this->target->getGalaxy(), $this->target->getSystem(), $this->target->getPlanet()))
			->whereIn('planet_type', $this->target->getType() == PlanetType::DEBRIS ? [PlanetType::PLANET, PlanetType::MILITARY_BASE] : [$this->target->getType()])
			->first();
	}

	public function setMission(Mission $mission)
	{
		$this->mission = $mission;
	}

	public function setFleets(array $fleets)
	{
		$this->fleetArray = $fleets;
	}

	public function setAssault(Assault $assault)
	{
		$this->assault = $assault;
	}

	public function setExpeditionTime(int $time)
	{
		$this->expeditionTime = $time;
	}

	public function setStayTime(int $time)
	{
		$this->stayTime = $time;
	}

	public function setResources(array $resources)
	{
		$this->resources = array_map('intval', $resources);
	}

	public function setFleetSpeed(int $value)
	{
		$this->fleetSpeed = $value;
	}

	protected function verify()
	{
		if ($this->target->getGalaxy() > (int) config('game.maxGalaxyInWorld') || $this->target->getGalaxy() < 1) {
			throw new Exception('Ошибочная галактика!');
		} elseif ($this->target->getSystem() > (int) config('game.maxSystemInGalaxy') || $this->target->getSystem() < 1) {
			throw new Exception('Ошибочная система!');
		} elseif ($this->target->getPlanet() > (int) config('game.maxPlanetInSystem') || $this->target->getPlanet() < 1) {
			throw new Exception('Ошибочная планета!');
		} elseif (!in_array($this->target->getType(), PlanetType::cases())) {
			throw new Exception('Неизвестный тип планеты!');
		}

		if (in_array($this->mission, [Mission::Attack, Mission::Assault, Mission::Spy, Mission::Destruction]) && config('game.disableAttacks', 0) > 0 && time() < config('game.disableAttacks', 0)) {
			throw new PageException('Посылать флот в атаку временно запрещено.<br>Дата включения атак ' . Game::datezone('d.m.Y H ч. i мин.', config('game.disableAttacks', 0)), '/fleet');
		}

		if (!in_array($this->fleetSpeed, [10, 9, 8, 7, 6, 5, 4, 3, 2, 1])) {
			throw new Exception('Читеришь со скоростью?');
		}

		if (empty(array_filter($this->fleetArray))) {
			throw new PageException('Недостаточно флота для отправки на планете!');
		}

		foreach ($this->fleetArray as $ShipId => $count) {
			if ($count > $this->planet->getLevel($ShipId)) {
				throw new PageException('Недостаточно флота для отправки на планете!');
			}
		}

		$flyingFleets = Fleet::query()->whereBelongsTo($this->planet->user)->count();

		$maxFleets = 1 + $this->planet->user->getTechLevel('computer');

		if ($this->planet->user->rpg_admiral?->isFuture()) {
			$maxFleets += 2;
		}

		if ($maxFleets <= $flyingFleets) {
			throw new PageException('Все слоты флота заняты. Изучите компьютерную технологию для увеличения кол-ва летящего флота.');
		}

		if ($this->planet->coordinates->isSame($this->target)) {
			throw new Exception('Невозможно отправить флот на эту же планету!');
		}

		if ($this->mission != Mission::Expedition) {
			if (!$this->targetPlanet && $this->mission != Mission::Colonization && $this->mission != Mission::CreateBase) {
				throw new Exception('Данной планеты не существует! - [' . $this->target->getGalaxy() . ':' . $this->target->getSystem() . ':' . $this->target->getPlanet() . ']');
			} elseif ($this->mission == Mission::Destruction && !$this->targetPlanet) {
				throw new Exception('Данной планеты не существует! - [' . $this->target->getGalaxy() . ':' . $this->target->getSystem() . ':' . $this->target->getPlanet() . ']');
			} elseif (!$this->targetPlanet && $this->mission == Mission::Colonization && $this->target->getType() != PlanetType::PLANET) {
				throw new Exception('Колонизировать можно только планету!');
			}
		} else {
			if ($this->planet->user->getTechLevel('expedition') > 0) {
				$ExpeditionEnCours = Fleet::query()
					->whereBelongsTo($this->planet->user)
					->where('mission', Mission::Expedition)
					->count();

				$MaxExpedition = 1 + floor($this->planet->user->getTechLevel('expedition') / 3);
			} else {
				$MaxExpedition = 0;
				$ExpeditionEnCours = 0;
			}

			if (!$this->planet->user->getTechLevel('expedition')) {
				throw new PageException('Вами не изучена "Экспедиционная технология"!');
			} elseif ($ExpeditionEnCours >= $MaxExpedition) {
				throw new PageException('Вы уже отправили максимальное количество экспедиций!');
			}

			if ($this->expeditionTime <= 0 || $this->expeditionTime > (round($this->planet->user->getTechLevel('expedition') / 2) + 1)) {
				throw new Exception('Вы не можете столько времени летать в экспедиции!');
			}
		}

		$missions = [];

		foreach (Mission::cases() as $m) {
			if (MissionFactory::getMission($m)::isMissionPossible($this->planet, $this->target, $this->targetPlanet, $this->fleetArray, !empty($this->assault))) {
				$missions[] = $m;
			}
		}

		if (!in_array($this->mission, $missions)) {
			throw new Exception('Выполнение данной миссии невозможно!');
		}

		if ($this->mission == Mission::Recycling && $this->targetPlanet->debris_metal <= 0 && $this->targetPlanet->debris_crystal <= 0) {
			throw new PageException('Нет обломков для сбора.');
		}

		if ($this->targetPlanet) {
			$targerUser = $this->targetPlanet->user;

			if (!$targerUser) {
				throw new PageException('Неизвестная ошибка #FLTNFU' . $this->targetPlanet->user_id);
			}
		} else {
			$targerUser = $this->planet->user;
		}

		if (($targerUser->roles->isNotEmpty() && $this->planet->user->roles->isEmpty()) && ($this->mission != Mission::Stay && $this->mission != Mission::Transport)) {
			throw new PageException('На этого игрока запрещено нападать');
		}

		if ($targerUser->isVacation() && $this->mission != Mission::Recycling && !$this->planet->user->isAdmin()) {
			throw new PageException('Игрок в режиме отпуска!');
		}

		if ($this->planet->user->alliance_id != 0 && $targerUser->alliance_id != 0 && $this->mission == Mission::Attack) {
			$this->diplomacy = AllianceDiplomacy::query()
				->where('alliance_id', $targerUser->alliance_id)
				->where('diplomacy_id', $this->planet->user->alliance_id)
				->where('status', 1)
				->where('type', '<', 3)
				->first();

			if ($this->diplomacy) {
				throw new PageException('Заключён мир или перемирие с альянсом атакуемого игрока.');
			}
		}

		$protection = (int) config('game.noobprotection') > 0;

		if ($protection && $this->targetPlanet && in_array($this->mission, [Mission::Attack, Mission::Assault, Mission::StayAlly, Mission::Spy, Mission::Destruction]) && ($this->planet->user->roles->isEmpty() || $this->planet->user->hasExactRoles('operator'))) {
			$protectionPoints = (int) config('game.noobprotectionPoints');
			$protectionFactor = (int) config('game.noobprotectionFactor');

			if ($protectionPoints <= 0) {
				$protection = false;
			}

			if ($targerUser->onlinetime->diffInDays() > 7 || $targerUser->blocked_at) {
				$protection = false;
			}

			if ($this->mission == Mission::StayAlly && $targerUser->alliance_id == $this->planet->user->alliance_id) {
				$protection = false;
			}

			if ($protection) {
				$MyPoints = Statistic::select('total_points')
					->where('stat_type', 1)
					->where('stat_code', 1)
					->whereBelongsTo($this->planet->user)
					->value('total_points') ?? 0;

				$HePoints = Statistic::select('total_points')
					->where('stat_type', 1)
					->where('stat_code', 1)
					->whereBelongsTo($targerUser)
					->value('total_points') ?? 0;

				if ($HePoints < $protectionPoints) {
					throw new PageException('Игрок находится под защитой новичков!');
				}

				if ($protectionFactor && $MyPoints > $HePoints * $protectionFactor) {
					throw new PageException('Этот игрок слишком слабый для вас!');
				}
			}
		}

		if ($this->mission == Mission::Transport && array_sum($this->resources) < 1) {
			throw new Exception('Нет сырья для транспорта!');
		}

		if ($this->mission != Mission::Expedition) {
			if (!$this->targetPlanet && $this->mission->value < 7) {
				throw new Exception('Планеты не существует!');
			}

			if ($this->targetPlanet && ($this->mission == Mission::Colonization || $this->mission == Mission::CreateBase)) {
				throw new Exception('Место занято');
			}

			if ($this->targetPlanet && $this->targetPlanet->getLevel('ally_deposit') == 0 && $targerUser->id != $this->planet->user->id && $this->mission == Mission::StayAlly) {
				throw new Exception('На планете нет склада альянса!');
			}

			if ($this->mission == Mission::StayAlly) {
				$isFriends = Friend::hasFriends($this->planet->user, $targerUser);

				if ($targerUser->alliance_id != $this->planet->user->alliance_id && !$isFriends && (!$this->diplomacy || $this->diplomacy->type != 2)) {
					throw new Exception('Нельзя охранять вражеские планеты!');
				}
			}

			if ($this->targetPlanet && $this->targetPlanet->user_id == $this->planet->user->id && ($this->mission == Mission::Attack || $this->mission == Mission::Assault)) {
				throw new Exception('Невозможно атаковать самого себя!');
			}

			if ($this->targetPlanet && $this->targetPlanet->user_id == $this->planet->user->id && $this->mission == Mission::Spy) {
				throw new Exception('Невозможно шпионить самого себя!');
			}
		}
	}

	public function send()
	{
		$this->verify();

		$fleet = new Fleet();

		$fleetCollection = FleetCollection::createFromArray($this->fleetArray, $this->planet);

		$distance = $fleetCollection->getDistance($this->planet->coordinates, $this->target);
		$duration = $fleetCollection->getDuration($this->fleetSpeed, $distance);
		$consumption = $fleetCollection->getConsumption($duration, $distance);

		$fleetGroupTime = null;

		if ($this->assault) {
			// Вычисляем время самого медленного флота в совместной атаке
			$assaultFleets = Fleet::query()
				->where('assault_id', $this->assault->id)
				->get(['id', 'start_date', 'end_date']);

			$fleetGroupTime = now()->toImmutable()->addSeconds($duration);
			$arrr = [];

			foreach ($assaultFleets as $i => $flt) {
				if ($flt->start_date->greaterThan($fleetGroupTime)) {
					$fleetGroupTime = $flt->start_date;
				}

				$arrr[$i]['id'] = $flt->id;
				$arrr[$i]['start'] = $flt->start_date;
				$arrr[$i]['end'] = $flt->end_date;
			}
		}

		if ($fleetGroupTime) {
			$fleet->start_date = $fleetGroupTime;
		} else {
			$fleet->start_date = now()->toImmutable()->addSeconds($duration);
		}

		if ($this->mission == Mission::Expedition) {
			$StayDuration = $this->expeditionTime * 3600;
			$StayTime = $fleet->start_date->addSeconds($StayDuration);
		} else {
			$StayDuration = 0;
			$StayTime = null;
		}

		$fleetStorage = $fleetCollection->getStorage();
		$fleetStorage -= $consumption;

		foreach ($this->fleetArray as $shipId => $count) {
			$this->planet->updateAmount($shipId, -((int) $count), true);
		}

		$TransMetal = max(0, (int) ($this->resources['metal'] ?? 0));
		$TransCrystal = max(0, (int) ($this->resources['crystal'] ?? 0));
		$TransDeuterium = max(0, (int) ($this->resources['deuterium'] ?? 0));

		$storageNeeded = array_sum($this->resources);

		$totalFleetCons = 0;

		if ($this->mission == Mission::StayAlly) {
			if (!in_array($this->stayTime, [0, 1, 2, 4, 8, 16, 32])) {
				$this->stayTime = 0;
			}

			$fleetStayConsumption = $fleetCollection->getStayConsumption();

			$fleetStayAll = $fleetStayConsumption * $this->stayTime;

			if ($fleetStayAll >= ($this->planet->deuterium - $TransDeuterium)) {
				$totalFleetCons = $this->planet->deuterium - $TransDeuterium;
			} else {
				$totalFleetCons = $fleetStayAll;
			}

			if ($fleetStorage < $totalFleetCons) {
				$totalFleetCons = $fleetStorage;
			}

			$FleetStayTime = round(($totalFleetCons / $fleetStayConsumption) * 3600);

			$StayDuration = $FleetStayTime;
			$StayTime = $fleet->start_date->getTimestamp() + $FleetStayTime;
		}

		if ($fleetGroupTime) {
			$fleet->end_date = $fleetGroupTime->addSeconds($StayDuration + $duration);
		} else {
			$fleet->end_date = now()->toImmutable()->addSeconds($StayDuration + (2 * $duration));
		}

		$hasResources = $this->planet->metal >= $TransMetal &&
			$this->planet->crystal >= $TransCrystal &&
			($this->planet->deuterium - ($consumption + $totalFleetCons)) >= $TransDeuterium;

		if ($this->planet->deuterium < $consumption) {
			throw new Exception('Не хватает топлива на полёт! (необходимо еще ' . ($consumption - $this->planet->deuterium) . ')');
		}

		if (!$hasResources && !$this->targetPlanet) {
			throw new Exception(__('fleet.fl_noressources') . Format::number($consumption));
		}

		if ($storageNeeded > $fleetStorage) {
			throw new Exception(__('fleet.fl_nostoragespa') . Format::number($storageNeeded - $fleetStorage));
		}

		if ($this->assault && $fleetGroupTime && !empty($arrr)) {
			foreach ($arrr as $row) {
				Fleet::findOne($row['id'])
					->update([
						'start_date' => $fleetGroupTime,
						'end_date' => $fleetGroupTime->addSeconds($row['end']->timestamp - $row['start']->timestamp),
						'updated_at' => $fleetGroupTime,
					]);
			}
		}

		if ($this->mission == Mission::Transport && $this->targetPlanet->user_id != $this->planet->user->id) {
			if ($this->targetPlanet->user->onlinetime->lessThan(now()->subDays(7))) {
				throw new Exception('Вы не можете посылать флот с миссией "Транспорт" к неактивному игроку.');
			}

			$cnt = LogsTransfer::query()
				->whereBelongsTo($this->planet->user)
				->whereBelongsTo($this->targetPlanet->user, 'target')
				->where('created_at', '>', now()->subDays(7))
				->count();

			if ($cnt >= 3) {
				throw new Exception('Вы не можете посылать флот с миссией "Транспорт" другому игроку чаще 3х раз в неделю.');
			}

			$cnt = LogsTransfer::query()
				->whereBelongsTo($this->planet->user)
				->whereBelongsTo($this->targetPlanet->user, 'target')
				->where('created_at', '>', now()->subDay())
				->count();

			if ($cnt > 0) {
				throw new Exception('Вы не можете посылать флот с миссией "Транспорт" другому игроку чаще одного раза в день.');
			}

			LogsTransfer::create([
				'user_id' => $this->planet->user->id,
				'data' => [
					'planet' => $this->planet->coordinates->toArray(),
					'target' => $this->target->toArray(),
					'fleet' => $this->fleetArray,
					'resources' => ['metal' => $TransMetal, 'crystal' => $TransCrystal, 'deuterium' => $TransDeuterium],
				],
				'target_id' => $this->targetPlanet->user_id,
			]);
		}

		// Баш контроль
		if ($this->mission == Mission::Attack && !$this->planet->user->isAdmin()) {
			$log = LogsFleet::query()->where('s_id', $this->planet->user->id)
				->where('mission', Mission::Attack)
				->where('e_galaxy', $this->targetPlanet->galaxy)
				->where('e_system', $this->targetPlanet->system)
				->where('e_planet', $this->targetPlanet->planet)
				->where('created_at', '>', now()->startOfDay())
				->first();

			if ($log && $log->amount > 2 && (!$this->diplomacy || $this->diplomacy->type != 3)) {
				throw new PageException('Баш-контроль. Лимит ваших нападений на планету исчерпан.');
			}

			if ($log) {
				$log->increment('amount');
			} else {
				LogsFleet::create([
					'mission' => Mission::Attack,
					'amount' => 1,
					's_id' => $this->planet->user->id,
					's_galaxy' => $this->planet->galaxy,
					's_system' => $this->planet->system,
					's_planet' => $this->planet->planet,
					'e_id' => $this->targetPlanet->user_id,
					'e_galaxy' => $this->targetPlanet->galaxy,
					'e_system' => $this->targetPlanet->system,
					'e_planet' => $this->targetPlanet->planet,
				]);
			}
		}

		if ($this->mission == Mission::Attack) {
			$rounds = max(min(10, 6), 6);
		} else {
			$rounds = 0;
		}

		$fleet->fill([
			'user_id' 				=> $this->planet->user->id,
			'user_name' 			=> $this->planet->name,
			'mission' 				=> $this->mission,
			'entities' 				=> FleetEntityCollection::createFromArray($this->fleetArray),
			'start_galaxy' 			=> $this->planet->galaxy,
			'start_system' 			=> $this->planet->system,
			'start_planet' 			=> $this->planet->planet,
			'start_type' 			=> $this->planet->planet_type,
			'end_stay' 				=> $StayTime ?: null,
			'end_galaxy' 			=> $this->target->getGalaxy(),
			'end_system' 			=> $this->target->getSystem(),
			'end_planet' 			=> $this->target->getPlanet(),
			'end_type' 				=> $this->target->getType(),
			'resource_metal' 		=> $TransMetal,
			'resource_crystal' 		=> $TransCrystal,
			'resource_deuterium' 	=> $TransDeuterium,
			'target_user_id' 		=> $this->targetPlanet?->user_id,
			'target_user_name' 		=> $this->targetPlanet->name ?? '',
			'assault_id' 			=> $this->assault->id ?? null,
			'rounds' 				=> $rounds,
			'updated_at' 			=> $fleet->start_date,
		]);

		$fleet->save();

		$this->planet->metal 		-= $TransMetal;
		$this->planet->crystal 		-= $TransCrystal;
		$this->planet->deuterium 	-= $TransDeuterium + $consumption + $totalFleetCons;
		$this->planet->update();

		event(new FleetSended($fleet));

		return $fleet;
	}
}
