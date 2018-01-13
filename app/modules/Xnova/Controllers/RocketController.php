<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Models\Fleet;
use Xnova\Models\Planet;

/**
 * @RoutePrefix("/rocket")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class RocketController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		if (!$this->request->isPost())
		{
			$this->response->redirect('galaxy/');
			return;
		}

		$g = $this->request->getQuery('galaxy', 'int');
		$s = $this->request->getQuery('system', 'int');
		$i = $this->request->getQuery('planet', 'int');

		$anz = intval($_POST['SendMI']);
		$destroyType = $_POST['Target'];
		
		$tempvar1 = (($s - $this->planet->system) * (-1));
		$tempvar2 = ($this->user->getTechLevel('impulse_motor') * 5) - 1;
		$tempvar3 = Planet::findByCoords($g, $s, $i, 1);

		$error = 0;
		
		if ($this->planet->getBuildLevel('missile_facility') < 4)
			$error = 1;
		elseif ($this->user->getTechLevel('impulse_motor') == 0)
			$error = 2;
		elseif ($tempvar1 >= $tempvar2 || $g != $this->planet->galaxy)
			$error = 3;
		elseif (!$tempvar3)
			$error = 4;
		elseif ($anz > $this->planet->getUnitCount('interplanetary_misil'))
			$error = 5;
		elseif ((!is_numeric($destroyType) && $destroyType != "all") OR ($destroyType < 0 && $destroyType > 7 && $destroyType != "all"))
			$error = 6;
		
		if ($error != 0)
			throw new ErrorException('Возможно у вас нет столько межпланетных ракет, или вы не имеете достоточно развитую технологию импульсного двигателя, или вводите неккоректные данные при отправке.', 'Ошибка ' . $error . '');
		
		if ($destroyType == "all")
			$destroyType = 0;
		else
			$destroyType = intval($destroyType);
		
		$select = $this->db->fetchOne("SELECT id, vacation FROM game_users WHERE id = " . $tempvar3->id_owner);
		
		if (!isset($select['id']))
			throw new ErrorException('Игрока не существует');
		
		if ($select['vacation'] > 0)
			throw new ErrorException('Игрок в режиме отпуска');
		
		if ($this->user->vacation > 0)
			throw new ErrorException('Вы в режиме отпуска');
		
		$time = 30 + (60 * $tempvar1);
		
		$fleet = new Fleet();
		$fleet->create([
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
			'target_owner' 		=> $tempvar3->id_owner,
			'target_owner_name' => $tempvar3->name,
			'create_time' 		=> time(),
			'update_time' 		=> time() + $time,
		]);

		if ($fleet->id > 0)
		{
			$this->planet->setUnit('interplanetary_misil', -$anz, true);
			$this->planet->update();
		}

		$this->view->setVar('anz', $anz);

		$this->tag->setTitle('Межпланетная атака');
		$this->showTopPanel(false);
	}
}