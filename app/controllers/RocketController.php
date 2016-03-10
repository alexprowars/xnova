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
		if (!$this->request->isPost())
		{
			$this->response->redirect('galaxy/');
			return;
		}

		$g = intval($_GET['galaxy']);
		$s = intval($_GET['system']);
		$i = intval($_GET['planet']);
		$anz = intval($_POST['SendMI']);
		$destroyType = $_POST['Target'];
		
		$tempvar1 = (($s - $this->planet->system) * (-1));
		$tempvar2 = ($this->user->impulse_motor_tech * 5) - 1;
		$tempvar3 = $this->db->query("SELECT * FROM game_planets WHERE galaxy = " . $g . " AND system = " . $s . " AND planet = " . $i . " AND planet_type = 1")->fetch();
		
		$error = 0;
		
		if ($this->planet->silo < 4)
			$error = 1;
		elseif ($this->user->impulse_motor_tech == 0)
			$error = 2;
		elseif ($tempvar1 >= $tempvar2 || $g != $this->planet->galaxy)
			$error = 3;
		elseif (!isset($tempvar3['id']))
			$error = 4;
		elseif ($anz > $this->planet->interplanetary_misil)
			$error = 5;
		elseif ((!is_numeric($destroyType) && $destroyType != "all") OR ($destroyType < 0 && $destroyType > 7 && $destroyType != "all"))
			$error = 6;
		
		if ($error != 0)
			$this->message('Возможно у вас нет столько межпланетных ракет, или вы не имеете достоточно развитую технологию импульсного двигателя, или вводите неккоректные данные при отправке.', 'Ошибка ' . $error . '');
		
		if ($destroyType == "all")
			$destroyType = 0;
		else
			$destroyType = intval($destroyType);
		
		$select = $this->db->fetchOne("SELECT id, vacation FROM game_users WHERE id = " . $tempvar3['id_owner']);
		
		if (!isset($select['id']))
			$this->message('Игрока не существует');
		
		if ($select['vacation'] > 0)
			$this->message('Игрок в режиме отпуска');
		
		if ($this->user->vacation > 0)
			$this->message('Вы в режиме отпуска');
		
		$time = 30 + (60 * $tempvar1);
		
		$this->db->insertAsDict('game_fleets', 
		[
			'owner' 			=> $this->user->id,
			'owner_name' 		=> $this->planet->name,
			'mission' 			=> 20,
			'fleet_array' 		=> '503,'.$anz.'!'.$destroyType,
			'start_time' 		=> time() + $time,
			'start_galaxy' 		=> $this->planet->galaxy,
			'start_system' 		=> $this->planet->system,
			'start_planet' 		=> $this->planet->planet,
			'start_type' 		=> 1,
			'end_time' 			=> 0,
			'end_galaxy' 		=> $g,
			'end_system' 		=> $s,
			'end_planet' 		=> $i,
			'end_type' 			=> 1,
			'target_owner' 		=> $tempvar3['id_owner'],
			'target_owner_name' => $tempvar3['name'],
			'create_time' 		=> time(),
			'update_time' 		=> time() + $time,
		]);
		
		$this->db->query("UPDATE game_planets SET interplanetary_misil = interplanetary_misil - " . $anz . " WHERE id = '" . $this->user->planet_current . "'");

		$this->view->setVar('anz', $anz);

		$this->tag->setTitle('Межпланетная атака');
		$this->showTopPanel(false);
	}
}

?>