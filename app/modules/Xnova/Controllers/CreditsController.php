<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;

/**
 * @RoutePrefix("/credits")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class CreditsController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$userinf = $this->db->query("SELECT email FROM game_users_info WHERE id = " . $this->user->getId())->fetch();

		if ($this->request->hasPost('OutSum'))
		{
			do
			{
				$id = mt_rand(1000000000000, 9999999999999);
			}
			while (isset($this->db->fetchOne("SELECT id FROM game_users_payments WHERE transaction_id = ".$id)['id']));

			$this->view->setVar('invid', $id);
		}

		$userId = $this->request->hasPost('userId') && (int) $this->request->getPost('userId') > 0 ?
			(int) $this->request->getPost('userId') : $this->user->getId();

		$this->view->setVar('userId', $userId);
		$this->view->setVar('userEmail', $userinf['email']);

		$this->tag->setTitle('Покупка кредитов');
		$this->showTopPanel(false);
	}
}