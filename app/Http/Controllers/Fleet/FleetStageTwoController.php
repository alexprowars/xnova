<?php

namespace Xnova\Http\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Input;
use Xnova\Controller;
use Xnova\Exceptions\PageException;
use Xnova\Fleet;
use Xnova\Format;
use Xnova\Planet;
use Xnova\Vars;

class FleetStageTwoController extends Controller
{
	public function index ()
	{
		if ($this->user->vacation > 0)
			throw new PageException("Нет доступа!");

		$moon = (int) Input::post('moon', 0);

		if ($moon && $moon != $this->planet->id)
			$this->checkJumpGate($moon);

		$parse = [];

		$galaxy = (int) Input::post('galaxy', 0);
		$system = (int) Input::post('system', 0);
		$planet = (int) Input::post('planet', 0);
		$type 	= (int) Input::post('planet_type', 0);
		$acs 	= (int) Input::post('alliance', 0);

		$mission 	= (int) Input::post('mission', 0);
		$fleets 	= json_decode(base64_decode(str_rot13(Input::post('fleet', ''))), true);

		if (!count($fleets))
			throw new PageException(__('fleet.fl_unselectall'), '/fleet/');

		$YourPlanet = false;
		$UsedPlanet = false;

		$targetPlanet = Planet::findByCoords($galaxy, $system, $planet, $type);

		if ($targetPlanet)
		{
			$UsedPlanet = true;

			if ($targetPlanet->id_owner == $this->user->id)
				$YourPlanet = true;
		}

		$missions = Fleet::getFleetMissions($fleets, [$galaxy, $system, $planet, $type], $YourPlanet, $UsedPlanet, ($acs > 0));

		if ($targetPlanet && ($targetPlanet->id_owner == 1 || $this->user->isAdmin()))
			$missions[] = 4;

		$missions = array_unique($missions);

		$fleetSpeed = (int) Input::post('speed', 10);
		$stayConsumption = Fleet::GetFleetStay($fleets);

		if ($this->user->rpg_meta > time())
			$stayConsumption = ceil($stayConsumption * 0.9);

		if (in_array(15, $missions))
		{
			if ($this->user->getTechLevel('expedition') <= 0)
				unset($missions[array_search(15, $missions)]);
			else
				$parse['expedition_hours'] = round($this->user->getTechLevel('expedition') / 2) + 1;
		}

		if (!$mission && $acs && in_array(2, $missions))
			$mission = 2;

		$parse['mission'] = 0;
		$parse['missions'] = [];

		if (count($missions) > 0)
		{
			foreach ($missions as $i => $id)
			{
				if (($mission > 0 && $mission == $id) || ($i == 0 && !in_array($mission, $missions)) || count($missions) == 1)
					$parse['mission'] = $id;

				$parse['missions'][] = $id;
			}
		}

		$parse['target'] = [
			'galaxy' => $galaxy,
			'system' => $system,
			'planet' => $planet,
			'planet_type' => $type,
		];

		$parse['hold'] = $stayConsumption;
		$parse['alliance'] = $acs;
		$parse['speed'] = $fleetSpeed;
		$parse['fleet'] = Input::post('fleet', '');

		$parse['ships'] = [];

		foreach ($fleets as $i => $count)
		{
			$ship = Fleet::getShipInfo($i);
			$ship['count'] = $count;

			$parse['ships'][] = $ship;
		}

		$this->setTitle(__('fleet.fl_title_2'));

		return $parse;
	}

	private function checkJumpGate ($planetId)
	{
		if (!$this->planet->isAvailableJumpGate())
			throw new PageException(__('fleet.gate_no_dest_g'), '/fleet/');

		$nextJumpTime = $this->planet->getNextJumpTime();

		if ($nextJumpTime > 0)
			throw new PageException(__('fleet.gate_wait_star')." - ".Format::time($nextJumpTime), '/fleet/');

		/** @var Planet $targetPlanet */
		$targetPlanet = Planet::query()->find($planetId);

		if (!$targetPlanet->isAvailableJumpGate())
			throw new PageException(__('fleet.gate_no_dest_g'), '/fleet/');

		$nextJumpTime = $targetPlanet->getNextJumpTime();

		if ($nextJumpTime > 0)
			throw new PageException(__('fleet.gate_wait_dest')." - ".Format::time($nextJumpTime), '/fleet/');

		$success = false;

		$ships = Input::post('ship');
		$ships = array_map('intval', $ships);
		$ships = array_map('abs', $ships);

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $ship)
		{
			if (!isset($ships[$ship]) || !$ships[$ship])
				continue;

			if ($ships[$ship] > $this->planet->getUnitCount($ship))
				$count = $this->planet->getUnitCount($ship);
			else
				$count = $ships[$ship];

			if ($count > 0)
			{
				$this->planet->setUnit($ship, -$count, true);
				$targetPlanet->setUnit($ship, $count, true);

				$success = true;
			}
		}

		if (!$success)
			throw new PageException(__('fleet.gate_wait_data'), '/fleet/');

		$this->planet->last_jump_time = time();
		$this->planet->update();

		$targetPlanet->last_jump_time = time();
		$targetPlanet->update();

		$this->user->update(['planet_current' => $targetPlanet->id]);

		throw new PageException(__('fleet.gate_jump_done')." ".Format::time($this->planet->getNextJumpTime()), '/fleet/');
	}
}