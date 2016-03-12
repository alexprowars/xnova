<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class CalculateController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		$this->user->loadPlanet();
	}

	public function cost()
	{
		$this->view->pick('calculate/cost');
		$this->view->setVar('planet', $this->planet->toArray());
		$this->view->setVar('user', $this->user->toArray());

		$this->tag->setTitle('Калькуляторы');
		$this->showTopPanel(false);
	}

	public function moon()
	{
		$this->view->pick('calculate/moon');

		$this->tag->setTitle('Калькуляторы');
		$this->showTopPanel(false);
	}

	public function fleet()
	{
		$this->view->pick('calculate/fleet');
		$this->view->setVar('planet', $this->planet->toArray());
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