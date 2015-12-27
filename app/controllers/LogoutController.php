<?php

namespace App\Controllers;

class LogoutController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function show ()
	{
		global $session;

		$session->ClearSession();

		$this->message('Выход', 'Сессия закрыта', "/", 3);
	}
}

?>