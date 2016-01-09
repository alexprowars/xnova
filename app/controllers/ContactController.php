<?php

namespace App\Controllers;

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
		$contacts = array();

		$GameOps = $this->db->query("SELECT u.`username`, ui.`email`, u.`authlevel` FROM game_users u, game_users_info ui WHERE ui.id = u.id AND u.`authlevel` != '0' ORDER BY u.`authlevel` DESC");

		while ($Ops = $GameOps->fetch())
		{
			$contacts[] = array
			(
				'ctc_data_name' => $Ops['username'],
				'ctc_data_auth' => _getText('user_level', $Ops['authlevel']),
				'ctc_data_mail' => $Ops['email']
			);
		}

		$this->view->setVar('contacts', $contacts);

		$this->tag->setTitle(_getText('ctc_title'));
		$this->showTopPanel(false);
	}
}