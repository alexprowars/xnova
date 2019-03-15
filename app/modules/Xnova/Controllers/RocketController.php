<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
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
			return $this->response->redirect('galaxy/');

		$g = (int) $this->request->getQuery('galaxy', 'int');
		$s = (int) $this->request->getQuery('system', 'int');
		$i = (int) $this->request->getQuery('planet', 'int');

		$count = (int) $this->request->getPost('count', 'int');
		$destroyType = $this->request->getPost('target', 'string');
		
		$distance = abs($s - $this->planet->system);
		$maxDistance = ($this->user->getTechLevel('impulse_motor') * 5) - 1;

		$targetPlanet = Planet::findByCoords($g, $s, $i, 1);

		if ($this->planet->getBuildLevel('missile_facility') < 4)
			throw new ErrorException('Постройте ракетную шахту');
		elseif ($this->user->getTechLevel('impulse_motor') == 0)
			throw new ErrorException('Необходима технология "Импульсный двигатель"');
		elseif ($distance >= $maxDistance || $g != $this->planet->galaxy)
			throw new ErrorException('Превышена дистанция ракетной атаки');
		elseif (!$targetPlanet)
			throw new ErrorException('Планета не найдена');
		elseif ($count > $this->planet->getUnitCount('interplanetary_misil'))
			throw new ErrorException('У вас нет такого кол-ва ракет');
		elseif ((!is_numeric($destroyType) && $destroyType != "all") OR ($destroyType < 0 && $destroyType > 7 && $destroyType != "all"))
			throw new ErrorException('Не найдена цель');
		
		if ($destroyType == 'all')
			$destroyType = 0;
		else
			$destroyType = (int) $destroyType;
		
		$select = $this->db->fetchOne("SELECT id, vacation FROM game_users WHERE id = " . $targetPlanet->id_owner);
		
		if (!$select)
			throw new ErrorException('Игрока не существует');
		
		if ($select['vacation'] > 0)
			throw new ErrorException('Игрок в режиме отпуска');
		
		if ($this->user->vacation > 0)
			throw new ErrorException('Вы в режиме отпуска');
		
		$time = 30 + (60 * $distance);
		
		$fleet = new Fleet();
		$fleet->create([
			'owner' 			=> $this->user->id,
			'owner_name' 		=> $this->planet->name,
			'mission' 			=> 20,
			'fleet_array' 		=> [['id' => 503, 'count' => $count, 'target' => $destroyType]],
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
			'target_owner' 		=> $targetPlanet->id_owner,
			'target_owner_name' => $targetPlanet->name,
			'create_time' 		=> time(),
			'update_time' 		=> time() + $time,
		]);

		if ($fleet->id > 0)
		{
			$this->planet->setUnit('interplanetary_misil', -$count, true);
			$this->planet->update();
		}

		throw new ErrorException('<b>'.$count.'</b> межпланетные ракеты запущены для атаки удалённой планеты!');
	}
}