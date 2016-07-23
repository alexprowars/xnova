<?php

namespace Admin\Controllers;

use Admin\Controller;
use App\Galaxy;
use App\Helpers;

class PlanetsController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->user->authlevel < 2)
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}

	public function indexAction ()
	{
		$p = $this->request->getQuery('p', 'int', 1);
		if ($p < 1)
			$p = 1;

		$list = $this->db->query("SELECT `id`, `name`, `galaxy`, `system`, `planet` FROM game_planets WHERE planet_type = '1' ORDER by id LIMIT " . (($p - 1) * 50) . ", 50");

		$total = $this->db->query("SELECT COUNT(*) AS num FROM game_planets WHERE planet_type = '1'")->fetch();

		$this->view->setVar('planetlist', $this->db->extractResult($list));
		$this->view->setVar('all', $total['num']);

		$pagination = Helpers::pagination($total['num'], 50, '/admin/planets/', $p);

		$this->view->setVar('pagination', $pagination);
		$this->tag->setTitle('Список планет');
	}

	public function addAction ()
	{
		if (isset($_POST['user']))
		{
			$Galaxy = $this->request->getPost('galaxy', 'int', 0);
			$System = $this->request->getPost('system', 'int', 0);
			$Planet = $this->request->getPost('planet', 'int', 0);
			$UserId = $this->request->getPost('user', 'int', 0);

			if ($Galaxy > $this->config->game->maxGalaxyInWorld || $Galaxy < 1)
				$this->message('Ошибочная галактика!');
			if ($System > $this->config->game->maxSystemInGalaxy || $System < 1)
				$this->message('Ошибочная система!');
			if ($Planet > $this->config->game->maxPlanetInSystem || $Planet < 1)
				$this->message('Ошибочная планета!');

			$check = $this->db->query("SELECT id FROM game_users WHERE id = " . $UserId . "")->fetch();

			if (!isset($check['id']))
				$this->message('Пользователя не существует');

			$galaxy = new Galaxy();

			$planet = $galaxy->createPlanet($Galaxy, $System, $Planet, $UserId, _getText('sys_colo_defaultname'), false);

			if ($planet !== false)
				$this->message('ID: ' . $planet);
			else
				$this->message('Луна не создана');
		}

		$this->tag->setTitle('Создание планеты');
	}
}