<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Helpers;
use App\Models\Planet;

class ResourcesController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		$this->user->loadPlanet();
	}

	private function buy ($parse)
	{
		if ($this->user->vacation > 0)
			$this->message("Включен режим отпуска!");

		if ($this->user->credits >= 10)
		{
			if ($this->planet->merchand < time())
			{
				$this->planet->merchand = time() + 172800;

				foreach ($this->storage->reslist['res'] AS $res)
					$this->planet->{$res} += $parse['buy_'.$res];

				$this->planet->update();

				$this->db->query('UPDATE game_users SET credits = credits - 10 WHERE id = ' . $this->user->id . ';');
				$this->db->query("INSERT INTO game_log_credits (uid, time, credits, type) VALUES (" . $this->user->id . ", " . time() . ", " . (10 * (-1)) . ", 2)");

				$this->message('Вы успешно купили ' . $parse['buy_metal'] . ' металла, ' . $parse['buy_crystal'] . ' кристалла, ' . $parse['buy_deuterium'] . ' дейтерия', 'Успешная покупка', '/resources/', 2);
			}
			else
				$this->message('Покупать ресурсы можно только раз в 48 часов', 'Ошибка', '/resources/', 2);
		}
		else
			$this->message('Для покупки вам необходимо еще ' . (10 - $this->user->credits) . ' кредитов', 'Ошибка', '/resources/', 2);
	}

	public function productionAction ()
	{
		if ($this->user->vacation > 0)
			$this->message("Включен режим отпуска!");

		$production = $this->request->getQuery('active', null, 'Y');
		$production = $production == 'Y' ? 10 : 0;

		$planets = $this->db->query("SELECT * FROM game_planets WHERE `id_owner` = '" . $this->user->id . "'");

		$pl_class = new Planet();
		$pl_class->assignUser($this->user);

		while ($planet = $planets->fetch())
		{
			$pl_class->assign($planet);
			$pl_class->copyTempParams();
			$pl_class->PlanetResourceUpdate();
		}

		$arFields = [
			$this->storage->resource[4].'_porcent' 	=> $production,
			$this->storage->resource[12].'_porcent' 	=> $production,
			$this->storage->resource[212].'_porcent' 	=> $production
		];

		foreach ($this->storage->reslist['res'] AS $res)
		{
			$this->planet->{$res.'_mine_porcent'} = $production;
			$arFields[$res.'_mine_porcent'] = $production;
		}

		$this->db->updateAsDict('game_planets', $arFields, 'id_owner = '.$this->user->id);

		$this->planet->{$this->storage->resource[4].'_porcent'} 	= $production;
		$this->planet->{$this->storage->resource[12].'_porcent'} 	= $production;
		$this->planet->{$this->storage->resource[212].'_porcent'}	= $production;

		$this->planet->PlanetResourceUpdate(time(), true);

		return $this->indexAction();
	}
	
	public function indexAction ()
	{
		if ($this->planet->planet_type == 3 || $this->planet->planet_type == 5)
		{
			foreach ($this->storage->reslist['res'] AS $res)
				$this->config->game->offsetSet($res.'_basic_income', 0);
		}

		$CurrentUser['energy_tech'] = $this->user->energy_tech;
		$ValidList['percent'] = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
		
		if ($this->request->isPost())
		{
			if ($this->user->vacation > 0)
				$this->message("Включен режим отпуска!");

			foreach ($this->request->getPost() as $field => $value)
			{
				if (isset($this->planet->{$field.'_porcent'}) && in_array($value, $ValidList['percent']))
					$this->planet->{$field.'_porcent'} = $value;
			}

			$this->planet->update();
			$this->planet->PlanetResourceUpdate(time(), true);
		}
		
		$parse = [];

		$production_level = $this->planet->production_level;

		$parse['bonus_h'] = ($this->user->bonusValue('storage') - 1) * 100;

		$parse['resource_row'] = [];

		foreach ($this->storage->reslist['prod'] as $ProdID)
		{
			if ($this->planet->{$this->storage->resource[$ProdID]} > 0 && isset($this->storage->ProdGrid[$ProdID]))
			{
				$BuildLevelFactor = $this->planet->{$this->storage->resource[$ProdID] . "_porcent"};
				$BuildLevel = $this->planet->{$this->storage->resource[$ProdID]};

				$result = $this->planet->getProductionLevel($ProdID, $BuildLevel, $BuildLevelFactor);

				foreach ($this->storage->reslist['res'] AS $res)
				{
					$$res = $result[$res];
					$$res = round($$res * 0.01 * $production_level);
				}

				$energy = $result['energy'];

				$CurrRow = [];
		        $CurrRow['id'] = $ProdID;
				$CurrRow['name'] = $this->storage->resource[$ProdID];
				$CurrRow['porcent'] = $this->planet->{$this->storage->resource[$ProdID] . "_porcent"};

				$CurrRow['bonus'] = ($ProdID == 4 || $ProdID == 12 || $ProdID == 212) ? (($ProdID == 212) ? $this->user->bonusValue('solar') : $this->user->bonusValue('energy')) : (($ProdID == 1) ? $this->user->bonusValue('metal') : (($ProdID == 2) ? $this->user->bonusValue('crystal') : (($ProdID == 3) ? $this->user->bonusValue('deuterium') : 0)));

				if ($ProdID == 4)
					$CurrRow['bonus'] += $this->user->energy_tech / 100;

				$CurrRow['bonus'] = ($CurrRow['bonus'] - 1) * 100;

				$CurrRow['level_type'] = $this->planet->{$this->storage->resource[$ProdID]};

				foreach ($this->storage->reslist['res'] AS $res)
				{
					$CurrRow[$res.'_type'] = $$res;
				}

				$CurrRow['energy_type'] = $energy;

				$parse['resource_row'][] = $CurrRow;
			}
		}

		foreach ($this->storage->reslist['res'] AS $res)
		{
			$parse[$res.'_basic_income'] = $this->config->game->get($res.'_basic_income', 0) * $this->config->game->get('resource_multiplier', 1);

			$parse[$res.'_max'] = '<font color="#' . (($this->planet->{$res.'_max'} < $this->planet->{$res}) ? 'ff00' : '00ff') . '00">';
			$parse[$res.'_max'] .= Helpers::pretty_number($this->planet->{$res.'_max'} / 1000) . " k</font>";

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
		}

		if (isset($_GET['buy']) && $this->planet->id > 0 && $this->planet->planet_type == 1)
		{
			$this->buy($parse);
		}

		foreach ($this->storage->reslist['res'] AS $res)
			$parse['buy_'.$res] = Helpers::colorNumber(Helpers::pretty_number($parse['buy_'.$res]));

		$parse['energy_basic_income'] = $this->config->game->get('energy_basic_income');

		$parse['energy_total'] = Helpers::colorNumber(Helpers::pretty_number(floor(($this->planet->energy_max + $parse['energy_basic_income']) + $this->planet->energy_used)));
		$parse['energy_max'] = Helpers::pretty_number(floor($this->planet->energy_max));

		$parse['merchand'] = $this->planet->merchand;

		$parse['production_level_bar'] = $production_level;
		$parse['production_level'] = "{$production_level}%";
		$parse['production_level_barcolor'] = '#00ff00';
		$parse['name'] = $this->planet->name;
		
		$parse['et'] = $this->user->energy_tech;

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Сырьё');
	}
}