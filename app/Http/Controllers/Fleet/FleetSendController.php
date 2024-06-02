<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Coordinates;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet;
use App\Engine\Fleet\FleetCollection;
use App\Engine\Fleet\Mission;
use App\Engine\Game;
use App\Engine\Vars;
use App\Exceptions\PageException;
use App\Format;
use App\Http\Controllers\Controller;
use App\Models;
use App\Models\Planet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FleetSendController extends Controller
{
	/** @noinspection PhpRedundantCatchClauseInspection */
	public function index(Request $request)
	{
		if ($this->user->isVacation()) {
			throw new PageException('Нет доступа!');
		}

		$moon = (int) $request->post('moon', 0);

		if ($moon && $moon != $this->planet->id) {
			$this->checkJumpGate($moon);
		}

		$galaxy = (int) $request->post('galaxy', 0);
		$system = (int) $request->post('system', 0);
		$planet = (int) $request->post('planet', 0);

		$planetType = (int) $request->post('planet_type', 0);
		$planetType = PlanetType::tryFrom($planetType);

		$fleetMission = (int) $request->post('mission', 0);
		$fleetMission = Mission::tryFrom($fleetMission);

		$assaultId = (int) $request->post('alliance', 0);

		$resources = $request->post('resource');
		$resources = array_map('intval', $resources);

		$fleetSpeedFactor = $request->post('speed', 10);

		$fleetArray = json_decode(base64_decode(str_rot13($request->post('fleet', ''))), true);

		$target = new Coordinates($galaxy, $system, $planet, $planetType);

		$sender = new Fleet\FleetSend($target, $this->planet);
		$sender->setMission($fleetMission);
		$sender->setFleets($fleetArray);
		$sender->setResources($resources);
		$sender->setFleetSpeed($fleetSpeedFactor);

		if ($fleetMission == Mission::Expedition) {
			$sender->setExpeditionTime((int) $request->post('expeditiontime', 0));
		}

		$holdTime = (int) $request->post('holdingtime', 0);

		if ($holdTime) {
			$sender->setStayTime($holdTime);
		}

		if ($assaultId > 0 && $fleetMission == Mission::Assault) {
			$assault = Models\Assault::query()
				->whereHas('users', function (Builder $query) use ($assaultId) {
					$query->where('user_id', $this->user->id)->where('aks_id', $assaultId);
				})
				->first();

			if ($assault && $assault->galaxy == $galaxy && $assault->system == $system && $assault->planet == $planet && $assault->planet_type == $planetType) {
				$sender->setAssault($assault);
			}

			if (!$assault) {
				$sender->setMission(Mission::Attack);
			}
		}

		try {
			$fleet = DB::transaction(fn() => $sender->send());
		} catch (PageException $e) {
			throw new PageException('<span class="error"><b>' . $e->getMessage() . '</b></span>', '/fleet');
		}

		$fleetCollection = FleetCollection::createFromArray($fleetArray, $this->planet);

		$maxFleetSpeed = $fleetCollection->getSpeed();

		$distance = $fleetCollection->getDistance($this->planet->getCoordinates(), $target);
		$duration = $fleetCollection->getDuration($fleetSpeedFactor, $distance);

		$consumption = $fleetCollection->getConsumption($duration, $distance);

		$tutorial = $this->user->quests()
			->where('finish', 0)->where('stage', 0)
			->first();

		if ($tutorial) {
			$quest = __('tutorial.tutorial.' . $tutorial->quest_id);

			foreach ($quest['TASK'] as $taskKey => $taskVal) {
				if ($taskKey == 'FLEET_MISSION' && $taskVal == $fleetMission) {
					$tutorial->update(['stage' => 1]);
				}
			}
		}

		$html  = '<div class="block-table">';
		$html .= '<div class="row">';
		$html .= '<div class="c col-12"><span class="success">' . __('fleet.fl_fleet_send') . '</span></div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="th col-6">' . __('fleet.fl_mission') . '</div>';
		$html .= '<div class="th col-6">' . $fleetMission->title() . '</div>';
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
		$html .= '<div class="th col-6">' . Game::datezone("d.m H:i:s", $fleet->start_time) . '</div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="th col-6">' . __('fleet.fl_time_back') . '</div>';
		$html .= '<div class="th col-6">' . Game::datezone("d.m H:i:s", $fleet->end_time) . '</div>';
		$html .= '</div><div class="row">';
		$html .= '<div class="c col-12">Корабли</div>';

		foreach ($fleetArray as $Ship => $Count) {
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

		$targetPlanet = Planet::find($planetId);

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

		$this->planet->last_jump_time = now();
		$this->planet->update();

		$targetPlanet->last_jump_time = now();
		$targetPlanet->update();

		$this->user->update(['planet_current' => $targetPlanet->id]);

		throw new PageException(__('fleet.gate_jump_done') . ' ' . Format::time($this->planet->getNextJumpTime()), '/fleet/');
	}
}
