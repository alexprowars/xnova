<?php

namespace Admin\Controllers;

use Admin\Controller;

class ServerController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->user->authlevel < 3)
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}

	public function indexAction ()
	{
		$this->tag->setTitle('Серверное окружение');
	}
}