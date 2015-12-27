<?php

namespace App\Controllers;

use Xnova\User;
use Xnova\app;
use Xnova\pageHelper;

class CalculateController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();

		app::loadPlanet();
	}

	public function cost()
	{
		$this->setTemplate('calculate/cost');
		$this->set('planet', app::$planetrow->data);
		$this->set('user', user::get()->data);

		$this->setTitle('Калькуляторы');
		$this->showTopPanel(false);
		$this->display();
	}

	public function moon()
	{
		$this->setTemplate('calculate/moon');

		$this->setTitle('Калькуляторы');
		$this->showTopPanel(false);
		$this->display();
	}

	public function fleet()
	{
		$this->setTemplate('calculate/fleet');
		$this->set('planet', app::$planetrow->data);
		$this->set('user', user::get()->data);

		$this->setTitle('Калькуляторы');
		$this->showTopPanel(false);
		$this->display();
	}
	
	public function show ()
	{
		$this->setTemplate('calculate/index');

		$this->setTitle('Калькуляторы');
		$this->showTopPanel(false);
		$this->display();
	}
}

?>