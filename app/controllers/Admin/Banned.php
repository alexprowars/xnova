<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;

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

			$controller->db->insertAsDict('game_banned', [
				'who'		=> $userz['id'],
				'theme'		=> $reas,
				'time'		=> time(),
				'longer'	=> $BanTime,
				'author'	=> $controller->user->getId()
			]);

			$update = ['banned' => $BanTime];

			if ($controller->request->getPost('ro', 'int', 0) == 1)
				$update['vacation'] = 1;

			$controller->user->saveData($update, $userz['id']);

			if ($controller->request->getPost('ro', 'int', 0) == 1)
			{
				$arFields = [
					$controller->storage->resource[4].'_porcent' 	=> 0,
					$controller->storage->resource[12].'_porcent' 	=> 0,
					$controller->storage->resource[212].'_porcent' 	=> 0
				];

				foreach ($controller->storage->reslist['res'] AS $res)
					$arFields[$res.'_mine_porcent'] = 0;

				$controller->db->updateAsDict($arFields, "id_owner = ".$userz['id']);
			}

			$controller->message(_getText('adm_bn_thpl') . " " . $name . " " . _getText('adm_bn_isbn'), _getText('adm_bn_ttle'));
		}

		$controller->view->pick('admin/banned');
		$controller->tag->setTitle(_getText('adm_bn_ttle'));
	}
}

?>