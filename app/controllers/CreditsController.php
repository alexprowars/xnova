<?php

namespace App\Controllers;

class CreditsController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$userinf = $this->db->query("SELECT email FROM game_users_info WHERE id = " . $this->user->getId() . ";")->fetch();

		if (!isset($_SESSION['OKAPI']))
			$this->view->pick('credits');
		else
			$this->view->pick('credits_ok');

		$this->view->setVar('userid', $this->user->getId());
		$this->view->setVar('useremail', $userinf['email']);

		$this->tag->setTitle('Покупка кредитов');
		$this->showTopPanel(false);
	}
}

?>