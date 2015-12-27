<?php

namespace App\Controllers;

use Xcms\db;
use Xnova\User;
use Xnova\pageHelper;

class ChatController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
	}
	
	public function show ()
	{
		$regTime = db::first(db::query("SELECT register_time FROM game_users_info WHERE id = ".user::get()->getId()."", true));

		//if ($regTime > (time() - 43200))
		//	$this->message('Доступ к чату будет открыт спустя 12 часов после регистрации.');

		$this->setTemplate('chat');

		$this->setTitle('Межгалактический чат');
		$this->showTopPanel(false);
		$this->showLeftPanel(!isset($_GET['frame']));
		$this->display();
	}
}

?>