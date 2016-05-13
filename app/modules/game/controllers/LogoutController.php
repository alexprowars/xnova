<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class LogoutController extends Application
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$this->auth->remove();

		$this->message('Выход', 'Сессия закрыта', "/", 3);
	}
}