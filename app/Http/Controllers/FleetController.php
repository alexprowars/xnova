<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Request;
use Xnova\Exceptions\ErrorException;
use Xnova\Fleet;
use Xnova\Models;
use Xnova\Controller;
use Xnova\Vars;

class FleetController extends Controller
{
	private $loadPlanet = true;

	public function __construct ()
	{
		parent::__construct();

		$this->middleware(function ($request, $next)
		{
			// Устанавливаем обновлённые двигателя кораблей
			Fleet::SetShipsEngine($this->user);

			return $next($request);
		});
	}

	public function index ()
	{
		if (!$this->planet)
			throw new ErrorException(__('fleet.fl_noplanetrow'));

		$flyingFleets = Models\Fleet::query()->where('owner', $this->user->id)->count();

		$expeditionTech = $this->user->getTechLevel('expedition');
		$curExpeditions = 0;
		$maxExpeditions = 0;

		if ($expeditionTech >= 1)
		{
			$curExpeditions = Models\Fleet::query()->where('owner', $this->user->id)->where('mission', 15)->count();
			$maxExpeditions = 1 + floor($expeditionTech / 3);
		}

		$maxFleets = 1 + $this->user->getTechLevel('computer');

		if ($this->user->rpg_admiral > time())
			$maxFleets += 2;

		$galaxy = (int) Request::query('galaxy', 0);
		$system = (int) Request::query('system', 0);
		$planet = (int) Request::query('planet', 0);
		$planet_type = (int) Request::query('type', 0);
		$mission = (int) Request::query('mission', 0);

		if (!$galaxy)
			$galaxy = (int) $this->planet->galaxy;

		if (!$system)
			$system = (int) $this->planet->system;

		if (!$planet)
			$planet = (int) $this->planet->planet;

		if (!$planet_type)
			$planet_type = 1;

		$parse = [];
		$parse['curFleets'] = $flyingFleets;
		$parse['maxFleets'] = $maxFleets;
		$parse['curExpeditions'] = $curExpeditions;
		$parse['maxExpeditions'] = $maxExpeditions;
		$parse['mission'] = (int) $mission;

		$fleets = Models\Fleet::query()->where('owner', $this->user->id)->get();

		$parse['fleets'] = [];

		foreach ($fleets as $fleet)
		{
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

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $n => $i)
		{
			if ($this->planet->getUnitCount($i) > 0)
			{
				$ship = Fleet::getShipInfo($i);
				$ship['count'] = $this->planet->getUnitCount($i);

				$parse['ships'][] = $ship;
			}
		}

		$this->setTitle(__('fleet.fl_title_0'));

		return $parse;
	}
}