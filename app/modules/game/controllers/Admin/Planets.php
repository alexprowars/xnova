<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Galaxy;
use App\Helpers;

class Planets
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel >= 2)
		{
			$action = $controller->request->getQuery('mode', 'string', '');

			if ($action == 'add')
			{
				if (isset($_POST['user']))
				{
					$Galaxy = $controller->request->getPost('galaxy', 'int', 0);
					$System = $controller->request->getPost('system', 'int', 0);
					$Planet = $controller->request->getPost('planet', 'int', 0);
					$UserId = $controller->request->getPost('user', 'int', 0);

					if ($Galaxy > $controller->config->game->maxGalaxyInWorld || $Galaxy < 1)
						$controller->message('Ошибочная галактика!');
					if ($System > $controller->config->game->maxSystemInGalaxy || $System < 1)
						$controller->message('Ошибочная система!');
					if ($Planet > $controller->config->game->maxPlanetInSystem || $Planet < 1)
						$controller->message('Ошибочная планета!');

					$check = $controller->db->query("SELECT id FROM game_users WHERE id = " . $UserId . "")->fetch();

					if (!isset($check['id']))
						$controller->message('Пользователя не существует');

					$galaxy = new Galaxy();

					$planet = $galaxy->createPlanet($Galaxy, $System, $Planet, $UserId, _getText('sys_colo_defaultname'), false);

					if ($planet !== false)
						$controller->message('ID: ' . $planet);
					else
						$controller->message('Луна не создана');
				}

				$controller->view->pick('admin/planetlist_add');
				$controller->tag->setTitle('Создание планеты');
			}
			else
			{
				$controller->view->pick('admin/planetlist');

				$p = $controller->request->getQuery('p', 'int', 1);
				if ($p < 1)
					$p = 1;

				$list = $controller->db->query("SELECT `id`, `name`, `galaxy`, `system`, `planet` FROM game_planets WHERE planet_type = '1' ORDER by id LIMIT " . (($p - 1) * 50) . ", 50");

				$total = $controller->db->query("SELECT COUNT(*) AS num FROM game_planets WHERE planet_type = '1'")->fetch();

				$controller->view->setVar('planetlist', $controller->db->extractResult($list));
				$controller->view->setVar('all', $total['num']);

				$pagination = Helpers::pagination($total['num'], 50, '/admin/planetlist/', $p);

				$controller->view->setVar('pagination', $pagination);
				$controller->tag->setTitle('Список планет');
			}
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>