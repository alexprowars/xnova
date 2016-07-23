<?php

namespace Admin\Controllers;

use Admin\Controller;
use App\Galaxy;

class MoonsController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->user->authlevel < 2)
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}

	public function indexAction ()
	{
		$parse = [];

		$parse['moons'] = [];

		$query = $this->db->query("SELECT * FROM game_planets WHERE planet_type='3' ORDER BY galaxy,system,planet");

		while ($u = $query->fetch())
		{
			$parse['moons'][] = $u;
		}

		$this->tag->setTitle('Список лун');

		$this->view->setVar('parse', $parse);
	}

	public function addAction ()
	{
		if (isset($_POST['user']))
		{
			$Galaxy = $this->request->getPost('galaxy', 'int', 0);
			$System = $this->request->getPost('system', 'int', 0);
			$Planet = $this->request->getPost('planet', 'int', 0);
			$UserId = $this->request->getPost('user', 'int', 0);
			$Diamet = $this->request->getPost('diameter', 'int', 0);

			if ($Galaxy > $this->config->game->maxGalaxyInWorld || $Galaxy < 1)
				$this->message('Ошибочная галактика!');
			if ($System > $this->config->game->maxSystemInGalaxy || $System < 1)
				$this->message('Ошибочная система!');
			if ($Planet > $this->config->game->maxPlanetInSystem || $Planet < 1)
				$this->message('Ошибочная планета!');

			$check = $this->db->query("SELECT id FROM game_users WHERE id = " . $UserId . "")->fetch();

			if (!isset($check['id']))
				$this->message('Пользователя не существует');

			$Diamet = min(max($Diamet, 20), 0);

			$galaxy = new Galaxy();

			$moon = $galaxy->createMoon($Galaxy, $System, $Planet, $UserId, $Diamet);

			if ($moon !== false)
				$this->message('ID: ' . $moon);
			else
				$this->message('Луна не создана');
		}

		$this->tag->setTitle('Создание луны');
	}
}