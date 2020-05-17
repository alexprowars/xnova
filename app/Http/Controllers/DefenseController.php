<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Xnova\Construction;
use Xnova\Controller;
use Xnova\Exceptions\PageException;

class DefenseController extends Controller
{
	protected $loadPlanet = true;

	public function index()
	{
		if ($this->user->vacation > 0) {
			throw new PageException('Нет доступа!');
		}

		if ($this->planet->planet_type == 5) {
			$this->user->setUserOption('only_available', true);
		}

		$construction = new Construction($this->user, $this->planet);
		$parse = $construction->pageShipyard('defense');

		$parse['mode'] = Route::current()->getName();
		$parse['queue'] = $construction->ElementBuildListBox();

		$this->setTitle('Оборона');

		return $parse;
	}
}
