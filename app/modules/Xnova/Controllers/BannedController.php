<?php

namespace Xnova\Controllers;

use Xnova\Controller;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

/**
 * @RoutePrefix("/banned")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 */
class BannedController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}

	public function indexAction ()
	{
		$query = $this->db->query('SELECT u.username AS user_1, u2.username AS user_2, b.* FROM game_banned b LEFT JOIN game_users u ON u.id = b.who LEFT JOIN game_users u2 ON u2.id = b.author ORDER BY b.`id` DESC');

		$bannedList = [];

		while ($u = $query->fetch())
		{
			$bannedList[] = $u;
		}

		$this->view->setVar('bannedList', $bannedList);

		$this->tag->setTitle('Список заблокированных игроков');
		$this->showTopPanel(false);
	}
}