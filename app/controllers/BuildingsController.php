<?php

namespace App\Controllers;

use Xcms\strings;
use Xnova\User;
use Xnova\app;
use Xnova\building;
use Xnova\pageHelper;

class BuildingsController extends ApplicationController
{
	/**
	 * @var building $building
	 */
	private $building;

	function __construct ()
	{
		parent::__construct();

		strings::includeLang('buildings');

		app::loadPlanet();

		if (user::get()->data['urlaubs_modus_time'] > 0)
		{
			$this->message("Нет доступа!");
		}

		$this->building = new building();
	}

	public function fleet()
	{
		global $resource;

		if (app::$planetrow->data[$resource[21]] == 0)
			$this->message(_getText('need_hangar'), _getText('tech', 21));

		$parse = $this->building->pageShipyard('fleet');
		$parse['mode'] = $this->mode;

		$this->setTemplate('buildings/buildings_shipyard');
		$this->set('parse', $parse);

		$data = $this->building->ElementBuildListBox();

		if ($data['count'] > 0)
		{
			$this->setTemplate('buildings/buildings_script');
			$this->set('parse', $data);
		}

		$this->setTitle('Верфь');
		$this->display();
	}

	public function research()
	{
		global $resource;

		if (app::$planetrow->data[$resource[31]] == 0)
			$this->message(_getText('no_laboratory'), _getText('Research'));

		$parse = $this->building->pageResearch(($this->mode == 'research_fleet' ? 'fleet' : ''));

		$this->setTemplate('buildings/buildings_research');
		$this->set('parse', $parse);

		$this->setTitle('Исследования');
		$this->display();
	}

	public function research_fleet()
	{
		$this->research();
	}

	public function defense()
	{
		global $resource;

		if (app::$planetrow->data[$resource[21]] == 0 && app::$planetrow->data['planet_type'] != 5)
			$this->message(_getText('need_hangar'), _getText('tech', 21));

		if (app::$planetrow->data['planet_type'] == 5)
			user::get()->setUserOption('only_available', 1);

		$parse = $this->building->pageShipyard('defense');
		$parse['mode'] = $this->mode;

		$this->setTemplate('buildings/buildings_shipyard');
		$this->set('parse', $parse);

		$data = $this->building->ElementBuildListBox();

		if ($data['count'] > 0)
		{
			$this->setTemplate('buildings/buildings_script');
			$this->set('parse', $data);
		}

		$this->setTitle('Оборона');
		$this->display();
	}
	
	public function show ()
	{
		$parse = $this->building->pageBuilding();

		if (app::$planetrow->data['planet_type'] == 3)
			$parse['planettype'] = 'moon';
		elseif (app::$planetrow->data['planet_type'] == 5)
			$parse['planettype'] = 'base';
		else
			$parse['planettype'] = 'planet';

		$this->setTemplate('buildings/buildings_area');
		$this->set('parse', $parse);

		$this->setTitle('Постройки');
		$this->display();
	}
}

?>