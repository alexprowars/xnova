<?php

namespace App\Controllers;

use Xcms\db;
use Xnova\User;
use Xnova\pageHelper;

class CreditsController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
	}
	
	public function show ()
	{
		$userinf = db::query("SELECT email FROM game_users_info WHERE id = " . user::get()->getId() . ";", true);

		if (!isset($_SESSION['OKAPI']))
			$this->setTemplate('credits');
		else
			$this->setTemplate('credits_ok');

		$this->set('userid', user::get()->getId());
		$this->set('useremail', $userinf['email']);

		$this->setTitle('Покупка кредитов');
		$this->showTopPanel(false);
		$this->display();
	}
}

?>