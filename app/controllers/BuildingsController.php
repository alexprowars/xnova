<?php

namespace App\Controllers;

use App\Lang;

class BuildingsController extends ApplicationController
{
	/**
	 * @var building $building
	 */
	private $building;

	public function initialize ()
	{
		parent::initialize();

		Lang::includeLang('buildings');

		$this->user->loadPlanet();

		if ($this->user->banned > 0)
		{
			$this->message("Нет доступа!");
		}

		$this->building = new building();
	}

	public function fleet()
	{
		global $resource;

		if ($this->planet->data[$resource[21]] == 0)
			$this->message(_getText('need_hangar'), _getText('tech', 21));

		$parse = $this->building->pageShipyard('fleet');
		$parse['mode'] = $this->mode;

		$this->view->pick('buildings/buildings_shipyard');
		$this->view->setVar('parse', $parse);

		$data = $this->building->ElementBuildListBox();

		if ($data['count'] > 0)
		{
			$this->view->pick('buildings/buildings_script');
			$this->view->setVar('parse', $data);
		}

		$this->tag->setTitle('Верфь');
	}

	public function research()
	{
		global $resource;

		if ($this->planet->data[$resource[31]] == 0)
			$this->message(_getText('no_laboratory'), _getText('Research'));

		$parse = $this->building->pageResearch(($this->mode == 'research_fleet' ? 'fleet' : ''));

		$this->view->pick('buildings/buildings_research');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Исследования');
	}

	public function research_fleet()
	{
		$this->research();
	}

	public function defense()
	{
		global $resource;

		if ($this->planet->data[$resource[21]] == 0 && $this->planet->planet_type != 5)
			$this->message(_getText('need_hangar'), _getText('tech', 21));

		if ($this->planet->planet_type == 5)
			$this->user->setUserOption('only_available', 1);

		$parse = $this->building->pageShipyard('defense');
		$parse['mode'] = $this->mode;

		$this->view->pick('buildings/buildings_shipyard');
		$this->view->setVar('parse', $parse);

		$data = $this->building->ElementBuildListBox();

		if ($data['count'] > 0)
		{
			$this->view->pick('buildings/buildings_script');
			$this->view->setVar('parse', $data);
		}

		$this->tag->setTitle('Оборона');
	}
	
	public function show ()
	{
		$parse = $this->building->pageBuilding();

		if ($this->planet->planet_type == 3)
			$parse['planettype'] = 'moon';
		elseif ($this->planet->planet_type == 5)
			$parse['planettype'] = 'base';
		else
			$parse['planettype'] = 'planet';

		$this->view->pick('buildings/buildings_area');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Постройки');
	}
}

?>