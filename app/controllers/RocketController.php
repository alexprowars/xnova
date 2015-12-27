<?php

namespace App\Controllers;

use Xcms\core;
use Xcms\db;
use Xnova\User;
use Xnova\app;
use Xnova\pageHelper;

class RocketController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();

		app::loadPlanet();
	}
	
	public function show ()
	{
		$g = intval($_GET['galaxy']);
		$s = intval($_GET['system']);
		$i = intval($_GET['planet']);
		$anz = intval($_POST['SendMI']);
		$pziel = $_POST['Target'];
		
		$tempvar1 = (($s - app::$planetrow->data['system']) * (-1));
		$tempvar2 = (user::get()->data['impulse_motor_tech'] * 5) - 1;
		$tempvar3 = db::query("SELECT * FROM game_planets WHERE galaxy = " . $g . " AND system = " . $s . " AND planet = " . $i . " AND planet_type = 1", true);
		
		$error = 0;
		
		if (app::$planetrow->data['silo'] < 4)
		{
			$error = 1;
		}
		elseif (user::get()->data['impulse_motor_tech'] == 0)
		{
			$error = 2;
		}
		elseif ($tempvar1 >= $tempvar2 || $g != app::$planetrow->data['galaxy'])
		{
			$error = 3;
		}
		elseif (!isset($tempvar3['id']))
		{
			$error = 4;
		}
		elseif ($anz > app::$planetrow->data['interplanetary_misil'])
		{
			$error = 5;
		}
		elseif ((!is_numeric($pziel) && $pziel != "all") OR ($pziel < 0 && $pziel > 7 && $pziel != "all"))
		{
			$error = 6;
		}
		
		if ($error != 0)
			$this->message('Возможно у вас нет столько межпланетных ракет, или вы не имеете достоточно развитую технологию импульсного двигателя, или вводите неккоректные данные при отправке.', 'Ошибка ' . $error . '');
		
		if ($pziel == "all")
			$pziel = 0;
		else
			$pziel = intval($pziel);
		
		$select = db::query("SELECT id, urlaubs_modus_time FROM game_users WHERE id = " . $tempvar3['id_owner'], true);
		
		if (!isset($select['id']))
			$this->message('Игрока не существует');
		
		if ($select['urlaubs_modus_time'] > 0)
			$this->message('Игрок в режиме отпуска');
		
		if (user::get()->data['urlaubs_modus_time'] > 0)
			$this->message('Вы в режиме отпуска');
		
		$flugzeit = round(((30 + (60 * $tempvar1)) * 2500) / core::getConfig('game_speed'));
		
		$QryInsertFleet = "INSERT INTO game_fleets SET ";
		$QryInsertFleet .= "`fleet_owner` = '" . user::get()->data['id'] . "', ";
		$QryInsertFleet .= "`fleet_owner_name` = '" . app::$planetrow->data['name'] . "', ";
		$QryInsertFleet .= "`fleet_mission` = '20', ";
		$QryInsertFleet .= "`fleet_array` = '503," . $anz . "!" . $pziel . "', ";
		$QryInsertFleet .= "`fleet_start_time` = '" . (time() + $flugzeit) . "', ";
		$QryInsertFleet .= "`fleet_start_galaxy` = '" . app::$planetrow->data['galaxy'] . "', ";
		$QryInsertFleet .= "`fleet_start_system` = '" . app::$planetrow->data['system'] . "', ";
		$QryInsertFleet .= "`fleet_start_planet` = '" . app::$planetrow->data['planet'] . "', ";
		$QryInsertFleet .= "`fleet_start_type` = '1', ";
		$QryInsertFleet .= "`fleet_end_time` = '" . (time() + $flugzeit + 3600) . "', ";
		$QryInsertFleet .= "`fleet_end_galaxy` = '" . $g . "', ";
		$QryInsertFleet .= "`fleet_end_system` = '" . $s . "', ";
		$QryInsertFleet .= "`fleet_end_planet` = '" . $i . "', ";
		$QryInsertFleet .= "`fleet_end_type` = '1', ";
		$QryInsertFleet .= "`fleet_target_owner` = '" . $tempvar3['id_owner'] . "', ";
		$QryInsertFleet .= "`fleet_target_owner_name` = '" . $tempvar3['name'] . "', ";
		$QryInsertFleet .= "`start_time` = '" . time() . "', fleet_time = '" . (time() + $flugzeit) . "';";
		db::query($QryInsertFleet);
		
		db::query("UPDATE game_planets SET interplanetary_misil = interplanetary_misil - " . $anz . " WHERE id = '" . user::get()->data['current_planet'] . "'");
		
		$this->setTemplate('rak');
		$this->set('anz', $anz);

		$this->setTitle('Межпланетная атака');
		$this->showTopPanel(false);
		$this->display();
	}
}

?>