<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class ChatController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$regTime = $this->db->fetchColumn("SELECT create_time FROM game_users_info WHERE id = ".$this->user->getId()."");

		//if ($regTime > (time() - 43200))
		//	$this->message('Доступ к чату будет открыт спустя 12 часов после регистрации.');

		$this->tag->setTitle('Межгалактический чат');
		$this->showTopPanel(false);
		$this->showLeftPanel(!isset($_GET['frame']));
	}
}