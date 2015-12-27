<?php

namespace App\Controllers;

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
		$this->view->setVar('planet', $this->planet->data);
		$this->view->setVar('user', $this->user->data);

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
		$this->view->setVar('planet', $this->planet->data);
		$this->view->setVar('user', $this->user->data);

		$this->tag->setTitle('Калькуляторы');
		$this->showTopPanel(false);
	}
	
	public function show ()
	{
		$this->view->pick('calculate/index');

		$this->tag->setTitle('Калькуляторы');
		$this->showTopPanel(false);
	}
}

?>