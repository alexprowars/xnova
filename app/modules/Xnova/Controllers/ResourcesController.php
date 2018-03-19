<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Format;
use Xnova\Helpers;
use Xnova\Models\Planet;
use Xnova\Controller;
use Xnova\Vars;

/**
 * @RoutePrefix("/resources")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class ResourcesController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		$this->user->loadPlanet();
	}

	private function buy ($parse)
	{
		if ($this->user->vacation > 0)
			throw new ErrorException("Включен режим отпуска!");

		if ($this->user->credits >= 10)
		{
			if ($this->planet->merchand < time())
			{
				$this->planet->merchand = time() + 172800;

				foreach (Vars::getResources() AS $res)
					$this->planet->{$res} += $parse['buy_'.$res];

				$this->planet->update();

				$this->db->query('UPDATE game_users SET credits = credits - 10 WHERE id = ' . $this->user->id . ';');
				$this->db->query("INSERT INTO game_log_credits (uid, time, credits, type) VALUES (" . $this->user->id . ", " . time() . ", " . (10 * (-1)) . ", 2)");

				throw new RedirectException('Вы успешно купили ' . $parse['buy_metal'] . ' металла, ' . $parse['buy_crystal'] . ' кристалла, ' . $parse['buy_deuterium'] . ' дейтерия', 'Успешная покупка', '/resources/', 2);
			}
			else
				throw new RedirectException('Покупать ресурсы можно только раз в 48 часов', 'Ошибка', '/resources/', 2);
		}
		else
			throw new RedirectException('Для покупки вам необходимо еще ' . (10 - $this->user->credits) . ' кредитов', 'Ошибка', '/resources/', 2);
	}

	public function productionAction ()
	{
		if ($this->user->vacation > 0)
			throw new ErrorException("Включен режим отпуска!");

		$production = $this->request->getQuery('active', null, 'Y');
		$production = $production == 'Y' ? 10 : 0;

		$planetsId = [];

		$planets = Planet::find(['id_owner = ?0', 'bind' => [$this->user->id]]);

		foreach ($planets as $planet)
		{
			$planet->assignUser($this->user);
			$planet->resourceUpdate();

			$planetsId[] = $planet->id;
		}

		unset($planets, $planet);

		$buildsId = [4, 12, 212];

		foreach (Vars::getResources() AS $res)
			$buildsId[] = $this->registry->resource_flip[$res.'_mine'];

		$this->db->updateAsDict('game_planets_buildings', [
			'power' => $production
		], 'planet_id IN ('.implode(',', $planetsId).') AND build_id IN ('.implode(',', $buildsId).')');

		$this->db->updateAsDict('game_planets_units', [
			'power' => $production
		], 'planet_id IN ('.implode(',', $planetsId).') AND unit_id IN ('.implode(',', $buildsId).')');

		$this->planet->clearBuildingsData();
		$this->planet->clearUnitsData();
		$this->planet->resourceUpdate(time(), true);

		return $this->indexAction();
	}
	
	public function indexAction ()
	{
		if ($this->planet->planet_type == 3 || $this->planet->planet_type == 5)
		{
			foreach (Vars::getResources() AS $res)
				$this->config->game->offsetSet($res.'_basic_income', 0);
		}

		if ($this->request->isPost())
		{
			if ($this->user->vacation > 0)
				throw new ErrorException("Включен режим отпуска!");

			foreach ($this->request->getPost() as $field => $value)
			{
				if (!Vars::getIdByName($field))
					continue;

				$value = max(0, min(10, (int) $value));

				if (Vars::getItemType($field) == Vars::ITEM_TYPE_BUILING)
				{
					$this->db->updateAsDict('game_planets_buildings', [
						'power' => $value
					], ['conditions' => 'planet_id = ? AND build_id = ?', 'bind' => [$this->planet->id, Vars::getIdByName($field)]]);
				}

				if (Vars::getItemType($field) == Vars::ITEM_TYPE_FLEET)
				{
					$this->db->updateAsDict('game_planets_units', [
						'power' => $value
					], ['conditions' => 'planet_id = ? AND unit_id = ?', 'bind' => [$this->planet->id, Vars::getIdByName($field)]]);
				}
			}

			$this->planet->clearBuildingsData();
			$this->planet->clearUnitsData();

			$this->planet->update();
			$this->planet->resourceUpdate(time(), true);
		}
		
		$parse = [];

		$production_level = $this->planet->production_level;

		$parse['buy_form'] = ($this->planet->planet_type == 1 && $this->user->vacation <= 0);
		$parse['bonus_h'] = ($this->user->bonusValue('storage') - 1) * 100;

		$parse['resource_row'] = [];

		foreach ($this->registry->reslist['prod'] as $ProdID)
		{
			$type = Vars::getItemType($ProdID);

			if ($type == Vars::ITEM_TYPE_BUILING && $this->planet->getBuildLevel($ProdID) <= 0)
				continue;
			elseif ($type == Vars::ITEM_TYPE_FLEET && $this->planet->getUnitCount($ProdID) <= 0)
				continue;

			if (!isset($this->registry->ProdGrid[$ProdID]))
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

			$CurrRow = [];
			$CurrRow['id'] = $ProdID;
			$CurrRow['name'] = Vars::getName($ProdID);
			$CurrRow['porcent'] = $BuildLevelFactor;

			$CurrRow['bonus'] = ($ProdID == 4 || $ProdID == 12 || $ProdID == 212) ? (($ProdID == 212) ? $this->user->bonusValue('solar') : $this->user->bonusValue('energy')) : (($ProdID == 1) ? $this->user->bonusValue('metal') : (($ProdID == 2) ? $this->user->bonusValue('crystal') : (($ProdID == 3) ? $this->user->bonusValue('deuterium') : 0)));

			if ($ProdID == 4)
				$CurrRow['bonus'] += $this->user->getTechLevel('energy') / 100;

			$CurrRow['bonus'] = ($CurrRow['bonus'] - 1) * 100;

			$CurrRow['level_type'] = $BuildLevel;

			foreach (Vars::getResources() AS $res)
				$CurrRow[$res.'_type'] = $$res;

			$CurrRow['energy_type'] = $energy;

			$parse['resource_row'][] = $CurrRow;
		}

		foreach (Vars::getResources() AS $res)
		{
			if (!$this->user->isVacation())
				$parse[$res.'_basic_income'] = $this->config->game->get($res.'_basic_income', 0) * $this->config->game->get('resource_multiplier', 1);
			else
				$parse[$res.'_basic_income'] = 0;

			$parse[$res.'_max'] = '<font color="#' . (($this->planet->{$res.'_max'} < $this->planet->{$res}) ? 'ff00' : '00ff') . '00">';
			$parse[$res.'_max'] .= Format::number($this->planet->{$res.'_max'} / 1000) . " k</font>";

			$parse[$res.'_total'] = $this->planet->{$res.'_perhour'} + $parse[$res.'_basic_income'];
			$parse[$res.'_storage'] = floor($this->planet->{$res} / $this->planet->{$res.'_max'} * 100);
			$parse[$res.'_storage_bar'] = floor(($this->planet->{$res} / $this->planet->{$res.'_max'}) * 100);

			if ($parse[$res.'_storage_bar'] >= 100)
				$parse[$res.'_storage_barcolor'] = '#C00000';
			elseif ($parse[$res.'_storage_bar'] >= 80)
				$parse[$res.'_storage_barcolor'] = '#C0C000';
			else
				$parse[$res.'_storage_barcolor'] = '#00C000';

			$parse['buy_'.$res] = $parse[$res.'_total'] * 8;

			if ($parse['buy_'.$res] < 0)
				$parse['buy_'.$res] = 0;
		}

		if ($this->request->hasQuery('buy') && $this->planet->id > 0 && $this->planet->planet_type == 1)
		{
			$this->buy($parse);
		}

		foreach (Vars::getResources() AS $res)
			$parse['buy_'.$res] = Helpers::colorNumber(Format::number($parse['buy_'.$res]));

		$parse['energy_basic_income'] = $this->config->game->get('energy_basic_income');

		$parse['energy_total'] = Helpers::colorNumber(Format::number(floor(($this->planet->energy_max + $parse['energy_basic_income']) + $this->planet->energy_used)));
		$parse['energy_max'] = Format::number(floor($this->planet->energy_max));

		$parse['merchand'] = $this->planet->merchand;

		$parse['production_level_bar'] = $production_level;
		$parse['production_level'] = "{$production_level}%";
		$parse['production_level_barcolor'] = '#00ff00';
		$parse['name'] = $this->planet->name;

		$parse['et'] = $this->user->getTechLevel('energy');

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Сырьё');
	}
}