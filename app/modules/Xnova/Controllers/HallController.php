<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;
use Xnova\Request;

/**
 * @RoutePrefix("/hall")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class HallController extends Controller
{
	public function indexAction ()
	{
		$type = (int) $this->request->get('type', 'int', 0);

		$parse = [];
		$parse['type'] = $type;
		$parse['hall'] = [];

		$halls = $this->db->query("SELECT * FROM game_hall WHERE time < " . (time() - 3600) . " AND sab = " . $type . " ORDER BY debris DESC LIMIT 50");

		$time = 0;

		while ($hall = $halls->fetch())
		{
			$parse['hall'][] = $hall;

			if ($time < $hall['time'])
				$time = $hall['time'];
		}

		$parse['time'] = $time;

		Request::addData('page', $parse);

		$this->tag->setTitle('Зал славы');
		$this->showTopPanel(false);
	}
}