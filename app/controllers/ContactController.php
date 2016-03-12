<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Lang;

class ContactController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		Lang::includeLang('contact');
	}

	function indexAction ()
	{
		$contacts = [];

		$GameOps = $this->db->query("SELECT u.`username`, ui.`email`, u.`authlevel` FROM game_users u, game_users_info ui WHERE ui.id = u.id AND u.`authlevel` != '0' ORDER BY u.`authlevel` DESC");

		while ($Ops = $GameOps->fetch())
		{
			$contacts[] = [
				'ctc_data_name' => $Ops['username'],
				'ctc_data_auth' => _getText('user_level', $Ops['authlevel']),
				'ctc_data_mail' => $Ops['email']
			];
		}

		$this->view->setVar('contacts', $contacts);

		$this->tag->setTitle(_getText('ctc_title'));
		$this->showTopPanel(false);
	}
}