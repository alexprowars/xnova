<?php

namespace Admin\Controllers;

use Admin\Controller;

class BannedController extends Controller
{
	public function indexAction ()
	{
		if ($this->request->getPost('name', 'string', '') != '')
		{
			$name = htmlspecialchars($this->request->getPost('name', 'string', ''));
			$reas = htmlspecialchars($this->request->getPost('why', 'string', ''));

			$days = $this->request->getPost('days', 'int', 0);
			$hour = $this->request->getPost('hour', 'int', 0);
			$mins = $this->request->getPost('mins', 'int', 0);

			$userz = $this->db->query("SELECT id FROM game_users WHERE username = '" . $name . "';")->fetch();

			if (!isset($userz['id']))
				$this->message(_getText('sys_noalloaw'), 'Игрок не найден');

			$BanTime = $days * 86400;
			$BanTime += $hour * 3600;
			$BanTime += $mins * 60;
			$BanTime += time();

			$this->db->insertAsDict('game_banned', [
				'who'		=> $userz['id'],
				'theme'		=> $reas,
				'time'		=> time(),
				'longer'	=> $BanTime,
				'author'	=> $this->user->getId()
			]);

			$update = ['banned' => $BanTime];

			if ($this->request->getPost('ro', 'int', 0) == 1)
				$update['vacation'] = 1;

			$this->user->saveData($update, $userz['id']);

			if ($this->request->getPost('ro', 'int', 0) == 1)
			{
				$arFields = [
					$this->storage->resource[4].'_porcent' 	=> 0,
					$this->storage->resource[12].'_porcent' 	=> 0,
					$this->storage->resource[212].'_porcent' 	=> 0
				];

				foreach ($this->storage->reslist['res'] AS $res)
					$arFields[$res.'_mine_porcent'] = 0;

				$this->db->updateAsDict($arFields, "id_owner = ".$userz['id']);
			}

			$this->message(_getText('adm_bn_thpl') . " " . $name . " " . _getText('adm_bn_isbn'), _getText('adm_bn_ttle'));
		}
		
		$this->tag->setTitle(_getText('adm_bn_ttle'));
	}
}