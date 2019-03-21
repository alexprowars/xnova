<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;
use Xnova\Exceptions\RedirectException;

/**
 * @RoutePrefix("/logout")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Public
 */
class LogoutController extends Controller
{
	public function indexAction ()
	{
		if ($this->auth->isAuthorized())
			$this->auth->remove();

		throw new RedirectException('Вы вышли из игры', "/");
	}
}