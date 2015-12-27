<?php

namespace App\Controllers;

class LogoutController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$this->auth->remove();

		$this->message('Выход', 'Сессия закрыта', "/", 3);
	}
}

?>