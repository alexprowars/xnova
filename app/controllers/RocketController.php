<?php

namespace App\Controllers;

class RocketController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		$g = intval($_GET['galaxy']);
		$s = intval($_GET['system']);
		$i = intval($_GET['planet']);
		$anz = intval($_POST['SendMI']);
		$pziel = $_POST['Target'];
		
		$tempvar1 = (($s - $this->planet->system) * (-1));
		$tempvar2 = ($this->user->impulse_motor_tech * 5) - 1;
		$tempvar3 = $this->db->query("SELECT * FROM game_planets WHERE galaxy = " . $g . " AND system = " . $s . " AND planet = " . $i . " AND planet_type = 1")->fetch();
		
		$error = 0;
		
		if ($this->planet->silo < 4)
		{
			$error = 1;
		}
		elseif ($this->user->impulse_motor_tech == 0)
		{
			$error = 2;
		}
		elseif ($tempvar1 >= $tempvar2 || $g != $this->planet->galaxy)
		{
			$error = 3;
		}
		elseif (!isset($tempvar3['id']))
		{
			$error = 4;
		}
		elseif ($anz > $this->planet->interplanetary_misil)
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
		
		$select = $this->db->query("SELECT id, vacation FROM game_users WHERE id = " . $tempvar3['id_owner'])->fetch();
		
		if (!isset($select['id']))
			$this->message('Игрока не существует');
		
		if ($select['vacation'] > 0)
			$this->message('Игрок в режиме отпуска');
		
		if ($this->user->vacation > 0)
			$this->message('Вы в режиме отпуска');
		
		$flugzeit = round(((30 + (60 * $tempvar1)) * 2500) / $this->config->game->get('game_speed'));
		
		$QryInsertFleet = "INSERT INTO game_fleets SET ";
		$QryInsertFleet .= "`fleet_owner` = '" . $this->user->id . "', ";
		$QryInsertFleet .= "`fleet_owner_name` = '" . $this->planet->name . "', ";
		$QryInsertFleet .= "`fleet_mission` = '20', ";
		$QryInsertFleet .= "`fleet_array` = '503," . $anz . "!" . $pziel . "', ";
		$QryInsertFleet .= "`fleet_start_time` = '" . (time() + $flugzeit) . "', ";
		$QryInsertFleet .= "`fleet_start_galaxy` = '" . $this->planet->galaxy . "', ";
		$QryInsertFleet .= "`fleet_start_system` = '" . $this->planet->system . "', ";
		$QryInsertFleet .= "`fleet_start_planet` = '" . $this->planet->planet . "', ";
		$QryInsertFleet .= "`fleet_start_type` = '1', ";
		$QryInsertFleet .= "`fleet_end_time` = '" . (time() + $flugzeit + 3600) . "', ";
		$QryInsertFleet .= "`fleet_end_galaxy` = '" . $g . "', ";
		$QryInsertFleet .= "`fleet_end_system` = '" . $s . "', ";
		$QryInsertFleet .= "`fleet_end_planet` = '" . $i . "', ";
		$QryInsertFleet .= "`fleet_end_type` = '1', ";
		$QryInsertFleet .= "`fleet_target_owner` = '" . $tempvar3['id_owner'] . "', ";
		$QryInsertFleet .= "`fleet_target_owner_name` = '" . $tempvar3['name'] . "', ";
		$QryInsertFleet .= "`start_time` = '" . time() . "', fleet_time = '" . (time() + $flugzeit) . "';";
		$this->db->query($QryInsertFleet);
		
		$this->db->query("UPDATE game_planets SET interplanetary_misil = interplanetary_misil - " . $anz . " WHERE id = '" . $this->user->planet_current . "'");

		$this->view->setVar('anz', $anz);

		$this->tag->setTitle('Межпланетная атака');
		$this->showTopPanel(false);
	}
}

?>