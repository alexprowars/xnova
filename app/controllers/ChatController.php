<?php

namespace App\Controllers;

class ChatController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function show ()
	{
		$regTime = $this->db->fetchColumn("SELECT register_time FROM game_users_info WHERE id = ".$this->user->getId()."");

		//if ($regTime > (time() - 43200))
		//	$this->message('Доступ к чату будет открыт спустя 12 часов после регистрации.');

		$this->view->pick('chat');

		$this->tag->setTitle('Межгалактический чат');
		$this->showTopPanel(false);
		$this->showLeftPanel(!isset($_GET['frame']));
	}
}

?>