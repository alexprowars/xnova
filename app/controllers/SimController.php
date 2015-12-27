<?php

namespace App\Controllers;

class SimController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		global $reslist, $resource;
		
		$r = (isset($_GET['r'])) ? $_GET['r'] : '';
		$r = explode(";", $r);

		define('MAX_SLOTS', $this->config->game->get('maxSlotsInSim', 5));
		
		$parse = array();
		$parse['slot_0'] = array();
		$parse['slot_'.MAX_SLOTS] = array();

		$parse['tech'] = array(109, 110, 111, 120, 121, 122);
		
		foreach ($r AS $row)
		{
			if ($row != '')
			{
				$Element = explode(",", $row);
				$Count = explode("!", $Element[1]);

				if (isset($Count[1]))
					$parse['slot_'.MAX_SLOTS][$Element[0]] = array('c' => $Count[0], 'l' => $Count[1]);
			}
		}
		
		$res = array_merge($reslist['fleet'], $reslist['defense'], $reslist['tech']);
		
		foreach ($res AS $id)
		{
			if (isset($this->planet->data[$resource[$id]]) && $this->planet->data[$resource[$id]] > 0)
				$parse['slot_0'][$id] = array('c' => $this->planet->data[$resource[$id]], 'l' => ((isset($this->user->data['fleet_' . $id])) ? $this->user->data['fleet_' . $id] : 0));
		
			if (isset($this->user->data[$resource[$id]]) && $this->user->data[$resource[$id]] > 0)
				$parse['slot_0'][$id] = array('c' => $this->user->data[$resource[$id]]);
		}
		
		$this->view->pick('sim');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Симулятор');
		$this->showTopPanel(false);
	}
}

?>