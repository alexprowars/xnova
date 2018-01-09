<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Construction;
use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;

/**
 * @RoutePrefix("/buildings")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class BuildingsController extends Controller
{
	/**
	 * @var \Xnova\Construction $building
	 */
	private $building;

	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('buildings', 'xnova');

		$this->user->loadPlanet();

		if ($this->user->vacation > 0)
			throw new ErrorException("Нет доступа!");

		$this->building = new Construction($this->user, $this->planet);
		$this->building->mode = $this->dispatcher->getActionName();
	}

	public function fleetAction ()
	{
		if ($this->planet->{$this->registry->resource[21]} == 0)
			throw new ErrorException(_getText('need_hangar'), _getText('tech', 21));

		$parse = $this->building->pageShipyard('fleet');
		$parse['mode'] = $this->dispatcher->getActionName();

		$this->view->partial('buildings/shipyard');
		$this->view->setVar('parse', $parse);

		$data = $this->building->ElementBuildListBox();

		if ($data['count'] > 0)
		{
			$this->view->setVar('build', $data);
			$this->view->partial('buildings/script');
		}

		$this->tag->setTitle('Верфь');
	}

	public function researchAction ()
	{
		if ($this->planet->getBuildlevel('laboratory') == 0)
			throw new ErrorException(_getText('no_laboratory'), _getText('Research'));

		$parse = $this->building->pageResearch(($this->dispatcher->getActionName() == 'research_fleet' ? 'fleet' : ''));

		$this->view->pick('buildings/research');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Исследования');
	}

	public function research_fleetAction ()
	{
		$this->researchAction();
	}

	public function defenseAction ()
	{
		if ($this->planet->{$this->registry->resource[21]} == 0 && $this->planet->planet_type != 5)
			throw new ErrorException(_getText('need_hangar'), _getText('tech', 21));

		if ($this->planet->planet_type == 5)
			$this->user->setUserOption('only_available', 1);

		$parse = $this->building->pageShipyard('defense');
		$parse['mode'] = $this->dispatcher->getActionName();

		$this->view->partial('buildings/shipyard');
		$this->view->setVar('parse', $parse);

		$data = $this->building->ElementBuildListBox();

		if ($data['count'] > 0)
		{
			$this->view->setVar('build', $data);
			$this->view->partial('buildings/script');
		}

		$this->tag->setTitle('Оборона');
	}
	
	public function indexAction ()
	{
		$parse = $this->building->pageBuilding();

		if ($this->planet->planet_type == 3)
			$parse['planettype'] = 'moon';
		elseif ($this->planet->planet_type == 5)
			$parse['planettype'] = 'base';
		else
			$parse['planettype'] = 'planet';

		$this->view->pick('buildings/index');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Постройки');
	}
}