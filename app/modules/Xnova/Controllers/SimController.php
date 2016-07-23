<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;

class SimController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		$this->user->loadPlanet();
	}
	
	public function indexAction ($data = '')
	{
		$data = explode(";", $data);

		define('MAX_SLOTS', $this->config->game->get('maxSlotsInSim', 5));
		
		$parse = [];
		$parse['slot_0'] = [];
		$parse['slot_'.MAX_SLOTS] = [];

		$parse['tech'] = [109, 110, 111, 120, 121, 122];
		
		foreach ($data AS $row)
		{
			if ($row != '')
			{
				$Element = explode(",", $row);
				$Count = explode("!", $Element[1]);

				if (isset($Count[1]))
					$parse['slot_'.MAX_SLOTS][$Element[0]] = ['c' => $Count[0], 'l' => $Count[1]];
			}
		}
		
		$res = array_merge($this->storage->reslist['fleet'], $this->storage->reslist['defense'], $this->storage->reslist['tech']);
		
		foreach ($res AS $id)
		{
			if (isset($this->planet->{$this->storage->resource[$id]}) && $this->planet->{$this->storage->resource[$id]} > 0)
				$parse['slot_0'][$id] = ['c' => $this->planet->{$this->storage->resource[$id]}, 'l' => ((isset($this->user->{'fleet_' . $id})) ? $this->user->{'fleet_' . $id} : 0)];
		
			if (isset($this->user->{$this->storage->resource[$id]}) && $this->user->{$this->storage->resource[$id]} > 0)
				$parse['slot_0'][$id] = ['c' => $this->user->{$this->storage->resource[$id]}];
		}

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Симулятор');
		$this->showTopPanel(false);
	}
}