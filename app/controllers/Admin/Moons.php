<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Galaxy;

class Moons
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel >= 2)
		{
			$parse = [];

			$action = $controller->request->getQuery('mode', 'string', '');

			if ($action == 'add')
			{
				if (isset($_POST['user']))
				{
					$Galaxy = $controller->request->getPost('galaxy', 'int', 0);
					$System = $controller->request->getPost('system', 'int', 0);
					$Planet = $controller->request->getPost('planet', 'int', 0);
					$UserId = $controller->request->getPost('user', 'int', 0);
					$Diamet = $controller->request->getPost('diameter', 'int', 0);

					if ($Galaxy > $controller->config->game->maxGalaxyInWorld || $Galaxy < 1)
						$controller->message('Ошибочная галактика!');
					if ($System > $controller->config->game->maxSystemInGalaxy || $System < 1)
						$controller->message('Ошибочная система!');
					if ($Planet > $controller->config->game->maxPlanetInSystem || $Planet < 1)
						$controller->message('Ошибочная планета!');

					$check = $controller->db->query("SELECT id FROM game_users WHERE id = " . $UserId . "")->fetch();

					if (!isset($check['id']))
						$controller->message('Пользователя не существует');

					$Diamet = min(max($Diamet, 20), 0);

					$galaxy = new Galaxy();

					$moon = $galaxy->createMoon($Galaxy, $System, $Planet, $UserId, $Diamet);

					if ($moon !== false)
						$controller->message('ID: ' . $moon);
					else
						$controller->message('Луна не создана');
				}

				$controller->view->pick('admin/moonlist_add');
				$controller->tag->setTitle('Создание луны');
			}
			else
			{
				$parse['moons'] = [];

				$query = $controller->db->query("SELECT * FROM game_planets WHERE planet_type='3' ORDER BY galaxy,system,planet");

				while ($u = $query->fetch())
				{
					$parse['moons'][] = $u;
				}

				$controller->view->pick('admin/moonlist');
				$controller->tag->setTitle('Список лун');
			}
			$controller->view->setVar('parse', $parse);
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>