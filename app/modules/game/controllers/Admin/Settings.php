<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Lang;

class Settings
{
	public function show (AdminController $controller)
	{
		Lang::includeLang('admin/settings');

		if ($controller->user->authlevel >= 3)
		{
			if (isset($_POST['save']))
			{
				foreach ($_POST['setting'] AS $key => $value)
				{
					$controller->game->updateConfig($key, addslashes($value));
				}

				$controller->message('Настройки игры успешно сохранены!', 'Выполнено');
			}
			else
			{
				$parse = [];
				$parse['settings'] = [];

				$settings = $controller->db->query("SELECT * FROM game_config ORDER BY `key`");

				while ($setting = $settings->fetch())
				{
					$parse['settings'][] = $setting;
				}

				$controller->view->pick('admin/options');
				$controller->view->setVar('parse', $parse);
				$controller->tag->setTitle(_getText('adm_opt_title'));
			}
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>