<?php

namespace App\Controllers;

use Xnova\pageHelper;

class LogoutController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
	}
	
	public function show ()
	{
		global $session;

		$session->ClearSession();

		$this->message('Выход', 'Сессия закрыта', "/", 3);
	}
}

?>