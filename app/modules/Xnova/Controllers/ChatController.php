<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;
use Xnova\Request;

/**
 * @RoutePrefix("/chat")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class ChatController extends Controller
{
	public function indexAction ()
	{
		//$regTime = $this->db->fetchColumn("SELECT create_time FROM game_users_info WHERE id = ".$this->user->getId()."");

		//if ($regTime > (time() - 43200))
		//	$this->message('Доступ к чату будет открыт спустя 12 часов после регистрации.');

		Request::addData('page', ['loaded' => true]);

		$this->tag->setTitle('Межгалактический чат');
	}
}