<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;

/**
 * @RoutePrefix("/calculate")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class CalculateController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		$this->user->loadPlanet();
	}

	public function costAction ()
	{
		$this->view->pick('calculate/cost');
		$this->view->setVar('object', $this->planet->toArray());
		$this->view->setVar('user', $this->user->toArray());

		$this->tag->setTitle('Калькуляторы');
		$this->showTopPanel(false);
	}

	public function moonAction ()
	{
		$this->view->pick('calculate/moon');

		$this->tag->setTitle('Калькуляторы');
		$this->showTopPanel(false);
	}

	public function fleetAction ()
	{
		$this->view->pick('calculate/fleet');
		$this->view->setVar('object', $this->planet->toArray());
		$this->view->setVar('user', $this->user->toArray());

		$this->tag->setTitle('Калькуляторы');
		$this->showTopPanel(false);
	}
	
	public function indexAction ()
	{
		$this->view->pick('calculate/index');

		$this->tag->setTitle('Калькуляторы');
		$this->showTopPanel(false);
	}
}