<?php

namespace Xnova\Http\Controllers\Admin;

use Admin\Controller;
use Illuminate\Support\Facades\View;
use Xnova\AdminController;
use Xnova\Galaxy;

class MoonsController extends AdminController
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'moons',
			'title' => 'Список лун',
			'icon'	=> 'star',
			'sort'	=> 100
		]];
	}

	public function index ()
	{
		$parse = [];

		$parse['moons'] = [];

		$query = $this->db->query("SELECT * FROM planets WHERE planet_type='3' ORDER BY galaxy,system,planet");

		while ($u = $query->fetch())
		{
			$parse['moons'][] = $u;
		}

		View::share('title', 'Список лун');

		return view('admin.moons.index', ['parse' => $parse]);
	}

	public function add ()
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

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

			$check = $this->db->query("SELECT id FROM users WHERE id = " . $UserId . "")->fetch();

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

		View::share('title', 'Создание луны');

		return view('admin.moons.add', []);
	}
}