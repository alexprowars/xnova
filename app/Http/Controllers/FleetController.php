<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\ErrorException;
use App\Models;
use App\Controller;
use App\Vars;
use App\Entity;

class FleetController extends Controller
{
	protected $loadPlanet = true;

	public function index(Request $request)
	{
		if (!$this->planet) {
			throw new ErrorException(__('fleet.fl_noplanetrow'));
		}

		$flyingFleets = Models\Fleet::query()->where('owner', $this->user->id)->count();

		$expeditionTech = $this->user->getTechLevel('expedition');
		$curExpeditions = 0;
		$maxExpeditions = 0;

		if ($expeditionTech >= 1) {
			$curExpeditions = Models\Fleet::query()->where('owner', $this->user->id)->where('mission', 15)->count();
			$maxExpeditions = 1 + floor($expeditionTech / 3);
		}

		$maxFleets = 1 + $this->user->getTechLevel('computer');

		if ($this->user->rpg_admiral > time()) {
			$maxFleets += 2;
		}

		$galaxy = (int) $request->query('galaxy', 0);
		$system = (int) $request->query('system', 0);
		$planet = (int) $request->query('planet', 0);
		$planet_type = (int) $request->query('type', 0);
		$mission = (int) $request->query('mission', 0);

		if (!$galaxy) {
			$galaxy = (int) $this->planet->galaxy;
		}

		if (!$system) {
			$system = (int) $this->planet->system;
		}

		if (!$planet) {
			$planet = (int) $this->planet->planet;
		}

		if (!$planet_type) {
			$planet_type = 1;
		}

		$parse = [];
		$parse['curFleets'] = $flyingFleets;
		$parse['maxFleets'] = $maxFleets;
		$parse['curExpeditions'] = $curExpeditions;
		$parse['maxExpeditions'] = $maxExpeditions;
		$parse['mission'] = (int) $mission;

		$fleets = Models\Fleet::query()->where('owner', $this->user->id)->get();

		$parse['fleets'] = [];

		foreach ($fleets as $fleet) {
			$parse['fleets'][] = [
				'id' => (int) $fleet->id,
				'mission' => (int) $fleet->mission,
				'amount' => $fleet->getTotalShips(),
				'units' => $fleet->getShips(),
				'start' => [
					'galaxy' => (int) $fleet->start_galaxy,
					'system' => (int) $fleet->start_system,
					'planet' => (int) $fleet->start_planet,
					'time' => (int) $fleet->start_time
				],
				'target' => [
					'galaxy' => (int) $fleet->end_galaxy,
					'system' => (int) $fleet->end_system,
					'planet' => (int) $fleet->end_planet,
					'time' => (int) $fleet->end_time,
					'id' => (int) $fleet->target_owner
				],
				'stage' => (int) $fleet->mess
			];
		}

		$isCurrent = $galaxy == $this->planet->galaxy && $system == $this->planet->system && $planet == $this->planet->planet;

		$parse['selected'] = [
			'mission' => $mission,
			'galaxy' => !$isCurrent ? $galaxy : 0,
			'system' => !$isCurrent ? $system : 0,
			'planet' => !$isCurrent ? $planet : 0,
			'planet_type' => $planet_type,
		];

		$parse['ships'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $i) {
			if ($this->planet->getLevel($i) > 0) {
				$parse['ships'][] = (new \App\Planet\Entity\Ship($i, $this->planet->getLevel($i)))->getInfo();
			}
		}

		$this->setTitle(__('fleet.fl_title_0'));

		return $parse;
	}
}
