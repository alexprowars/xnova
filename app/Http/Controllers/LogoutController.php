<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Controller;
use App\Exceptions\PageException;

class LogoutController extends Controller
{
	public function index()
	{
		Auth::logout();

		$this->showTopPanel(false);
		$this->showLeftPanel(false);

		throw new PageException('Вы вышли из игры', "/");
	}
}
