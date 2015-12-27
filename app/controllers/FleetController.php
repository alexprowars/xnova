<?php

namespace App\Controllers;

class FleetController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
		
		$this->user->loadPlanet();
	}
	
	public function show ()
	{
		global $resource, $reslist, $CombatCaps;
	
		// Устанавливаем обновлённые двигателя кораблей
		SetShipsEngine($this->user->data);

		$module = (isset($_GET['page'])) ? $_GET['page'] : '';

		switch ($module)
		{
			case 'fleet_1':
				include(ROOT_DIR.APP_PATH.'controllers/fleet/fleet_1.php');
				break;
			case 'fleet_2':
				include(ROOT_DIR.APP_PATH.'controllers/fleet/fleet_2.php');
				break;
			case 'fleet_3':
				include(ROOT_DIR.APP_PATH.'controllers/fleet/fleet_3.php');
				break;
			case 'back':
				include(ROOT_DIR.APP_PATH.'controllers/fleet/back.php');
				break;
			case 'quick':
				include(ROOT_DIR.APP_PATH.'controllers/fleet/quick.php');
				break;
			case 'shortcut':
				include(ROOT_DIR.APP_PATH.'controllers/fleet/shortcut.php');
				break;
			case 'verband':
				include(ROOT_DIR.APP_PATH.'controllers/fleet/verband.php');
				break;
			default:
				include(ROOT_DIR.APP_PATH.'controllers/fleet/fleet_0.php');
		}
	}
}

?>