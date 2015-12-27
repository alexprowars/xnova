<?php

namespace App\Controllers;

use Xcms\core;
use Xcms\db;
use Xcms\sql;
use Xcms\strings;
use Xnova\User;
use Xnova\app;
use Xnova\pageHelper;
use Xnova\planet;

class ResourcesController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();

		app::loadPlanet();
	}

	private function buy ($parse)
	{
		global $reslist;

		if (user::get()->data['urlaubs_modus_time'] > 0)
			$this->message("Включен режим отпуска!");

		if (user::get()->data['credits'] >= 10)
		{
			if (app::$planetrow->data['merchand'] < time())
			{
				$arFields = array('merchand' => (time() + 172800));

				foreach ($reslist['res'] AS $res)
					$arFields['+'.$res] = $parse['buy_'.$res];

				app::$planetrow->saveData($arFields);

				db::query('UPDATE game_users SET credits = credits - 10 WHERE id = ' . user::get()->data['id'] . ';');
				db::query("INSERT INTO game_log_credits (uid, time, credits, type) VALUES (" . user::get()->data['id'] . ", " . time() . ", " . (10 * (-1)) . ", 2)");

				$this->message('Вы успешно купили ' . $parse['buy_metal'] . ' металла, ' . $parse['buy_crystal'] . ' кристалла, ' . $parse['buy_deuterium'] . ' дейтерия', 'Успешная покупка', '?set=resources', 2);
			}
			else
				$this->message('Покупать ресурсы можно только раз в 48 часов', 'Ошибка', '?set=resources', 2);
		}
		else
			$this->message('Для покупки вам необходимо еще ' . (10 - user::get()->data['credits']) . ' кредитов', 'Ошибка', '?set=resources', 2);
	}
	
	public function show ()
	{
		global $reslist, $resource, $ProdGrid;

		if (app::$planetrow->data['planet_type'] == 3 || app::$planetrow->data['planet_type'] == 5)
		{
			foreach ($reslist['res'] AS $res)
				core::setConfig($res.'_basic_income', 0);
		}

		$CurrentUser['energy_tech'] = user::get()->data['energy_tech'];
		$ValidList['percent'] = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
		
		if (isset($_GET['production_full']) || isset($_GET['production_empty']))
		{
			if (user::get()->data['urlaubs_modus_time'] > 0)
				$this->message("Включен режим отпуска!");

			$planets = db::query("SELECT * FROM game_planets WHERE `id_owner` = '" . user::get()->data['id'] . "'");
		
			$pl_class = new planet();
			$pl_class->load_user_info(user::get());

			while ($planet = db::fetch($planets))
			{
				$pl_class->load_from_array($planet);
				$pl_class->PlanetResourceUpdate();
			}

			$production = (isset($_GET['production_full'])) ? 10 : 0;

			$arFields = array
			(
				$resource[4].'_porcent' 	=> $production,
				$resource[12].'_porcent' 	=> $production,
				$resource[212].'_porcent' 	=> $production
			);

			foreach ($reslist['res'] AS $res)
			{
				app::$planetrow->data[$res.'_mine_porcent'] = $production;
				$arFields[$res.'_mine_porcent'] = $production;
			}

			sql::build()->update('game_planets')->set($arFields)->where('id_owner', '=', user::get()->data['id'])->execute();

			app::$planetrow->data[$resource[4].'_porcent'] 		= $production;
			app::$planetrow->data[$resource[12].'_porcent'] 	= $production;
			app::$planetrow->data[$resource[212].'_porcent']	= $production;
		
			app::$planetrow->PlanetResourceUpdate(time(), true);
		}
		
		if ($_POST)
		{
			if (user::get()->data['urlaubs_modus_time'] > 0)
				$this->message("Включен режим отпуска!");

			$arFields = array();

			foreach ($_POST as $Field => $Value)
			{
				if (isset(app::$planetrow->data[$Field.'_porcent']) && in_array($Value, $ValidList['percent']))
				{
					$arFields[$Field.'_porcent'] = $Value;

					app::$planetrow->data[$Field.'_porcent'] = $Value;
				}
			}

			if (count($arFields))
				app::$planetrow->saveData($arFields);

			app::$planetrow->PlanetResourceUpdate(time(), true);
		}
		
		$parse = array();

		$production_level = app::$planetrow->data['production_level'];

		$parse['bonus_h'] = (user::get()->bonusValue('storage') - 1) * 100;

		$parse['resource_row'] = array();

		foreach ($reslist['prod'] as $ProdID)
		{
			if (app::$planetrow->data[$resource[$ProdID]] > 0 && isset($ProdGrid[$ProdID]))
			{
				$BuildLevelFactor = app::$planetrow->data[$resource[$ProdID] . "_porcent"];
				$BuildLevel = app::$planetrow->data[$resource[$ProdID]];

				$result = app::$planetrow->getProductionLevel($ProdID, $BuildLevel, $BuildLevelFactor);

				foreach ($reslist['res'] AS $res)
				{
					$$res = $result[$res];
					$$res = round($$res * 0.01 * $production_level);
				}

				$energy = $result['energy'];

				$CurrRow = array();
		        $CurrRow['id'] = $ProdID;
				$CurrRow['name'] = $resource[$ProdID];
				$CurrRow['porcent'] = app::$planetrow->data[$resource[$ProdID] . "_porcent"];

				$CurrRow['bonus'] = ($ProdID == 4 || $ProdID == 12 || $ProdID == 212) ? (($ProdID == 212) ? user::get()->bonusValue('solar') : user::get()->bonusValue('energy')) : (($ProdID == 1) ? user::get()->bonusValue('metal') : (($ProdID == 2) ? user::get()->bonusValue('crystal') : (($ProdID == 3) ? user::get()->bonusValue('deuterium') : 0)));

				if ($ProdID == 4)
					$CurrRow['bonus'] += user::get()->data['energy_tech'] / 100;

				$CurrRow['bonus'] = ($CurrRow['bonus'] - 1) * 100;

				$CurrRow['level_type'] = app::$planetrow->data[$resource[$ProdID]];

				foreach ($reslist['res'] AS $res)
				{
					$CurrRow[$res.'_type'] = $$res;
				}

				$CurrRow['energy_type'] = $energy;

				$parse['resource_row'][] = $CurrRow;
			}
		}

		foreach ($reslist['res'] AS $res)
		{
			$parse[$res.'_basic_income'] = core::getConfig($res.'_basic_income', 0) * core::getConfig('resource_multiplier', 1);

			$parse[$res.'_max'] = '<font color="#' . ((app::$planetrow->data[$res.'_max'] < app::$planetrow->data[$res]) ? 'ff00' : '00ff') . '00">';
			$parse[$res.'_max'] .= strings::pretty_number(app::$planetrow->data[$res.'_max'] / 1000) . " k</font>";

			$parse[$res.'_total'] = app::$planetrow->data[$res.'_perhour'] + $parse[$res.'_basic_income'];
			$parse[$res.'_storage'] = floor(app::$planetrow->data[$res] / app::$planetrow->data[$res.'_max'] * 100);
			$parse[$res.'_storage_bar'] = floor((app::$planetrow->data[$res] / app::$planetrow->data[$res.'_max']) * 100);

			if ($parse[$res.'_storage_bar'] >= 100)
				$parse[$res.'_storage_barcolor'] = '#C00000';
			elseif ($parse[$res.'_storage_bar'] >= 80)
				$parse[$res.'_storage_barcolor'] = '#C0C000';
			else
				$parse[$res.'_storage_barcolor'] = '#00C000';

			$parse['buy_'.$res] = $parse[$res.'_total'] * 8;
		}

		if (isset($_GET['buy']) && app::$planetrow->data['id'] > 0 && app::$planetrow->data['planet_type'] == 1)
		{
			$this->buy($parse);
		}

		foreach ($reslist['res'] AS $res)
			$parse['buy_'.$res] = strings::colorNumber(strings::pretty_number($parse['buy_'.$res]));

		$parse['energy_basic_income'] = core::getConfig('energy_basic_income');

		$parse['energy_total'] = strings::colorNumber(strings::pretty_number(floor((app::$planetrow->data['energy_max'] + $parse['energy_basic_income']) + app::$planetrow->data['energy_used'])));
		$parse['energy_max'] = strings::pretty_number(floor(app::$planetrow->data['energy_max']));

		$parse['merchand'] = app::$planetrow->data['merchand'];

		$parse['production_level_bar'] = $production_level;
		$parse['production_level'] = "{$production_level}%";
		$parse['production_level_barcolor'] = '#00ff00';
		$parse['name'] = app::$planetrow->data['name'];
		
		$parse['et'] = user::get()->data['energy_tech'];
		
		$this->setTemplate('resources');
		$this->set('parse', $parse);

		$this->setTitle('Сырьё');
		$this->display();
	}
}

?>