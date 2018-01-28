<?php

namespace Xnova\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controllers\FleetController;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Fleet;
use Xnova\Helpers;
use Friday\Core\Lang;
use Xnova\Models\Planet;
use Xnova\Request;
use Xnova\User;
use Xnova\Vars;

class StageOne
{
	public function show (FleetController $controller)
	{
		if ($controller->user->vacation > 0)
			throw new ErrorException("Нет доступа!");

		Lang::includeLang('fleet', 'xnova');

		$parse = [];

		$g = (int) $controller->request->getPost('galaxy', 'int', 0);
		$s = (int) $controller->request->getPost('system', 'int', 0);
		$p = (int) $controller->request->getPost('planet', 'int', 0);
		$t = (int) $controller->request->getPost('planet_type', 'int', 0);

		if (!$g)
			$g = (int) $controller->planet->galaxy;

		if (!$s)
			$s = (int) $controller->planet->system;

		if (!$p)
			$p = (int) $controller->planet->planet;

		if (!$t)
			$t = 1;

		$parse['ships'] = [];
		$fleets = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $i)
		{
			if (isset($_POST['ship'][$i]) && intval($_POST['ship'][$i]) > 0)
			{
				$cnt = (int) $_POST['ship'][$i];

				if ($cnt > $controller->planet->getUnitCount($i))
					continue;

				$fleets[$i] = $cnt;

				$ship = $controller->getShipInfo($i);
				$ship['count'] = $cnt;

				$parse['ships'][] = $ship;
			}
		}

		if (!count($fleets))
			throw new RedirectException(_getText('fl_unselectall'), _getText('fl_error'), "/fleet/", 1);

		$parse['fleet'] = str_rot13(base64_encode(json_encode($fleets)));

		$parse['target'] = [
			'galaxy' => (int) $g,
			'system' => (int) $s,
			'planet' => (int) $p,
			'planet_type' => (int) $t,
		];

		$parse['galaxy_max'] = (int) $controller->config->game->maxGalaxyInWorld;
		$parse['system_max'] = (int) $controller->config->game->maxSystemInGalaxy;
		$parse['planet_max'] = (int) $controller->config->game->maxPlanetInSystem + 1;

		$parse['shortcuts'] = [];

		$inf = $controller->db->query("SELECT fleet_shortcut FROM game_users_info WHERE id = " . $controller->user->id . ";")->fetch();

		if ($inf['fleet_shortcut'])
		{
			$scarray = explode("\r\n", $inf['fleet_shortcut']);

			foreach ($scarray as $a => $b)
			{
				if ($b != '')
				{
					$c = explode(',', $b);

					$parse['shortcuts'][] = $c;
				}
			}
		}

		$parse['planets'] = [];

		$kolonien = User::getPlanets($controller->user->getId(), true, $controller->user->ally_id);

		if (count($kolonien) > 1)
		{
			foreach ($kolonien AS $row)
			{
				if ($row['id'] == $controller->planet->id)
					continue;

				if ($row['planet_type'] == 3)
					$row['name'] .= " " . _getText('fl_shrtcup3');

				$parse['planets'][] =  [
					'id' => $row['id'],
					'name' => $row['name'],
					'galaxy' => $row['galaxy'],
					'system' => $row['system'],
					'planet' => $row['planet'],
					'planet_type' => $row['planet_type'],
				];
			}
		}

		$parse['moon_timer'] = '';
		$parse['moons'] = [];

		if ($controller->planet->planet_type == 3 || $controller->planet->planet_type == 5)
		{
			$moons = Planet::find([
				'(planet_type = 3 OR planet_type = 5) AND id != ?0 AND id_owner = ?1',
				'bind' => [$controller->planet->id, $controller->user->id]
			]);

			if (count($moons))
			{
				$timer = $controller->planet->getNextJumpTime();

				if ($timer != 0)
					$parse['moon_timer'] = Helpers::InsertJavaScriptChronoApplet("Gate", "1", $timer);

				foreach ($moons as $moon)
				{
					if ($moon->getBuildLevel('jumpgate') <= 0)
						continue;

					$parse['moons'][] = [
						'id' => $moon->id,
						'name' => $moon->name,
						'galaxy' => $moon->galaxy,
						'system' => $moon->system,
						'planet' => $moon->planet,
						'timer' => $moon->getNextJumpTime()
					];
				}
			}
		}

		$parse['alliances'] = [];

		$alliances = $controller->db->query("SELECT a.* FROM game_aks a, game_aks_user au WHERE au.aks_id = a.id AND au.user_id = " . $controller->user->id . " ;");

		if ($alliances->numRows())
		{
			while ($row = $alliances->fetch())
				$parse['alliances'][] = $row;
		}

		$parse['mission'] = (int) $controller->request->getPost('mission', 'int', 0);

		Request::addData('page', $parse);

		$controller->tag->setTitle(_getText('fl_title_1'));
	}
}