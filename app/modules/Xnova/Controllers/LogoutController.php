<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Request;

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

		Request::addData('user', null);

		$this->showTopPanel(false);
		$this->showLeftPanel(false);

		throw new PageException('Вы вышли из игры', "/");
	}
}