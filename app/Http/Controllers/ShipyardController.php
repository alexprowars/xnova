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

class ShipyardController extends Controller
{
	protected $loadPlanet = true;

	public function index()
	{
		if ($this->user->vacation > 0) {
			throw new PageException("Нет доступа!");
		}

		$construction = new Construction($this->user, $this->planet);
		$parse = $construction->pageShipyard('fleet');

		$parse['mode'] = Route::current()->getName();
		$parse['queue'] = $construction->ElementBuildListBox();

		$this->setTitle('Верфь');

		return $parse;
	}
}
