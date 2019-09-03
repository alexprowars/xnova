<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Auth;
use Xnova\Controller;
use Xnova\Exceptions\PageException;

class LogoutController extends Controller
{
	public function index ()
	{
		if (Auth::check())
			$this->auth->remove();

		$this->showTopPanel(false);
		$this->showLeftPanel(false);

		throw new PageException('Вы вышли из игры', "/");
	}
}