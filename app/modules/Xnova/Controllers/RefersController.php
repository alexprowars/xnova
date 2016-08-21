<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;

/**
 * @RoutePrefix("/refers")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class RefersController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$refers = $this->db->query("SELECT u.id, u.username, u.lvl_minier, u.lvl_raid, ui.create_time FROM game_refs r LEFT JOIN game_users u ON u.id = r.r_id LEFT JOIN game_users_info ui ON ui.id = r.r_id WHERE r.u_id = " . $this->user->getId() . " ORDER BY u.id DESC;");

		$parse['ref'] = [];

		while ($refer = $refers->fetch())
		{
			$parse['ref'][] = $refer;
		}

		$refers = $this->db->query("SELECT u.id, u.username FROM game_refs r LEFT JOIN game_users u ON u.id = r.u_id WHERE r.r_id = " . $this->user->getId() . "")->fetch();

		if (isset($refers['id']))
			$parse['you'] = $refers;

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Рефералы');
		$this->showTopPanel(false);
	}
}