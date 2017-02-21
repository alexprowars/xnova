<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Exceptions\RedirectException;

/**
 * @RoutePrefix("/merchant")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class MerchantController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;
		
		Lang::includeLang('marchand', 'xnova');

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		$parse = [];
		$Message = '';
		
		if (isset($_POST['ress']))
		{
			if ($this->user->credits <= 0)
				throw new RedirectException('Недостаточно кредитов для проведения обменной операции', 'Ошибка', '/merchant/', 3);
		
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

				$this->planet->update();

				$this->user->credits -= 1;
				$this->user->update();

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
		
			throw new RedirectException($parse['mes'], $parse['title'], '/merchant/', 2);
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

					throw new RedirectException('Злобный читер!', 'Ошибка', '/merchant/', 2);

					break;
			}
		}
		else
			$parse['type'] = 'main';

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Торговец');
	}
}