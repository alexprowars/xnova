<?php

namespace App\Controllers;

use Xcms\db;
use Xnova\pageHelper;

class HallController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
	}
	
	public function show ()
	{
		$sab = (!isset($_POST['visible']) || $_POST['visible'] <= 1) ? 0 : 1;

		$parse = array();
		$parse['hall'] = array();

		$halls = db::query("SELECT * FROM game_hall WHERE time < " . (time() - 3600) . " AND sab = " . $sab . " ORDER BY debris DESC LIMIT 50");

		$time = 0;

		while ($hall = db::fetch_assoc($halls))
		{
			$parse['hall'][] = $hall;

			if ($time < $hall['time'])
				$time = $hall['time'];
		}

		$parse['time'] = $time;

		$this->setTemplate('hall');
		$this->set('parse', $parse);

		$this->setTitle('Зал славы');
		$this->showTopPanel(false);
		$this->display();
	}
}

?>