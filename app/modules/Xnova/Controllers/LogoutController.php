<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;
use Xnova\Exceptions\MessageException;

/**
 * @RoutePrefix("/logout")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class LogoutController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$this->auth->remove();

		throw new MessageException('Вы вышли из игры', 'Сессия закрыта', "/", 3, false);
	}
}