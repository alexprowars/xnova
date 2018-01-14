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
use Xnova\Vars;

class StageZero
{
	public function show (FleetController $controller)
	{
		if (!$controller->planet)
			throw new ErrorException(_getText('fl_noplanetrow'), _getText('fl_error'));

		$MaxFlyingFleets = Fleet::count(['owner = ?0', 'bind' => [$controller->user->id]]);

		$MaxExpedition = $controller->user->getTechLevel('expedition');
		$ExpeditionEnCours = 0;
		$EnvoiMaxExpedition = 0;

		if ($MaxExpedition >= 1)
		{
			$ExpeditionEnCours = Fleet::count(['owner = ?0 AND mission = ?1', 'bind' => [$controller->user->id, 15]]);;
			$EnvoiMaxExpedition = 1 + floor($MaxExpedition / 3);
		}

		$MaxFlottes = 1 + $controller->user->getTechLevel('computer');
		if ($controller->user->rpg_admiral > time())
			$MaxFlottes += 2;

		Lang::includeLang('fleet', 'xnova');

		$galaxy = $controller->request->getQuery('galaxy', 'int', 0);
		$system = $controller->request->getQuery('system', 'int', 0);
		$planet = $controller->request->getQuery('planet', 'int', 0);
		$planettype = $controller->request->getQuery('type', 'int', 0);
		$target_mission = $controller->request->getQuery('mission', 'int', 0);

		if (!$galaxy)
			$galaxy = $controller->planet->galaxy;

		if (!$system)
			$system = $controller->planet->system;

		if (!$planet)
			$planet = $controller->planet->planet;

		if (!$planettype)
			$planettype = 1;

		$parse = [];
		$parse['maxFlyingFleets'] = $MaxFlyingFleets;
		$parse['maxFlottes'] = $MaxFlottes;
		$parse['currentExpeditions'] = $ExpeditionEnCours;
		$parse['maxExpeditions'] = $EnvoiMaxExpedition;
		$parse['galaxy'] = $galaxy;
		$parse['system'] = $system;
		$parse['planet'] = $planet;
		$parse['planettype'] = $planettype;
		$parse['mission'] = $target_mission;

		$fq = Fleet::find(['owner = ?0', 'bind' => [$controller->user->id]]);

		$parse['fleets'] = [];

		foreach ($fq as $f)
		{
			$item = $f->toArray();
			$item['fleet_count'] = $f->getTotalShips();
			$item['fleet_array'] = $f->getShips();

			$parse['fleets'][] = $item;
		}

		$parse['mission_text'] = '';

		if ($target_mission > 0)
			$parse['mission_text'] = ' для миссии "' . _getText('type_mission', $target_mission) . '"';
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

		$controller->view->setVar('parse', $parse);
		$controller->tag->setTitle(_getText('fl_title_0'));
	}
}