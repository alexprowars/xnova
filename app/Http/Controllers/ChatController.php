<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;

class ChatController extends Controller
{
	public function index ()
	{
		//$regTime = $this->db->fetchColumn("SELECT create_time FROM users_info WHERE id = ".$this->user->getId()."");

		//if ($regTime > (time() - 43200))
		//	$this->message('Доступ к чату будет открыт спустя 12 часов после регистрации.');

		$this->setTitle('Межгалактический чат');
		$this->showTopPanel(false);

		return ['loaded' => true];
	}
}