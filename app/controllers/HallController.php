<?php

namespace App\Controllers;

class HallController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$sab = (!isset($_POST['visible']) || $_POST['visible'] <= 1) ? 0 : 1;

		$parse = array();
		$parse['hall'] = array();

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

?>