<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Controller;
use Xnova\Planet;
use Xnova\Vars;

class ResourcesController extends Controller
{
	private $loadPlanet = true;

	private function buy ($parse)
	{
		if ($this->user->vacation > 0)
			throw new ErrorException("Включен режим отпуска!");

		if ($this->user->credits < 10)
			throw new ErrorException('Для покупки вам необходимо еще ' . (10 - $this->user->credits) . ' кредитов');

		if ($this->planet->merchand > time())
			throw new ErrorException('Покупать ресурсы можно только раз в 48 часов');

		$this->planet->merchand = time() + 172800;

		foreach (Vars::getResources() AS $res)
			$this->planet->{$res} += $parse['buy_form'][$res];

		$this->planet->update();

		$this->user->credits -= 10;
		$this->user->update();

		DB::table('log_credits')->insert([
			'uid' => $this->user->id,
			'time' => time(),
			'credits' => 10 * (-1),
			'type' => 2
		]);

		throw new RedirectException('Вы успешно купили ' . $parse['buy_form']['metal'] . ' металла, ' . $parse['buy_form']['crystal'] . ' кристалла, ' . $parse['buy_form']['deuterium'] . ' дейтерия', '/resources/');
	}

	public function productionAction ()
	{
		if ($this->user->vacation > 0)
			throw new PageException("Включен режим отпуска!");

		$production = Input::query('active', 'Y');
		$production = $production == 'Y' ? 10 : 0;

		$planetsId = [];

		$planets = Planet::query()
			->where('id_owner', $this->user->id)
			->get();

		foreach ($planets as $planet)
		{
			$planet->assignUser($this->user);
			$planet->resourceUpdate();

			$planetsId[] = $planet->id;
		}

		unset($planets, $planet);

		$buildsId = [4, 12, 212];

		foreach (Vars::getResources() AS $res)
			$buildsId[] = Vars::getIdByName($res.'_mine');

		DB::table('planets_buildings')
			->whereIn('planet_id', $planetsId)
			->whereIn('build_id', $buildsId)
			->update([
				'power' => $production,
			]);

		DB::table('planets_units')
			->whereIn('planet_id', $planetsId)
			->whereIn('unit_id', $buildsId)
			->update([
				'power' => $production,
			]);

		$this->planet->clearBuildingsData();
		$this->planet->clearUnitsData();
		$this->planet->resourceUpdate(time(), true);
	}

	public function index ()
	{
		if (Input::has('production'))
			$this->productionAction();

		if ($this->planet->planet_type == 3 || $this->planet->planet_type == 5)
		{
			foreach (Vars::getResources() AS $res)
				Config::set('game.'.$res.'_basic_income', 0);
		}

		if (Request::instance()->isMethod('post'))
		{
			if ($this->user->vacation > 0)
				throw new ErrorException("Включен режим отпуска!");

			foreach (Input::post() as $field => $value)
			{
				if (!Vars::getIdByName($field))
					continue;

				$value = max(0, min(10, (int) $value));

				if (Vars::getItemType($field) == Vars::ITEM_TYPE_BUILING)
				{
					DB::table('planets_buildings')
						->whereIn('planet_id', $this->planet->id)
						->whereIn('build_id', Vars::getIdByName($field))
						->update([
							'power' => $value,
						]);
				}

				if (Vars::getItemType($field) == Vars::ITEM_TYPE_FLEET)
				{
					DB::table('planets_units')
						->whereIn('planet_id', $this->planet->id)
						->whereIn('unit_id', Vars::getIdByName($field))
						->update([
							'power' => $value,
						]);
				}
			}

			$this->planet->clearBuildingsData();
			$this->planet->clearUnitsData();

			$this->planet->update();
			$this->planet->resourceUpdate(time(), true);
		}

		$parse = [];

		$parse['resources'] = Vars::getResources();

		$production_level = $this->planet->production_level;

		$parse['buy_form'] = [
			'visible' => ($this->planet->planet_type == 1 && $this->user->vacation <= 0),
			'time' => max(0, $this->planet->merchand - time())
		];

		$parse['bonus_h'] = ($this->user->bonusValue('storage') - 1) * 100;

		$parse['items'] = [];

		foreach (Vars::getItemsByType('prod') as $ProdID)
		{
			$type = Vars::getItemType($ProdID);

			if ($type == Vars::ITEM_TYPE_BUILING && $this->planet->getBuildLevel($ProdID) <= 0)
				continue;
			elseif ($type == Vars::ITEM_TYPE_FLEET && $this->planet->getUnitCount($ProdID) <= 0)
				continue;

			if (!Vars::getBuildProduction($ProdID))
				continue;

			$BuildLevelFactor = $BuildLevel = 0;

			if ($type == Vars::ITEM_TYPE_BUILING)
			{
				$build = $this->planet->getBuild($ProdID);

				$BuildLevel = $build['level'];
				$BuildLevelFactor = $build['power'];
			}
			elseif ($type == Vars::ITEM_TYPE_FLEET)
			{
				$unit = $this->planet->getUnit($ProdID);

				$BuildLevel = $unit['amount'];
				$BuildLevelFactor = $unit['power'];
			}

			$result = $this->planet->getResourceProductionLevel($ProdID, $BuildLevel, $BuildLevelFactor);

			foreach (Vars::getResources() AS $res)
			{
				$$res = $result[$res];
				$$res = round($$res * 0.01 * $production_level);
			}

			$energy = $result['energy'];

			$row = [];
			$row['id'] = $ProdID;
			$row['name'] = Vars::getName($ProdID);
			$row['factor'] = $BuildLevelFactor;
			$row['bonus'] = 0;

			if ($ProdID == 4 || $ProdID == 12)
			{
				$row['bonus'] += $this->user->bonusValue('energy');
				$row['bonus'] += ($this->user->getTechLevel('energy') * 2) / 100;
			}

			if ($ProdID == 212)
				$row['bonus'] += $this->user->bonusValue('solar');

			if ($ProdID == 1)
				$row['bonus'] += $this->user->bonusValue('metal');

			if ($ProdID == 2)
				$row['bonus'] += $this->user->bonusValue('crystal');

			if ($ProdID == 3)
				$row['bonus'] += $this->user->bonusValue('deuterium');

			$row['bonus'] = ($row['bonus'] - 1) * 100;

			$row['level'] = $BuildLevel;

			foreach (Vars::getResources() AS $res)
				$row['resources'][$res] = $$res;

			$row['resources']['energy'] = $energy;

			$parse['items'][] = $row;
		}

		$parse['production'] = [];

		foreach (Vars::getResources() AS $res)
		{
			$row = [];

			if (!$this->user->isVacation())
				$row['basic'] = Config::get('game.'.$res.'_basic_income', 0) * Config::get('game.resource_multiplier', 1);
			else
				$row['basic'] = 0;

			$row['max'] = (int) $this->planet->{$res.'_max'};
			$row['total'] = $this->planet->{$res.'_perhour'} + $row['basic'];
			$row['storage'] = floor($this->planet->{$res} / $this->planet->{$res.'_max'} * 100);

			$parse['buy_form'][$res] = $row['total'] * 8;

			if ($parse['buy_form'][$res] < 0)
				$parse['buy_form'][$res] = 0;

			$parse['production'][$res] = $row;
		}

		if (Input::query('buy') && $this->planet->id > 0 && $this->planet->planet_type == 1)
		{
			$this->buy($parse);
		}

		$parse['production']['energy'] = [
			'basic' => (int) Config::get('game.energy_basic_income'),
			'max' => floor($this->planet->energy_max),
			'total' => floor(($this->planet->energy_max + Config::get('game.energy_basic_income')) + $this->planet->energy_used)
		];

		$parse['production_level'] = $production_level;

		$parse['planet_name'] = $this->planet->name;
		$parse['energy_tech'] = $this->user->getTechLevel('energy');

		$this->setTitle('Сырьё');

		return $parse;
	}
}