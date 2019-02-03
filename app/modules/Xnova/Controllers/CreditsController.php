<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;
use Xnova\Models\UserInfo;
use Xnova\Request;

/**
 * @RoutePrefix("/credits")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class CreditsController extends Controller
{
	public function indexAction ()
	{
		$parse = [];

		$userId = $this->request->hasPost('userId') && (int) $this->request->getPost('userId') > 0 ?
			(int) $this->request->getPost('userId') : $this->user->getId();

		$parse['id'] = $userId;
		$parse['payment'] = false;

		if ($this->request->hasPost('summ'))
		{
			$summ = (int) $this->request->getPost('summ', 'int');

			do
			{
				$id = mt_rand(1000000000000, 9999999999999);
			}
			while (isset($this->db->fetchOne("SELECT id FROM game_users_payments WHERE transaction_id = ".$id)['id']));

			$info = UserInfo::findFirst($this->user->getId());

			$parse['payment'] = [
				'id' => $id,
				'hash' => md5($this->config->robokassa->login.":".$summ.":".$id.":".$this->config->robokassa->public.":Shp_UID=".$parse['id']),
				'summ' => $summ,
				'email' => $info->email,
				'merchant' => $this->config->robokassa->login
			];
		}

		Request::addData('page', $parse);

		$this->tag->setTitle('Покупка кредитов');
		$this->showTopPanel(false);
	}
}