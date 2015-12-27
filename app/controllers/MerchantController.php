<?php

namespace App\Controllers;

use App\Lang;

class MerchantController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
		
		Lang::includeLang('marchand');

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		$parse = array();
		$Message = '';
		
		if (isset($_POST['ress']))
		{
			if ($this->user->credits <= 0)
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
					elseif ($this->planet->metal > $Necessaire)
						$this->planet->metal -= $Necessaire;
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
					elseif ($this->planet->crystal > $Necessaire)
						$this->planet->crystal -= $Necessaire;
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
					elseif ($this->planet->deuterium > $Necessaire)
						$this->planet->deuterium -= $Necessaire;
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
					$this->planet->metal += $metal;
				if ($_POST['ress'] != "cristal")
					$this->planet->crystal += $cristal;
				if ($_POST['ress'] != "deuterium")
					$this->planet->deuterium += $deut;

				$this->planet->saveData(Array
				(
					'metal' 	=> $this->planet->metal,
					'crystal' 	=> $this->planet->crystal,
					'deuterium' => $this->planet->deuterium,
				), $this->planet->id);

				$this->db->query("UPDATE game_users SET `credits` = `credits` - 1 WHERE id = " . $this->user->id . "");
				$this->user->credits -= 1;

				$tutorial = $this->db->query("SELECT id FROM game_users_quests WHERE user_id = ".$this->user->getId()." AND quest_id = 6 AND finish = '0' AND stage = 0")->fetch();

				if (isset($tutorial['id']))
					$this->db->query("UPDATE game_users_quests SET stage = 1 WHERE id = " . $tutorial['id'] . ";");
		
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
		
		$this->view->pick('merchand');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Торговец');
	}
}

?>