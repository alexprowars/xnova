<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Route;
use Xnova\Construction;
use Xnova\Controller;
use Xnova\Exceptions\PageException;

class ShipyardController extends Controller
{
	private $loadPlanet = true;

	public function index ()
	{
		if ($this->user->vacation > 0)
			throw new PageException("Нет доступа!");

		$construction = new Construction($this->user, $this->planet);
		$parse = $construction->pageShipyard('fleet');

		$parse['mode'] = Route::current()->getName();
		$parse['queue'] = $construction->ElementBuildListBox();

		$this->setTitle('Верфь');

		return $parse;
	}
}