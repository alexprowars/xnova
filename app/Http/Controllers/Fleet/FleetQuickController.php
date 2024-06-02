<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Coordinates;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\FleetSend;
use App\Engine\Fleet\Mission;
use App\Engine\Game;
use App\Engine\Vars;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Exceptions\SuccessException;
use App\Http\Controllers\Controller;
use App\Models\Planet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FleetQuickController extends Controller
{
	/** @noinspection PhpRedundantCatchClauseInspection */
	public function index(Request $request)
	{
		if ($this->user->isVacation()) {
			throw new Exception('Нет доступа!');
		}

		$mission = (int) $request->query('mission', 0);
		$mission = Mission::tryFrom($mission);

		if (!$mission) {
			throw new Exception('<span class="error"><b>Не выбрана миссия!</b></span>');
		}

		$num = (int) $request->query('count', 0);

		$galaxy 	= (int) $request->query('galaxy', 0);
		$system 	= (int) $request->query('system', 0);
		$planet 	= (int) $request->query('planet', 0);

		$planetType = (int) $request->query('type', 0);
		$planetType = PlanetType::tryFrom($planetType);

		$target = Planet::coordinates(new Coordinates($galaxy, $system, $planet))
			->whereIn('planet_type', $planetType == PlanetType::DEBRIS ? [PlanetType::PLANET, PlanetType::MILITARY_BASE] : [$planetType])
			->first();

		if (!$target) {
			throw new Exception('Цели не существует!');
		}

		$fleetArray = [];

		if ($mission == Mission::Spy && ($planetType == PlanetType::PLANET || $planetType == PlanetType::MOON || $planetType == PlanetType::MILITARY_BASE)) {
			$fleetArray[210] = $num;
		} elseif ($mission == Mission::Recycling && $planetType == PlanetType::DEBRIS) {
			$debrisSize = $target->debris_metal + $target->debris_crystal;

			if ($debrisSize <= 0) {
				throw new Exception('Нет обломков для сбора!');
			}

			$recyclerNeeded = 0;

			if ($this->planet->getLevel('recycler')) {
				$fleetData = Vars::getUnitData(Vars::getIdByName('recycler'));

				$recyclerNeeded = floor($debrisSize / ($fleetData['capacity'])) + 1;
				$recyclerNeeded = min($recyclerNeeded, $this->planet->getLevel('recycler'));
			}

			if ($recyclerNeeded > 0) {
				$fleetArray[209] = $recyclerNeeded;
			} else {
				throw new Exception('Произошла какая-то непонятная ситуация');
			}
		} else {
			throw new Exception('Такой миссии не существует!');
		}

		$sender = new FleetSend(new Coordinates($galaxy, $system, $planet, $planetType), $this->planet);
		$sender->setMission($mission);
		$sender->setFleets($fleetArray);

		try {
			$fleet = DB::transaction(fn() => $sender->send());
		} catch (PageException $e) {
			throw new PageException('<span class="error"><b>' . $e->getMessage() . '</b></span>', '/fleet');
		}

		$tutorial = $this->user->quests()
			->where('finish', 0)->where('stage', 0)
			->first();

		if ($tutorial) {
			$quest = __('tutorial.tutorial', $tutorial->quest_id);

			foreach ($quest['TASK'] as $taskKey => $taskVal) {
				if ($taskKey == 'FLEET_MISSION' && $taskVal == $mission) {
					$tutorial->update(['stage' => 1]);
				}
			}
		}

		throw new SuccessException('Флот отправлен на координаты [' . $galaxy . ':' . $system . ':' . $planet . '] с миссией ' . $mission->title() . ' и прибудет к цели в ' . Game::datezone('H:i:s', $fleet->start_time));
	}
}
