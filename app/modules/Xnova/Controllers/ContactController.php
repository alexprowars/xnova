<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Lang;
use Xnova\Controller;

/**
 * @RoutePrefix("/contact")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 */
class ContactController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}

	function indexAction ()
	{
		Lang::includeLang('contact', 'xnova');

		$contacts = [];

		$GameOps = $this->db->query("SELECT u.id, u.username, ui.email, u.authlevel, ui.about FROM game_users u, game_users_info ui WHERE ui.id = u.id AND u.authlevel != '0' ORDER BY u.authlevel DESC");

		while ($Ops = $GameOps->fetch())
		{
			$contacts[] = [
				'id' 	=> $Ops['id'],
				'name' 	=> $Ops['username'],
				'auth' 	=> _getText('user_level', $Ops['authlevel']),
				'mail' 	=> $Ops['email'],
				'info' 	=> $Ops['about'],
			];
		}

		$this->view->setVar('contacts', $contacts);

		$this->tag->setTitle(_getText('ctc_title'));
		$this->showTopPanel(false);
	}
}