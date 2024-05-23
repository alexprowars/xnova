<?php

namespace App\Http\Controllers\Fleet;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Controller;
use App\Entity\Coordinates;
use App\Exceptions\Exception;
use App\Exceptions\SuccessException;
use App\Models;
use App\Game;
use App\Planet;
use App\Vars;
use App\Entity;

class FleetQuickController extends Controller
{
	public function index(Request $request)
	{
		if ($this->user->vacation > 0) {
			throw new Exception('Нет доступа!');
		}

		$maxfleet = Models\Fleet::query()->where('user_id', $this->user->id)->count();

		$MaxFlottes = 1 + $this->user->getTechLevel('computer');

		if ($this->user->rpg_admiral > time()) {
			$MaxFlottes += 2;
		}

		$mission 	= (int) $request->query('mission', 0);
		$galaxy 	= (int) $request->query('galaxy', 0);
		$system 	= (int) $request->query('system', 0);
		$planet 	= (int) $request->query('planet', 0);
		$planetType = (int) $request->query('type', 0);
		$num 		= (int) $request->query('count', 0);

		if ($MaxFlottes <= $maxfleet) {
			throw new Exception('Все слоты флота заняты');
		} elseif ($galaxy > config('settings.maxGalaxyInWorld') || $galaxy < 1) {
			throw new Exception('Ошибочная галактика!');
		} elseif ($system > config('settings.maxSystemInGalaxy') || $system < 1) {
			throw new Exception('Ошибочная система!');
		} elseif ($planet > config('settings.maxPlanetInSystem') || $planet < 1) {
			throw new Exception('Ошибочная планета!');
		} elseif ($planetType != 1 && $planetType != 2 && $planetType != 3 && $planetType != 5) {
			throw new Exception('Ошибочный тип планеты!');
		}

		$target = Planet::query()
			->where('galaxy', $galaxy)
			->where('system', $system)
			->where('planet', $planet)
			->where(function (Builder $query) use ($planetType) {
				if ($planetType == 2) {
					$query->where('planet_type', 1)->where('planet_type', 5);
				} else {
					$query->where('planet_type');
				}
			})
			->first();

		if (!$target) {
			throw new Exception('Цели не существует!');
		}

		if (in_array($mission, [1, 2, 6, 9]) && config('settings.disableAttacks', 0) > 0 && time() < config('settings.disableAttacks', 0)) {
			throw new Exception("<span class=\"error\"><b>Посылать флот в атаку временно запрещено.<br>Дата включения атак " . Game::datezone("d.m.Y H ч. i мин.", config('settings.disableAttacks', 0)) . "</b></span>");
		}

		$FleetArray = [];
		$HeDBRec = false;

		if ($mission == 6 && ($planetType == 1 || $planetType == 3 || $planetType == 5)) {
			if ($num <= 0) {
				throw new Exception('Вы были забанены за читерство!');
			}
			if ($this->planet->getLevel('spy_sonde') == 0) {
				throw new Exception('Нет шпионских зондов ля отправки!');
			}
			if ($target->user_id == $this->user->id) {
				throw new Exception('Невозможно выполнить задание!');
			}

			$HeDBRec = Models\User::query()
				->find($target->user_id, ['id', 'onlinetime', 'vacation']);

			$MyGameLevel = Models\Statistic::query()
				->select('total_points')
				->where('stat_type', 1)
				->where('stat_code', 1)
				->where('user_id', $this->user->id)
				->value('total_points') ?? 0;

			$HeGameLevel = Models\Statistic::query()
				->select('total_points')
				->where('stat_type', 1)
				->where('stat_code', 1)
				->where('user_id', $HeDBRec->id)
				->value('total_points') ?? 0;

			if (!$HeGameLevel) {
				$HeGameLevel = 0;
			}

			if ($HeDBRec->onlinetime < (time() - 60 * 60 * 24 * 7)) {
				$NoobNoActive = 1;
			} else {
				$NoobNoActive = 0;
			}

			if ($this->user->authlevel != 3) {
				if ($NoobNoActive == 0) {
					$protectionPoints = (int) config('settings.noobprotectionPoints');
					$protectionFactor = (int) config('settings.noobprotectionFactor');

					if ($HeGameLevel < $protectionPoints) {
						throw new Exception('Игрок находится под защитой новичков!');
					}

					if ($protectionFactor && $MyGameLevel > $HeGameLevel * $protectionFactor) {
						throw new Exception('Этот игрок слишком слабый для вас!');
					}
				}
			}

			if ($HeDBRec->vacation > 0) {
				throw new Exception('Игрок в режиме отпуска!');
			}

			if ($this->planet->getLevel('spy_sonde') < $num) {
				$num = $this->planet->getLevel('spy_sonde');
			}

			$FleetArray[210] = $num;
		} elseif ($mission == 8 && $planetType == 2) {
			$DebrisSize = $target->debris_metal + $target->debris_crystal;

			if ($DebrisSize == 0) {
				throw new Exception('Нет обломков для сбора!');
			}
			if ($this->planet->getLevel('recycler') == 0) {
				throw new Exception('Нет переработчиков для сбора обломков!');
			}

			$RecyclerNeeded = 0;

			if ($this->planet->getLevel('recycler') > 0 && $DebrisSize > 0) {
				$fleetData = Vars::getUnitData(Vars::getIdByName('recycler'));

				$RecyclerNeeded = floor($DebrisSize / ($fleetData['capacity'])) + 1;

				if ($RecyclerNeeded > $this->planet->getLevel('recycler')) {
					$RecyclerNeeded = $this->planet->getLevel('recycler');
				}
			}

			if ($RecyclerNeeded > 0) {
				$FleetArray[209] = $RecyclerNeeded;
			} else {
				throw new Exception('Произошла какая-то непонятная ситуация');
			}
		} else {
			throw new Exception('Такой миссии не существует!');
		}

		$fleetCollection = Entity\FleetCollection::createFromArray($FleetArray, $this->planet);

		$FleetSpeed = $fleetCollection->getSpeed();

		if ($FleetSpeed > 0 && count($FleetArray) > 0) {
			$distance = $fleetCollection->getDistance(
				new Coordinates($this->planet->galaxy, $this->planet->system, $this->planet->planet),
				new Coordinates($galaxy, $system, $planet)
			);
			$duration = $fleetCollection->getDuration(10, $distance);
			$consumption = $fleetCollection->getConsumption($duration, $distance);

			$shipArray = [];

			foreach ($FleetArray as $shipId => $count) {
				$count = (int) $count;

				$this->planet->updateAmount($shipId, -$count, true);

				$shipArray[] = [
					'id' => (int) $shipId,
					'count' => $count
				];
			}

			$FleetStorage = $fleetCollection->getStorage();

			if ($FleetStorage < $consumption) {
				throw new Exception('Не хватает места в трюме для топлива! (необходимо еще ' . ($consumption - $FleetStorage) . ')');
			}
			if ($this->planet->deuterium < $consumption) {
				throw new Exception('Не хватает топлива на полёт! (необходимо еще ' . ($consumption - $this->planet->deuterium) . ')');
			}

			if (count($shipArray)) {
				$fleet = new Models\Fleet();

				$fleet->user_id = $this->user->id;
				$fleet->user_name = $this->planet->name;
				$fleet->mission = $mission;
				$fleet->fleet_array = $shipArray;
				$fleet->start_time = $duration + time();
				$fleet->start_galaxy = $this->planet->galaxy;
				$fleet->start_system = $this->planet->system;
				$fleet->start_planet = $this->planet->planet;
				$fleet->start_type = $this->planet->planet_type;
				$fleet->end_time = ($duration * 2) + time();
				$fleet->end_galaxy = $galaxy;
				$fleet->end_system = $system;
				$fleet->end_planet = $planet;
				$fleet->end_type = $planetType;
				$fleet->updated_at = now()->addSeconds($duration);

				if ($mission == 6 && $HeDBRec) {
					$fleet->target_user_id = $HeDBRec['id'];
					$fleet->target_user_name = $target->name;
				}

				if ($fleet->save()) {
					$this->planet->deuterium -= $consumption;
					$this->planet->update();

					$tutorial = $this->user->quests()
						->where('finish', 0)
						->where('stage', 0)
						->first();

					if ($tutorial) {
						$quest = __('tutorial.tutorial', $tutorial->quest_id);

						foreach ($quest['TASK'] as $taskKey => $taskVal) {
							if ($taskKey == 'FLEET_MISSION' && $taskVal == $mission) {
								$tutorial->update(['stage' => 1]);
							}
						}
					}

					throw new SuccessException("Флот отправлен на координаты [" . $galaxy . ":" . $system . ":" . $planet . "] с миссией " . __('main.type_mission.' . $mission) . " и прибудет к цели в " . Game::datezone("H:i:s", $duration + time()));
				}
			}
		}

		throw new Exception('Произошла ошибка');
	}
}
