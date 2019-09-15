<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Construction;
use Xnova\Controller;
use Xnova\Exceptions\PageException;

class ResearchController extends Controller
{
	protected $loadPlanet = true;

	public function index ()
	{
		if ($this->user->vacation > 0)
			throw new PageException('Нет доступа!');

		$this->setTitle('Исследования');

		$construction = new Construction($this->user, $this->planet);
		return $construction->pageResearch();
	}
}