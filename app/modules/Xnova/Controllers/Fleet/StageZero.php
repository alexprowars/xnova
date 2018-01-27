<?php

namespace Xnova\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controllers\FleetController;
use Friday\Core\Lang;
use Xnova\Exceptions\ErrorException;
use Xnova\Models\Fleet;
use Xnova\Request;
use Xnova\Vars;

class StageZero
{
	public function show (FleetController $controller)
	{
		if (!$controller->planet)
			throw new ErrorException(_getText('fl_noplanetrow'), _getText('fl_error'));

		$flyingFleets = Fleet::count(['owner = ?0', 'bind' => [$controller->user->id]]);

		$expeditionTech = $controller->user->getTechLevel('expedition');
		$curExpeditions = 0;
		$maxExpeditions = 0;

		if ($expeditionTech >= 1)
		{
			$curExpeditions = Fleet::count(['owner = ?0 AND mission = ?1', 'bind' => [$controller->user->id, 15]]);
			$maxExpeditions = 1 + floor($expeditionTech / 3);
		}

		$maxFleets = 1 + $controller->user->getTechLevel('computer');

		if ($controller->user->rpg_admiral > time())
			$maxFleets += 2;

		Lang::includeLang('fleet', 'xnova');

		$galaxy = $controller->request->getQuery('galaxy', 'int', 0);
		$system = $controller->request->getQuery('system', 'int', 0);
		$planet = $controller->request->getQuery('planet', 'int', 0);
		$planet_type = $controller->request->getQuery('type', 'int', 0);
		$mission = $controller->request->getQuery('mission', 'int', 0);

		if (!$galaxy)
			$galaxy = $controller->planet->galaxy;

		if (!$system)
			$system = $controller->planet->system;

		if (!$planet)
			$planet = $controller->planet->planet;

		if (!$planet_type)
			$planet_type = 1;

		$parse = [];
		$parse['curFleets'] = $flyingFleets;
		$parse['maxFleets'] = $maxFleets;
		$parse['curExpeditions'] = $curExpeditions;
		$parse['maxExpeditions'] = $maxExpeditions;
		$parse['galaxy'] = (int) $galaxy;
		$parse['system'] = (int) $system;
		$parse['planet'] = (int) $planet;
		$parse['planet_type'] = (int) $planet_type;
		$parse['mission'] = (int) $mission;

		$fleets = Fleet::find(['owner = ?0', 'bind' => [$controller->user->id]]);

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

		$parse['mission_text'] = '';

		if ($mission > 0)
			$parse['mission_text'] = ' для миссии "' . _getText('type_mission', $mission) . '"';
		if (($system > 0 && $galaxy > 0 && $planet > 0) && ($galaxy != $controller->planet->galaxy || $system != $controller->planet->system || $planet != $controller->planet->planet))
			$parse['mission_text'] = ' на координаты [' . $galaxy . ':' . $system . ':' . $planet . ']';

		$parse['ships'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $n => $i)
		{
			if ($controller->planet->getUnitCount($i) > 0)
			{
				$ship = $controller->getShipInfo($i);
				$ship['count'] = $controller->planet->getUnitCount($i);

				$parse['ships'][] = $ship;
			}
		}

		Request::addData('page', $parse);

		$controller->tag->setTitle(_getText('fl_title_0'));
	}
}