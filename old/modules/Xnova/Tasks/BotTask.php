<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Lang;
use Friday\Core\Modules;
use App\Vars;

class BotTask extends ApplicationTask
{
	public function updateAction ()
	{
		Modules::init('xnova');
		Lang::setLang($this->config->app->language, 'xnova');

		Vars::init();

		$bots = $this->db->query("SELECT * FROM game_bots_users WHERE 1 ORDER BY last_update ASC");

		while ($bot = $bots->fetch())
		{
			$ai = new \App\Ai($bot['user_id']);
			$ai->update();

			$ai->getLog();
		}
	}
}