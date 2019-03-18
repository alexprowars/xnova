<?php

namespace Xnova\Controllers;

use Xnova\Controller;
use Xnova\Request;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
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
	public function indexAction ()
	{
		$query = $this->db->query('SELECT u.username AS user_1, u2.username AS user_2, b.* FROM game_banned b LEFT JOIN game_users u ON u.id = b.who LEFT JOIN game_users u2 ON u2.id = b.author ORDER BY b.id DESC');

		$items = [];

		while ($u = $query->fetch())
		{
			$items[] = [
				'user' => [
					'id' => (int) $u['who'],
					'name' => $u['user_1']
				],
				'moderator' => [
					'id' => (int) $u['author'],
					'name' => $u['user_2']
				],
				'time' => (int) $u['time'],
				'time_end' => (int) $u['longer'],
				'reason' => $u['theme'],
			];
		}

		Request::addData('page', [
			'items' => $items
		]);

		$this->tag->setTitle('Список заблокированных игроков');
	}
}