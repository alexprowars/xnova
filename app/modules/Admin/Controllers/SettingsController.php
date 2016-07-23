<?php
namespace Xnova\Admin\Controllers;

use App\Lang;

class SettingsController extends Application
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->user->authlevel < 3)
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}

	public function indexAction ()
	{
		Lang::includeLang('admin/settings');

		if (isset($_POST['save']))
		{
			foreach ($_POST['setting'] AS $key => $value)
			{
				$this->game->updateConfig($key, addslashes($value));
			}

			$this->message('Настройки игры успешно сохранены!', 'Выполнено');
		}
		else
		{
			$parse = [];
			$parse['settings'] = [];

			$settings = $this->db->query("SELECT * FROM game_config ORDER BY `key`");

			while ($setting = $settings->fetch())
			{
				$parse['settings'][] = $setting;
			}
			
			$this->view->setVar('parse', $parse);
			$this->tag->setTitle(_getText('adm_opt_title'));
		}
	}
}

?>