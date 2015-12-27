<?php

namespace App\Controllers;

use Xcms\db;
use Xcms\sql;
use Xcms\strings;
use Xnova\User;
use Xnova\app;
use Xnova\pageHelper;

class MerchantController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
		
		strings::includeLang('marchand');

		app::loadPlanet();
	}
	
	public function show ()
	{
		$parse = array();
		$Message = '';
		
		if (isset($_POST['ress']))
		{
			if (user::get()->data['credits'] <= 0)
				$this->message('Недостаточно кредитов для проведения обменной операции', 'Ошибка', '?set=marchand', 3);
		
			$Error = false;
		
			$metal = (isset($_POST['metal'])) ? intval($_POST['metal']) : 0;
			$cristal = (isset($_POST['cristal'])) ? intval($_POST['cristal']) : 0;
			$deut = (isset($_POST['deut'])) ? intval($_POST['deut']) : 0;
		
			switch ($_POST['ress'])
			{
				case 'metal':
					$Necessaire = ($cristal * 2) + ($deut * 4);
					if ($cristal < 0 || $deut < 0 || $metal != 0 || $Necessaire == 0)
					{
						$Message = "Failed";
						$Error = true;
					}
					elseif (app::$planetrow->data['metal'] > $Necessaire)
						app::$planetrow->data['metal'] -= $Necessaire;
					else
					{
						$Message = _getText('mod_ma_noten') . " " . _getText('Metal') . "! ";
						$Error = true;
					}
					break;
		
				case 'cristal':
		
					$Necessaire = ($metal * 0.5) + ($deut * 2);
					if ($metal < 0 || $deut < 0 || $cristal != 0 || $Necessaire == 0)
					{
						$Message = "Failed";
						$Error = true;
					}
					elseif (app::$planetrow->data['crystal'] > $Necessaire)
						app::$planetrow->data['crystal'] -= $Necessaire;
					else
					{
						$Message = _getText('mod_ma_noten') . " " . _getText('Crystal') . "! ";
						$Error = true;
					}
					break;
		
				case 'deuterium':
		
					$Necessaire = ($metal * 0.25) + ($cristal * 0.5);
					if ($metal < 0 || $cristal < 0 || $deut != 0 || $Necessaire == 0)
					{
						$Message = "Failed";
						$Error = true;
					}
					elseif (app::$planetrow->data['deuterium'] > $Necessaire)
						app::$planetrow->data['deuterium'] -= $Necessaire;
					else
					{
						$Message = _getText('mod_ma_noten') . " " . _getText('Deuterium') . "! ";
						$Error = true;
					}
					break;
		
				default :
		
					$Message = "Ошибочная операция";
					$Error = true;
					break;
		
			}
		
			if ($Error == false)
			{
				if ($_POST['ress'] != "metal")
					app::$planetrow->data['metal'] += $metal;
				if ($_POST['ress'] != "cristal")
					app::$planetrow->data['crystal'] += $cristal;
				if ($_POST['ress'] != "deuterium")
					app::$planetrow->data['deuterium'] += $deut;

				app::$planetrow->saveData(Array
				(
					'metal' 	=> app::$planetrow->data['metal'],
					'crystal' 	=> app::$planetrow->data['crystal'],
					'deuterium' => app::$planetrow->data['deuterium'],
				), app::$planetrow->data['id']);

				db::query("UPDATE game_users SET `credits` = `credits` - 1 WHERE id = " . user::get()->data['id'] . "");
				user::get()->data['credits'] -= 1;

				$tutorial = db::query("SELECT id FROM game_users_quests WHERE user_id = ".user::get()->getId()." AND quest_id = 6 AND finish = '0' AND stage = 0", true);

				if (isset($tutorial['id']))
					db::query("UPDATE game_users_quests SET stage = 1 WHERE id = " . $tutorial['id'] . ";");
		
				$Message = _getText('mod_ma_done');
			}
		
			if ($Error == true)
				$parse['title'] = _getText('mod_ma_error');
			else
				$parse['title'] = _getText('mod_ma_donet');
		
			$parse['mes'] = $Message;
		
			$this->message($parse['mes'], $parse['title'], '?set=marchand', 2);
		}
		elseif (isset($_POST['choix']))
		{
			$parse['mod_ma_res'] = "1";
			$parse['type'] = $_POST['choix'];
		
			switch ($_POST['choix'])
			{
				case 'metal':
					$parse['mod_ma_res_a'] = "2";
					$parse['mod_ma_res_b'] = "4";
					break;
				case 'cristal':
					$parse['mod_ma_res_a'] = "0.5";
					$parse['mod_ma_res_b'] = "2";
					break;
				case 'deut':
					$parse['mod_ma_res_a'] = "0.25";
					$parse['mod_ma_res_b'] = "0.5";
					break;
				default:
					$this->message('Злобный читер!', 'Ошибка', '?set=marchand', 2);
					break;
			}
		}
		else
			$parse['type'] = 'main';
		
		$this->setTemplate('merchand');
		$this->set('parse', $parse);

		$this->setTitle('Торговец');
		$this->display();
	}
}

?>