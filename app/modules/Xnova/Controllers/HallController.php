<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;

/**
 * @RoutePrefix("/hall")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class HallController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$sab = (!isset($_POST['visible']) || $_POST['visible'] <= 1) ? 0 : 1;

		$parse = [];
		$parse['hall'] = [];

		$halls = $this->db->query("SELECT * FROM game_hall WHERE time < " . (time() - 3600) . " AND sab = " . $sab . " ORDER BY debris DESC LIMIT 50");

		$time = 0;

		while ($hall = $halls->fetch())
		{
			$parse['hall'][] = $hall;

			if ($time < $hall['time'])
				$time = $hall['time'];
		}

		$parse['time'] = $time;

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Зал славы');
		$this->showTopPanel(false);
	}
}