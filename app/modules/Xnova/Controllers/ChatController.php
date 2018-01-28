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
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		//$regTime = $this->db->fetchColumn("SELECT create_time FROM game_users_info WHERE id = ".$this->user->getId()."");

		//if ($regTime > (time() - 43200))
		//	$this->message('Доступ к чату будет открыт спустя 12 часов после регистрации.');

		$parse = [
			'key' => md5($this->user->getId().'|'.$this->user->username.'SuperPuperChat'),
			'server' => 'https://uni5.xnova.su:6677',
			'color' => (int) $this->user->color
		];

		Request::addData('page', $parse);

		$this->tag->setTitle('Межгалактический чат');
		$this->showTopPanel(false);
	}
}