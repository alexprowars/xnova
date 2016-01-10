<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Sql;

class Banned
{
	public function show (AdminController $controller)
	{
		if ($controller->request->getPost('name', 'string', '') != '')
		{
			$name = htmlspecialchars($controller->request->getPost('name', 'string', ''));
			$reas = htmlspecialchars($controller->request->getPost('why', 'string', ''));

			$days = $controller->request->getPost('days', 'int', 0);
			$hour = $controller->request->getPost('hour', 'int', 0);
			$mins = $controller->request->getPost('mins', 'int', 0);

			$userz = $controller->db->query("SELECT id FROM game_users WHERE username = '" . $name . "';")->fetch();

			if (!isset($userz['id']))
				$controller->message(_getText('sys_noalloaw'), 'Игрок не найден');

			$BanTime = $days * 86400;
			$BanTime += $hour * 3600;
			$BanTime += $mins * 60;
			$BanTime += time();

			Sql::build()->insert('game_banned')->set(array
			(
				'who'		=> $userz['id'],
				'theme'		=> $reas,
				'time'		=> time(),
				'longer'	=> $BanTime,
				'author'	=> $controller->user->getId()
			))->execute();

			Sql::build()->update('game_users')->setField('banned', $BanTime);

			if ($controller->request->getPost('ro', 'int', 0) == 1)
				Sql::build()->setField('vacation', 1);

			Sql::build()->where('id', '=', $userz['id'])->execute();

			if ($controller->request->getPost('ro', 'int', 0) == 1)
			{
				global $reslist, $resource;

				$arFields = array
				(
					$resource[4].'_porcent' 	=> 0,
					$resource[12].'_porcent' 	=> 0,
					$resource[212].'_porcent' 	=> 0
				);

				foreach ($reslist['res'] AS $res)
					$arFields[$res.'_mine_porcent'] = 0;

				Sql::build()->update('game_planets')->set($arFields)->where('id_owner', '=', $userz['id'])->execute();
			}

			$controller->message(_getText('adm_bn_thpl') . " " . $name . " " . _getText('adm_bn_isbn'), _getText('adm_bn_ttle'));
		}

		$controller->view->pick('admin/banned');
		$controller->tag->setTitle(_getText('adm_bn_ttle'));
	}
}

?>