<?php

namespace Xnova\Http\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Xnova\Controller;
use Xnova\Exceptions\PageException;
use Xnova\Fleet;
use Xnova\Planet;
use Xnova\Models;
use Xnova\Vars;

class FleetStageOneController extends Controller
{
	public function index ()
	{
		if ($this->user->vacation > 0)
			throw new PageException('Нет доступа!');

		$parse = [];

		$g = (int) Input::post('galaxy', 0);
		$s = (int) Input::post('system', 0);
		$p = (int) Input::post('planet', 0);
		$t = (int) Input::post('planet_type', 0);

		if (!$g)
			$g = (int) $this->planet->galaxy;

		if (!$s)
			$s = (int) $this->planet->system;

		if (!$p)
			$p = (int) $this->planet->planet;

		if (!$t)
			$t = 1;

		$parse['ships'] = [];
		$fleets = [];

		$ships = Input::post('ship');

		if (!is_array($ships))
			$ships = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $i)
		{
			if (isset($ships[$i]) && (int) $ships[$i] > 0)
			{
				$cnt = (int) $ships[$i];

				if ($cnt > $this->planet->getUnitCount($i))
					continue;

				$fleets[$i] = $cnt;

				$ship = Fleet::getShipInfo($i);
				$ship['count'] = $cnt;

				$parse['ships'][] = $ship;
			}
		}

		if (!count($fleets))
			throw new PageException(__('fleet.fl_unselectall'), '/fleet/');

		$parse['fleet'] = str_rot13(base64_encode(json_encode($fleets)));

		$parse['target'] = [
			'galaxy' => (int) $g,
			'system' => (int) $s,
			'planet' => (int) $p,
			'planet_type' => (int) $t,
		];

		$parse['galaxy_max'] = (int) Config::get('game.maxGalaxyInWorld');
		$parse['system_max'] = (int) Config::get('game.maxSystemInGalaxy');
		$parse['planet_max'] = (int) Config::get('game.maxPlanetInSystem') + 1;

		$parse['shortcuts'] = [];

		$shortcuts = Models\UsersInfo::query()->find($this->user->id, ['fleet_shortcut'])->value('fleet_shortcut');

		if ($shortcuts)
		{
			$scarray = explode("\r\n", $shortcuts);

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

		$kolonien = $this->user->getPlanets();

		if (count($kolonien) > 1)
		{
			foreach ($kolonien AS $row)
			{
				if ($row->id == $this->planet->id)
					continue;

				if ($row->planet_type == 3)
					$row->name .= " " . __('fleet.fl_shrtcup3');

				$parse['planets'][] =  [
					'id' => $row->id,
					'name' => $row->name,
					'galaxy' => $row->galaxy,
					'system' => $row->system,
					'planet' => $row->planet,
					'planet_type' => $row->planet_type,
				];
			}
		}

		$parse['gate_time'] = 0;
		$parse['moons'] = [];

		if ($this->planet->planet_type == 3 || $this->planet->planet_type == 5)
		{
			$moons = Planet::query()
				->where(function (Builder $planet) {
					$planet->where('planet_type', 3)
						->orWhere('planet_type', 5);
				})
				->where('id', '!=', $this->planet->id)
				->where('id_owner', $this->user->id)
				->get();

			if ($moons->count())
			{
				$timer = $this->planet->getNextJumpTime();

				if ($timer != 0)
					$parse['gate_time'] = $timer;

				/** @var Planet $moon */
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

		$alliances = DB::table('aks')
			->select('aks.*')
			->join('aks_user', 'aks_user.aks_id', '=', 'aks.id')
			->where('aks_user.user_id', $this->user->id)
			->get();

		if ($alliances->count())
		{
			foreach ($alliances as $row)
			{
				$parse['alliances'][] = [
					'id' => (int) $row->id,
					'name' => $row->name,
					'galaxy' => (int) $row->galaxy,
					'system' => (int) $row->system,
					'planet' => (int) $row->planet,
					'planet_type' => (int) $row->planet_type,
				];
			}
		}

		$parse['mission'] = (int) Input::post('mission', 0);

		$this->setTitle(__('fleet.fl_title_1'));

		return $parse;
	}
}