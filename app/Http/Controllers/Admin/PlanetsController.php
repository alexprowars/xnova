<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Xnova\AdminController;
use Xnova\Galaxy;
use Xnova\Helpers;

class PlanetsController extends AdminController
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'planets',
			'title' => 'Список планет',
			'icon'	=> 'globe',
			'sort'	=> 80
		]];
	}

	public function index ()
	{
		$p = $this->request->getQuery('p', 'int', 1);
		if ($p < 1)
			$p = 1;

		$list = $this->db->query("SELECT `id`, `name`, `galaxy`, `system`, `planet` FROM planets WHERE planet_type = '1' ORDER by id LIMIT " . (($p - 1) * 50) . ", 50");

		$total = $this->db->query("SELECT COUNT(*) AS num FROM planets WHERE planet_type = '1'")->fetch();

		$pagination = Helpers::pagination($total['num'], 50, '/admin/planets/', $p);

		View::share('title', 'Список планет');

		return view('admin.planets.index', ['planetlist' => $this->db->extractResult($list), 'all' => $total['num'], 'pagination' => $pagination]);
	}

	public function add ()
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

			$check = $this->db->query("SELECT id FROM users WHERE id = " . $UserId . "")->fetch();

			if (!isset($check['id']))
				$this->message('Пользователя не существует');

			$galaxy = new Galaxy();

			$planet = $galaxy->createPlanet($Galaxy, $System, $Planet, $UserId, _getText('sys_colo_defaultname'), false);

			if ($planet !== false)
				$this->message('ID: ' . $planet);
			else
				$this->message('Луна не создана');
		}

		View::share('title', 'Создание планеты');

		return view('admin.planets.add', []);
	}
}