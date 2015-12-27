<?php

namespace App\Controllers;

class FleetController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
		
		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		global $resource, $reslist, $CombatCaps;
	
		// Устанавливаем обновлённые двигателя кораблей
		SetShipsEngine($this->user->data);

		$module = (isset($_GET['page'])) ? $_GET['page'] : '';

		switch ($module)
		{
			case 'fleet_1':
				include(APP_PATH.'controllers/fleet/fleet_1.php');
				break;
			case 'fleet_2':
				include(APP_PATH.'controllers/fleet/fleet_2.php');
				break;
			case 'fleet_3':
				include(APP_PATH.'controllers/fleet/fleet_3.php');
				break;
			case 'back':
				include(APP_PATH.'controllers/fleet/back.php');
				break;
			case 'quick':
				include(APP_PATH.'controllers/fleet/quick.php');
				break;
			case 'shortcut':
				include(APP_PATH.'controllers/fleet/shortcut.php');
				break;
			case 'verband':
				include(APP_PATH.'controllers/fleet/verband.php');
				break;
			default:
				include(APP_PATH.'controllers/fleet/fleet_0.php');
		}
	}
}

?>