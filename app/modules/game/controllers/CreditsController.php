<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class CreditsController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$userinf = $this->db->query("SELECT email FROM game_users_info WHERE id = " . $this->user->getId())->fetch();

		if (isset($_SESSION['OKAPI']))
			$this->view->pick('credits_ok');

		$this->view->setVar('userid', $this->user->getId());
		$this->view->setVar('useremail', $userinf['email']);

		$this->tag->setTitle('Покупка кредитов');
		$this->showTopPanel(false);
	}
}